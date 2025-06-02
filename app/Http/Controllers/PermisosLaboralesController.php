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
     * Mostrar formulario de permiso
     */
    public function create(Trabajador $trabajador)
    {
        // Verificar que el trabajador esté activo
        if (!$trabajador->estaActivo()) {
            return back()->withErrors(['error' => 'Solo se pueden asignar permisos a trabajadores activos']);
        }

        return view('permisos.create', compact('trabajador'));
    }

    /**
     * Procesar asignación de permiso laboral
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // Validar que el trabajador esté activo
        if (!$trabajador->estaActivo()) {
            return back()->withErrors(['error' => 'Solo se pueden asignar permisos a trabajadores activos']);
        }

        // Validar datos del formulario
        $validated = $request->validate([
            'tipo_permiso' => 'required|in:vacaciones,incapacidad_medica,licencia_maternidad,licencia_paternidad,licencia_sin_goce,permiso_especial',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'tipo_permiso.required' => 'El tipo de permiso es obligatorio',
            'tipo_permiso.in' => 'El tipo de permiso seleccionado no es válido',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres',
        ]);

        // Validar que no haya conflictos de fechas
        $permisoExistente = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->where(function($query) use ($validated) {
                $query->whereBetween('fecha_inicio', [$validated['fecha_inicio'], $validated['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$validated['fecha_inicio'], $validated['fecha_fin']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('fecha_inicio', '<=', $validated['fecha_inicio'])
                            ->where('fecha_fin', '>=', $validated['fecha_fin']);
                      });
            })->first();

        if ($permisoExistente) {
            return back()->withErrors(['fecha_inicio' => 'Ya existe un permiso activo en el rango de fechas seleccionado']);
        }

        DB::beginTransaction();
        
        try {
            // Crear registro de permiso
            $permiso = PermisosLaborales::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_permiso' => $validated['tipo_permiso'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'observaciones' => $validated['observaciones'],
            ]);

            // Actualizar estado del trabajador
            $trabajador->update([
                'estatus' => $validated['tipo_permiso'],
            ]);

            DB::commit();

            // Log de la acción
            Log::info('Permiso laboral asignado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $validated['tipo_permiso'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'dias_permiso' => $permiso->dias_de_permiso,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', "Permiso de {$trabajador->estatus_texto} asignado exitosamente a {$trabajador->nombre_completo}");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al asignar permiso laboral', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al asignar el permiso: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Mostrar detalles del permiso
     */
    public function show(PermisosLaborales $permiso)
    {
        $permiso->load('trabajador.fichaTecnica.categoria.area');
        
        return view('permisos.show', compact('permiso'));
    }

    /**
     * Listar todos los permisos
     */
    public function index(Request $request)
    {
        $query = PermisosLaborales::with([
            'trabajador.fichaTecnica.categoria.area'
        ]);

        // Filtros
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

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('fecha_fin', '>=', now());
            } elseif ($request->estado === 'vencidos') {
                $query->where('fecha_fin', '<', now());
            }
        }

        $permisos = $query->orderBy('fecha_inicio', 'desc')->paginate(20);

        // Estadísticas
        $stats = [
            'total' => PermisosLaborales::count(),
            'activos' => PermisosLaborales::where('fecha_fin', '>=', now())->count(),
            'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                         ->whereYear('fecha_inicio', now()->year)
                                         ->count(),
            'vencidos' => PermisosLaborales::where('fecha_fin', '<', now())->count(),
        ];

        // Tipos de permisos para filtro
        $tiposPermisos = [
            'vacaciones' => 'Vacaciones',
            'incapacidad_medica' => 'Incapacidad Médica',
            'licencia_maternidad' => 'Licencia por Maternidad',
            'licencia_paternidad' => 'Licencia por Paternidad',
            'licencia_sin_goce' => 'Licencia sin Goce de Sueldo',
            'permiso_especial' => 'Permiso Especial',
        ];

        return view('permisos.index', compact('permisos', 'stats', 'tiposPermisos'));
    }

    /**
     * Finalizar permiso anticipadamente (reactivar trabajador)
     */
    public function finalizar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        // Verificar que el trabajador esté en ausencia
        if (!$trabajador->estaEnAusencia()) {
            return back()->withErrors(['error' => 'Solo se pueden finalizar permisos de trabajadores en ausencia']);
        }

        // Verificar que el permiso no haya vencido
        if ($permiso->fecha_fin < now()) {
            return back()->withErrors(['error' => 'No se puede finalizar un permiso que ya ha vencido']);
        }

        DB::beginTransaction();
        
        try {
            // Actualizar fecha de fin del permiso a hoy
            $permiso->update([
                'fecha_fin' => now()->format('Y-m-d'),
                'observaciones' => $permiso->observaciones . "\n[FINALIZADO ANTICIPADAMENTE EL " . now()->format('d/m/Y') . "]"
            ]);

            // Reactivar trabajador
            $trabajador->update([
                'estatus' => 'activo',
            ]);

            DB::commit();

            Log::info('Permiso laboral finalizado anticipadamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'fecha_original_fin' => $permiso->fecha_fin,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', "Permiso finalizado. {$trabajador->nombre_completo} ha sido reactivado");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al finalizar permiso laboral', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al finalizar el permiso: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancelar permiso (eliminar y reactivar trabajador)
     */
    public function cancelar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        // Verificar que el trabajador esté en ausencia
        if (!$trabajador->estaEnAusencia()) {
            return back()->withErrors(['error' => 'Solo se pueden cancelar permisos de trabajadores en ausencia']);
        }

        DB::beginTransaction();
        
        try {
            // Reactivar trabajador
            $trabajador->update([
                'estatus' => 'activo',
            ]);

            // Eliminar registro de permiso
            $permiso->delete();

            DB::commit();

            Log::info('Permiso laboral cancelado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', "Permiso cancelado. {$trabajador->nombre_completo} ha sido reactivado");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al cancelar permiso laboral', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al cancelar el permiso: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;
        
        $estadisticas = [
            'totales' => [
                'total' => PermisosLaborales::count(),
                'activos' => PermisosLaborales::where('fecha_fin', '>=', now())->count(),
                'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                             ->whereYear('fecha_inicio', now()->year)
                                             ->count(),
            ],
            'por_tipo' => PermisosLaborales::selectRaw('tipo_permiso, COUNT(*) as total')
                                          ->whereYear('fecha_inicio', $añoActual)
                                          ->groupBy('tipo_permiso')
                                          ->orderBy('total', 'desc')
                                          ->get(),
            'por_mes' => PermisosLaborales::selectRaw('MONTH(fecha_inicio) as mes, COUNT(*) as total')
                                         ->whereYear('fecha_inicio', $añoActual)
                                         ->groupBy('mes')
                                         ->orderBy('mes')
                                         ->get(),
            'promedio_dias' => PermisosLaborales::whereYear('fecha_inicio', $añoActual)
                                               ->get()
                                               ->avg('dias_de_permiso'),
        ];

        return response()->json($estadisticas);
    }

    /**
     * Verificar permisos vencidos y reactivar trabajadores automáticamente
     */
    public function verificarVencidos()
    {
        $permisosVencidos = PermisosLaborales::with('trabajador')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->whereHas('trabajador', function($query) {
                $query->whereIn('estatus', Trabajador::ESTADOS_TEMPORALES);
            })
            ->get();

        $reactivados = 0;

        foreach ($permisosVencidos as $permiso) {
            try {
                $permiso->trabajador->update(['estatus' => 'activo']);
                $reactivados++;
                
                Log::info('Trabajador reactivado automáticamente por permiso vencido', [
                    'trabajador_id' => $permiso->trabajador->id_trabajador,
                    'permiso_id' => $permiso->id_permiso,
                    'fecha_vencimiento' => $permiso->fecha_fin
                ]);
            } catch (\Exception $e) {
                Log::error('Error al reactivar trabajador automáticamente', [
                    'permiso_id' => $permiso->id_permiso,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'permisos_vencidos' => $permisosVencidos->count(),
            'trabajadores_reactivados' => $reactivados
        ]);
    }
}