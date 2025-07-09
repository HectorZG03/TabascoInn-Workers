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
    /**
     * Vista principal de vacaciones del trabajador
     */
    public function show(Trabajador $trabajador): View
    {
        $trabajador->load([
            'vacaciones.creadoPor:id,nombre',
            'fichaTecnica.categoria.area'
        ]);

        $estadisticas = $trabajador->getEstadisticasVacaciones();

        return view('trabajadores.secciones_perfil.vacaciones', compact('trabajador', 'estadisticas'));
    }

/**
     * API: Obtener vacaciones del trabajador - CON FECHAS FORMATEADAS
     */
    public function index(Trabajador $trabajador): JsonResponse
    {
        try {
            $vacaciones = $trabajador->vacaciones()
                ->with(['creadoPor:id,nombre'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($vacacion) {
                    // Formatear fechas para evitar problemas de timezone en JS
                    $vacacion->fecha_inicio_formatted = $vacacion->fecha_inicio ? 
                        $vacacion->fecha_inicio->format('d/m/Y') : null;
                    $vacacion->fecha_fin_formatted = $vacacion->fecha_fin ? 
                        $vacacion->fecha_fin->format('d/m/Y') : null;
                    $vacacion->fecha_reintegro_formatted = $vacacion->fecha_reintegro ? 
                        $vacacion->fecha_reintegro->format('d/m/Y') : null;
                    
                    // Mantener las fechas originales para el JS que las necesite
                    return $vacacion;
                });

            $estadisticas = $trabajador->getEstadisticasVacaciones();

            return response()->json([
                'success' => true,
                'vacaciones' => $vacaciones,
                'estadisticas' => $estadisticas,
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
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar nuevas vacaciones al trabajador
     */
    public function store(Request $request, Trabajador $trabajador): JsonResponse
    {
        try {
            // Validar entrada
            $validator = $this->validarVacaciones($request, $trabajador);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $datos = $validator->validated();

            // Validaciones de negocio
            $erroresNegocio = $trabajador->puedeAsignarVacaciones($datos);
            
            if (!empty($erroresNegocio)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden asignar las vacaciones',
                    'errors' => ['general' => $erroresNegocio]
                ], 422);
            }

            // Crear vacaciones
            $vacacion = $trabajador->asignarVacaciones($datos, Auth::id());

            // Si la fecha de inicio es hoy, iniciar automáticamente
            if (Carbon::parse($datos['fecha_inicio'])->isToday() && 
                !$trabajador->tieneVacacionesActivas()) {
                $vacacion->iniciar(Auth::id());
            }

            return response()->json([
                'success' => true,
                'message' => 'Vacaciones asignadas correctamente',
                'vacacion' => $vacacion->load('creadoPor:id,nombre'),
                'trabajador_estatus' => $trabajador->fresh()->estatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar vacaciones pendientes
     */
    public function iniciar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        try {
            if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no válida para este trabajador'
                ], 403);
            }

            if ($vacacion->iniciar(Auth::id())) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vacaciones iniciadas correctamente',
                    'vacacion' => $vacacion->fresh(),
                    'trabajador_estatus' => $trabajador->fresh()->estatus
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden iniciar estas vacaciones'
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar vacaciones activas
     */
    public function finalizar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        try {
            if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no válida para este trabajador'
                ], 403);
            }

            $motivo = $request->input('motivo_finalizacion');

            if ($vacacion->finalizar($motivo, Auth::id())) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vacaciones finalizadas correctamente',
                    'vacacion' => $vacacion->fresh(),
                    'trabajador_estatus' => $trabajador->fresh()->estatus
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden finalizar estas vacaciones'
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar vacaciones pendientes
     */
    public function cancelar(Request $request, Trabajador $trabajador, VacacionesTrabajador $vacacion): JsonResponse
    {
        try {
            if ($vacacion->id_trabajador !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no válida para este trabajador'
                ], 403);
            }

            if (!$vacacion->esPendiente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar vacaciones pendientes'
                ], 422);
            }

            $motivo = $request->input('motivo_cancelacion', 'Cancelada por usuario');
            
            $vacacion->update([
                'estado' => 'finalizada',
                'motivo_finalizacion' => 'CANCELADA: ' . $motivo,
                'dias_disfrutados' => 0,
                'dias_restantes' => $vacacion->dias_solicitados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vacaciones canceladas correctamente',
                'vacacion' => $vacacion->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar vacaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular días de vacaciones para un trabajador
     */
    public function calcularDias(Trabajador $trabajador): JsonResponse
    {
        try {
            $diasCorrespondientes = $trabajador->dias_vacaciones_correspondientes;
            $diasRestantes = $trabajador->dias_vacaciones_restantes_este_año;
            
            return response()->json([
                'success' => true,
                'dias_correspondientes' => $diasCorrespondientes,
                'dias_restantes' => $diasRestantes,
                'antiguedad' => $trabajador->antiguedad,
                'puede_tomar_vacaciones' => $trabajador->puedeTomarVacaciones()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular días: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validador para vacaciones - SIMPLIFICADO
     */
    private function validarVacaciones(Request $request, Trabajador $trabajador): \Illuminate\Validation\Validator
    {
        $reglas = [
            'dias_solicitados' => [
                'required',
                'integer',
                'min:1',
                'max:' . $trabajador->dias_vacaciones_restantes_este_año
            ],
            'fecha_inicio' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio'
            ],
            'año_correspondiente' => [
                'nullable',
                'integer',
                'min:' . (Carbon::now()->year - 1),
                'max:' . (Carbon::now()->year + 1)
            ],
            'observaciones' => 'nullable|string|max:500'
        ];

        $mensajes = [
            'dias_solicitados.required' => 'Los días solicitados son obligatorios',
            'dias_solicitados.min' => 'Debe solicitar al menos 1 día',
            'dias_solicitados.max' => 'Excede los días disponibles',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser en el pasado',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior al inicio',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ];

        return Validator::make($request->all(), $reglas, $mensajes);
    }
}