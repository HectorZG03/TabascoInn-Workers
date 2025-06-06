<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\Despidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DespidosController extends Controller
{
    /**
     * Mostrar formulario de despido
     */
    public function create(Trabajador $trabajador)
    {
        // Verificar que el trabajador esté activo
        if (!$trabajador->estaActivo()) {
            return back()->withErrors(['error' => 'Solo se pueden despedir trabajadores activos']);
        }

        // ✅ NUEVO: Verificar que no tenga ya un despido ACTIVO
        if ($trabajador->tieneSpidoActivo()) {
            return back()->withErrors(['error' => 'Este trabajador ya tiene un despido activo']);
        }

        return view('despidos.create', compact('trabajador'));
    }

    /**
     * Procesar despido del trabajador
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // Validar que el trabajador esté activo
        if (!$trabajador->estaActivo()) {
            return back()->withErrors(['error' => 'Solo se pueden despedir trabajadores activos']);
        }

        // ✅ NUEVO: Verificar que no tenga ya un despido ACTIVO
        if ($trabajador->tieneDespidoActivo()) {
            return back()->withErrors(['error' => 'Este trabajador ya tiene un despido activo']);
        }

        // Validar datos del formulario
        $validated = $request->validate([
            'fecha_baja' => 'required|date|before_or_equal:today|after_or_equal:' . $trabajador->fecha_ingreso->format('Y-m-d'),
            'motivo' => 'required|string|min:10|max:500',
            'condicion_salida' => 'required|in:Voluntaria,Despido con Causa,Despido sin Causa,Mutuo Acuerdo,Abandono de Trabajo,Fin de Contrato',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'fecha_baja.required' => 'La fecha de baja es obligatoria',
            'fecha_baja.before_or_equal' => 'La fecha de baja no puede ser futura',
            'fecha_baja.after_or_equal' => 'La fecha de baja no puede ser anterior a la fecha de ingreso',
            'motivo.required' => 'El motivo es obligatorio',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres',
            'condicion_salida.required' => 'La condición de salida es obligatoria',
            'condicion_salida.in' => 'La condición de salida seleccionada no es válida',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres',
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ CREAR REGISTRO DE DESPIDO CON ESTADO ACTIVO
            $despido = Despidos::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'fecha_baja' => $validated['fecha_baja'],
                'motivo' => $validated['motivo'],
                'condicion_salida' => $validated['condicion_salida'],
                'observaciones' => $validated['observaciones'],
                'estado' => Despidos::ESTADO_ACTIVO, // ✅ ESTADO ACTIVO
            ]);

            // Actualizar estado del trabajador a inactivo
            $trabajador->update([
                'estatus' => 'inactivo',
                'id_baja' => $despido->id_baja,
            ]);

            DB::commit();

            // Log de la acción
            Log::info('Trabajador despedido', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'despido_id' => $despido->id_baja,
                'motivo' => $validated['motivo'],
                'condicion_salida' => $validated['condicion_salida'],
                'fecha_baja' => $validated['fecha_baja'],
                'estado' => $despido->estado,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', "Trabajador {$trabajador->nombre_completo} ha sido despedido exitosamente");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al procesar despido', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al procesar el despido: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Mostrar detalles del despido
     */
    public function show(Despidos $despido)
    {
        $despido->load('trabajador.fichaTecnica.categoria.area', 'usuarioCancelacion');
        
        return view('despidos.show', compact('despido'));
    }

    /**
     * Listar todos los despidos
     */
    public function index(Request $request)
    {
        $query = Despidos::with([
            'trabajador.fichaTecnica.categoria.area',
            'usuarioCancelacion'
        ]);

        // ✅ FILTRO POR ESTADO (por defecto solo activos)
        $estadoFiltro = $request->get('estado', 'activo');
        
        if ($estadoFiltro === 'activo') {
            $query->activos();
        } elseif ($estadoFiltro === 'cancelado') {
            $query->cancelados();
        }
        // Si es 'todos', no aplicar filtro de estado

        // Filtros existentes
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trabajador', function($q) use ($search) {
                $q->where('nombre_trabajador', 'like', "%{$search}%")
                  ->orWhere('ape_pat', 'like', "%{$search}%")
                  ->orWhere('ape_mat', 'like', "%{$search}%");
            })->orWhere('motivo', 'like', "%{$search}%");
        }

        if ($request->filled('condicion_salida')) {
            $query->where('condicion_salida', $request->condicion_salida);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_baja', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_baja', '<=', $request->fecha_hasta);
        }

        $despidos = $query->orderBy('fecha_baja', 'desc')->paginate(20);

        // ✅ ESTADÍSTICAS ACTUALIZADAS
        $stats = [
            'total_activos' => Despidos::activos()->count(),
            'total_cancelados' => Despidos::cancelados()->count(),
            'este_mes' => Despidos::delMesActual()->count(),
            'este_año' => Despidos::delAnoActual()->count(),
            'voluntarias' => Despidos::activos()->where('condicion_salida', 'Voluntaria')->count(),
        ];

        // Condiciones de salida para filtro
        $condiciones = [
            'Voluntaria',
            'Despido con Causa',
            'Despido sin Causa',
            'Mutuo Acuerdo',
            'Abandono de Trabajo',
            'Fin de Contrato'
        ];

        // ✅ ESTADOS PARA FILTRO
        $estados = [
            'activo' => 'Bajas Activas',
            'cancelado' => 'Bajas Canceladas',
            'todos' => 'Todas las Bajas'
        ];

        return view('trabajadores.estatus.despidos_lista', compact(
            'despidos', 
            'stats', 
            'condiciones', 
            'estados',
            'estadoFiltro'
        ));
    }

    /**
     * ✅ CANCELAR DESPIDO (reactivar trabajador) - SIN ELIMINAR REGISTRO
     */
    public function cancelar(Request $request, Despidos $despido)
    {
        $trabajador = $despido->trabajador;

        // Verificar que el despido pueda ser cancelado
        if (!$despido->puedeSerCancelado()) {
            return back()->withErrors(['error' => 'Este despido ya ha sido cancelado']);
        }

        // Verificar que el trabajador esté inactivo
        if (!$trabajador->estaInactivo()) {
            return back()->withErrors(['error' => 'Solo se pueden cancelar despidos de trabajadores inactivos']);
        }

        // Validar motivo de cancelación (opcional)
        $validated = $request->validate([
            'motivo_cancelacion' => 'nullable|string|max:255',
        ], [
            'motivo_cancelacion.max' => 'El motivo de cancelación no puede exceder 255 caracteres',
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ CANCELAR DESPIDO (NO ELIMINAR)
            $despido->cancelar(
                $validated['motivo_cancelacion'] ?? 'Trabajador reactivado desde panel administrativo',
                Auth::id()
            );

            // Reactivar trabajador
            $trabajador->update([
                'estatus' => 'activo',
                'id_baja' => null, // Quitar referencia al despido activo
            ]);

            DB::commit();

            Log::info('Despido cancelado - Trabajador reactivado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'despido_id' => $despido->id_baja,
                'motivo_cancelacion' => $validated['motivo_cancelacion'] ?? 'Sin motivo específico',
                'usuario' => Auth::user()->email ?? 'Sistema',
                'fecha_cancelacion' => now(),
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', "Despido cancelado. {$trabajador->nombre_completo} ha sido reactivado");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al cancelar despido', [
                'despido_id' => $despido->id_baja,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al cancelar el despido: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ OBTENER HISTORIAL DE BAJAS DE UN TRABAJADOR
     */
    public function historial(Trabajador $trabajador)
    {
        $historialBajas = Despidos::historialTrabajador($trabajador->id_trabajador)
                                 ->with('usuarioCancelacion')
                                 ->get();

        return response()->json([
            'trabajador' => $trabajador->nombre_completo,
            'total_bajas' => $historialBajas->count(),
            'bajas_activas' => $historialBajas->where('estado', 'activo')->count(),
            'bajas_canceladas' => $historialBajas->where('estado', 'cancelado')->count(),
            'historial' => $historialBajas->map(function($baja) {
                return [
                    'id' => $baja->id_baja,
                    'fecha_baja' => $baja->fecha_baja->format('d/m/Y'),
                    'motivo' => $baja->motivo,
                    'condicion_salida' => $baja->condicion_salida,
                    'estado' => $baja->estado_texto,
                    'fecha_cancelacion' => $baja->fecha_cancelacion?->format('d/m/Y H:i'),
                    'cancelado_por' => $baja->usuarioCancelacion?->name ?? 'Sistema',
                ];
            })
        ]);
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;
        
        $estadisticas = [
            'totales' => [
                'total_activos' => Despidos::activos()->count(),
                'total_cancelados' => Despidos::cancelados()->count(),
                'este_mes' => Despidos::delMesActual()->count(),
                'este_año' => Despidos::delAnoActual()->count(),
            ],
            'por_mes' => Despidos::estadisticasPorMes($añoActual),
            'por_motivo' => Despidos::estadisticasPorMotivo($añoActual),
            'por_condicion' => Despidos::activos()
                                     ->selectRaw('condicion_salida, COUNT(*) as total')
                                     ->whereYear('fecha_baja', $añoActual)
                                     ->groupBy('condicion_salida')
                                     ->orderBy('total', 'desc')
                                     ->get(),
            'por_estado' => Despidos::contarPorEstado(),
            'multiples_bajas' => Despidos::trabajadoresConMultiplesBajas()->take(10),
        ];

        return response()->json($estadisticas);
    }
}