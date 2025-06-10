<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContratoController extends Controller
{
    /**
     * ✅ MEJORADO: Generar preview del contrato - CON todos los campos necesarios
     */
    public function generarPreview(Request $request)
    {
        $request->validate([
            // ✅ DATOS DEL TRABAJADOR - Incluir TODOS los campos que usa la vista
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'nullable|string|max:255',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'telefono' => 'nullable|string|max:15',
            'correo' => 'nullable|email|max:100',
            'no_nss' => 'nullable|string|max:20',
            
            // ✅ NUEVOS: Campos de ubicación
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            
            // ✅ NUEVOS: Campos para ficha técnica temporal
            'sueldo_diarios' => 'nullable|numeric|min:0',
            'categoria_nombre' => 'nullable|string|max:100',
            'area_nombre' => 'nullable|string|max:100',
            'horas_trabajo' => 'nullable|numeric|min:1|max:24',
            'turno' => 'nullable|in:diurno,nocturno,mixto',
            
            // ✅ DATOS DEL CONTRATO
            'fecha_inicio_contrato' => 'required|date|after_or_equal:today',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
        ]);

        try {
            // ✅ MEJORADO: Crear objeto trabajador temporal CON TODOS LOS CAMPOS
            $trabajadorTemp = (object) [
                'nombre_trabajador' => $request->nombre_trabajador,
                'ape_pat' => $request->ape_pat,
                'ape_mat' => $request->ape_mat,
                'nombre_completo' => trim($request->nombre_trabajador . ' ' . $request->ape_pat . ' ' . ($request->ape_mat ?? '')),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'direccion' => $request->direccion,
                'curp' => $request->curp,
                'rfc' => $request->rfc,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'no_nss' => $request->no_nss,
                
                // ✅ NUEVOS: Campos de ubicación que usa la vista
                'lugar_nacimiento' => $request->lugar_nacimiento,
                'estado_actual' => $request->estado_actual,
                'ciudad_actual' => $request->ciudad_actual,
                
                // ✅ MEJORADO: Objeto fichaTecnica temporal con datos reales
                'fichaTecnica' => (object) [
                    'categoria' => (object) [
                        'nombre_categoria' => $request->categoria_nombre ?? 'CATEGORÍA PREVIEW',
                        'area' => (object) [
                            'nombre_area' => $request->area_nombre ?? 'ÁREA PREVIEW'
                        ]
                    ],
                    'sueldo_diarios' => $request->sueldo_diarios ?? 0,
                    'horas_trabajo' => $request->horas_trabajo ?? 8,
                    'turno' => $request->turno ?? 'diurno',
                    'turno_texto' => $this->getTurnoTexto($request->turno ?? 'diurno')
                ]
            ];

            // Calcular duración automáticamente
            $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio_contrato);
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin_contrato);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $request->tipo_duracion);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($request->sueldo_diarios ?? 0);
            
            // ✅ GENERAR PDF temporal CON TODAS LAS VARIABLES
            $pdf = PDF::loadView('Formatos.contrato', [
                'trabajador' => $trabajadorTemp,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion),
                'salario_texto' => $salarioTexto // ✅ NUEVO: Salario en texto
            ]);

            // Guardar archivo temporal
            $hash = Str::random(32);
            $nombreArchivo = 'preview_contrato_' . $hash . '.pdf';
            $rutaTemporal = 'temp/contratos/' . $nombreArchivo;
            Storage::disk('public')->put($rutaTemporal, $pdf->output());

            Log::info('✅ Contrato preview generado', [
                'hash' => $hash,
                'trabajador' => $trabajadorTemp->nombre_completo,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion' => $duracionCalculada
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contrato generado exitosamente',
                'data' => [
                    'hash' => $hash,
                    'download_url' => route('ajax.contratos.preview.download', $hash),
                    'trabajador_nombre' => $trabajadorTemp->nombre_completo,
                    'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                    'fecha_fin' => $fechaFin->format('d/m/Y'),
                    'duracion' => $duracionCalculada,
                    'tipo_duracion' => $request->tipo_duracion,
                    'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error al generar contrato preview', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trabajador' => $request->nombre_trabajador . ' ' . $request->ape_pat
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MEJORADO: Generar contrato definitivo con carga de relaciones
     */
    public function generarDefinitivo($trabajador, $datosContrato)
    {
        try {
            // ✅ NUEVO: Cargar relaciones necesarias si no están cargadas
            if (!$trabajador->relationLoaded('fichaTecnica')) {
                $trabajador->load(['fichaTecnica.categoria.area']);
            }

            $fechaInicio = \Carbon\Carbon::parse($datosContrato['fecha_inicio_contrato']);
            $fechaFin = \Carbon\Carbon::parse($datosContrato['fecha_fin_contrato']);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $datosContrato['tipo_duracion']);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($trabajador->fichaTecnica->sueldo_diarios ?? 0);

            // Generar PDF final
            $pdf = PDF::loadView('formatos.contrato', [
                'trabajador' => $trabajador,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $datosContrato['tipo_duracion'],
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $datosContrato['tipo_duracion']),
                'salario_texto' => $salarioTexto // ✅ NUEVO: Salario en texto
            ]);

            // Guardar archivo definitivo
            $nombreArchivo = 'contrato_' . $trabajador->id_trabajador . '_' . time() . '.pdf';
            $ruta = 'contratos/' . $nombreArchivo;
            Storage::disk('public')->put($ruta, $pdf->output());

            // Crear registro en BD
            $contrato = ContratoTrabajador::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'fecha_inicio_contrato' => $fechaInicio,
                'fecha_fin_contrato' => $fechaFin,
                'tipo_duracion' => $datosContrato['tipo_duracion'],
                'duracion' => $duracionCalculada,
                'duracion_meses' => $datosContrato['tipo_duracion'] === 'meses' ? $duracionCalculada : null,
                'ruta_archivo' => $ruta
            ]);

            Log::info('✅ Contrato definitivo guardado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'duracion' => $duracionCalculada
            ]);

            return $contrato;

        } catch (\Exception $e) {
            Log::error('💥 Error al generar contrato definitivo', [
                'error' => $e->getMessage(),
                'trabajador_id' => $trabajador->id_trabajador ?? 'N/A'
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ MEJORADO: Generar contrato individual con carga de relaciones
     */
    public function generar(Request $request, Trabajador $trabajador)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo_duracion' => 'required|in:dias,meses',
        ]);

        try {
            // ✅ NUEVO: Cargar relaciones necesarias
            $trabajador->load(['fichaTecnica.categoria.area']);

            $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio);
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $request->tipo_duracion);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($trabajador->fichaTecnica->sueldo_diarios ?? 0);

            // Generar PDF
            $pdf = PDF::loadView('formatos.contrato', [
                'trabajador' => $trabajador,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion),
                'salario_texto' => $salarioTexto // ✅ NUEVO: Salario en texto
            ]);

            // Guardar archivo
            $nombreArchivo = 'contrato_'.$trabajador->id_trabajador.'_'.time().'.pdf';
            $ruta = 'contratos/'.$nombreArchivo;
            Storage::disk('public')->put($ruta, $pdf->output());

            // Almacenar en BD
            ContratoTrabajador::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'fecha_inicio_contrato' => $fechaInicio,
                'fecha_fin_contrato' => $fechaFin,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion' => $duracionCalculada,
                'duracion_meses' => $request->tipo_duracion === 'meses' ? $duracionCalculada : null,
                'ruta_archivo' => $ruta
            ]);

            return redirect()->route('trabajadores.show', $trabajador)
                ->with('success', 'Contrato generado y almacenado exitosamente');

        } catch (\Exception $e) {
            Log::error('💥 Error al generar contrato', [
                'error' => $e->getMessage(),
                'trabajador_id' => $trabajador->id_trabajador
            ]);

            return back()->withErrors(['error' => 'Error al generar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ NUEVO: Método para convertir número a texto (salario)
     */
    private function numeroATexto($numero)
    {
        if (!$numero || $numero == 0) {
            return 'CERO PESOS';
        }

        $unidades = [
            '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'
        ];

        $decenas = [
            '', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
        ];

        $centenas = [
            '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 
            'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
        ];

        $numero = intval($numero); // Convertir a entero (sin centavos por simplicidad)

        if ($numero < 20) {
            return $unidades[$numero] . ' PESOS';
        } elseif ($numero < 100) {
            $dec = intval($numero / 10);
            $uni = $numero % 10;
            return $decenas[$dec] . ($uni > 0 ? ' Y ' . $unidades[$uni] : '') . ' PESOS';
        } elseif ($numero < 1000) {
            $cen = intval($numero / 100);
            $resto = $numero % 100;
            $centena = ($numero == 100) ? 'CIEN' : $centenas[$cen];
            return $centena . ($resto > 0 ? ' ' . $this->numeroATexto($resto) : ' PESOS');
        }

        // Para números mayores, simplificar
        return number_format($numero, 2) . ' PESOS';
    }

    /**
     * ✅ NUEVO: Obtener texto del turno
     */
    private function getTurnoTexto($turno)
    {
        $turnos = [
            'diurno' => 'DIURNO',
            'nocturno' => 'NOCTURNO',
            'mixto' => 'MIXTO/ROTATIVO'
        ];

        return $turnos[$turno] ?? 'A ASIGNAR';
    }

    /**
     * ✅ MÉTODO CENTRALIZADO: Calcular duración
     */
    private function calcularDuracion(\Carbon\Carbon $fechaInicio, \Carbon\Carbon $fechaFin, string $tipo): int
    {
        if ($tipo === 'dias') {
            return $fechaInicio->diffInDays($fechaFin);
        } else {
            $duracion = $fechaInicio->diffInMonths($fechaFin);
            
            // Ajustar por días adicionales si no es exacto
            $fechaTemporal = $fechaInicio->copy()->addMonths($duracion);
            if ($fechaTemporal->lt($fechaFin)) {
                $duracion++;
            }
            
            return $duracion;
        }
    }

    /**
     * ✅ Formatea la duración para mostrar
     */
    private function formatearDuracion(int $cantidad, string $tipo): string
    {
        if ($tipo === 'dias') {
            return $cantidad . ' ' . ($cantidad === 1 ? 'día' : 'días');
        } else {
            return $cantidad . ' ' . ($cantidad === 1 ? 'mes' : 'meses');
        }
    }

    /**
     * ✅ Descargar contrato preview temporal
     */
    public function descargarPreview($hash)
    {
        try {
            $nombreArchivo = 'preview_contrato_' . $hash . '.pdf';
            $rutaTemporal = 'temp/contratos/' . $nombreArchivo;

            if (!Storage::disk('public')->exists($rutaTemporal)) {
                abort(404, 'Archivo de contrato no encontrado o expirado');
            }

            $rutaCompleta = Storage::disk('public')->path($rutaTemporal);
            
            if (!file_exists($rutaCompleta)) {
                abort(404, 'Archivo físico no encontrado');
            }

            return Response::download($rutaCompleta, 'Contrato_Preview.pdf', [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error al descargar contrato preview', [
                'error' => $e->getMessage(),
                'hash' => $hash
            ]);
            
            abort(500, 'Error al procesar la descarga del contrato');
        }
    }

    /**
     * ✅ Descarga un contrato existente
     */
    public function descargar(ContratoTrabajador $contrato)
    {
        try {
            if (!Storage::disk('public')->exists($contrato->ruta_archivo)) {
                abort(404, 'Archivo de contrato no encontrado');
            }

            $rutaCompleta = Storage::disk('public')->path($contrato->ruta_archivo);
            
            if (!file_exists($rutaCompleta)) {
                abort(404, 'Archivo físico no encontrado');
            }

            $nombreDescarga = 'Contrato_' . $contrato->trabajador->nombre_completo . '.pdf';
            
            return Response::download($rutaCompleta, $nombreDescarga, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error al descargar contrato', [
                'error' => $e->getMessage(),
                'contrato_id' => $contrato->id_contrato ?? 'N/A'
            ]);
            
            abort(500, 'Error al procesar la descarga del contrato');
        }
    }

    /**
     * ✅ Limpiar archivos temporales
     */
    public function limpiarArchivosTemporales()
    {
        try {
            $archivosTemporales = Storage::disk('public')->allFiles('temp/contratos');
            $archivosEliminados = 0;

            foreach ($archivosTemporales as $archivo) {
                if (Storage::disk('public')->lastModified($archivo) < now()->subHours(2)->timestamp) {
                    Storage::disk('public')->delete($archivo);
                    $archivosEliminados++;
                }
            }

            Log::info("🧹 Limpieza completada: {$archivosEliminados} archivos eliminados");
            return $archivosEliminados;

        } catch (\Exception $e) {
            Log::error('Error al limpiar archivos temporales', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * ✅ Formulario para crear contrato individual
     */
    public function crear(Trabajador $trabajador)
    {
        // ✅ NUEVO: Cargar relaciones necesarias
        $trabajador->load(['fichaTecnica.categoria.area']);
        
        return view('contratos.crear', compact('trabajador'));
    }
}