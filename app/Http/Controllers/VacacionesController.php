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

    public function store(Request $request, Trabajador $trabajador): JsonResponse
    {
        try {
            $validator = $this->validarVacaciones($request, $trabajador);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
            }

            $datos = $validator->validated();

            // ✅ RECALCULAR FECHA FIN CON DÍAS LABORABLES
            if ($fichaTecnica = $trabajador->fichaTecnica) {
                $fechaFinCalculada = $fichaTecnica->calcularFechaFinVacaciones($datos['fecha_inicio'], $datos['dias_solicitados']);
                if ($fechaFinCalculada) {
                    $datos['fecha_fin'] = $fechaFinCalculada->format('Y-m-d');
                }
            }

            // ✅ VALIDACIONES DE NEGOCIO SIMPLIFICADAS
            $erroresNegocio = $trabajador->puedeAsignarVacaciones($datos);
            if (!empty($erroresNegocio)) {
                return response()->json(['success' => false, 'message' => 'No se pueden asignar las vacaciones', 'errors' => ['general' => $erroresNegocio]], 422);
            }

            $vacacion = $trabajador->asignarVacaciones($datos, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Vacaciones asignadas correctamente',
                'vacacion' => $vacacion->load('creadoPor:id,nombre'),
                'trabajador_estatus' => $trabajador->fresh()->estatus,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al asignar vacaciones: ' . $e->getMessage()], 500);
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

    public function calcularFechasVacaciones(Request $request, Trabajador $trabajador): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'dias_solicitados' => 'required|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $diasSolicitados = (int) $request->input('dias_solicitados');

        if ($diasSolicitados > $trabajador->dias_vacaciones_restantes_este_año) {
            return response()->json(['success' => false, 'message' => "Días solicitados exceden los disponibles"], 422);
        }

        $fichaTecnica = $trabajador->fichaTecnica;
        if (!$fichaTecnica) {
            return response()->json(['success' => false, 'message' => 'El trabajador no tiene ficha técnica configurada'], 422);
        }

        $resumen = $fichaTecnica->getResumenVacaciones($fechaInicio, $diasSolicitados);
        if (!$resumen) {
            return response()->json(['success' => false, 'message' => 'Error al calcular las fechas de vacación'], 422);
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

    // ✅ VALIDADOR SIMPLIFICADO
    private function validarVacaciones(Request $request, Trabajador $trabajador): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'dias_solicitados' => ['required', 'integer', 'min:1', 'max:' . $trabajador->dias_vacaciones_restantes_este_año],
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'año_correspondiente' => ['nullable', 'integer', 'min:' . (Carbon::now()->year - 1), 'max:' . (Carbon::now()->year + 1)],
            'observaciones' => 'nullable|string|max:500'
        ]);
    }
}