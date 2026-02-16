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
        if ($trabajador->tieneDespidoActivo()) {
            return back()->withErrors(['error' => 'Este trabajador ya tiene un despido activo']);
        }

        return view('despidos.create', compact('trabajador'));
    }

    /**
     * ✅ PROCESAR DESPIDO CON FORMATO DD/MM/YYYY
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // Verificar que el trabajador esté activo
        if (!$trabajador->estaActivo()) {
            return back()->withErrors(['error' => 'Solo se pueden despedir trabajadores activos']);
        }

        // ✅ NUEVO: Verificar que no tenga ya un despido ACTIVO
        if ($trabajador->tieneDespidoActivo()) {
            return back()->withErrors(['error' => 'Este trabajador ya tiene un despido activo']);
        }

        // ✅ VALIDACIÓN ACTUALIZADA - SIN RESTRICCIÓN DE FECHAS PASADAS
        $validated = $request->validate([
            'fecha_baja' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($trabajador) {
                    // Convertir fecha DD/MM/YYYY a Date para validaciones
                    $fechaPartes = explode('/', $value);
                    if (count($fechaPartes) !== 3) {
                        $fail('El formato de fecha debe ser DD/MM/YYYY');
                        return;
                    }
                    
                    $dia = (int)$fechaPartes[0];
                    $mes = (int)$fechaPartes[1];
                    $año = (int)$fechaPartes[2];
                    
                    // Validar fecha válida
                    if (!checkdate($mes, $dia, $año)) {
                        $fail('La fecha ingresada no es válida');
                        return;
                    }
                    
                    try {
                        $fechaBaja = Carbon::createFromFormat('d/m/Y', $value);
                        $fechaIngreso = $trabajador->fecha_ingreso;
                        
                        // ✅ SOLO VALIDAR QUE NO SEA ANTERIOR A FECHA DE INGRESO
                        if ($fechaBaja->lessThan($fechaIngreso)) {
                            $fail('La fecha de baja no puede ser anterior a la fecha de ingreso (' . $fechaIngreso->format('d/m/Y') . ')');
                            return;
                        }
                    } catch (\Exception $e) {
                        $fail('Fecha inválida');
                    }
                }
            ],
            
            'motivo' => 'required|string|min:10|max:500',
            'condicion_salida' => 'required|in:Voluntaria,Despido con Causa,Despido sin Causa,Castigo,Mutuo Acuerdo,Abandon de Trabajo,Fin de Contrato,Incapacidad Permanente,Jubilación,Defunción,OTRO',
            'condicion_personalizada' => 'nullable|required_if:condicion_salida,OTRO|string|min:3|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'tipo_baja' => 'required|in:temporal,definitiva',
            
            // ✅ VALIDACIÓN DE FECHA DE REINTEGRO ACTUALIZADA - SIN RESTRICCIÓN TEMPORAL ESTRICTA
            'fecha_reintegro' => [
                'nullable',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->tipo_baja === 'temporal') {
                        if (!$value) {
                            $fail('La fecha de reintegro es obligatoria para bajas temporales');
                            return;
                        }
                        
                        // Convertir fechas DD/MM/YYYY para comparar
                        $fechaPartes = explode('/', $value);
                        if (count($fechaPartes) !== 3) {
                            $fail('El formato de fecha debe ser DD/MM/YYYY');
                            return;
                        }
                        
                        $dia = (int)$fechaPartes[0];
                        $mes = (int)$fechaPartes[1];
                        $año = (int)$fechaPartes[2];
                        
                        if (!checkdate($mes, $dia, $año)) {
                            $fail('La fecha de reintegro no es válida');
                            return;
                        }
                        
                        try {
                            $fechaReintegro = Carbon::createFromFormat('d/m/Y', $value);
                            $fechaBaja = Carbon::createFromFormat('d/m/Y', $request->fecha_baja);
                            
                            // ✅ SOLO VALIDAR: Debe ser posterior a la fecha de baja
                            if ($fechaReintegro->lessThanOrEqualTo($fechaBaja)) {
                                $fail('La fecha de reintegro debe ser posterior a la fecha de baja');
                                return;
                            }
                            
                        } catch (\Exception $e) {
                            $fail('Fecha de reintegro inválida');
                        }
                    }
                }
            ],
        ], [
            // ✅ MENSAJES DE ERROR ACTUALIZADOS
            'fecha_baja.required' => 'La fecha de baja es obligatoria',
            'fecha_baja.regex' => 'La fecha de baja debe tener el formato DD/MM/YYYY',
            'motivo.required' => 'El motivo es obligatorio',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres',
            'condicion_salida.required' => 'La condición de salida es obligatoria',
            'condicion_salida.in' => 'La condición de salida seleccionada no es válida',
            'condicion_personalizada.required_if' => 'Debe especificar la condición de salida cuando selecciona "Otro"',
            'condicion_personalizada.min' => 'La condición personalizada debe tener al menos 3 caracteres',
            'condicion_personalizada.max' => 'La condición personalizada no puede exceder 100 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres',
            'tipo_baja.required' => 'El tipo de baja es obligatorio',
            'tipo_baja.in' => 'El tipo de baja debe ser temporal o definitiva',
            'fecha_reintegro.regex' => 'La fecha de reintegro debe tener el formato DD/MM/YYYY',
        ]);

        // ✅ CONVERTIR FECHAS DE DD/MM/YYYY A Y-m-d PARA LA BASE DE DATOS
        try {
            $fechaBajaParaBD = Carbon::createFromFormat('d/m/Y', $validated['fecha_baja'])->format('Y-m-d');
            $fechaReintegroParaBD = null;
            
            if ($validated['fecha_reintegro']) {
                $fechaReintegroParaBD = Carbon::createFromFormat('d/m/Y', $validated['fecha_reintegro'])->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar las fechas: ' . $e->getMessage()])
                        ->withInput();
        }

        // ✅ DETERMINAR LA CONDICIÓN FINAL A GUARDAR
        $condicionFinal = $validated['condicion_salida'] === 'OTRO' 
            ? trim($validated['condicion_personalizada'])
            : $validated['condicion_salida'];

        DB::beginTransaction();
        
        try {
            // ✅ CREAR REGISTRO DE DESPIDO CON FECHAS CONVERTIDAS
            $despido = Despidos::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'fecha_baja' => $fechaBajaParaBD, // ✅ Fecha convertida a Y-m-d
                'motivo' => $validated['motivo'],
                'condicion_salida' => $condicionFinal, // ✅ Usar condición final
                'observaciones' => $validated['observaciones'],
                'estado' => Despidos::ESTADO_ACTIVO,
                'tipo_baja' => $request->tipo_baja,
                'fecha_reintegro' => $fechaReintegroParaBD, // ✅ Fecha convertida a Y-m-d o null
                'creado_por' => Auth::id(),
            ]);

            // ✅ ACTUALIZAR ESTADO DEL TRABAJADOR
            $nuevoEstatus = $request->tipo_baja === 'temporal' ? 'suspendido' : 'inactivo';
            
            $trabajador->update([
                'estatus' => $nuevoEstatus,
                'id_baja' => $despido->id_baja,
            ]);

            DB::commit();

            // ✅ LOG MEJORADO CON FECHAS ORIGINALES Y CONVERTIDAS
            Log::info('Trabajador despedido', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'despido_id' => $despido->id_baja,
                'motivo' => $validated['motivo'],
                'condicion_salida' => $condicionFinal,
                'condicion_fue_personalizada' => $validated['condicion_salida'] === 'OTRO',
                'fecha_baja_original' => $validated['fecha_baja'], // DD/MM/YYYY
                'fecha_baja_bd' => $fechaBajaParaBD, // Y-m-d
                'fecha_reintegro_original' => $validated['fecha_reintegro'], // DD/MM/YYYY o null
                'fecha_reintegro_bd' => $fechaReintegroParaBD, // Y-m-d o null
                'tipo_baja' => $request->tipo_baja,
                'nuevo_estatus' => $nuevoEstatus,
                'estado' => $despido->estado,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            // ✅ MENSAJE DE ÉXITO MEJORADO CON FECHAS LEGIBLES
            $tipoAccion = $request->tipo_baja === 'temporal' ? 'suspendido temporalmente' : 'dado de baja';
            $mensaje = "Trabajador {$trabajador->nombre_completo} ha sido {$tipoAccion} exitosamente";
            
            // Agregar información de fechas al mensaje
            $mensaje .= " con fecha {$validated['fecha_baja']}";
            
            if ($validated['fecha_reintegro']) {
                $mensaje .= " hasta {$validated['fecha_reintegro']}";
                
                // Calcular duración para mensaje
                try {
                    $fechaBajaObj = Carbon::createFromFormat('d/m/Y', $validated['fecha_baja']);
                    $fechaReintegroObj = Carbon::createFromFormat('d/m/Y', $validated['fecha_reintegro']);
                    $duracionDias = $fechaBajaObj->diffInDays($fechaReintegroObj);
                    $mensaje .= " ({$duracionDias} días)";
                } catch (\Exception $e) {
                    // Si hay error calculando, continuar sin la duración
                }
            }
            
            if ($validated['condicion_salida'] === 'OTRO') {
                $mensaje .= " con condición personalizada: \"{$condicionFinal}\"";
            }

            return redirect()->route('trabajadores.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al procesar despido', [
                'trabajador_id' => $trabajador->id_trabajador,
                'fecha_baja_original' => $validated['fecha_baja'] ?? null,
                'fecha_reintegro_original' => $validated['fecha_reintegro'] ?? null,
                'condicion_solicitada' => $validated['condicion_salida'] ?? null,
                'condicion_personalizada' => $validated['condicion_personalizada'] ?? null,
                'tipo_baja' => $request->tipo_baja ?? null,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
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
     * ✅ LISTAR TODOS LOS DESPIDOS - ÍNDICE LIMPIO
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

        // ✅ OBTENER ESTADÍSTICAS DEL CONTROLADOR DEDICADO
        $estadisticasController = new EstadisticasController();
        $stats = $estadisticasController->obtenerEstadisticasDespidos();

        // ✅ CONDICIONES DINÁMICAS (incluye las personalizadas ya guardadas)
        $condicionesBasicas = [
            'Voluntaria',
            'Despido con Causa',
            'Despido sin Causa',
            'Castigo',
            'Mutuo Acuerdo',
            'Abandono de Trabajo',
            'Fin de Contrato',
            'Incapacidad Permanente',
            'Jubilación',
            'Defunción'
        ];

        // Obtener condiciones personalizadas que ya existen en BD
        $condicionesPersonalizadas = Despidos::select('condicion_salida')
            ->whereNotIn('condicion_salida', $condicionesBasicas)
            ->distinct()
            ->pluck('condicion_salida')
            ->toArray();

        $condiciones = array_merge($condicionesBasicas, $condicionesPersonalizadas);

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

    public function cancelar(Request $request, Despidos $despido)
    {
        $trabajador = $despido->trabajador;

        // Verificar que el despido pueda ser cancelado
        if (!$despido->puedeSerCancelado()) {
            return back()->withErrors(['error' => 'Este despido ya ha sido cancelado']);
        }

        // Verificar que el trabajador esté suspendido o inactivo
        if (!in_array($trabajador->estatus, ['inactivo', 'suspendido'])) {
            return back()->withErrors(['error' => 'Solo se pueden reactivar trabajadores suspendidos o inactivos']);
        }

        $validated = $request->validate([
            'motivo_cancelacion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Actualiza despido como cancelado
            $despido->cancelar(
                $validated['motivo_cancelacion'] ?? 'Reactivación del trabajador desde el sistema',
                Auth::id()
            );

            // Reactivar trabajador
            $trabajador->update([
                'estatus' => 'activo',
                'id_baja' => null,
            ]);

            DB::commit();

            Log::info('Trabajador reactivado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_baja' => $despido->tipo_baja,
                'condicion_original' => $despido->condicion_salida,
                'usuario' => Auth::user()->email ?? 'Sistema',
            ]);

            return redirect()->route('trabajadores.index')
                            ->with('success', "{$trabajador->nombre_completo} fue reactivado correctamente");

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al reactivar trabajador', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'No se pudo reactivar al trabajador: ' . $e->getMessage()]);
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
     * ✅ ESTADÍSTICAS DELEGADAS AL CONTROLADOR ESPECIALIZADO
     */
    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;
        
        $estadisticasController = new EstadisticasController();
        $estadisticasBasicas = $estadisticasController->obtenerEstadisticasDespidos();
        
        $estadisticas = array_merge($estadisticasBasicas, [
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
        ]);

        return response()->json($estadisticas);
    }
}