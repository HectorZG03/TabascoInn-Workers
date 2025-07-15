<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\DocumentoVacaciones;
use App\Models\VacacionesTrabajador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DocumentosVacacionesController extends Controller
{
    /**
     * Mostrar la vista principal de documentos de vacaciones
     */
    public function index(Trabajador $trabajador)
    {
        $trabajador->load([
            'documentosVacaciones.vacaciones',
            'vacacionesPendientes'
        ]);

        return view('trabajadores.documentos_vacaciones.index', compact('trabajador'));
    }

    /**
     * Generar y descargar PDF de amortización de vacaciones
     */
    public function descargarPDF(Trabajador $trabajador)
    {
        try {
            // Obtener vacaciones pendientes
            $vacacionesPendientes = $trabajador->vacacionesPendientes()
                ->orderBy('fecha_inicio', 'asc')
                ->get();

            if ($vacacionesPendientes->isEmpty()) {
                return redirect()->back()->with('error', 'No hay vacaciones pendientes para generar documento');
            }

            // Generar PDF
            $pdf = $this->generarPDFAmortizacion($trabajador, $vacacionesPendientes);

            // Nombre del archivo
            $nombreArchivo = $this->generarNombreArchivo($trabajador);

            // Descargar directamente (no guardar en BD)
            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Subir documento firmado y asociar con vacaciones
     */
    public function subirDocumento(Request $request, Trabajador $trabajador): JsonResponse
    {
        try {
            // Validar entrada
            $validator = $this->validarSubidaDocumento($request);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $datos = $validator->validated();
            $archivo = $request->file('documento');

            // Validar archivo
            $erroresArchivo = DocumentoVacaciones::validarArchivo($archivo);
            if (!empty($erroresArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo inválido',
                    'errors' => ['documento' => $erroresArchivo]
                ], 422);
            }

            // Validar que las vacaciones existan y sean del trabajador
            $vacacionesIds = $datos['vacaciones_ids'];
            $vacaciones = VacacionesTrabajador::whereIn('id_vacacion', $vacacionesIds)
                ->where('id_trabajador', $trabajador->id_trabajador)
                ->where('estado', 'pendiente')
                ->get();

            if ($vacaciones->count() !== count($vacacionesIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Una o más vacaciones no son válidas'
                ], 422);
            }

            // Guardar archivo
            $rutaArchivo = DocumentoVacaciones::generarRutaArchivo(
                $trabajador->id_trabajador,
                $archivo->getClientOriginalName()
            );

            if (!$archivo->storeAs('public', $rutaArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el archivo'
                ], 500);
            }

            // Crear registro en BD
            $documento = DocumentoVacaciones::create([
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta' => $rutaArchivo
            ]);

            // Asociar con vacaciones
            $documento->vacaciones()->attach($vacacionesIds);

            // Actualizar vacaciones como justificadas
            VacacionesTrabajador::whereIn('id_vacacion', $vacacionesIds)
                ->update(['justificada_por_documento' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido y asociado correctamente',
                'documento' => $documento->load('vacaciones')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de documentos de vacaciones
     */
    public function obtenerDocumentos(Trabajador $trabajador): JsonResponse
    {
        try {
            $documentos = $trabajador->documentosVacaciones()
                ->with('vacaciones')
                ->get()
                ->map(function ($documento) {
                    return [
                        'id' => $documento->id,
                        'nombre_original' => $documento->nombre_original,
                        'tamaño' => $documento->tamaño,
                        'url' => $documento->url,
                        'created_at' => $documento->created_at->format('d/m/Y H:i'),
                        'vacaciones_asociadas' => $documento->vacaciones->count(),
                        'vacaciones' => $documento->vacaciones->map(function ($vacacion) {
                            return [
                                'id' => $vacacion->id_vacacion,
                                'dias_solicitados' => $vacacion->dias_solicitados,
                                'fecha_inicio' => $vacacion->fecha_inicio->format('d/m/Y'),
                                'fecha_fin' => $vacacion->fecha_fin->format('d/m/Y'),
                                'estado' => $vacacion->estado
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'documentos' => $documentos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento de vacaciones
     */
    public function eliminarDocumento(Trabajador $trabajador, DocumentoVacaciones $documento): JsonResponse
    {
        try {
            // Verificar que el documento pertenece al trabajador
            if ($documento->trabajador_id !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no válido para este trabajador'
                ], 403);
            }

            // Obtener vacaciones asociadas antes de eliminar
            $vacacionesIds = $documento->vacaciones->pluck('id_vacacion')->toArray();

            // Eliminar archivo físico
            $documento->eliminarArchivo();

            // Eliminar registro de BD (esto también elimina las relaciones pivot)
            $documento->delete();

            // Actualizar vacaciones como no justificadas
            VacacionesTrabajador::whereIn('id_vacacion', $vacacionesIds)
                ->update(['justificada_por_documento' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS
    // ===============================================

    /**
     * Generar PDF de amortización de vacaciones
     */
    private function generarPDFAmortizacion(Trabajador $trabajador, $vacacionesPendientes)
    {
        $datos = [
            'trabajador' => $trabajador,
            'vacaciones' => $vacacionesPendientes,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'año_actual' => Carbon::now()->year,
            'total_dias' => $vacacionesPendientes->sum('dias_solicitados')
        ];

        return Pdf::loadView('trabajadores.documentos_vacaciones.pdf_amortizacion', $datos)
                  ->setPaper('a4', 'portrait');
    }

    /**
     * Generar nombre de archivo
     */
    private function generarNombreArchivo(Trabajador $trabajador): string
    {
        $nombreLimpio = str_replace(' ', '_', $trabajador->nombre_completo);
        $fecha = Carbon::now()->format('Y-m-d');
        
        return "Amortizacion_Vacaciones_{$nombreLimpio}_{$fecha}.pdf";
    }

    /**
     * Validador para subida de documentos
     */
    private function validarSubidaDocumento(Request $request): \Illuminate\Validation\Validator
    {
        $reglas = [
            'documento' => 'required|file|mimes:pdf|max:2048',
            'vacaciones_ids' => 'required|array|min:1',
            'vacaciones_ids.*' => 'exists:vacaciones_trabajadores,id_vacacion'
        ];

        $mensajes = [
            'documento.required' => 'El documento es obligatorio',
            'documento.file' => 'Debe ser un archivo válido',
            'documento.mimes' => 'Solo se permiten archivos PDF',
            'documento.max' => 'El archivo no puede ser mayor a 2MB',
            'vacaciones_ids.required' => 'Debe seleccionar al menos una vacación',
            'vacaciones_ids.array' => 'Las vacaciones deben ser un arreglo',
            'vacaciones_ids.min' => 'Debe seleccionar al menos una vacación',
            'vacaciones_ids.*.exists' => 'Una o más vacaciones no son válidas'
        ];

        return Validator::make($request->all(), $reglas, $mensajes);
    }
}