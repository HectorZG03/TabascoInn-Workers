<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\DocumentoVacaciones;
use App\Models\VacacionesTrabajador;
use App\Models\Gerente;
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

        // Obtener TODOS los gerentes activos para selección libre
        $gerentes = Gerente::activos()
            ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'cargo')
            ->orderBy('cargo')
            ->orderBy('apellido_paterno')
            ->get()
            ->map(function ($gerente) {
                return [
                    'id' => $gerente->id,
                    'nombre_completo' => $gerente->nombre_completo,
                    'cargo' => $gerente->cargo,
                    'para_firma' => $gerente->nombre_completo_con_cargo
                ];
            });

        return view('trabajadores.documentos_vacaciones.index', [
            'trabajador' => $trabajador,
            'vacacionesPendientesSinDocumento' => $vacacionesPendientesSinDocumento,
            'gerentes' => $gerentes
        ]);
    }

    /**
     * Mostrar modal de selección de firmas
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

        // Obtener TODOS los gerentes activos para selección libre
        $gerentes = Gerente::activos()
            ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'cargo')
            ->orderBy('cargo')
            ->orderBy('apellido_paterno')
            ->get()
            ->map(function ($gerente) {
                return [
                    'id' => $gerente->id,
                    'nombre_completo' => $gerente->nombre_completo,
                    'cargo' => $gerente->cargo,
                    'para_firma' => $gerente->nombre_completo_con_cargo
                ];
            });

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
     * Generar y descargar PDF con 3 gerentes seleccionados
     */
    public function descargarPDF(Request $request, Trabajador $trabajador)
    {
        try {
            // Validar que se seleccionen exactamente 3 gerentes
            $validator = Validator::make($request->all(), [
                'gerente_1' => 'required|exists:gerentes,id',
                'gerente_2' => 'required|exists:gerentes,id|different:gerente_1',
                'gerente_3' => 'required|exists:gerentes,id|different:gerente_1,gerente_2',
            ], [
                'gerente_1.required' => 'Debe seleccionar el primer gerente',
                'gerente_1.exists' => 'El primer gerente seleccionado no es válido',
                'gerente_2.required' => 'Debe seleccionar el segundo gerente',
                'gerente_2.exists' => 'El segundo gerente seleccionado no es válido',
                'gerente_2.different' => 'El segundo gerente debe ser diferente al primero',
                'gerente_3.required' => 'Debe seleccionar el tercer gerente',
                'gerente_3.exists' => 'El tercer gerente seleccionado no es válido',
                'gerente_3.different' => 'El tercer gerente debe ser diferente a los otros dos',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar 3 gerentes diferentes',
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

            // Obtener los 3 gerentes seleccionados
            $gerente1 = Gerente::findOrFail($request->gerente_1);
            $gerente2 = Gerente::findOrFail($request->gerente_2);
            $gerente3 = Gerente::findOrFail($request->gerente_3);

            // Generar PDF con las 3 firmas seleccionadas
            $pdf = $this->generarPDFAmortizacion($trabajador, $vacacionesPendientes, [
                'gerente_1' => $gerente1,
                'gerente_2' => $gerente2,
                'gerente_3' => $gerente3
            ]);

            // Nombre del archivo
            $nombreArchivo = $this->generarNombreArchivo($trabajador);

            // Retornar PDF para descarga
            return response()->json([
                'success' => true,
                'message' => 'PDF generado correctamente',
                'download_url' => route('trabajadores.documentos-vacaciones.descargar-pdf-directo', [
                    'trabajador' => $trabajador->id_trabajador,
                    'gerente_1' => $request->gerente_1,
                    'gerente_2' => $request->gerente_2,
                    'gerente_3' => $request->gerente_3
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
            // Validar parámetros requeridos
            $request->validate([
                'gerente_1' => 'required|exists:gerentes,id',
                'gerente_2' => 'required|exists:gerentes,id|different:gerente_1',
                'gerente_3' => 'required|exists:gerentes,id|different:gerente_1,gerente_2',
            ]);

            // Obtener los 3 gerentes
            $gerente1 = Gerente::findOrFail($request->gerente_1);
            $gerente2 = Gerente::findOrFail($request->gerente_2);
            $gerente3 = Gerente::findOrFail($request->gerente_3);

            $vacacionesPendientes = $trabajador->vacacionesPendientes()
                ->orderBy('fecha_inicio', 'asc')
                ->get();

            if ($vacacionesPendientes->isEmpty()) {
                return redirect()->back()->with('error', 'No hay vacaciones pendientes para generar documento');
            }

            $pdf = $this->generarPDFAmortizacion($trabajador, $vacacionesPendientes, [
                'gerente_1' => $gerente1,
                'gerente_2' => $gerente2,
                'gerente_3' => $gerente3
            ]);

            $nombreArchivo = $this->generarNombreArchivo($trabajador);

            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el documento: ' . $e->getMessage());
        }
    }

    // ... resto de métodos sin cambios hasta generarPDFAmortizacion

    private function generarPDFAmortizacion(Trabajador $trabajador, $vacacionesPendientes, $firmas = null)
    {
        // Convertir imagen logo a base64
        $imagenPath = public_path('image/estaticas/images.png');
        $imagenBase64 = null;
        
        if (file_exists($imagenPath)) {
            $imagenData = file_get_contents($imagenPath);
            $imagenBase64 = 'data:image/png;base64,' . base64_encode($imagenData);
        }

        // Convertir imagen de marca de agua a base64
        $watermarkPath = public_path('image/estaticas/watermark.jpg');
        $watermarkBase64 = null;
        
        if (file_exists($watermarkPath)) {
            $watermarkData = file_get_contents($watermarkPath);
            $watermarkBase64 = 'data:image/jpeg;base64,' . base64_encode($watermarkData);
        }

        // Obtener periodo vacacional
        $primerVacacion = $vacacionesPendientes->first();
        $periodoVacacional = $primerVacacion->periodo_vacacional ?? 
                            (Carbon::now()->year - 1) . '-' . Carbon::now()->year;

        // Calcular días disfrutados del mismo periodo
        $todasVacacionesPeriodo = $trabajador->vacaciones()
            ->where('periodo_vacacional', $periodoVacacional)
            ->whereIn('estado', ['pendiente', 'activa', 'finalizada'])
            ->orderBy('created_at', 'asc')
            ->get();

        $diasDisfrutados = 0;
        $diasSolicitadosAcumulados = 0;
        
        $idsPendientes = $vacacionesPendientes->pluck('id_vacacion')->toArray();
        
        foreach ($todasVacacionesPeriodo as $vacacion) {
            if (!in_array($vacacion->id_vacacion, $idsPendientes)) {
                $diasDisfrutados += $vacacion->dias_solicitados;
            }
            $diasSolicitadosAcumulados += $vacacion->dias_solicitados;
        }

        // Obtener días correspondientes según antigüedad
        $diasCorrespondientes = $primerVacacion->dias_correspondientes ?? 
                            $trabajador->dias_vacaciones_correspondientes;

        // Calcular días pendientes del periodo
        $diasPendientesPeriodo = $diasCorrespondientes - $diasSolicitadosAcumulados;
        $diasPendientesPeriodo = max(0, $diasPendientesPeriodo);

        // Calcular total de días solicitados
        $totalDiasSolicitados = $vacacionesPendientes->sum('dias_solicitados');
        
        // Obtener días de descanso del trabajador
        $diasDescanso = $this->obtenerDiasDescansoTrabajador($trabajador);

        $datos = [
            'trabajador' => $trabajador,
            'vacaciones' => $vacacionesPendientes,
            'fecha_generacion' => Carbon::now()->format('d/m/Y'),
            'año_actual' => Carbon::now()->year,
            'periodo_vacacional' => $periodoVacacional,
            'total_dias' => $totalDiasSolicitados,
            'dias_correspondientes' => $diasCorrespondientes,
            'dias_disfrutados' => $diasDisfrutados,
            'dias_pendientes' => $diasPendientesPeriodo,
            'dias_descanso' => $diasDescanso,
            'firmas' => $firmas,
            'imagen_empresa' => $imagenBase64,
            'watermark_empresa' => $watermarkBase64
        ];

        return Pdf::loadView('trabajadores.documentos_vacaciones.pdf_amortizacion', $datos)
                ->setPaper('a4', 'portrait');
    }

    private function obtenerDiasDescansoTrabajador(Trabajador $trabajador): string
    {
        try {
            if (!$trabajador->fichaTecnica) {
                Log::warning("Trabajador sin ficha técnica", [
                    'trabajador_id' => $trabajador->id_trabajador,
                    'nombre' => $trabajador->nombre_completo
                ]);
                return 'Domingo';
            }
            
            $fichaTecnica = $trabajador->fichaTecnica;
            $diasDescansoArray = $fichaTecnica->dias_descanso;
            
            if (empty($diasDescansoArray) || !is_array($diasDescansoArray)) {
                if (method_exists($fichaTecnica, 'getDiasDescansoTextoAttribute')) {
                    $texto = $fichaTecnica->dias_descanso_texto;
                    if ($texto && $texto !== 'No especificado') {
                        return $texto;
                    }
                }
                return 'Domingo';
            }
            
            $diasSemana = [
                'lunes' => 'Lunes',
                'martes' => 'Martes',
                'miercoles' => 'Miércoles',
                'jueves' => 'Jueves',
                'viernes' => 'Viernes',
                'sabado' => 'Sábado',
                'domingo' => 'Domingo'
            ];
            
            $diasFormateados = [];
            foreach ($diasDescansoArray as $dia) {
                $diaLower = strtolower($dia);
                $diasFormateados[] = $diasSemana[$diaLower] ?? ucfirst($dia);
            }
            
            if (count($diasFormateados) == 0) {
                return 'Sin días de descanso asignados';
            } else if (count($diasFormateados) == 1) {
                return $diasFormateados[0];
            } else if (count($diasFormateados) == 2) {
                return implode(' y ', $diasFormateados);
            } else {
                $ultimo = array_pop($diasFormateados);
                return implode(', ', $diasFormateados) . ' y ' . $ultimo;
            }
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo días de descanso", [
                'trabajador_id' => $trabajador->id_trabajador ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            
            return 'Domingo';
        }
    }

    // ... resto de métodos sin cambios (subirDocumento, obtenerDocumentos, etc.)

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

            try {
                $rutaCompleta = Storage::disk('public')->putFileAs(
                    'vacaciones/documentos/trabajador_' . $trabajador->id_trabajador,
                    $archivo,
                    basename($rutaArchivo)
                );

                if (!$rutaCompleta) {
                    throw new \Exception('Error al guardar el archivo en storage');
                }

                if (!Storage::disk('public')->exists($rutaCompleta)) {
                    throw new \Exception('El archivo no se guardó correctamente');
                }

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

            // Crear registro en BD
            $documento = DocumentoVacaciones::create([
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta' => $rutaCompleta
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

    public function obtenerDocumentos(Trabajador $trabajador): JsonResponse
    {
        try {
            $documentos = $trabajador->documentosVacaciones()
                ->with('vacaciones')
                ->get()
                ->map(function ($documento) {
                    $problemas = $documento->verificarIntegridad();
                    $existe = $documento->existe();
                    
                    return [
                        'id' => $documento->id,
                        'nombre_original' => $documento->nombre_original,
                        'tamaño' => $existe ? $documento->tamaño : 'Archivo no encontrado',
                        'url' => $existe ? $documento->url : '#',
                        'created_at' => $documento->created_at->format('d/m/Y H:i'),
                        'vacaciones_asociadas' => $documento->vacaciones->count(),
                        'existe_archivo' => $existe,
                        'problemas' => $problemas,
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

    public function eliminarDocumento(Trabajador $trabajador, DocumentoVacaciones $documento): JsonResponse
    {
        try {
            if ($documento->trabajador_id !== $trabajador->id_trabajador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no válido para este trabajador'
                ], 403);
            }

            $vacacionesIds = $documento->vacaciones->pluck('id_vacacion')->toArray();
            $documento->eliminarArchivo();
            $documento->delete();

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

    private function generarNombreArchivo(Trabajador $trabajador): string
    {
        $nombreLimpio = str_replace(' ', '_', $trabajador->nombre_completo);
        $fecha = Carbon::now()->format('Y-m-d');
        
        return "Amortizacion_Vacaciones_{$nombreLimpio}_{$fecha}.pdf";
    }

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