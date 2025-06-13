<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use App\Models\FichaTecnica;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContratoController extends Controller
{
    /**
     * ✅ CORREGIDO: Generar preview del contrato - Enviar fechas como objetos Carbon
     */
    public function generarPreview(Request $request)
    {
        $request->validate([
            // ✅ DATOS DEL TRABAJADOR - Incluir TODOS los campos que usa la vista
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
            
            // ✅ NUEVOS: Campos de ubicación
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            
            // ✅ NUEVOS: Campos para ficha técnica temporal
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
            
            // ✅ NUEVOS: Campos de beneficiario
            'beneficiario_nombre' => 'nullable|string|max:100',
            'beneficiario_parentesco' => 'nullable|string|max:50',
            
            // ✅ NUEVOS: Días laborables (array)
            'dias_laborables' => 'nullable|array',
            'dias_laborables.*' => 'string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            
            // ✅ DATOS DEL CONTRATO
            'fecha_inicio_contrato' => 'required|date|after_or_equal:today',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
        ]);

        try {
            // ✅ PROCESAR DÍAS LABORABLES Y DE DESCANSO
            $diasLaborables = $request->dias_laborables ?? ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
            $todosLosDias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            $diasDescanso = array_diff($todosLosDias, $diasLaborables);
            
            // ✅ CALCULAR HORAS SEMANALES SI NO SE PROPORCIONA
            $horasPorDia = $request->horas_trabajo ?? 8;
            $horasSemanales = $request->horas_semanales ?? (count($diasLaborables) * $horasPorDia);
            
            // ✅ MEJORADO: Crear objeto trabajador temporal CON TODOS LOS CAMPOS
            $trabajadorTemp = (object) [
                'nombre_trabajador' => $request->nombre_trabajador,
                'ape_pat' => $request->ape_pat,
                'ape_mat' => $request->ape_mat,
                'nombre_completo' => trim($request->nombre_trabajador . ' ' . $request->ape_pat . ' ' . ($request->ape_mat ?? '')),
                'fecha_nacimiento' => \Carbon\Carbon::parse($request->fecha_nacimiento), // ✅ CORREGIDO: Como objeto Carbon
                'fecha_ingreso' => \Carbon\Carbon::parse($request->fecha_ingreso), // ✅ CORREGIDO: Como objeto Carbon
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

            // Calcular duración automáticamente
            $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio_contrato);
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin_contrato);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $request->tipo_duracion);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($request->sueldo_diarios ?? 0);
            
            // ✅ CORREGIDO: Enviar fechas como objetos Carbon, no como strings formateados
            $pdf = PDF::loadView('Formatos.contrato', [
                'trabajador' => $trabajadorTemp,
                'fecha_inicio' => $fechaInicio, // ✅ CORREGIDO: Objeto Carbon
                'fecha_fin' => $fechaFin,       // ✅ CORREGIDO: Objeto Carbon
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion),
                'salario_texto' => $salarioTexto
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
                'duracion' => $duracionCalculada,
                'dias_laborables' => count($diasLaborables)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contrato generado exitosamente',
                'data' => [
                    'hash' => $hash,
                    'download_url' => route('ajax.contratos.preview.download', $hash),
                    'trabajador_nombre' => $trabajadorTemp->nombre_completo,
                    'fecha_inicio' => $fechaInicio->format('d/m/Y'), // ✅ AQUÍ sí formatear para el JSON
                    'fecha_fin' => $fechaFin->format('d/m/Y'),       // ✅ AQUÍ sí formatear para el JSON
                    'duracion' => $duracionCalculada,
                    'tipo_duracion' => $request->tipo_duracion,
                    'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion),
                    'turno' => $request->turno ?? 'diurno',
                    'horas_semanales' => $horasSemanales,
                    'dias_laborables' => count($diasLaborables)
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
     * ✅ CORREGIDO: Generar contrato definitivo con fechas como objetos Carbon
     */
    public function generarDefinitivo($trabajador, $datosContrato)
    {
        try {
            // ✅ NUEVO: Cargar TODAS las relaciones necesarias
            if (!$trabajador->relationLoaded('fichaTecnica')) {
                $trabajador->load(['fichaTecnica.categoria.area']);
            }

            // ✅ CALCULAR/COMPLETAR DATOS FALTANTES DE LA FICHA TÉCNICA
            $this->completarDatosFichaTecnica($trabajador);

            $fechaInicio = \Carbon\Carbon::parse($datosContrato['fecha_inicio_contrato']);
            $fechaFin = \Carbon\Carbon::parse($datosContrato['fecha_fin_contrato']);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $datosContrato['tipo_duracion']);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($trabajador->fichaTecnica->sueldo_diarios ?? 0);

            // ✅ CORREGIDO: Enviar fechas como objetos Carbon
            $pdf = PDF::loadView('Formatos.contrato', [
                'trabajador' => $trabajador,
                'fecha_inicio' => $fechaInicio, // ✅ CORREGIDO: Objeto Carbon
                'fecha_fin' => $fechaFin,       // ✅ CORREGIDO: Objeto Carbon
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $datosContrato['tipo_duracion'],
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $datosContrato['tipo_duracion']),
                'salario_texto' => $salarioTexto
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
     * ✅ CORREGIDO: Generar contrato individual con fechas como objetos Carbon
     */
    public function generar(Request $request, Trabajador $trabajador)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo_duracion' => 'required|in:dias,meses',
        ]);

        try {
            // ✅ NUEVO: Cargar TODAS las relaciones necesarias
            $trabajador->load(['fichaTecnica.categoria.area']);

            // ✅ COMPLETAR DATOS FALTANTES
            $this->completarDatosFichaTecnica($trabajador);

            $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio);
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin);
            
            $duracionCalculada = $this->calcularDuracion($fechaInicio, $fechaFin, $request->tipo_duracion);
            
            // ✅ NUEVO: Convertir salario a texto
            $salarioTexto = $this->numeroATexto($trabajador->fichaTecnica->sueldo_diarios ?? 0);

            // ✅ CORREGIDO: Enviar fechas como objetos Carbon
            $pdf = PDF::loadView('Formatos.contrato', [
                'trabajador' => $trabajador,
                'fecha_inicio' => $fechaInicio, // ✅ CORREGIDO: Objeto Carbon
                'fecha_fin' => $fechaFin,       // ✅ CORREGIDO: Objeto Carbon
                'duracion' => $duracionCalculada,
                'tipo_duracion' => $request->tipo_duracion,
                'duracion_texto' => $this->formatearDuracion($duracionCalculada, $request->tipo_duracion),
                'salario_texto' => $salarioTexto
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
     * ✅ NUEVO: Completar datos calculados de la ficha técnica
     */
    private function completarDatosFichaTecnica($trabajador)
    {
        if (!$trabajador->fichaTecnica) {
            return;
        }

        $ficha = $trabajador->fichaTecnica;

        // ✅ CALCULAR DÍAS LABORABLES SI NO ESTÁN DEFINIDOS
        if (empty($ficha->dias_laborables)) {
            $ficha->dias_laborables = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        }

        // ✅ CALCULAR DÍAS DE DESCANSO
        $todosLosDias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $ficha->dias_descanso = array_values(array_diff($todosLosDias, $ficha->dias_laborables));

        // ✅ CALCULAR HORAS SEMANALES
        $horasPorDia = $ficha->horas_trabajo ?? 8;
        $diasLaborablesPorSemana = count($ficha->dias_laborables);
        $ficha->horas_semanales_calculadas = $ficha->horas_semanales ?? ($diasLaborablesPorSemana * $horasPorDia);
        $ficha->horas_trabajadas_calculadas = $horasPorDia;

        // ✅ ASEGURAR TURNO CALCULADO
        $ficha->turno_calculado = $ficha->turno ?? 'diurno';

        // ✅ HORARIOS POR DEFECTO
        if (!$ficha->hora_entrada) {
            $ficha->hora_entrada = $ficha->turno === 'nocturno' ? '22:00' : '08:00';
        }
        if (!$ficha->hora_salida) {
            $ficha->hora_salida = $ficha->turno === 'nocturno' ? '06:00' : '17:00';
        }

        Log::info('✅ Datos de ficha técnica completados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'dias_laborables' => count($ficha->dias_laborables),
            'horas_semanales' => $ficha->horas_semanales_calculadas,
            'turno' => $ficha->turno_calculado
        ]);
    }

    /**
     * ✅ MEJORADO: Método para convertir número a texto (salario)
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
                // Recursión para el resto, pero quitamos " PESOS" del final
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
        // ✅ NUEVO: Cargar TODAS las relaciones necesarias
        $trabajador->load(['fichaTecnica.categoria.area']);
        
        // ✅ COMPLETAR DATOS CALCULADOS
        $this->completarDatosFichaTecnica($trabajador);
        
        return view('contratos.crear', compact('trabajador'));
    }
}