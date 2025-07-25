<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\DocumentoVacaciones;
use App\Models\VacacionesTrabajador;
use App\Models\Gerente; // ✅ NUEVO IMPORT
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        // Cargar relaciones necesarias
        $trabajador->load('documentosVacaciones.vacaciones');

        // Obtener IDs de vacaciones que ya están ligadas a documentos
        $vacacionesConDocumento = DB::table('documento_vacacion_vacaciones')
            ->pluck('vacacion_id')
            ->toArray();

        // Obtener vacaciones pendientes filtrando las que NO tienen documento
        $vacacionesPendientesSinDocumento = $trabajador->vacacionesPendientes
            ->whereNotIn('id_vacacion', $vacacionesConDocumento);

        // Obtener gerentes para firmas (excluyendo Gerente General)
        $gerentes = Gerente::paraFirmasDocumentos();

        // Obtener Gerente General
        $gerenteGeneral = Gerente::getGerenteGeneral();

        if (!$gerenteGeneral) { 
            // Redirigir con mensaje flash
            return redirect()->route('gerentes.index') // Cambia por la ruta real de administración de gerentes
                ->with('error', 'Por favor ingrese registros en la administración de gerentes antes de continuar.');
        }

        return view('trabajadores.documentos_vacaciones.index', [
            'trabajador' => $trabajador,
            'vacacionesPendientesSinDocumento' => $vacacionesPendientesSinDocumento,
            'gerentes' => $gerentes,
            'gerenteGeneral' => $gerenteGeneral
        ]);
    }


    /**
     * ✅ NUEVA RUTA: Mostrar modal de selección de firmas
     */
    public function mostrarSeleccionFirmas(Trabajador $trabajador)
    {
        // Verificar que hay vacaciones pendientes
        $vacacionesPendientes = $trabajador->vacacionesPendientes()
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        if ($vacacionesPendientes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay vacaciones pendientes para generar documento'
            ], 422);
        }

        // Obtener gerentes activos para selección
        $gerentes = Gerente::paraFirmasDocumentos();

        return response()->json([
            'success' => true,
            'data' => [
                'trabajador' => [
                    'id' => $trabajador->id_trabajador,
                    'nombre_completo' => $trabajador->nombre_completo,
                    'categoria' => $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría'
                ],
                'vacaciones_pendientes' => $vacacionesPendientes->count(),
                'total_dias' => $vacacionesPendientes->sum('dias_solicitados'),
                'gerentes' => $gerentes,
                'usuario_actual' => [
                    'id' => Auth::id(),
                    'nombre' => Auth::user()->nombre,
                    'tipo' => Auth::user()->tipo
                ]
            ]
        ]);
    }

    /**
     * ✅ ACTUALIZADA: Generar y descargar PDF de amortización de vacaciones con firmas seleccionadas
     */
    public function descargarPDF(Request $request, Trabajador $trabajador)
    {
        try {
            // ✅ VALIDAR SOLO UNA SELECCIÓN DE GERENTE ADICIONAL
            $validator = Validator::make($request->all(), [
                'firma_gerente_id' => 'required|exists:gerentes,id',
            ], [
                'firma_gerente_id.required' => 'Debe seleccionar un gerente para firmar',
                'firma_gerente_id.exists' => 'El gerente seleccionado no es válido',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de firmas inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener vacaciones pendientes
            $vacacionesPendientes = $trabajador->vacacionesPendientes()
                ->orderBy('fecha_inicio', 'asc')
                ->get();

            if ($vacacionesPendientes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay vacaciones pendientes para generar documento'
                ], 422);
            }

            // ✅ OBTENER GERENTE GENERAL FIJO
            $gerenteGeneral = Gerente::where('cargo', 'Gerente General')
                ->where('activo', true)
                ->first();

            if (!$gerenteGeneral) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un Gerente General activo configurado en el sistema'
                ], 422);
            }

            // ✅ OBTENER GERENTE ADICIONAL SELECCIONADO
            $gerenteAdicional = Gerente::findOrFail($request->firma_gerente_id);
            $usuarioRecursosHumanos = Auth::user();

            // ✅ GENERAR PDF CON NUEVA ESTRUCTURA DE FIRMAS
            $pdf = $this->generarPDFAmortizacion($trabajador, $vacacionesPendientes, [
                'gerente_general' => $gerenteGeneral,
                'gerente_adicional' => $gerenteAdicional,
                'recursos_humanos' => $usuarioRecursosHumanos
            ]);

            // Nombre del archivo
            $nombreArchivo = $this->generarNombreArchivo($trabajador);

            // Retornar PDF para descarga
            return response()->json([
                'success' => true,
                'message' => 'PDF generado correctamente',
                'download_url' => route('trabajadores.documentos-vacaciones.descargar-pdf-directo', [
                    'trabajador' => $trabajador->id_trabajador,
                    'firma_gerente_id' => $request->firma_gerente_id
                ])
            ]);

        } catch (\Exception $e) {
            Log::error("Error generando PDF de amortización", [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarPDFDirecto(Request $request, Trabajador $trabajador)
    {
        try {
            // Validar parámetro requerido
            $request->validate([
                'firma_gerente_id' => 'required|exists:gerentes,id',
            ]);

            $gerenteAdicional = Gerente::findOrFail($request->firma_gerente_id);
            $gerenteGeneral = Gerente::where('cargo', 'Gerente General')->where('activo', true)->firstOrFail();
            $usuarioRecursosHumanos = Auth::user();

            $vacacionesPendientes = $trabajador->vacacionesPendientes()
                ->orderBy('fecha_inicio', 'asc')
                ->get();

            if ($vacacionesPendientes->isEmpty()) {
                return redirect()->back()->with('error', 'No hay vacaciones pendientes para generar documento');
            }

            $pdf = $this->generarPDFAmortizacion($trabajador, $vacacionesPendientes, [
                'gerente_general' => $gerenteGeneral,
                'gerente_adicional' => $gerenteAdicional,
                'recursos_humanos' => $usuarioRecursosHumanos,
            ]);

            $nombreArchivo = $this->generarNombreArchivo($trabajador);

            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el documento: ' . $e->getMessage());
        }
    }


    /**
     * Subir documento firmado y asociar con vacaciones - CORREGIDO
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

            // ✅ CORREGIR: Guardar archivo con creación de directorios
            $rutaArchivo = DocumentoVacaciones::generarRutaArchivo(
                $trabajador->id_trabajador,
                $archivo->getClientOriginalName()
            );

            // ✅ NUEVO: Crear directorio si no existe y guardar archivo
            try {
                // Usar Storage::putFileAs para mejor control
                $rutaCompleta = Storage::disk('public')->putFileAs(
                    'vacaciones/documentos/trabajador_' . $trabajador->id_trabajador,
                    $archivo,
                    basename($rutaArchivo)
                );

                if (!$rutaCompleta) {
                    throw new \Exception('Error al guardar el archivo en storage');
                }

                // Verificar que el archivo se guardó correctamente
                if (!Storage::disk('public')->exists($rutaCompleta)) {
                    throw new \Exception('El archivo no se guardó correctamente');
                }

                // Log para debugging
                Log::info("Archivo guardado exitosamente", [
                    'ruta_generada' => $rutaArchivo,
                    'ruta_guardada' => $rutaCompleta,
                    'existe' => Storage::disk('public')->exists($rutaCompleta),
                    'tamaño' => Storage::disk('public')->size($rutaCompleta)
                ]);

            } catch (\Exception $e) {
                Log::error("Error guardando archivo", [
                    'error' => $e->getMessage(),
                    'ruta' => $rutaArchivo,
                    'trabajador_id' => $trabajador->id_trabajador
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el archivo: ' . $e->getMessage()
                ], 500);
            }

            // Crear registro en BD con la ruta que realmente se guardó
            $documento = DocumentoVacaciones::create([
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta' => $rutaCompleta // ✅ Usar la ruta real guardada
            ]);

            // Asociar con vacaciones
            $documento->vacaciones()->attach($vacacionesIds);

            // Actualizar vacaciones como justificadas
            VacacionesTrabajador::whereIn('id_vacacion', $vacacionesIds)
                ->update(['justificada_por_documento' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido y asociado correctamente',
                'documento' => $documento->load('vacaciones'),
                'debug' => [
                    'ruta_guardada' => $rutaCompleta,
                    'existe_archivo' => Storage::disk('public')->exists($rutaCompleta)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error en subirDocumento", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de documentos de vacaciones - CORREGIDO
     */
    public function obtenerDocumentos(Trabajador $trabajador): JsonResponse
    {
        try {
            $documentos = $trabajador->documentosVacaciones()
                ->with('vacaciones')
                ->get()
                ->map(function ($documento) {
                    // ✅ Verificar integridad del documento
                    $problemas = $documento->verificarIntegridad();
                    $existe = $documento->existe();
                    
                    return [
                        'id' => $documento->id,
                        'nombre_original' => $documento->nombre_original,
                        'tamaño' => $existe ? $documento->tamaño : 'Archivo no encontrado',
                        'url' => $existe ? $documento->url : '#',
                        'created_at' => $documento->created_at->format('d/m/Y H:i'),
                        'vacaciones_asociadas' => $documento->vacaciones->count(),
                        'existe_archivo' => $existe, // ✅ Nuevo campo
                        'problemas' => $problemas, // ✅ Nuevo campo para debugging
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
                'documentos' => $documentos,
                'debug' => [
                    'total_documentos' => $documentos->count(),
                    'documentos_existentes' => $documentos->where('existe_archivo', true)->count(),
                    'documentos_faltantes' => $documentos->where('existe_archivo', false)->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error en obtenerDocumentos", [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
     * ✅ ACTUALIZADA: Generar PDF de amortización de vacaciones con firmas dinámicas, imagen logo y marca de agua
     */
    private function generarPDFAmortizacion(Trabajador $trabajador, $vacacionesPendientes, $firmas = null)
    {
        // ✅ CONVERTIR IMAGEN LOGO A BASE64 PARA DOMPDF
        $imagenPath = public_path('image/estaticas/images.png');
        $imagenBase64 = null;
        
        if (file_exists($imagenPath)) {
            $imagenData = file_get_contents($imagenPath);
            $imagenBase64 = 'data:image/png;base64,' . base64_encode($imagenData);
        }

        // ✅ NUEVO: CONVERTIR IMAGEN DE MARCA DE AGUA A BASE64
        $watermarkPath = public_path('image/estaticas/watermark.jpg');
        $watermarkBase64 = null;
        
        if (file_exists($watermarkPath)) {
            $watermarkData = file_get_contents($watermarkPath);
            $watermarkBase64 = 'data:image/jpeg;base64,' . base64_encode($watermarkData);
        }

        $datos = [
            'trabajador' => $trabajador,
            'vacaciones' => $vacacionesPendientes,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'año_actual' => Carbon::now()->year,
            'total_dias' => $vacacionesPendientes->sum('dias_solicitados'),
            'firmas' => $firmas, // ✅ PASAR FIRMAS AL PDF
            'imagen_empresa' => $imagenBase64, // ✅ Pasar imagen logo como base64
            'watermark_empresa' => $watermarkBase64 // ✅ NUEVO: Pasar marca de agua como base64
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