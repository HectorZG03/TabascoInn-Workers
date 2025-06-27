<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use App\Models\FichaTecnica;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ✅ OPTIMIZADO: Solo se encarga de GENERAR contratos y PDFs
 * No maneja CRUD ni descargas de contratos existentes
 */
class ContratoController extends Controller
{
    /**
     * ✅ GENERAR PREVIEW del contrato (sin crear registro en BD)
     */
    public function generarPreview(Request $request)
    {
        $request->validate([
            // Datos del trabajador
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'fecha_ingreso' => 'required|date',
            'direccion' => 'nullable|string|max:255',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'telefono' => 'nullable|string|max:15',
            'correo' => 'nullable|email|max:100',
            'no_nss' => 'nullable|string|max:20',
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            
            // Datos laborales
            'sueldo_diarios' => 'nullable|numeric|min:0',
            'categoria_nombre' => 'nullable|string|max:100',
            'area_nombre' => 'nullable|string|max:100',
            'horas_trabajo' => 'nullable|numeric|min:1|max:24',
            'horas_semanales' => 'nullable|numeric|min:1|max:168',
            'turno' => 'nullable|in:diurno,nocturno,mixto',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i',
            'formacion' => 'nullable|string|max:100',
            'grado_estudios' => 'nullable|string|max:100',
            'beneficiario_nombre' => 'nullable|string|max:100',
            'beneficiario_parentesco' => 'nullable|string|max:50',
            'dias_laborables' => 'nullable|array',
            'dias_laborables.*' => 'string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            
            // Datos del contrato
            'fecha_inicio_contrato' => 'required|date|after_or_equal:today',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
        ]);

        try {
            $trabajadorTemp = $this->crearTrabajadorTemporal($request);
            $datosContrato = $this->procesarDatosContrato($request);
            
            $pdf = $this->generarPDF($trabajadorTemp, $datosContrato);
            $hash = $this->guardarArchivoTemporal($pdf);

            Log::info('✅ Contrato preview generado', [
                'hash' => $hash,
                'trabajador' => $trabajadorTemp->nombre_completo,
                'duracion' => $datosContrato['duracion_texto']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contrato generado exitosamente',
                'data' => [
                    'hash' => $hash,
                    'download_url' => route('ajax.contratos.preview.download', $hash),
                    'trabajador_nombre' => $trabajadorTemp->nombre_completo,
                    'fecha_inicio' => $datosContrato['fecha_inicio']->format('d/m/Y'),
                    'fecha_fin' => $datosContrato['fecha_fin']->format('d/m/Y'),
                    'duracion_texto' => $datosContrato['duracion_texto']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error al generar contrato preview', [
                'error' => $e->getMessage(),
                'trabajador' => $request->nombre_trabajador . ' ' . $request->ape_pat
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ GENERAR CONTRATO DEFINITIVO (crea registro en BD)
     * Este método es llamado por AdminContratosController
     */
    public function generarDefinitivo(Trabajador $trabajador, array $datosContrato): ContratoTrabajador
    {
        try {
            // Cargar relaciones necesarias
            $trabajador->load(['fichaTecnica.categoria.area']);
            $this->completarDatosFichaTecnica($trabajador);

            $datosContratoProcesados = $this->procesarDatosContrato((object) $datosContrato);
            $pdf = $this->generarPDF($trabajador, $datosContratoProcesados);
            
            // Guardar archivo definitivo
            $nombreArchivo = 'contrato_' . $trabajador->id_trabajador . '_' . time() . '.pdf';
            $ruta = 'contratos/' . $nombreArchivo;
            Storage::disk('public')->put($ruta, $pdf->output());

            // Crear registro en BD
            $contrato = ContratoTrabajador::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'fecha_inicio_contrato' => $datosContratoProcesados['fecha_inicio'],
                'fecha_fin_contrato' => $datosContratoProcesados['fecha_fin'],
                'tipo_duracion' => $datosContratoProcesados['tipo_duracion'],
                'duracion' => $datosContratoProcesados['duracion'],
                'duracion_meses' => $datosContratoProcesados['tipo_duracion'] === 'meses' ? $datosContratoProcesados['duracion'] : null,
                'estatus' => ContratoTrabajador::ESTATUS_ACTIVO,
                'ruta_archivo' => $ruta
            ]);

            Log::info('✅ Contrato definitivo creado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador
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
     * ✅ DESCARGAR PREVIEW TEMPORAL
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
            
            return response()->download($rutaCompleta, 'Contrato_Preview.pdf', [
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
     * ✅ LIMPIAR ARCHIVOS TEMPORALES (método utilitario)
     */
    public function limpiarArchivosTemporales(): int
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

    // ========================================
    // MÉTODOS PRIVADOS DE PROCESAMIENTO
    // ========================================

    /**
     * ✅ Crear objeto trabajador temporal para preview
     */
    private function crearTrabajadorTemporal(Request $request): object
    {
        $diasLaborables = $request->dias_laborables ?? ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $todosLosDias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $diasDescanso = array_diff($todosLosDias, $diasLaborables);
        
        $horasPorDia = $request->horas_trabajo ?? 8;
        $horasSemanales = $request->horas_semanales ?? (count($diasLaborables) * $horasPorDia);
        
        return (object) [
            'nombre_trabajador' => $request->nombre_trabajador,
            'ape_pat' => $request->ape_pat,
            'ape_mat' => $request->ape_mat,
            'nombre_completo' => trim($request->nombre_trabajador . ' ' . $request->ape_pat . ' ' . ($request->ape_mat ?? '')),
            'fecha_nacimiento' => \Carbon\Carbon::parse($request->fecha_nacimiento),
            'fecha_ingreso' => \Carbon\Carbon::parse($request->fecha_ingreso),
            'direccion' => $request->direccion,
            'curp' => $request->curp,
            'rfc' => $request->rfc,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'no_nss' => $request->no_nss,
            'lugar_nacimiento' => $request->lugar_nacimiento,
            'estado_actual' => $request->estado_actual,
            'ciudad_actual' => $request->ciudad_actual,
            
            'fichaTecnica' => (object) [
                'categoria' => (object) [
                    'nombre_categoria' => $request->categoria_nombre ?? 'CATEGORÍA PREVIEW',
                    'area' => (object) [
                        'nombre_area' => $request->area_nombre ?? 'ÁREA PREVIEW'
                    ]
                ],
                'sueldo_diarios' => $request->sueldo_diarios ?? 0,
                'horas_trabajo' => $horasPorDia,
                'horas_semanales' => $horasSemanales,
                'horas_trabajadas_calculadas' => $horasPorDia,
                'horas_semanales_calculadas' => $horasSemanales,
                'turno' => $request->turno ?? 'diurno',
                'turno_calculado' => $request->turno ?? 'diurno',
                'turno_texto' => $this->getTurnoTexto($request->turno ?? 'diurno'),
                'hora_entrada' => $request->hora_entrada ?? '08:00',
                'hora_salida' => $request->hora_salida ?? '17:00',
                'formacion' => $request->formacion ?? 'No Especificada',
                'grado_estudios' => $request->grado_estudios ?? 'No Especificado',
                'beneficiario_nombre' => $request->beneficiario_nombre,
                'beneficiario_parentesco' => $request->beneficiario_parentesco,
                'dias_laborables' => $diasLaborables,
                'dias_descanso' => array_values($diasDescanso)
            ]
        ];
    }

    /**
     * ✅ Procesar datos del contrato y calcular duración
     */
    private function procesarDatosContrato($request): array
    {
        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio_contrato);
        $fechaFin = \Carbon\Carbon::parse($request->fecha_fin_contrato);
        $tipoDuracion = $request->tipo_duracion;
        
        $duracion = $this->calcularDuracion($fechaInicio, $fechaFin, $tipoDuracion);
        $duracionTexto = $this->formatearDuracion($duracion, $tipoDuracion);
        $salarioTexto = $this->numeroATexto($request->sueldo_diarios ?? 0);

        return [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'tipo_duracion' => $tipoDuracion,
            'duracion' => $duracion,
            'duracion_texto' => $duracionTexto,
            'salario_texto' => $salarioTexto
        ];
    }

    /**
     * ✅ Generar PDF del contrato (método central)
     */
    private function generarPDF($trabajador, array $datosContrato)
    {
        return PDF::loadView('Formatos.contrato', [
            'trabajador' => $trabajador,
            'fecha_inicio' => $datosContrato['fecha_inicio'],
            'fecha_fin' => $datosContrato['fecha_fin'],
            'duracion' => $datosContrato['duracion'],
            'tipo_duracion' => $datosContrato['tipo_duracion'],
            'duracion_texto' => $datosContrato['duracion_texto'],
            'salario_texto' => $datosContrato['salario_texto']
        ]);
    }

    /**
     * ✅ Guardar archivo temporal y retornar hash
     */
    private function guardarArchivoTemporal($pdf): string
    {
        $hash = Str::random(32);
        $nombreArchivo = 'preview_contrato_' . $hash . '.pdf';
        $rutaTemporal = 'temp/contratos/' . $nombreArchivo;
        Storage::disk('public')->put($rutaTemporal, $pdf->output());
        
        return $hash;
    }

    /**
     * ✅ Completar datos calculados de la ficha técnica
     */
    private function completarDatosFichaTecnica($trabajador): void
    {
        if (!$trabajador->fichaTecnica) {
            return;
        }

        $ficha = $trabajador->fichaTecnica;

        // Calcular días laborables si no están definidos
        if (empty($ficha->dias_laborables)) {
            $ficha->dias_laborables = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        }

        // Calcular días de descanso
        $todosLosDias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $ficha->dias_descanso = array_values(array_diff($todosLosDias, $ficha->dias_laborables));

        // Calcular horas semanales
        $horasPorDia = $ficha->horas_trabajo ?? 8;
        $diasLaborablesPorSemana = count($ficha->dias_laborables);
        $ficha->horas_semanales_calculadas = $ficha->horas_semanales ?? ($diasLaborablesPorSemana * $horasPorDia);
        $ficha->horas_trabajadas_calculadas = $horasPorDia;

        // Asegurar turno calculado
        $ficha->turno_calculado = $ficha->turno ?? 'diurno';

        // Horarios por defecto
        if (!$ficha->hora_entrada) {
            $ficha->hora_entrada = $ficha->turno === 'nocturno' ? '22:00' : '08:00';
        }
        if (!$ficha->hora_salida) {
            $ficha->hora_salida = $ficha->turno === 'nocturno' ? '06:00' : '17:00';
        }
    }

    /**
     * ✅ Calcular duración entre fechas
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
     * ✅ Formatear duración para mostrar
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
     * ✅ Convertir número a texto para el salario
     */
    private function numeroATexto($numero): string
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

        $numero = intval($numero);

        if ($numero < 20) {
            return ($unidades[$numero] ?: 'CERO') . ' PESOS';
        } elseif ($numero < 100) {
            $dec = intval($numero / 10);
            $uni = $numero % 10;
            return $decenas[$dec] . ($uni > 0 ? ' Y ' . $unidades[$uni] : '') . ' PESOS';
        } elseif ($numero < 1000) {
            $cen = intval($numero / 100);
            $resto = $numero % 100;
            $centena = ($numero == 100) ? 'CIEN' : $centenas[$cen];
            
            if ($resto > 0) {
                $restoTexto = str_replace(' PESOS', '', $this->numeroATexto($resto));
                return $centena . ' ' . $restoTexto . ' PESOS';
            } else {
                return $centena . ' PESOS';
            }
        } elseif ($numero < 1000000) {
            $miles = intval($numero / 1000);
            $resto = $numero % 1000;
            $milesTexto = ($miles == 1) ? 'MIL' : str_replace(' PESOS', '', $this->numeroATexto($miles)) . ' MIL';
            
            if ($resto > 0) {
                $restoTexto = str_replace(' PESOS', '', $this->numeroATexto($resto));
                return $milesTexto . ' ' . $restoTexto . ' PESOS';
            } else {
                return $milesTexto . ' PESOS';
            }
        }

        return number_format($numero, 2) . ' PESOS';
    }

    /**
     * ✅ Obtener texto del turno
     */
    private function getTurnoTexto($turno): string
    {
        $turnos = [
            'diurno' => 'DIURNO',
            'nocturno' => 'NOCTURNO',
            'mixto' => 'MIXTO/ROTATIVO'
        ];

        return $turnos[$turno] ?? 'A ASIGNAR';
    }
}