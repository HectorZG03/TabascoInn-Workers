<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PermisosLaboralesController extends Controller
{
    /**
     * ✅ PROCESAR ASIGNACIÓN DE PERMISO O SUSPENSIÓN - CORREGIDO CON ESTATUS_PERMISO
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDAR QUE EL TRABAJADOR PUEDA RECIBIR PERMISOS
        if (!$trabajador->puedeAsignarPermiso()) {
            return back()->withErrors([
                'error' => 'Solo se pueden asignar permisos a trabajadores activos o sin permisos activos. Estado actual: ' . $trabajador->estatus_texto
            ]);
        }

        // ✅ VALIDACIONES ACTUALIZADAS
        $validated = $request->validate([
            'tipo_permiso' => 'required|in:permiso,suspendido',
            'motivo' => 'required|string|max:100',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'tipo_permiso.required' => 'El tipo de acción es obligatorio',
            'tipo_permiso.in' => 'El tipo debe ser "Permiso" o "Suspendido"',
            'motivo.required' => 'El motivo es obligatorio',
            'motivo.max' => 'El motivo no puede exceder 100 caracteres',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres',
        ]);

        // ✅ VALIDAR MOTIVO SEGÚN TIPO DE PERMISO
        $motivosValidos = PermisosLaborales::getMotivosPorTipo($validated['tipo_permiso']);
        
        if (!empty($motivosValidos) && !array_key_exists($validated['motivo'], $motivosValidos)) {
            Log::info('Motivo personalizado usado', [
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo_personalizado' => $validated['motivo'],
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);
        }

        // ✅ VALIDAR CONFLICTOS SOLO CON PERMISOS ACTIVOS
        $permisoActivoExistente = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->where('estatus_permiso', 'activo') // ✅ SOLO PERMISOS ACTIVOS
            ->where(function($query) use ($validated) {
                $query->whereBetween('fecha_inicio', [$validated['fecha_inicio'], $validated['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$validated['fecha_inicio'], $validated['fecha_fin']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('fecha_inicio', '<=', $validated['fecha_inicio'])
                            ->where('fecha_fin', '>=', $validated['fecha_fin']);
                      });
            })->first();

        if ($permisoActivoExistente) {
            return back()->withErrors([
                'fecha_inicio' => 'Ya existe un permiso/suspensión ACTIVO en el rango de fechas seleccionado'
            ])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // ✅ CREAR REGISTRO CON ESTATUS_PERMISO
            $permiso = PermisosLaborales::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo' => $validated['motivo'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'observaciones' => $validated['observaciones'],
                'estatus_permiso' => 'activo', // ✅ NUEVO CAMPO
            ]);

            // ✅ ACTUALIZAR ESTADO DEL TRABAJADOR
            $trabajador->update([
                'estatus' => $validated['tipo_permiso'],
            ]);

            DB::commit();

            Log::info('Permiso/suspensión asignado con historial', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo' => $validated['motivo'],
                'estatus_permiso' => 'activo',
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'dias_permiso' => $permiso->dias_de_permiso,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            $tipoTexto = $validated['tipo_permiso'] === 'permiso' ? 'Permiso' : 'Suspensión';
            $motivoTexto = $permiso->motivo_texto;

            return redirect()->route('trabajadores.index')
                           ->with('success', 
                               "{$tipoTexto} asignado exitosamente a {$trabajador->nombre_completo}. Motivo: {$motivoTexto}"
                           );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al asignar permiso/suspensión', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo' => $validated['motivo'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al asignar: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ LISTAR PERMISOS - CORREGIDO CON TODAS LAS VARIABLES
     */
    public function index(Request $request)
    {
        $query = PermisosLaborales::with([
            'trabajador.fichaTecnica.categoria.area'
        ]);

        // ✅ FILTROS ACTUALIZADOS
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trabajador', function($q) use ($search) {
                $q->where('nombre_trabajador', 'like', "%{$search}%")
                  ->orWhere('ape_pat', 'like', "%{$search}%")
                  ->orWhere('ape_mat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_permiso')) {
            $query->where('tipo_permiso', $request->tipo_permiso);
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', $request->motivo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        // ✅ FILTRO POR ESTADO MEJORADO
        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('estatus_permiso', 'activo');
            } elseif ($request->estado === 'finalizados') {
                $query->where('estatus_permiso', 'finalizado');
            } elseif ($request->estado === 'cancelados') {
                $query->where('estatus_permiso', 'cancelado');
            } elseif ($request->estado === 'vencidos') {
                $query->where('fecha_fin', '<', now())
                      ->where('estatus_permiso', 'activo');
            }
        }

        $permisos = $query->orderBy('created_at', 'desc')->paginate(20);

        // ✅ ESTADÍSTICAS CON ESTATUS_PERMISO
        $stats = [
            'total' => PermisosLaborales::count(),
            'activos' => PermisosLaborales::where('estatus_permiso', 'activo')->count(),
            'permisos_activos' => PermisosLaborales::where('tipo_permiso', 'permiso')
                                                  ->where('estatus_permiso', 'activo')->count(),
            'suspensiones_activas' => PermisosLaborales::where('tipo_permiso', 'suspendido')
                                                       ->where('estatus_permiso', 'activo')->count(),
            'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                         ->whereYear('fecha_inicio', now()->year)
                                         ->count(),
            'finalizados' => PermisosLaborales::where('estatus_permiso', 'finalizado')->count(),
            'cancelados' => PermisosLaborales::where('estatus_permiso', 'cancelado')->count(),
            'vencidos' => PermisosLaborales::where('fecha_fin', '<', now())
                                         ->where('estatus_permiso', 'activo')
                                         ->count(),
        ];

        // ✅ VARIABLES NECESARIAS PARA LA VISTA
        $tiposPermisos = PermisosLaborales::TIPOS_PERMISO;
        $estatusPermisos = PermisosLaborales::ESTATUS_PERMISO;
        $motivosPermiso = PermisosLaborales::MOTIVOS_PERMISO;
        $motivosSuspension = PermisosLaborales::MOTIVOS_SUSPENSION;
        
        // ✅ COLORES E ICONOS PARA VISTA
        $coloresPermiso = [
            'permiso' => 'info',
            'suspendido' => 'danger',
        ];
        
        $iconosPermiso = [
            'permiso' => 'bi-calendar-event',
            'suspendido' => 'bi-exclamation-triangle',
        ];

        $coloresEstatus = [
            'activo' => 'success',
            'finalizado' => 'primary',
            'cancelado' => 'secondary',
        ];

        return view('trabajadores.estatus.permisos_lista', compact(
            'permisos', 
            'stats', 
            'tiposPermisos', 
            'estatusPermisos',
            'motivosPermiso',
            'motivosSuspension',
            'coloresPermiso',
            'iconosPermiso',
            'coloresEstatus'
        ));
    }

    /**
     * ✅ FINALIZAR PERMISO - ACTUALIZADO CON ESTATUS
     */
    public function finalizar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        // ✅ VERIFICAR QUE EL PERMISO ESTÉ ACTIVO
        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden finalizar permisos que estén activos'
            ]);
        }

        // Verificar que el trabajador esté en el estado correcto
        if (!in_array($trabajador->estatus, ['permiso', 'suspendido'])) {
            return back()->withErrors([
                'error' => 'El trabajador debe estar en estado de permiso o suspendido'
            ]);
        }

        DB::beginTransaction();
        
        try {
            // ✅ ACTUALIZAR PERMISO COMO FINALIZADO
            $permiso->update([
                'fecha_fin' => now()->format('Y-m-d'),
                'estatus_permiso' => 'finalizado', // ✅ CAMBIAR ESTATUS
                'observaciones' => $permiso->observaciones . 
                    "\n[FINALIZADO EL " . now()->format('d/m/Y') . " por " . (Auth::user()->email ?? 'Sistema') . "]"
            ]);

            // ✅ REACTIVAR TRABAJADOR
            $trabajador->update([
                'estatus' => 'activo',
            ]);

            DB::commit();

            Log::info('Permiso finalizado con historial', [
                'trabajador_id' => $trabajador->id_trabajador,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'estatus_anterior' => 'activo',
                'estatus_nuevo' => 'finalizado',
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            $tipoTexto = $permiso->tipo_permiso === 'permiso' ? 'Permiso' : 'Suspensión';

            return redirect()->route('permisos.index')
                           ->with('success', 
                               "{$tipoTexto} finalizado. {$trabajador->nombre_completo} ha sido reactivado"
                           );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al finalizar permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al finalizar: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ CANCELAR PERMISO - ACTUALIZADO CON ESTATUS
     */
    public function cancelar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        // ✅ VERIFICAR QUE EL PERMISO ESTÉ ACTIVO
        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden cancelar permisos que estén activos'
            ]);
        }

        DB::beginTransaction();
        
        try {
            // ✅ MARCAR COMO CANCELADO EN LUGAR DE ELIMINAR
            $permiso->update([
                'estatus_permiso' => 'cancelado',
                'observaciones' => $permiso->observaciones . 
                    "\n[CANCELADO EL " . now()->format('d/m/Y') . " por " . (Auth::user()->email ?? 'Sistema') . "]"
            ]);

            // ✅ REACTIVAR TRABAJADOR
            $trabajador->update([
                'estatus' => 'activo',
            ]);

            DB::commit();

            Log::info('Permiso cancelado con historial', [
                'trabajador_id' => $trabajador->id_trabajador,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'estatus_anterior' => 'activo',
                'estatus_nuevo' => 'cancelado',
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            $tipoTexto = $permiso->tipo_permiso === 'permiso' ? 'Permiso' : 'Suspensión';

            return redirect()->route('permisos.index')
                           ->with('success', 
                               "{$tipoTexto} cancelado. {$trabajador->nombre_completo} ha sido reactivado"
                           );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al cancelar permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al cancelar: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ VERIFICAR PERMISOS VENCIDOS - ACTUALIZADO
     */
    public function verificarVencidos()
    {
        // ✅ BUSCAR PERMISOS ACTIVOS VENCIDOS
        $permisosVencidos = PermisosLaborales::with('trabajador')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->where('estatus_permiso', 'activo') // Solo activos
            ->get();

        $procesados = 0;

        foreach ($permisosVencidos as $permiso) {
            try {
                DB::beginTransaction();

                // ✅ MARCAR PERMISO COMO FINALIZADO
                $permiso->update([
                    'estatus_permiso' => 'finalizado',
                    'observaciones' => $permiso->observaciones . 
                        "\n[AUTO-FINALIZADO POR VENCIMIENTO EL " . now()->format('d/m/Y') . "]"
                ]);

                // ✅ SOLO REACTIVAR PERMISOS, NO SUSPENSIONES
                if ($permiso->tipo_permiso === 'permiso') {
                    $permiso->trabajador->update(['estatus' => 'activo']);
                }

                DB::commit();
                $procesados++;
                
                Log::info('Permiso auto-finalizado por vencimiento', [
                    'trabajador_id' => $permiso->trabajador->id_trabajador,
                    'permiso_id' => $permiso->id_permiso,
                    'tipo_permiso' => $permiso->tipo_permiso,
                    'fecha_vencimiento' => $permiso->fecha_fin,
                    'reactivado' => $permiso->tipo_permiso === 'permiso'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error al auto-finalizar permiso vencido', [
                    'permiso_id' => $permiso->id_permiso,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'permisos_vencidos_encontrados' => $permisosVencidos->count(),
            'permisos_procesados' => $procesados,
            'suspensiones_requieren_revision' => $permisosVencidos->where('tipo_permiso', 'suspendido')->count()
        ]);
    }

    /**
     * ✅ ESTADÍSTICAS ACTUALIZADAS
     */
    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;
        
        $estadisticas = [
            'totales' => [
                'total' => PermisosLaborales::count(),
                'activos' => PermisosLaborales::where('estatus_permiso', 'activo')->count(),
                'finalizados' => PermisosLaborales::where('estatus_permiso', 'finalizado')->count(),
                'cancelados' => PermisosLaborales::where('estatus_permiso', 'cancelado')->count(),
                'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                             ->whereYear('fecha_inicio', now()->year)
                                             ->count(),
            ],
            'por_tipo_y_estatus' => PermisosLaborales::selectRaw('tipo_permiso, estatus_permiso, COUNT(*) as total')
                                          ->whereYear('fecha_inicio', $añoActual)
                                          ->groupBy(['tipo_permiso', 'estatus_permiso'])
                                          ->orderBy('total', 'desc')
                                          ->get(),
            'por_motivo' => PermisosLaborales::selectRaw('motivo, COUNT(*) as total')
                                            ->whereYear('fecha_inicio', $añoActual)
                                            ->groupBy('motivo')
                                            ->orderBy('total', 'desc')
                                            ->limit(10)
                                            ->get(),
            'por_mes' => PermisosLaborales::selectRaw('MONTH(fecha_inicio) as mes, COUNT(*) as total')
                                         ->whereYear('fecha_inicio', $añoActual)
                                         ->groupBy('mes')
                                         ->orderBy('mes')
                                         ->get(),
        ];

        return response()->json($estadisticas);
    }

    /**
     * ✅ API PARA OBTENER MOTIVOS SEGÚN TIPO
     */
    public function getMotivosPorTipo(Request $request)
    {
        $tipo = $request->get('tipo');
        
        if (!in_array($tipo, ['permiso', 'suspendido'])) {
            return response()->json(['error' => 'Tipo de permiso no válido'], 400);
        }
        
        $motivos = PermisosLaborales::getMotivosPorTipo($tipo);
        
        return response()->json($motivos);
    }

    /**
     * Mostrar detalles del permiso
     */
    public function show(PermisosLaborales $permiso)
    {
        $permiso->load('trabajador.fichaTecnica.categoria.area');
        
        return view('permisos.show', compact('permiso'));
    }
}