<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminContratosController extends Controller
{
    /**
     * ✅ ÚNICO MÉTODO NECESARIO: Mostrar contratos del trabajador
     */
    public function show(Trabajador $trabajador)
    {
        // Cargar relaciones necesarias
        $trabajador->load(['fichaTecnica.categoria.area']);

        // Obtener contratos del trabajador ordenados por fecha de inicio
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        // Calcular estadísticas de contratos
        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        // Procesar información adicional para cada contrato
        $contratos = $contratos->map(function ($contrato) {
            $contrato->dias_restantes_calculados = $contrato->diasRestantes();
            $contrato->esta_vigente_bool = $contrato->estaVigente();
            $contrato->estado_calculado = $contrato->estado;
            $contrato->duracion_completa = $this->formatearDuracionCompleta($contrato);
            $contrato->color_estado = $this->obtenerColorEstado($contrato->estado_calculado);
            $contrato->archivo_existe = $contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo);
            
            return $contrato;
        });

        Log::info('Contratos del trabajador consultados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'total_contratos' => $contratos->count()
        ]);

        return view('trabajadores.secciones_perfil.contrato_trabajador', compact(
            'trabajador',
            'contratos',
            'estadisticas'
        ));
    }

    /**
     * ✅ Descargar contrato específico
     */
    public function descargar(Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        try {
            if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
                abort(403, 'No autorizado para descargar este contrato');
            }

            if (!$contrato->ruta_archivo || !Storage::disk('public')->exists($contrato->ruta_archivo)) {
                return back()->withErrors(['error' => 'El archivo del contrato no existe']);
            }

            $rutaCompleta = Storage::disk('public')->path($contrato->ruta_archivo);
            $nombreDescarga = $this->generarNombreDescarga($trabajador, $contrato);

            return Response::download($rutaCompleta, $nombreDescarga, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descargar contrato', [
                'error' => $e->getMessage(),
                'contrato_id' => $contrato->id_contrato
            ]);
            
            return back()->withErrors(['error' => 'Error al procesar la descarga']);
        }
    }

    /**
     * ✅ Crear contrato para trabajador
     */

    public function store(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'fecha_inicio_contrato' => 'required|date', // ✅ QUITADO: after_or_equal:today
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
            'observaciones' => 'nullable|string|max:500'
        ]);

        // ✅ QUITADO: Verificación de contrato vigente existente

        DB::beginTransaction();
        
        try {
            $contratoController = new ContratoController();
            $contrato = $contratoController->generarDefinitivo($trabajador, $validated);

            if (!empty($validated['observaciones'])) {
                $contrato->update(['observaciones' => $validated['observaciones']]);
            }

            DB::commit();

            $fechaInicio = Carbon::parse($validated['fecha_inicio_contrato']);
            $fechaFin = Carbon::parse($validated['fecha_fin_contrato']);
            
            $mensaje = "Contrato creado exitosamente para {$trabajador->nombre_completo}. ";
            $mensaje .= "Vigencia: del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}. ";
            $mensaje .= "Estado: ACTIVO."; // ✅ CONFIRMAR que está activo

            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje)
                        ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear contrato', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al crear el contrato'])->withInput();
        }
    }

    /**
     * ✅ Renovar contrato existente
     */
    public function renovar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para renovar este contrato');
        }

        $validated = $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:' . $contrato->fecha_fin_contrato->format('Y-m-d'),
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo_duracion' => 'required|in:dias,meses'
        ]);

        DB::beginTransaction();
        
        try {
            // Marcar contrato actual como renovado
            $contrato->update(['estatus' => ContratoTrabajador::ESTATUS_RENOVADO]);

            // Crear nuevo contrato
            $contratoController = new ContratoController();
            $nuevoContrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio'],
                'fecha_fin_contrato' => $validated['fecha_fin'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            $nuevoContrato->update([
                'contrato_anterior_id' => $contrato->id_contrato,
                'observaciones' => "Renovación del contrato #{$contrato->id_contrato}",
                'estatus' => ContratoTrabajador::ESTATUS_ACTIVO
            ]);

            DB::commit();

            $fechaInicio = Carbon::parse($validated['fecha_inicio']);
            $fechaFin = Carbon::parse($validated['fecha_fin']);
            
            $mensaje = "Contrato renovado exitosamente. ";
            $mensaje .= "Nueva vigencia: del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}.";

            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al renovar contrato', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al renovar el contrato']);
        }
    }

    /**
     * ✅ NUEVO: Terminar contrato
     */
    public function terminar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403);
        }

        $validated = $request->validate([
            'motivo_terminacion' => 'required|string|max:500'
        ]);

        $contrato->update([
            'estatus' => ContratoTrabajador::ESTATUS_TERMINADO,
            'observaciones' => ($contrato->observaciones ? $contrato->observaciones . "\n" : '') . 
                              "Terminado: " . $validated['motivo_terminacion']
        ]);

        return back()->with('success', 'Contrato terminado exitosamente');
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS (SIMPLIFICADOS)
    // ========================================

    private function calcularEstadisticasContratos($contratos): array
    {
        $vigentes = $contratos->filter(fn($c) => $c->estaVigente())->count();
        $expirados = $contratos->filter(fn($c) => $c->estado === 'expirado')->count();
        $pendientes = $contratos->filter(fn($c) => $c->estado === 'pendiente')->count();
        $proximosVencer = $contratos->filter(fn($c) => $c->estaVigente() && $c->diasRestantes() <= 30)->count();
        $contratoActual = $contratos->filter(fn($c) => $c->estaVigente())->first();

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'expirados' => $expirados,
            'pendientes' => $pendientes,
            'proximos_vencer' => $proximosVencer,
            'contrato_actual' => $contratoActual,
            'tiene_contrato_vigente' => $vigentes > 0,
        ];
    }

    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        $inicio = $contrato->fecha_inicio_contrato->format('d/m/Y');
        $fin = $contrato->fecha_fin_contrato->format('d/m/Y');
        $duracion = $contrato->duracion_texto;
        
        return "{$duracion} (del {$inicio} al {$fin})";
    }

    private function obtenerColorEstado(string $estado): string
    {
        return match($estado) {
            'vigente' => 'success',
            'expirado' => 'danger',
            'pendiente' => 'warning',
            default => 'secondary'
        };
    }

    private function generarNombreDescarga(Trabajador $trabajador, ContratoTrabajador $contrato): string
    {
        $nombreTrabajador = str_replace(' ', '_', $trabajador->nombre_completo);
        $fechaInicio = $contrato->fecha_inicio_contrato->format('Y-m-d');
        $estado = ucfirst($contrato->estado);
        
        return "Contrato_{$nombreTrabajador}_{$fechaInicio}_{$estado}.pdf";
    }
}