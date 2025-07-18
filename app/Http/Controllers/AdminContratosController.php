<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * ✅ OPTIMIZADO: Solo se encarga de ADMINISTRAR contratos (CRUD)
 * La generación de PDFs se delega completamente a ContratoController
 */
class AdminContratosController extends Controller
{
    private ContratoController $contratoController;

    public function __construct(ContratoController $contratoController)
    {
        $this->contratoController = $contratoController;
    }

    /**
     * ✅ MOSTRAR contratos del trabajador
     */
    public function show(Trabajador $trabajador)
    {
        $trabajador->load(['fichaTecnica.categoria.area']);

        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        // Procesar información de contratos
        $contratos = $contratos->map(function ($contrato) {
            $contrato->estado_final_calculado = $contrato->estado_final;
            $contrato->color_estado_final = $contrato->color_estado_final;
            $contrato->texto_estado_final = $contrato->texto_estado_final;
            $contrato->info_estado = $contrato->info_estado;
            $contrato->dias_restantes_calculados = $contrato->diasRestantes();
            $contrato->esta_vigente_bool = $contrato->estaVigente();
            $contrato->puede_renovarse_bool = $contrato->puedeRenovarse();
            $contrato->esta_proximo_vencer_bool = $contrato->estaProximoAVencer();
            $contrato->ya_expiro_bool = $contrato->yaExpiro();
            $contrato->duracion_completa = $this->formatearDuracionCompleta($contrato);
            $contrato->archivo_existe = $contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo);
            
            return $contrato;
        });

        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        Log::info('Contratos del trabajador consultados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'total_contratos' => $contratos->count(),
            'vigentes' => $estadisticas['vigentes']
        ]);

        return view('trabajadores.secciones_perfil.contrato_trabajador', compact(
            'trabajador',
            'contratos',
            'estadisticas'
        ));
    }

    // AdminContratosController.php - Método store actualizado
    public function store(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'fecha_inicio_contrato' => 'required|date',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses', // ✅ Ahora viene del frontend
            'observaciones' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ VERIFICAR QUE EL TIPO DE DURACIÓN SEA CONSISTENTE
            $fechaInicio = Carbon::parse($validated['fecha_inicio_contrato']);
            $fechaFin = Carbon::parse($validated['fecha_fin_contrato']);
            $diasTotales = $fechaInicio->diffInDays($fechaFin);
            
            // ✅ MISMA LÓGICA: > 30 días = meses, <= 30 días = días
            $tipoDuracionCalculado = $diasTotales > 30 ? 'meses' : 'dias';
            
            // ✅ USAR EL CALCULADO EN LUGAR DEL ENVIADO PARA CONSISTENCIA
            $validated['tipo_duracion'] = $tipoDuracionCalculado;

            // ✅ DELEGAR generación a ContratoController
            $contrato = $this->contratoController->generarDefinitivo($trabajador, $validated);

            if (!empty($validated['observaciones'])) {
                $contrato->update(['observaciones' => $validated['observaciones']]);
            }

            DB::commit();

            $mensaje = "Contrato creado exitosamente para {$trabajador->nombre_completo}. ";
            $mensaje .= "Vigencia: del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}. ";
            $mensaje .= "Duración: " . ($tipoDuracionCalculado === 'meses' ? 'Por meses' : 'Por días') . ". ";
            $mensaje .= "Estado: ACTIVO.";

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
     * ✅ RENOVAR contrato existente
     */
    public function renovar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para renovar este contrato');
        }

        if (!$contrato->puedeRenovarse()) {
            return back()->withErrors([
                'error' => 'Este contrato no puede renovarse. Debe estar en período vigente y próximo a vencer (30 días o menos).'
            ]);
        }

        $validated = $request->validate([
            'fecha_inicio' => [
                'required', 
                'date', 
                'after_or_equal:' . $contrato->fecha_fin_contrato->addDay()->format('Y-m-d')
            ],
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo_duracion' => 'required|in:dias,meses',
            'observaciones_renovacion' => 'nullable|string|max:500'
        ], [
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser posterior al vencimiento del contrato actual',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio'
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ DELEGAR generación del nuevo contrato
            $nuevoContrato = $this->contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio'],
                'fecha_fin_contrato' => $validated['fecha_fin'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            // Configurar como renovación
            $observacionesRenovacion = "Renovación del contrato #{$contrato->id_contrato}";
            if (!empty($validated['observaciones_renovacion'])) {
                $observacionesRenovacion .= " - " . $validated['observaciones_renovacion'];
            }

            $nuevoContrato->update([
                'contrato_anterior_id' => $contrato->id_contrato,
                'observaciones' => $observacionesRenovacion,
                'estatus' => ContratoTrabajador::ESTATUS_ACTIVO
            ]);

            // Marcar contrato anterior como renovado
            $resultado = $contrato->marcarComoRenovado($nuevoContrato->id_contrato);
            
            if (!$resultado) {
                throw new \Exception('No se pudo marcar el contrato anterior como renovado');
            }

            DB::commit();

            $fechaInicio = Carbon::parse($validated['fecha_inicio']);
            $fechaFin = Carbon::parse($validated['fecha_fin']);
            
            Log::info('Contrato renovado exitosamente', [
                'contrato_anterior_id' => $contrato->id_contrato,
                'contrato_nuevo_id' => $nuevoContrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);
            
            $mensaje = "✅ Contrato renovado exitosamente. ";
            $mensaje .= "Nueva vigencia: del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}. ";
            $mensaje .= "Contrato anterior marcado como renovado.";

            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje)
                        ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al renovar contrato', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al renovar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ ELIMINAR contrato permanentemente
     */
    public function eliminar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para eliminar este contrato');
        }

        if ($contrato->estatus !== ContratoTrabajador::ESTATUS_ACTIVO) {
            return back()->withErrors([
                'error' => 'Solo se pueden eliminar contratos activos. Estado actual: ' . $contrato->texto_estatus
            ]);
        }

        $validated = $request->validate([
            'motivo_eliminacion' => 'required|string|max:500'
        ], [
            'motivo_eliminacion.required' => 'El motivo de eliminación es obligatorio'
        ]);

        DB::beginTransaction();
        
        try {
            $contratoInfo = [
                'id_contrato' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'fecha_inicio' => $contrato->fecha_inicio_contrato->format('Y-m-d'),
                'fecha_fin' => $contrato->fecha_fin_contrato->format('Y-m-d'),
                'motivo_eliminacion' => $validated['motivo_eliminacion'],
                'usuario' => Auth::user()->email ?? 'Sistema'
            ];

            // Eliminar archivo PDF si existe
            if ($contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo)) {
                Storage::disk('public')->delete($contrato->ruta_archivo);
            }

            // Eliminar registro permanentemente
            $contrato->delete();

            DB::commit();

            Log::warning('Contrato eliminado permanentemente', $contratoInfo);

            $mensaje = "✅ Contrato eliminado permanentemente. ";
            $mensaje .= "Se eliminó el contrato #{$contratoInfo['id_contrato']} ";
            $mensaje .= "del {$contratoInfo['fecha_inicio']} al {$contratoInfo['fecha_fin']}.";

            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje)
                        ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al eliminar contrato', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al eliminar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DESCARGAR contrato específico (ÚNICO método de descarga)
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
     * ✅ API: Obtener resumen de contratos
     */
    public function obtenerResumen(Trabajador $trabajador)
    {
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        return response()->json([
            'success' => true,
            'data' => [
                'total_contratos' => $estadisticas['total'],
                'contratos_vigentes' => $estadisticas['vigentes'],
                'proximos_vencer' => $estadisticas['proximos_vencer'],
                'contrato_actual' => $estadisticas['contrato_actual'] ? [
                    'id' => $estadisticas['contrato_actual']->id_contrato,
                    'fecha_inicio' => $estadisticas['contrato_actual']->fecha_inicio_contrato->format('d/m/Y'),
                    'fecha_fin' => $estadisticas['contrato_actual']->fecha_fin_contrato->format('d/m/Y'),
                    'estado' => $estadisticas['contrato_actual']->estado_final,
                    'dias_restantes' => $estadisticas['contrato_actual']->diasRestantes()
                ] : null
            ]
        ]);
    }

    /**
     * ✅ API: Verificar si puede crear contrato
     */
    public function verificarCreacion(Trabajador $trabajador)
    {
        $contratosVigentes = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->where('estatus', ContratoTrabajador::ESTATUS_ACTIVO)
            ->whereDate('fecha_fin_contrato', '>=', now())
            ->count();

        $puedeCrear = $contratosVigentes === 0;

        return response()->json([
            'success' => true,
            'puede_crear' => $puedeCrear,
            'motivo' => $puedeCrear ? 
                'Sin contratos vigentes, puede crear nuevo contrato' : 
                'Ya existe un contrato vigente, debe renovar o esperar a que expire'
        ]);
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS
    // ========================================

    /**
     * ✅ Calcular estadísticas de contratos
     */
    private function calcularEstadisticasContratos($contratos): array
    {
        $vigentes = $contratos->filter(fn($c) => $c->estaVigente())->count();
        $terminados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_TERMINADO)->count();
        $renovados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_RENOVADO)->count();
        $proximosVencer = $contratos->filter(fn($c) => $c->estaVigente() && $c->estaProximoAVencer())->count();
        $expirados = $contratos->filter(fn($c) => $c->estaVigente() && $c->yaExpiro())->count();
        
        $contratoActual = $contratos->filter(fn($c) => $c->estaVigente())->first();

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'terminados' => $terminados,
            'renovados' => $renovados,
            'proximos_vencer' => $proximosVencer,
            'expirados_pendientes' => $expirados,
            'contrato_actual' => $contratoActual,
            'tiene_contrato_vigente' => $vigentes > 0,
            'renovables' => $contratos->filter(fn($c) => $c->puedeRenovarse())->count(),
            'contratos_renovacion' => $contratos->filter(fn($c) => $c->esRenovacion())->count(),
        ];
    }

    /**
     * ✅ Formatear duración completa para mostrar
     */
    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        $inicio = $contrato->fecha_inicio_contrato->format('d/m/Y');
        $fin = $contrato->fecha_fin_contrato->format('d/m/Y');
        $duracion = $contrato->duracion_texto;
        
        return "{$duracion} (del {$inicio} al {$fin})";
    }

    /**
     * ✅ Generar nombre para descarga
     */
    private function generarNombreDescarga(Trabajador $trabajador, ContratoTrabajador $contrato): string
    {
        $nombreTrabajador = str_replace(' ', '_', $trabajador->nombre_completo);
        $fechaInicio = $contrato->fecha_inicio_contrato->format('Y-m-d');
        $estado = ucfirst($contrato->estado_final);
        
        return "Contrato_{$nombreTrabajador}_{$fechaInicio}_{$estado}.pdf";
    }
}