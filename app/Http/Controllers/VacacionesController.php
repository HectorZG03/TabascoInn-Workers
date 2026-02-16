<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\VacacionesTrabajador;
use App\Models\Area;
use App\Models\DocumentoVacaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VacacionesController extends Controller
{
    public function show(Trabajador $trabajador): View
    {
        $trabajador->load(['vacaciones.creadoPor:id,nombre', 'fichaTecnica.categoria.area']);
        $estadisticas = $trabajador->getEstadisticasVacaciones();

        return view('trabajadores.secciones_perfil.vacaciones', compact('trabajador', 'estadisticas'));
    }

    public function index(Trabajador $trabajador): JsonResponse
    {
        try {
            $vacaciones = $trabajador->vacaciones()
                ->with(['creadoPor:id,nombre'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($vacacion) {
                    $vacacion->fecha_inicio_formatted = $vacacion->fecha_inicio?->format('d/m/Y');
                    $vacacion->fecha_fin_formatted = $vacacion->fecha_fin?->format('d/m/Y');
                    $vacacion->fecha_reintegro_formatted = $vacacion->fecha_reintegro?->format('d/m/Y');
                    return $vacacion;
                });

            return response()->json([
                'success' => true,
                'vacaciones' => $vacaciones,
                'estadisticas' => $trabajador->getEstadisticasVacaciones(),
                'trabajador' => [
                    'id' => $trabajador->id_trabajador,
                    'nombre' => $trabajador->nombre_completo,
                    'estatus' => $trabajador->estatus,
                    'puede_tomar_vacaciones' => $trabajador->puedeTomarVacaciones(),
                    'dias_correspondientes' => $trabajador->dias_vacaciones_correspondientes,
                    'dias_restantes_año_actual' => $trabajador->dias_vacaciones_restantes_este_año
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar las vacaciones: ' . $e->getMessage()], 500);
        }
    }


    public function store(Request $request, Trabajador $trabajador): JsonResponse
    {
        try {
            $validator = $this->validarVacacionesRefactorizado($request, $trabajador);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Datos inválidos', 
                    'errors' => $validator->errors()
                ], 422);
            }

            $datos = $validator->validated();

            // ✅ RECALCULAR FECHA FIN CON DÍAS LABORABLES (si es posible)
            if ($fichaTecnica = $trabajador->fichaTecnica) {
                $fechaFinCalculada = $fichaTecnica->calcularFechaFinVacaciones($datos['fecha_inicio'], $datos['dias_solicitados']);
                if ($fechaFinCalculada) {
                    $datos['fecha_fin'] = $fechaFinCalculada->format('Y-m-d');
                }
            }
            
            // ✅ NUEVO: CALCULAR FECHA DE REINTEGRO CONSIDERANDO DÍAS DE DESCANSO Y FESTIVOS
            $fechaFin = Carbon::parse($datos['fecha_fin']); // Esta ya viene corregida del cálculo anterior
            $fechaReintegroInicial = $fechaFin->copy()->addDay();

            // Obtener días de descanso del trabajador
            $diasDescanso = [];
            if ($trabajador->fichaTecnica) {
                $diasDescanso = $trabajador->fichaTecnica->dias_descanso ?? [];
            }
            
            // Calcular el siguiente día hábil
            $fechaReintegroHabil = \App\Models\DiaFestivo::siguienteDiaHabil($fechaReintegroInicial, $diasDescanso);
            $datos['fecha_reintegro'] = $fechaReintegroHabil->format('Y-m-d');

            // ✅ VALIDACIONES DE NEGOCIO FLEXIBLES POR PERIODO


            // ✅ LOG PARA DEBUGGING (incluye fecha de reintegro)
            Log::info("Asignando vacaciones", [
                'trabajador' => $trabajador->nombre_completo,
                'periodo' => $datos['periodo_vacacional'],
                'año' => $datos['año_correspondiente'],
                'dias_correspondientes' => $datos['dias_correspondientes'],
                'dias_solicitados' => $datos['dias_solicitados'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'fecha_reintegro' => $datos['fecha_reintegro']
            ]);

            $vacacion = $trabajador->asignarVacacionesRefactorizado($datos, Auth::id());

            // ✅ OBTENER RESUMEN DEL PERIODO PARA RESPUESTA
            $resumenPeriodo = $vacacion->resumen_periodo;
            
            // ✅ CALCULAR INFORMACIÓN ADICIONAL SOBRE EL REINTEGRO
            $diasSaltados = [];
            $fechaTemporal = $fechaReintegroInicial->copy();
            
            while ($fechaTemporal->lt($fechaReintegroHabil)) {
                $razon = [];
                
                // Verificar si es día de descanso
                $diasSemanaMap = [
                    'domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3,
                    'jueves' => 4, 'viernes' => 5, 'sabado' => 6
                ];
                
                $diasDescansoNumeros = array_map(function($dia) use ($diasSemanaMap) {
                    return $diasSemanaMap[strtolower($dia)] ?? null;
                }, $diasDescanso);
                
                if (in_array($fechaTemporal->dayOfWeek, $diasDescansoNumeros)) {
                    $razon[] = 'Día de descanso';
                }
                
                // Verificar si es día festivo
                if (\App\Models\DiaFestivo::esDiaFestivo($fechaTemporal)) {
                    $diaFestivo = \App\Models\DiaFestivo::whereDate('fecha', $fechaTemporal)->first();
                    $razon[] = "Festivo: " . ($diaFestivo->nombre ?? 'Día festivo');
                }
                
                if (!empty($razon)) {
                    $diasSaltados[] = [
                        'fecha' => $fechaTemporal->format('d/m/Y'),
                        'razon' => implode(' y ', $razon)
                    ];
                }
                
                $fechaTemporal->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => 'Vacaciones asignadas correctamente',
                'vacacion' => $vacacion->load('creadoPor:id,nombre'),
                'trabajador_estatus' => $trabajador->fresh()->estatus,
                'resumen_periodo' => $resumenPeriodo,
                'info' => [
                    'dias_disfrutados_anteriormente' => $resumenPeriodo['dias_disfrutados_anteriormente'],
                    'dias_pendientes_periodo' => $resumenPeriodo['dias_pendientes'],
                    'posicion_en_periodo' => $resumenPeriodo['posicion_en_periodo'],
                    'fecha_reintegro' => $fechaReintegroHabil->format('d/m/Y'),
                    'dias_saltados_reintegro' => $diasSaltados
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al asignar vacaciones", [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al asignar vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NUEVO MÉTODO: Calcular fecha de reintegro vía AJAX
    public function calcularFechaReintegro(Request $request, Trabajador $trabajador): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha_fin' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Fecha de fin requerida', 
                'errors' => $validator->errors()
            ], 422);
        }

        $fechaFin = Carbon::parse($request->fecha_fin);
        $fechaReintegroInicial = $fechaFin->copy()->addDay();
        
        // Obtener días de descanso del trabajador
        $diasDescanso = [];
        if ($trabajador->fichaTecnica) {
            $diasDescanso = $trabajador->fichaTecnica->dias_descanso ?? [];
        }
        
        // Calcular el siguiente día hábil
        $fechaReintegroHabil = \App\Models\DiaFestivo::siguienteDiaHabil($fechaReintegroInicial, $diasDescanso);
            // Calcular información sobre días saltados
        $diasSaltados = [];
        $fechaTemporal = $fechaReintegroInicial->copy();
        
        while ($fechaTemporal->lt($fechaReintegroHabil)) {
            $razon = [];
            
            // Mapeo de días
            $diasSemanaMap = [
                'domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3,
                'jueves' => 4, 'viernes' => 5, 'sabado' => 6
            ];
            
            $diasDescansoNumeros = array_map(function($dia) use ($diasSemanaMap) {
                return $diasSemanaMap[strtolower($dia)] ?? null;
            }, $diasDescanso);
            
            if (in_array($fechaTemporal->dayOfWeek, $diasDescansoNumeros)) {
                $razon[] = 'Día de descanso';
            }
            
            if (\App\Models\DiaFestivo::esDiaFestivo($fechaTemporal)) {
                $diaFestivo = \App\Models\DiaFestivo::whereDate('fecha', $fechaTemporal)->first();
                $razon[] = "Festivo: " . ($diaFestivo->nombre ?? 'Día festivo');
            }
            
            if (!empty($razon)) {
                $diasSaltados[] = [
                    'fecha' => $fechaTemporal->format('d/m/Y'),
                    'dia_semana' => $fechaTemporal->locale('es')->dayName,
                    'razon' => implode(' y ', $razon)
                ];
            }
            
            $fechaTemporal->addDay();
        }
        
        return response()->json([
            'success' => true,
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'fecha_fin_formateada' => $fechaFin->format('d/m/Y'),
            'fecha_reintegro_inicial' => $fechaReintegroInicial->format('Y-m-d'),
            'fecha_reintegro_inicial_formateada' => $fechaReintegroInicial->format('d/m/Y'),
            'fecha_reintegro' => $fechaReintegroHabil->format('Y-m-d'),
            'fecha_reintegro_formateada' => $fechaReintegroHabil->format('d/m/Y'),
            'dias_saltados' => $diasSaltados,
            'total_dias_saltados' => count($diasSaltados),
            'tiene_dias_descanso' => !empty($diasDescanso),
            'dias_descanso_trabajador' => $diasDescanso
        ]);
    }

    public function iniciar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
            return response()->json(['success' => false, 'message' => 'Vacación no válida para este trabajador'], 403);
        }

        if ($vacacion->iniciar(Auth::id())) {
            return response()->json([
                'success' => true,
                'message' => 'Vacaciones iniciadas correctamente',
                'vacacion' => $vacacion->fresh(),
                'trabajador_estatus' => $trabajador->fresh()->estatus
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se pueden iniciar estas vacaciones'], 422);
    }

    public function finalizar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
            return response()->json(['success' => false, 'message' => 'Vacación no válida para este trabajador'], 403);
        }

        if (!$vacacion->puedeFinalizarse()) {
            return response()->json(['success' => false, 'message' => 'Solo se pueden finalizar vacaciones activas que hayan llegado a su fecha fin'], 422);
        }

        $motivo = $request->input('motivo_finalizacion', 'Vacaciones finalizadas por cumplimiento de fecha');

        if ($vacacion->finalizar($motivo, Auth::id())) {
            return response()->json([
                'success' => true,
                'message' => 'Vacaciones finalizadas correctamente',
                'vacacion' => $vacacion->fresh(['creadoPor', 'canceladoPor']),
                'trabajador_estatus' => $trabajador->fresh()->estatus
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se pueden finalizar estas vacaciones'], 422);
    }

    public function cancelar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
            return response()->json(['success' => false, 'message' => 'Vacación no válida para este trabajador'], 403);
        }

        if (!$vacacion->puedeCancelarse()) {
            return response()->json(['success' => false, 'message' => 'Solo se pueden cancelar vacaciones pendientes o activas'], 422);
        }

        $validator = Validator::make($request->all(), [
            'motivo_cancelacion' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Motivo de cancelación inválido', 'errors' => $validator->errors()], 422);
        }

        if ($vacacion->cancelar($request->input('motivo_cancelacion'), Auth::id())) {
            return response()->json([
                'success' => true,
                'message' => 'Vacaciones canceladas correctamente. Los días han sido devueltos.',
                'vacacion' => $vacacion->fresh(['creadoPor', 'canceladoPor']),
                'trabajador_estatus' => $trabajador->fresh()->estatus,
                'dias_devueltos' => $vacacion->dias_solicitados
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se pueden cancelar estas vacaciones'], 422);
    }

    public function calcularDias(Trabajador $trabajador): JsonResponse
    {
        return response()->json([
            'success' => true,
            'dias_correspondientes' => $trabajador->dias_vacaciones_correspondientes,
            'dias_restantes' => $trabajador->dias_vacaciones_restantes_este_año,
            'antiguedad' => $trabajador->antiguedad,
            'puede_tomar_vacaciones' => $trabajador->puedeTomarVacaciones()
        ]);
    }

    // ✅ CÁLCULO DE FECHAS SIN RESTRICCIONES TEMPORALES
    public function calcularFechasVacaciones(Request $request, Trabajador $trabajador): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date', // ✅ Sin restricción after_or_equal:today
            'dias_solicitados' => 'required|integer|min:1|max:365' // ✅ Límite más flexible
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Datos inválidos', 
                'errors' => $validator->errors()
            ], 422);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $diasSolicitados = (int) $request->input('dias_solicitados');

        $fichaTecnica = $trabajador->fichaTecnica;
        if (!$fichaTecnica) {
            return response()->json([
                'success' => false, 
                'message' => 'El trabajador no tiene ficha técnica configurada'
            ], 422);
        }

        $resumen = $fichaTecnica->getResumenVacaciones($fechaInicio, $diasSolicitados);
        if (!$resumen) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al calcular las fechas de vacación'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'calculo' => [
                'fecha_inicio' => $resumen['fecha_inicio']->format('Y-m-d'),
                'fecha_inicio_formatted' => $resumen['fecha_inicio']->format('d/m/Y'),
                'fecha_fin' => $resumen['fecha_fin']->format('Y-m-d'),
                'fecha_fin_formatted' => $resumen['fecha_fin']->format('d/m/Y'),
                'dias_laborables_solicitados' => $resumen['dias_laborables_solicitados'],
                'dias_calendario_total' => $resumen['dias_calendario_total'],
                'explicacion' => $resumen['explicacion']
            ]
        ]);
    }

    // ✅ VALIDADOR REFACTORIZADO - Entrada manual de año y período

    private function validarVacacionesRefactorizado(Request $request, Trabajador $trabajador): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            // ✅ NUEVOS CAMPOS MANUALES
            'año_correspondiente' => [
                'required', 
                'integer', 
                'min:2000', 
                'max:2050' // Rango flexible para datos históricos y futuros
            ],
            'periodo_vacacional' => [
                'required', 
                'string', 
                'min:3', 
                'max:30' // Entrada manual, más flexible
            ],
            'dias_correspondientes' => [
                'required',
                'integer',
                'min:6',
                'max:50' // LFT flexible
            ],
            
            // ✅ CAMPOS EXISTENTES ACTUALIZADOS
            'dias_solicitados' => [
                'required', 
                'integer', 
                'min:1', 
                'max:365' // ✅ Límite más flexible, sin restricción de días disponibles
            ],
            'fecha_inicio' => [
                'required', 
                'date'
                // ✅ SIN restricción after_or_equal:today
            ],
            'fecha_fin' => [
                'required', 
                'date', 
                'after_or_equal:fecha_inicio' // 🔧 CAMBIADO: Permite fechas iguales (vacaciones de 1 día)
            ],
            'observaciones' => 'nullable|string|max:500'
        ], [
            // ✅ MENSAJES PERSONALIZADOS
            'año_correspondiente.required' => 'El año correspondiente es obligatorio',
            'año_correspondiente.integer' => 'El año debe ser un número entero',
            'año_correspondiente.min' => 'El año no puede ser menor a 2000',
            'año_correspondiente.max' => 'El año no puede ser mayor a 2050',
            
            'periodo_vacacional.required' => 'El período vacacional es obligatorio',
            'periodo_vacacional.min' => 'El período debe tener al menos 3 caracteres',
            'periodo_vacacional.max' => 'El período no puede exceder 30 caracteres',
            
            'dias_correspondientes.required' => 'Los días correspondientes son obligatorios',
            'dias_correspondientes.min' => 'Los días correspondientes no pueden ser menos de 6',
            'dias_correspondientes.max' => 'Los días correspondientes no pueden exceder 50',
            
            'dias_solicitados.required' => 'Los días solicitados son obligatorios',
            'dias_solicitados.min' => 'Debe solicitar al menos 1 día',
            'dias_solicitados.max' => 'No puede solicitar más de 365 días',
            
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio' // 🔧 MENSAJE ACTUALIZADO
        ]);
    }

    public function listarTrabajadoresConVacaciones(Request $request)
    {
        // Query base para trabajadores con vacaciones
        $query = Trabajador::whereHas('vacaciones', function($q) use ($request) {
            // Filtrar por estados de vacaciones
            if ($request->filled('estado_vacacion')) {
                if ($request->estado_vacacion === 'todas') {
                    // Mostrar todas las vacaciones
                    $q->whereIn('estado', ['activa', 'pendiente', 'cancelada', 'finalizada']);
                } else {
                    $q->where('estado', $request->estado_vacacion);
                }
            } else {
                // Por defecto mostrar todas
                $q->whereIn('estado', ['activa', 'pendiente', 'cancelada', 'finalizada']);
            }
            
            // Filtro por rango de fechas de inicio
            if ($request->filled('fecha_inicio_desde')) {
                $q->whereDate('fecha_inicio', '>=', $request->fecha_inicio_desde);
            }
            
            if ($request->filled('fecha_inicio_hasta')) {
                $q->whereDate('fecha_inicio', '<=', $request->fecha_inicio_hasta);
            }
            
            // Filtro por rango de fechas de fin
            if ($request->filled('fecha_fin_desde')) {
                $q->whereDate('fecha_fin', '>=', $request->fecha_fin_desde);
            }
            
            if ($request->filled('fecha_fin_hasta')) {
                $q->whereDate('fecha_fin', '<=', $request->fecha_fin_hasta);
            }
            
            // Filtro por año correspondiente
            if ($request->filled('año_correspondiente')) {
                $q->where('año_correspondiente', $request->año_correspondiente);
            }
            
            // Filtro por periodo vacacional
            if ($request->filled('periodo_vacacional')) {
                $q->where('periodo_vacacional', 'LIKE', "%{$request->periodo_vacacional}%");
            }
        })
        ->with(['vacaciones' => function($q) use ($request) {
            // Aplicar los mismos filtros a las vacaciones cargadas
            if ($request->filled('estado_vacacion')) {
                if ($request->estado_vacacion === 'todas') {
                    $q->whereIn('estado', ['activa', 'pendiente', 'cancelada', 'finalizada']);
                } else {
                    $q->where('estado', $request->estado_vacacion);
                }
            } else {
                $q->whereIn('estado', ['activa', 'pendiente', 'cancelada', 'finalizada']);
            }
            
            if ($request->filled('fecha_inicio_desde')) {
                $q->whereDate('fecha_inicio', '>=', $request->fecha_inicio_desde);
            }
            
            if ($request->filled('fecha_inicio_hasta')) {
                $q->whereDate('fecha_inicio', '<=', $request->fecha_inicio_hasta);
            }
            
            if ($request->filled('fecha_fin_desde')) {
                $q->whereDate('fecha_fin', '>=', $request->fecha_fin_desde);
            }
            
            if ($request->filled('fecha_fin_hasta')) {
                $q->whereDate('fecha_fin', '<=', $request->fecha_fin_hasta);
            }
            
            if ($request->filled('año_correspondiente')) {
                $q->where('año_correspondiente', $request->año_correspondiente);
            }
            
            if ($request->filled('periodo_vacacional')) {
                $q->where('periodo_vacacional', 'LIKE', "%{$request->periodo_vacacional}%");
            }
            
            $q->orderBy('fecha_inicio', 'desc');
        }, 'fichaTecnica.categoria.area']);
        
        // Búsqueda por nombre
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                ->orWhere('curp', 'LIKE', "%{$search}%")
                ->orWhere('rfc', 'LIKE', "%{$search}%");
            });
        }
        
        // Filtro por área
        if ($request->filled('area')) {
            $query->whereHas('fichaTecnica.categoria.area', function($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }
        
        // Ordenamiento
        $orderBy = $request->get('order_by', 'nombre');
        $orderDir = $request->get('order_dir', 'asc');
        
        switch($orderBy) {
            case 'fecha_inicio':
                $query->leftJoin('vacaciones_trabajadores', 'trabajadores.id_trabajador', '=', 'vacaciones_trabajadores.id_trabajador')
                    ->select('trabajadores.*')
                    ->groupBy('trabajadores.id_trabajador')
                    ->orderBy(DB::raw('MAX(vacaciones_trabajadores.fecha_inicio)'), $orderDir);
                break;
            case 'nombre':
            default:
                $query->orderBy('nombre_trabajador', $orderDir)
                    ->orderBy('ape_pat', $orderDir);
                break;
        }
        
        // Paginación
        $trabajadores = $query->paginate(15)->withQueryString();
        
        // Calcular estadísticas
        $statsQuery = VacacionesTrabajador::query();
        
        // Aplicar filtros a las estadísticas
        if ($request->filled('fecha_inicio_desde')) {
            $statsQuery->whereDate('fecha_inicio', '>=', $request->fecha_inicio_desde);
        }
        if ($request->filled('fecha_inicio_hasta')) {
            $statsQuery->whereDate('fecha_inicio', '<=', $request->fecha_inicio_hasta);
        }
        if ($request->filled('año_correspondiente')) {
            $statsQuery->where('año_correspondiente', $request->año_correspondiente);
        }
        
        $stats = [
            'activas' => (clone $statsQuery)->where('estado', 'activa')->count(),
            'pendientes' => (clone $statsQuery)->where('estado', 'pendiente')->count(),
            'finalizadas' => (clone $statsQuery)->where('estado', 'finalizada')->count(),
            'canceladas' => (clone $statsQuery)->where('estado', 'cancelada')->count(),
            'total' => (clone $statsQuery)->whereIn('estado', ['activa', 'pendiente', 'finalizada', 'cancelada'])->count(),
            'trabajadores_con_vacaciones' => $trabajadores->total()
        ];
        
        // Obtener áreas para el filtro
        $areas = Area::orderBy('nombre_area')->get();
        
        // Obtener años disponibles
        $añosDisponibles = VacacionesTrabajador::select('año_correspondiente')
            ->distinct()
            ->orderBy('año_correspondiente', 'desc')
            ->pluck('año_correspondiente');
        
        // Obtener periodos disponibles
        $periodosDisponibles = VacacionesTrabajador::select('periodo_vacacional')
            ->distinct()
            ->orderBy('periodo_vacacional', 'desc')
            ->pluck('periodo_vacacional');
        
        return view('trabajadores.estatus.vacaciones_lista', compact(
            'trabajadores',
            'stats',
            'areas',
            'añosDisponibles',
            'periodosDisponibles'
        ));
    }
    // En VacacionesController.php - agregar este método
    public function eliminar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        try {
            // Verificar que la vacación pertenece al trabajador
            if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no válida para este trabajador'
                ], 403);
            }

            // Obtener documentos asociados antes de eliminar
            $documentosIds = $vacacion->documentos->pluck('id')->toArray();
            
            // Eliminar la vacación (esto activará cualquier evento de eliminación)
            $vacacion->delete();
            
            // Eliminar documentos asociados si existen
            if (!empty($documentosIds)) {
                foreach ($documentosIds as $documentoId) {
                    $documento = DocumentoVacaciones::find($documentoId);
                    if ($documento) {
                        // Verificar si el documento está asociado a otras vacaciones
                        if ($documento->vacaciones()->count() === 0) {
                            $documento->eliminarArchivo();
                            $documento->delete();
                        } else {
                            // Solo eliminar la relación con esta vacación
                            $documento->vacaciones()->detach($vacacion->id_vacacion);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Vacación y documentos asociados eliminados correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error("Error eliminando vacación", [
                'vacacion_id' => $vacacion->id_vacacion,
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la vacación: ' . $e->getMessage()
            ], 500);
        }
    }
}