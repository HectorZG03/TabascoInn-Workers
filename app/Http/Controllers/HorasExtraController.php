<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\HorasExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HorasExtraController extends Controller
{
    /**
     * ✅ ASIGNAR HORAS EXTRA (ACUMULAR)
     */
    public function asignar(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDAR QUE EL TRABAJADOR ESTÉ ACTIVO O EN PRUEBA
        if ($trabajador->estaSuspendido() || $trabajador->estaInactivo()) {
            return back()->withErrors([
                'error' => 'Solo se pueden asignar horas extra a trabajadores activos o en período de prueba. Estado actual: ' . $trabajador->estatus_texto
            ]);
        }

        // ✅ VALIDACIONES ACTUALIZADAS PARA ENTEROS
        $validated = $request->validate([
            'horas' => 'required|integer|min:1|max:24', // ✅ Entero entre 1 y 24
            'fecha' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subDays(30)->format('Y-m-d'),
            'descripcion' => 'nullable|string|max:200',
        ], [
            'horas.required' => 'Las horas son obligatorias',
            'horas.integer' => 'Las horas deben ser un número entero',
            'horas.min' => 'Mínimo 1 hora',
            'horas.max' => 'Máximo 24 horas por registro',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a 30 días',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear registro de horas acumuladas
            $horasExtra = HorasExtra::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo' => HorasExtra::TIPO_ACUMULADAS,
                'horas' => $validated['horas'],
                'fecha' => $validated['fecha'],
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
                'fecha' => $validated['fecha'],
                'nuevo_saldo' => $nuevoSaldo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            $mensajeHoras = $validated['horas'] == 1 ? '1 hora' : $validated['horas'] . ' horas';
            $mensajeSaldo = $nuevoSaldo == 1 ? '1 hora' : $nuevoSaldo . ' horas';

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
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al asignar horas extra: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ RESTAR HORAS EXTRA (DEVOLVER)
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

        // ✅ VALIDACIONES ACTUALIZADAS PARA ENTEROS
        $validated = $request->validate([
            'horas' => [
                'required',
                'integer',
                'min:1',
                'max:' . $saldoActual,
            ],
            'fecha' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subDays(7)->format('Y-m-d'),
            'descripcion' => 'nullable|string|max:200',
        ], [
            'horas.required' => 'Las horas son obligatorias',
            'horas.integer' => 'Las horas deben ser un número entero',
            'horas.min' => 'Mínimo 1 hora',
            'horas.max' => 'No hay suficientes horas acumuladas. Saldo disponible: ' . $saldoActual . ' horas',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a 7 días',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

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
                'fecha' => $validated['fecha'],
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
                'fecha' => $validated['fecha'],
                'saldo_anterior' => $saldoActual,
                'nuevo_saldo' => $nuevoSaldo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            $mensajeHoras = $validated['horas'] == 1 ? '1 hora' : $validated['horas'] . ' horas';
            $mensajeSaldo = $nuevoSaldo == 1 ? '1 hora' : $nuevoSaldo . ' horas';

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
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al compensar horas extra: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ OBTENER SALDO ACTUAL (API)
     */
    public function obtenerSaldo(Trabajador $trabajador)
    {
        $saldo = HorasExtra::calcularSaldo($trabajador->id_trabajador);
        
        return response()->json([
            'saldo' => $saldo,
            'saldo_formateado' => $saldo == 1 ? '1 hora' : $saldo . ' horas',
            'puede_restar' => $saldo > 0,
        ]);
    }
}