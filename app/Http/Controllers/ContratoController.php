<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use App\Models\FichaTecnica;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * ✅ SIMPLIFICADO: Solo se encarga de GENERAR contratos definitivos
 * Eliminada toda funcionalidad de preview temporal
 */
class ContratoController extends Controller
{
    /**
     * ✅ ACTUALIZADO: GENERAR CONTRATO DEFINITIVO (determinado e indeterminado)
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

            // ✅ CREAR REGISTRO CON DATOS CONDICIONALES
            $datosContratoDB = [
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_contrato' => $datosContratoProcesados['tipo_contrato'],
                'fecha_inicio_contrato' => $datosContratoProcesados['fecha_inicio'],
                'estatus' => ContratoTrabajador::ESTATUS_ACTIVO,
                'ruta_archivo' => $ruta
            ];

            // Solo añadir datos de duración para contratos determinados
            if ($datosContratoProcesados['tipo_contrato'] === 'determinado') {
                $datosContratoDB['fecha_fin_contrato'] = $datosContratoProcesados['fecha_fin'];
                $datosContratoDB['tipo_duracion'] = $datosContratoProcesados['tipo_duracion'];
                $datosContratoDB['duracion'] = $datosContratoProcesados['duracion'];
                $datosContratoDB['duracion_meses'] = $datosContratoProcesados['tipo_duracion'] === 'meses' ? $datosContratoProcesados['duracion'] : null;
            }

            $contrato = ContratoTrabajador::create($datosContratoDB);

            Log::info('✅ Contrato definitivo creado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_contrato' => $datosContratoProcesados['tipo_contrato']
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

    // ========================================
    // MÉTODOS PRIVADOS DE PROCESAMIENTO
    // ========================================

    /**
     * ✅ Procesar datos del contrato y calcular duración
     */
    private function procesarDatosContrato($request): array
    {
        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio_contrato);
        $tipoContrato = $request->tipo_contrato;
        
        $datos = [
            'tipo_contrato' => $tipoContrato,
            'fecha_inicio' => $fechaInicio,
            'salario_texto' => $this->numeroATexto($request->sueldo_diarios ?? 0)
        ];

        // ✅ PROCESAR DATOS SEGÚN TIPO DE CONTRATO
        if ($tipoContrato === 'determinado') {
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin_contrato);
            $tipoDuracion = $request->tipo_duracion;
            
            $duracion = $this->calcularDuracion($fechaInicio, $fechaFin, $tipoDuracion);
            $duracionTexto = $this->formatearDuracion($duracion, $tipoDuracion);

            $datos['fecha_fin'] = $fechaFin;
            $datos['tipo_duracion'] = $tipoDuracion;
            $datos['duracion'] = $duracion;
            $datos['duracion_texto'] = $duracionTexto;
        } else {
            // Para contratos indeterminados
            $datos['fecha_fin'] = null;
            $datos['tipo_duracion'] = null;
            $datos['duracion'] = null;
            $datos['duracion_texto'] = 'Tiempo Indeterminado';
        }

        return $datos;
    }


    /**
     * ✅ Generar PDF del contrato (método central)
     */
    private function generarPDF($trabajador, array $datosContrato)
    {
        return PDF::loadView('Formatos.contrato', [
            'trabajador' => $trabajador,
            'tipo_contrato' => $datosContrato['tipo_contrato'],
            'fecha_inicio' => $datosContrato['fecha_inicio'],
            'fecha_fin' => $datosContrato['fecha_fin'], // Puede ser null para indeterminados
            'duracion' => $datosContrato['duracion'],
            'tipo_duracion' => $datosContrato['tipo_duracion'],
            'duracion_texto' => $datosContrato['duracion_texto'],
            'salario_texto' => $datosContrato['salario_texto']
        ]);
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