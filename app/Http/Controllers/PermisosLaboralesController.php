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
     * ✅ PROCESAR ASIGNACIÓN DE PERMISO - TIPO Y MOTIVO SEPARADOS
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDAR QUE EL TRABAJADOR PUEDA RECIBIR PERMISOS
        if (!$trabajador->puedeAsignarPermiso()) {
            return back()->withErrors([
                'error' => 'Solo se pueden asignar permisos a trabajadores activos o sin permisos activos. Estado actual: ' . $trabajador->estatus_texto
            ]);
        }

        // ✅ VALIDACIONES - TIPO SELECT + MOTIVO TEXTO LIBRE
        $tiposValidos = array_keys(PermisosLaborales::getTiposDisponibles());
        
        $validated = $request->validate([
            'tipo_permiso' => 'required|string|in:' . implode(',', $tiposValidos),
            'motivo' => 'required|string|min:3|max:100',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'tipo_permiso.required' => 'El tipo de permiso es obligatorio',
            'tipo_permiso.in' => 'El tipo de permiso seleccionado no es válido',
            'motivo.required' => 'El motivo es obligatorio',
            'motivo.min' => 'El motivo debe tener al menos 3 caracteres',
            'motivo.max' => 'El motivo no puede exceder 100 caracteres',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
        ]);

        // ✅ VALIDAR CONFLICTOS CON PERMISOS ACTIVOS
        $permisoActivoExistente = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->where('estatus_permiso', 'activo')
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
                'fecha_inicio' => 'Ya existe un permiso ACTIVO en el rango de fechas seleccionado'
            ])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // ✅ CREAR REGISTRO CON TIPO Y MOTIVO SEPARADOS
            $permiso = PermisosLaborales::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo' => $validated['motivo'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'observaciones' => $validated['observaciones'],
                'estatus_permiso' => 'activo',
            ]);

            // ✅ ACTUALIZAR ESTADO DEL TRABAJADOR
            $trabajador->update([
                'estatus' => 'permiso',
            ]);

            DB::commit();

            Log::info('Permiso asignado exitosamente', [
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

            return redirect()->route('trabajadores.index')
                           ->with('success', 
                               "Permiso asignado exitosamente a {$trabajador->nombre_completo}. Tipo: {$validated['tipo_permiso']} - Motivo: {$validated['motivo']}"
                           );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al asignar permiso', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_permiso' => $validated['tipo_permiso'],
                'motivo' => $validated['motivo'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al asignar permiso: ' . $e->getMessage()])
                        ->withInput();
        }
    }

  /**
 * ✅ LISTAR PERMISOS - VERSIÓN CORREGIDA
 */
public function index(Request $request)
{
    $query = PermisosLaborales::with([
        'trabajador.fichaTecnica.categoria.area'
    ]);

    // ✅ FILTROS
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
        $query->where('motivo', 'like', "%{$request->motivo}%");
    }

    if ($request->filled('fecha_desde')) {
        $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
    }

    if ($request->filled('fecha_hasta')) {
        $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
    }

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

    // ✅ ESTADÍSTICAS
    $stats = [
        'total' => PermisosLaborales::count(),
        'activos' => PermisosLaborales::where('estatus_permiso', 'activo')->count(),
        'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                     ->whereYear('fecha_inicio', now()->year)
                                     ->count(),
        'finalizados' => PermisosLaborales::where('estatus_permiso', 'finalizado')->count(),
        'cancelados' => PermisosLaborales::where('estatus_permiso', 'cancelado')->count(),
        'vencidos' => PermisosLaborales::where('fecha_fin', '<', now())
                                     ->where('estatus_permiso', 'activo')
                                     ->count(),
    ];

    // ✅ DATOS PARA LA VISTA - ESTO ES LO QUE FALTABA
    $tiposPermisos = PermisosLaborales::getTiposDisponibles();
    
    // ✅ COLORES PARA LOS BADGES
    $coloresPermiso = [
        'Vacaciones' => 'success',
        'Licencia Médica' => 'danger',
        'Licencia por Maternidad' => 'info',
        'Licencia por Paternidad' => 'info', 
        'Permiso Personal' => 'warning',
        'Permiso por Estudios' => 'primary',
        'Permiso por Capacitación' => 'primary',
        'Licencia sin Goce de Sueldo' => 'secondary',
        'Permiso Especial' => 'dark',
        'Permiso por Duelo' => 'dark',
        'Permiso por Matrimonio' => 'success',
        'Incapacidad Temporal' => 'danger',
    ];

    // ✅ ICONOS PARA LOS BADGES
    $iconosPermiso = [
        'Vacaciones' => 'bi-sun',
        'Licencia Médica' => 'bi-heart-pulse',
        'Licencia por Maternidad' => 'bi-person-hearts',
        'Licencia por Paternidad' => 'bi-person-hearts', 
        'Permiso Personal' => 'bi-person',
        'Permiso por Estudios' => 'bi-mortarboard',
        'Permiso por Capacitación' => 'bi-book',
        'Licencia sin Goce de Sueldo' => 'bi-dash-circle',
        'Permiso Especial' => 'bi-star',
        'Permiso por Duelo' => 'bi-heart',
        'Permiso por Matrimonio' => 'bi-suit-heart',
        'Incapacidad Temporal' => 'bi-bandaid',
    ];

    // ✅ PASAR TODAS LAS VARIABLES A LA VISTA
    return view('trabajadores.estatus.permisos_lista', compact(
        'permisos', 
        'stats', 
        'tiposPermisos', 
        'coloresPermiso', 
        'iconosPermiso'
    ));
}
    /**
     * ✅ FINALIZAR PERMISO
     */
    public function finalizar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden finalizar permisos que estén activos'
            ]);
        }

        if ($trabajador->estatus !== 'permiso') {
            return back()->withErrors([
                'error' => 'El trabajador debe estar en estado de permiso'
            ]);
        }

        DB::beginTransaction();
        
        try {
            $permiso->update([
                'fecha_fin' => now()->format('Y-m-d'),
                'estatus_permiso' => 'finalizado',
                'observaciones' => $permiso->observaciones . 
                    "\n[FINALIZADO EL " . now()->format('d/m/Y') . " por " . (Auth::user()->email ?? 'Sistema') . "]"
            ]);

            $trabajador->update(['estatus' => 'activo']);

            DB::commit();

            Log::info('Permiso finalizado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'motivo' => $permiso->motivo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('permisos.index')
                           ->with('success', 
                               "Permiso finalizado. {$trabajador->nombre_completo} ha sido reactivado"
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
     * ✅ CANCELAR PERMISO
     */
    public function cancelar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden cancelar permisos que estén activos'
            ]);
        }

        DB::beginTransaction();

        try {
            // Guardar datos para log
            $datosPermiso = [
                'permiso_id' => $permiso->id_permiso,
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'tipo_permiso' => $permiso->tipo_permiso,
                'motivo' => $permiso->motivo,
                'fecha_inicio' => $permiso->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $permiso->fecha_fin->format('d/m/Y'),
            ];

            // Reactivar trabajador
            $trabajador->update(['estatus' => 'activo']);

            // Eliminar registro
            $permiso->delete();

            DB::commit();

            Log::info('Permiso eliminado exitosamente', [
                'datos_permiso' => $datosPermiso,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'fecha_eliminacion' => now()->format('d/m/Y H:i:s'),
            ]);
            
            return redirect()->route('permisos.index')
                        ->with('success', 
                            "Permiso eliminado exitosamente. {$datosPermiso['trabajador_nombre']} ha sido reactivado"
                        );

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al eliminar permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors([
                'error' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ ESTADÍSTICAS
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
            'por_tipo' => PermisosLaborales::selectRaw('tipo_permiso, COUNT(*) as total')
                                          ->whereYear('fecha_inicio', $añoActual)
                                          ->groupBy('tipo_permiso')
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
     * Mostrar detalles del permiso
     */
    public function show(PermisosLaborales $permiso)
    {
        $permiso->load('trabajador.fichaTecnica.categoria.area');
        
        return view('permisos.show', compact('permiso'));
    }
}