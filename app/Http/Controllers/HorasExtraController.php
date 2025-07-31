<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\HorasExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HorasExtraController extends Controller
{
    /**
     * ✅ CONVERTIR FECHA DD/MM/YYYY A Y-m-d
     */
    private function convertirFecha($fecha)
    {
        if (!$fecha) return null;
        
        // Si ya está en formato Y-m-d, devolverla tal como está
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        
        // Convertir de DD/MM/YYYY a Y-m-d
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
            $dia = $matches[1];
            $mes = $matches[2];
            $año = $matches[3];
            
            // Validar fecha válida
            if (checkdate($mes, $dia, $año)) {
                return sprintf('%04d-%02d-%02d', $año, $mes, $dia);
            }
        }
        
        return null;
    }

    /**
     * ✅ VALIDAR FECHA EN FORMATO DD/MM/YYYY - CORREGIDO
     */
    private function validarFechaFormato($fecha, $request, $campo)
    {
        // Validar formato básico
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha)) {
            $request->merge([$campo => null]);
            return false;
        }
        
        // Convertir y validar
        $fechaConvertida = $this->convertirFecha($fecha);
        if (!$fechaConvertida) {
            $request->merge([$campo => null]);
            return false;
        }
        
        // Reemplazar en el request con la fecha convertida para Laravel
        $request->merge([$campo => $fechaConvertida]);
        return true;
    }

    /**
     * ✅ ASIGNAR HORAS EXTRA (ACUMULAR) - ACTUALIZADO PARA DECIMALES Y SIN RESTRICCIONES DE FECHA
     */
    public function asignar(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDAR QUE EL TRABAJADOR ESTÉ ACTIVO O EN PRUEBA
        if ($trabajador->estaSuspendido() || $trabajador->estaInactivo()) {
            return back()->withErrors([
                'error' => 'Solo se pueden asignar horas extra a trabajadores activos o en período de prueba. Estado actual: ' . $trabajador->estatus_texto
            ]);
        }

        // ✅ PROCESAR FECHA ANTES DE VALIDACIÓN
        $fechaOriginal = $request->get('fecha');
        if ($fechaOriginal && !$this->validarFechaFormato($fechaOriginal, $request, 'fecha')) {
            return back()->withErrors([
                'fecha' => 'Formato de fecha inválido. Use DD/MM/YYYY'
            ])->withInput();
        }

        // ✅ VALIDACIONES ACTUALIZADAS - SIN RESTRICCIONES DE FECHA Y CON DECIMALES
        $validated = $request->validate([
            'horas' => 'required|numeric|min:0.1|max:24', // ✅ CAMBIO: numeric en lugar de integer, min 0.1
            'fecha' => 'required|date', // ✅ CAMBIO: Solo validar que sea una fecha válida
            'descripcion' => 'nullable|string|max:200',
        ], [
            'horas.required' => 'Las horas son obligatorias',
            'horas.numeric' => 'Las horas deben ser un número válido',
            'horas.min' => 'Mínimo 0.1 horas (6 minutos)',
            'horas.max' => 'Máximo 24 horas por registro',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'Formato de fecha inválido',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ REDONDEAR HORAS A 2 DECIMALES
            $validated['horas'] = round((float) $validated['horas'], 2);

            // Crear registro de horas acumuladas
            $horasExtra = HorasExtra::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo' => HorasExtra::TIPO_ACUMULADAS,
                'horas' => $validated['horas'],
                'fecha' => $validated['fecha'], // Ya está en formato Y-m-d
                'descripcion' => $validated['descripcion'],
                'autorizado_por' => Auth::user()->email ?? 'Sistema',
            ]);

            // Calcular nuevo saldo
            $nuevoSaldo = HorasExtra::calcularSaldo($trabajador->id_trabajador);

            DB::commit();

            Log::info('Horas extra asignadas exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'horas_asignadas' => $validated['horas'],
                'fecha_original' => $fechaOriginal,
                'fecha_procesada' => $validated['fecha'],
                'nuevo_saldo' => $nuevoSaldo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            // ✅ MENSAJE ACTUALIZADO PARA MANEJAR DECIMALES
            $mensajeHoras = $this->formatearHorasParaMensaje($validated['horas']);
            $mensajeSaldo = $this->formatearHorasParaMensaje($nuevoSaldo);

            return back()->with('success', 
                "Horas extra asignadas exitosamente a {$trabajador->nombre_completo}. " .
                "Horas asignadas: {$mensajeHoras}. " .
                "Saldo actual: {$mensajeSaldo}"
            );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al asignar horas extra', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'fecha_original' => $fechaOriginal,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al asignar horas extra: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ RESTAR HORAS EXTRA (DEVOLVER) - ACTUALIZADO PARA DECIMALES Y SIN RESTRICCIONES DE FECHA
     */
    public function restar(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDAR QUE EL TRABAJADOR ESTÉ ACTIVO O EN PRUEBA
        if ($trabajador->estaSuspendido() || $trabajador->estaInactivo()) {
            return back()->withErrors([
                'error' => 'Solo se pueden compensar horas extra a trabajadores activos o en período de prueba. Estado actual: ' . $trabajador->estatus_texto
            ]);
        }

        // Obtener saldo actual antes de validar
        $saldoActual = HorasExtra::calcularSaldo($trabajador->id_trabajador);

        // ✅ PROCESAR FECHA ANTES DE VALIDACIÓN
        $fechaOriginal = $request->get('fecha');
        if ($fechaOriginal && !$this->validarFechaFormato($fechaOriginal, $request, 'fecha')) {
            return back()->withErrors([
                'fecha' => 'Formato de fecha inválido. Use DD/MM/YYYY'
            ])->withInput();
        }

        // ✅ VALIDACIONES ACTUALIZADAS - SIN RESTRICCIONES DE FECHA Y CON DECIMALES
        $validated = $request->validate([
            'horas' => [
                'required',
                'numeric', // ✅ CAMBIO: numeric en lugar de integer
                'min:0.1',
                'max:' . $saldoActual,
            ],
            'fecha' => 'required|date', // ✅ CAMBIO: Solo validar que sea una fecha válida
            'descripcion' => 'nullable|string|max:200',
        ], [
            'horas.required' => 'Las horas son obligatorias',
            'horas.numeric' => 'Las horas deben ser un número válido',
            'horas.min' => 'Mínimo 0.1 horas (6 minutos)',
            'horas.max' => 'No hay suficientes horas acumuladas. Saldo disponible: ' . $saldoActual . ' horas',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'Formato de fecha inválido',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        // ✅ REDONDEAR HORAS A 2 DECIMALES
        $validated['horas'] = round((float) $validated['horas'], 2);

        // Validación adicional de saldo
        if ($saldoActual < $validated['horas']) {
            return back()->withErrors([
                'horas' => 'No hay suficientes horas acumuladas. Saldo disponible: ' . $saldoActual . ' horas'
            ])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Crear registro de horas devueltas
            $horasExtra = HorasExtra::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo' => HorasExtra::TIPO_DEVUELTAS,
                'horas' => $validated['horas'],
                'fecha' => $validated['fecha'], // Ya está en formato Y-m-d
                'descripcion' => $validated['descripcion'],
                'autorizado_por' => Auth::user()->email ?? 'Sistema',
            ]);

            // Calcular nuevo saldo
            $nuevoSaldo = HorasExtra::calcularSaldo($trabajador->id_trabajador);

            DB::commit();

            Log::info('Horas extra compensadas exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'horas_compensadas' => $validated['horas'],
                'fecha_original' => $fechaOriginal,
                'fecha_procesada' => $validated['fecha'],
                'saldo_anterior' => $saldoActual,
                'nuevo_saldo' => $nuevoSaldo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            // ✅ MENSAJE ACTUALIZADO PARA MANEJAR DECIMALES
            $mensajeHoras = $this->formatearHorasParaMensaje($validated['horas']);
            $mensajeSaldo = $this->formatearHorasParaMensaje($nuevoSaldo);

            return back()->with('success', 
                "Horas extra compensadas exitosamente a {$trabajador->nombre_completo}. " .
                "Horas compensadas: {$mensajeHoras}. " .
                "Saldo actual: {$mensajeSaldo}"
            );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al compensar horas extra', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage(),
                'fecha_original' => $fechaOriginal,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al compensar horas extra: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ NUEVO MÉTODO: Formatear horas para mensajes
     */
    private function formatearHorasParaMensaje(float $horas): string
    {
        if ($horas == 1) {
            return '1 hora';
        } elseif ($horas < 1) {
            $minutos = $horas * 60;
            return number_format($horas, 1) . ' horas (' . round($minutos) . ' min)';
        } else {
            return ($horas == floor($horas)) ? 
                number_format($horas, 0) . ' horas' : 
                number_format($horas, 1) . ' horas';
        }
    }

    /**
     * ✅ OBTENER SALDO ACTUAL (API) - ACTUALIZADO PARA DECIMALES
     */
    public function obtenerSaldo(Trabajador $trabajador)
    {
        $saldo = HorasExtra::calcularSaldo($trabajador->id_trabajador);
        
        return response()->json([
            'saldo' => $saldo,
            'saldo_formateado' => $this->formatearHorasParaMensaje($saldo),
            'puede_restar' => $saldo > 0,
        ]);
    }

    /**
     * ✅ OBTENER HISTORIAL (API) - SIN CAMBIOS SIGNIFICATIVOS
     */
    public function obtenerHistorial(Trabajador $trabajador)
    {
        $historial = HorasExtra::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'historial' => $historial->map(function ($registro) {
                return [
                    'id' => $registro->id,
                    'tipo' => $registro->tipo,
                    'tipo_texto' => $registro->tipo_texto,
                    'horas' => $registro->horas,
                    'horas_formateadas' => $registro->horas_formateadas,
                    'fecha' => $registro->fecha->format('d/m/Y'), // Devolver en formato DD/MM/YYYY
                    'fecha_formateada' => $registro->fecha_formateada,
                    'descripcion' => $registro->descripcion,
                    'autorizado_por' => $registro->autorizado_por,
                    'created_at' => $registro->created_at->format('d/m/Y H:i'),
                    'color_tipo' => $registro->color_tipo,
                    'icono_tipo' => $registro->icono_tipo,
                ];
            }),
            'saldo_actual' => HorasExtra::calcularSaldo($trabajador->id_trabajador)
        ]);
    }

    /**
     * ✅ OBTENER ESTADÍSTICAS (API) - SIN CAMBIOS SIGNIFICATIVOS
     */
    public function obtenerEstadisticas(Trabajador $trabajador)
    {
        $stats = [
            'total_acumuladas' => HorasExtra::where('id_trabajador', $trabajador->id_trabajador)
                ->where('tipo', HorasExtra::TIPO_ACUMULADAS)
                ->sum('horas'),
            'total_devueltas' => HorasExtra::where('id_trabajador', $trabajador->id_trabajador)
                ->where('tipo', HorasExtra::TIPO_DEVUELTAS)
                ->sum('horas'),
            'total_registros' => HorasExtra::where('id_trabajador', $trabajador->id_trabajador)->count(),
            'saldo_actual' => HorasExtra::calcularSaldo($trabajador->id_trabajador),
            'ultimo_registro' => HorasExtra::where('id_trabajador', $trabajador->id_trabajador)
                ->latest('fecha')
                ->latest('created_at')
                ->first()?->fecha?->format('d/m/Y'),
        ];

        return response()->json($stats);
    }
}