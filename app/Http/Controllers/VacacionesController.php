<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\VacacionesTrabajador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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

    // ✅ STORE REFACTORIZADO - Entrada manual de año y período
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

            // ✅ VALIDACIONES DE NEGOCIO FLEXIBLES (sin restricciones temporales)
            $erroresNegocio = $trabajador->puedeAsignarVacacionesFlexible($datos);
            if (!empty($erroresNegocio)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se pueden asignar las vacaciones', 
                    'errors' => ['general' => $erroresNegocio]
                ], 422);
            }

            $vacacion = $trabajador->asignarVacacionesRefactorizado($datos, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Vacaciones asignadas correctamente',
                'vacacion' => $vacacion->load('creadoPor:id,nombre'),
                'trabajador_estatus' => $trabajador->fresh()->estatus,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al asignar vacaciones: ' . $e->getMessage()
            ], 500);
        }
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
                'after:fecha_inicio'
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
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio'
        ]);
    }
}