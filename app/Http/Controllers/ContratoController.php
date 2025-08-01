<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use App\Models\PlantillaContrato;
use App\Models\VariableContrato;
use App\Models\FichaTecnica;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * ✅ ACTUALIZADO: ContratoController con sistema de plantillas dinámicas
 */
class ContratoController extends Controller
{
    /**
     * ✅ ACTUALIZADO: GENERAR CONTRATO DEFINITIVO usando plantillas dinámicas
     */
    public function generarDefinitivo(Trabajador $trabajador, array $datosContrato): ContratoTrabajador
    {
        try {
            // Cargar relaciones necesarias
            $trabajador->load(['fichaTecnica.categoria.area']);
            $this->completarDatosFichaTecnica($trabajador);

            $datosContratoProcesados = $this->procesarDatosContrato((object) $datosContrato);
            
            // ✅ NUEVO: Generar PDF usando plantilla dinámica
            $pdf = $this->generarPDFConPlantilla($trabajador, $datosContratoProcesados);
            
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

            Log::info('✅ Contrato definitivo creado con plantilla dinámica', [
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
    // ✅ NUEVOS MÉTODOS PARA PLANTILLAS DINÁMICAS
    // ========================================

    /**
     * ✅ NUEVO: Generar PDF usando plantilla dinámica
     */
    private function generarPDFConPlantilla($trabajador, array $datosContrato)
    {
        // Obtener plantilla activa para el tipo de contrato
        $tipoContrato = $datosContrato['tipo_contrato'];
        $plantilla = PlantillaContrato::obtenerActiva($tipoContrato);
        
        if (!$plantilla) {
            Log::warning('⚠️ No se encontró plantilla activa, usando plantilla por defecto', [
                'tipo_contrato' => $tipoContrato
            ]);
            
            // Fallback a la plantilla blade original
            return $this->generarPDFOriginal($trabajador, $datosContrato);
        }

        // Obtener valores de todas las variables
        $valoresVariables = $this->obtenerValoresVariables($trabajador, $datosContrato);
        
        // Reemplazar variables en la plantilla
        $contenidoFinal = $plantilla->reemplazarVariables($valoresVariables);
        
        // Agregar imagen de empresa si existe
        $contenidoFinal = $this->procesarImagenEmpresa($contenidoFinal);
        
        Log::info('📄 Generando PDF con plantilla dinámica', [
            'plantilla_id' => $plantilla->id_plantilla,
            'plantilla_version' => $plantilla->version,
            'variables_utilizadas' => count($valoresVariables)
        ]);

        return PDF::loadHTML($contenidoFinal);
    }

    /**
     * ✅ NUEVO: Obtener valores de todas las variables
     */
    private function obtenerValoresVariables($trabajador, array $datosContrato): array
    {
        // ✅ DEBUG TEMPORAL - Agregar estas líneas al inicio del método
        Log::info('🔍 DEBUG: Datos que llegan a obtenerValoresVariables', [
            'datos_keys' => array_keys($datosContrato),
            'tipo_contrato' => $datosContrato['tipo_contrato'] ?? 'NO EXISTE',
            'fecha_inicio_type' => gettype($datosContrato['fecha_inicio'] ?? null),
            'fecha_inicio_value' => isset($datosContrato['fecha_inicio']) ? $datosContrato['fecha_inicio']->format('Y-m-d H:i:s') : 'NO EXISTE',
            'fecha_fin_type' => gettype($datosContrato['fecha_fin'] ?? null),
            'fecha_fin_value' => isset($datosContrato['fecha_fin']) ? $datosContrato['fecha_fin']->format('Y-m-d H:i:s') : 'NO EXISTE',
        ]);

        $variables = VariableContrato::activas()->get();
        $valores = [];
        
        foreach ($variables as $variable) {
            // ✅ DEBUG ESPECÍFICO PARA VARIABLES DE FECHA
            if (in_array($variable->nombre_variable, ['contrato_fecha_inicio', 'contrato_fecha_fin'])) {
                Log::info("🎯 Procesando variable de fecha: {$variable->nombre_variable}", [
                    'datos_disponibles' => array_keys($datosContrato),
                    'fecha_inicio_disponible' => isset($datosContrato['fecha_inicio']),
                    'fecha_fin_disponible' => isset($datosContrato['fecha_fin'])
                ]);
            }
            
            try {
                $valor = $variable->obtenerValor($trabajador, $datosContrato);
                $valores[$variable->nombre_variable] = $valor;
                
                // ✅ DEBUG RESULTADO PARA VARIABLES DE FECHA
                if (in_array($variable->nombre_variable, ['contrato_fecha_inicio', 'contrato_fecha_fin'])) {
                    Log::info("✅ Resultado variable {$variable->nombre_variable}: '{$valor}'");
                }
                
            } catch (\Exception $e) {
                Log::warning('⚠️ Error obteniendo valor de variable', [
                    'variable' => $variable->nombre_variable,
                    'error' => $e->getMessage()
                ]);
                
                // Usar valor de ejemplo en caso de error
                $valores[$variable->nombre_variable] = $variable->formato_ejemplo ?? '';
            }
        }
        
        return $valores;
    }

    /**
     * ✅ NUEVO: Procesar imagen de empresa en el contenido HTML
     */
    private function procesarImagenEmpresa(string $contenidoHtml): string
    {
        $imagenPath = public_path('image/estaticas/images.png');
        
        if (file_exists($imagenPath)) {
            $imagenData = file_get_contents($imagenPath);
            $imagenBase64 = 'data:image/png;base64,' . base64_encode($imagenData);
            
            // Reemplazar placeholders de imagen si existen
            $contenidoHtml = str_replace('{{imagen_empresa}}', $imagenBase64, $contenidoHtml);
            $contenidoHtml = str_replace('{{logo_empresa}}', $imagenBase64, $contenidoHtml);
        }
        
        return $contenidoHtml;
    }

    /**
     * ✅ FALLBACK: Generar PDF con plantilla original (por compatibilidad)
     */
    private function generarPDFOriginal($trabajador, array $datosContrato)
    {
        // ✅ CONVERTIR IMAGEN LOGO A BASE64 PARA DOMPDF
        $imagenPath = public_path('image/estaticas/images.png');
        $imagenBase64 = null;
        
        if (file_exists($imagenPath)) {
            $imagenData = file_get_contents($imagenPath);
            $imagenBase64 = 'data:image/png;base64,' . base64_encode($imagenData);
        }

        return PDF::loadView('Formatos.contrato', [
            'trabajador' => $trabajador,
            'tipo_contrato' => $datosContrato['tipo_contrato'],
            'fecha_inicio' => $datosContrato['fecha_inicio'],
            'fecha_fin' => $datosContrato['fecha_fin'], // Puede ser null para indeterminados
            'duracion' => $datosContrato['duracion'],
            'tipo_duracion' => $datosContrato['tipo_duracion'],
            'duracion_texto' => $datosContrato['duracion_texto'],
            'salario_texto' => $datosContrato['salario_texto'],
            'imagen_empresa' => $imagenBase64 // ✅ NUEVO: Pasar imagen como base64
        ]);
    }

    // ========================================
    // MÉTODOS PRIVADOS DE PROCESAMIENTO (SIN CAMBIOS)
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
            return 'CERO';
        }

        $unidades = [
            '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'
        ];

        $decenas = [
            '', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
        ];

        $centenas = [
            '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 
            'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
        ];

        $numero = intval($numero);

        // Manejo de números menores a 20
        if ($numero < 20) {
            return $unidades[$numero] ?: 'CERO';
        } 
        // Manejo de números entre 20 y 99
        elseif ($numero < 100) {
            $dec = intval($numero / 10);
            $uni = $numero % 10;
            
            // Casos especiales para 20-29
            if ($dec == 2) {
                return $uni == 0 ? 'VEINTE' : $decenas[$dec] . $unidades[$uni];
            }
            // Resto de decenas
            return $decenas[$dec] . ($uni > 0 ? ' Y ' . $unidades[$uni] : '');
        } 
        // Manejo de números entre 100 y 999
        elseif ($numero < 1000) {
            $cen = intval($numero / 100);
            $resto = $numero % 100;
            $centena = ($numero == 100) ? 'CIEN' : $centenas[$cen];
            
            return $centena . ($resto > 0 ? ' ' . $this->numeroATexto($resto) : '');
        } 
        // Manejo de números entre 1000 y 999999
        elseif ($numero < 1000000) {
            $miles = intval($numero / 1000);
            $resto = $numero % 1000;
            $milesTexto = ($miles == 1) ? 'MIL' : $this->numeroATexto($miles) . ' MIL';
            
            return $milesTexto . ($resto > 0 ? ' ' . $this->numeroATexto($resto) : '');
        }

        return 'NÚMERO MUY GRANDE';
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