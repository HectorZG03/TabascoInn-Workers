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

class AdminContratosController extends Controller
{
    /**
     * ✅ ACTUALIZADO: Mostrar contratos del trabajador con nueva lógica
     */
    public function show(Trabajador $trabajador)
    {
        // Cargar relaciones necesarias
        $trabajador->load(['fichaTecnica.categoria.area']);

        // Obtener contratos del trabajador ordenados por fecha de inicio
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        // ✅ SIMPLIFICADO: Procesar información usando solo 3 estados
        $contratos = $contratos->map(function ($contrato) {
            // Usar propiedades calculadas del modelo simplificadas
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

        // Calcular estadísticas usando la nueva lógica
        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        Log::info('Contratos del trabajador consultados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'total_contratos' => $contratos->count(),
            'vigentes' => $estadisticas['vigentes'],
            'proximos_vencer' => $estadisticas['proximos_vencer']
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
            'fecha_inicio_contrato' => 'required|date',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
            'observaciones' => 'nullable|string|max:500'
        ]);

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
     * ✅ MEJORADO: Renovar contrato existente con lógica clara
     */
    public function renovar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para renovar este contrato');
        }

        // ✅ VERIFICACIÓN MEJORADA: Solo renovar si puede renovarse
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
            // 1. Crear nuevo contrato primero
            $contratoController = new ContratoController();
            $nuevoContrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio'],
                'fecha_fin_contrato' => $validated['fecha_fin'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            // 2. Configurar el nuevo contrato como renovación
            $observacionesRenovacion = "Renovación del contrato #{$contrato->id_contrato}";
            if (!empty($validated['observaciones_renovacion'])) {
                $observacionesRenovacion .= " - " . $validated['observaciones_renovacion'];
            }

            $nuevoContrato->update([
                'contrato_anterior_id' => $contrato->id_contrato,
                'observaciones' => $observacionesRenovacion,
                'estatus' => ContratoTrabajador::ESTATUS_ACTIVO
            ]);

            // 3. Marcar contrato anterior como renovado
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
                'fecha_inicio_nueva' => $fechaInicio->format('Y-m-d'),
                'fecha_fin_nueva' => $fechaFin->format('Y-m-d'),
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
     * ✅ NUEVO: Eliminar contrato permanentemente
     */
    public function eliminar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para eliminar este contrato');
        }

        // ✅ VALIDAR: Solo eliminar contratos activos
        if ($contrato->estatus !== ContratoTrabajador::ESTATUS_ACTIVO) {
            return back()->withErrors([
                'error' => 'Solo se pueden eliminar contratos activos. Estado actual: ' . $contrato->texto_estatus
            ]);
        }

        $validated = $request->validate([
            'motivo_eliminacion' => 'required|string|max:500'
        ], [
            'motivo_eliminacion.required' => 'El motivo de eliminación es obligatorio',
            'motivo_eliminacion.max' => 'El motivo no puede exceder 500 caracteres'
        ]);

        DB::beginTransaction();
        
        try {
            // Guardar información para el log antes de eliminar
            $contratoInfo = [
                'id_contrato' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'fecha_inicio' => $contrato->fecha_inicio_contrato->format('Y-m-d'),
                'fecha_fin' => $contrato->fecha_fin_contrato->format('Y-m-d'),
                'duracion' => $contrato->duracion_texto,
                'motivo_eliminacion' => $validated['motivo_eliminacion'],
                'archivo_path' => $contrato->ruta_archivo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ];

            // ✅ ELIMINAR ARCHIVO PDF SI EXISTE
            if ($contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo)) {
                Storage::disk('public')->delete($contrato->ruta_archivo);
            }

            // ✅ ELIMINAR REGISTRO PERMANENTEMENTE
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

    // ========================================
    // MÉTODOS HELPER PRIVADOS (ACTUALIZADOS)
    // ========================================

    /**
     * ✅ SIMPLIFICADO: Calcular estadísticas usando solo 3 estados
     */
    private function calcularEstadisticasContratos($contratos): array
    {
        $vigentes = $contratos->filter(fn($c) => $c->estaVigente())->count();
        $terminados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_TERMINADO)->count();
        $renovados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_RENOVADO)->count();
        $proximosVencer = $contratos->filter(fn($c) => $c->estaVigente() && $c->estaProximoAVencer())->count();
        $expirados = $contratos->filter(fn($c) => $c->estaVigente() && $c->yaExpiro())->count();
        
        // Contrato actual es cualquier vigente
        $contratoActual = $contratos->filter(fn($c) => $c->estaVigente())->first();

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'terminados' => $terminados,
            'renovados' => $renovados,
            'proximos_vencer' => $proximosVencer,
            'expirados_pendientes' => $expirados, // ✅ NUEVO: Vigentes que ya expiraron
            'contrato_actual' => $contratoActual,
            'tiene_contrato_vigente' => $vigentes > 0,
            'renovables' => $contratos->filter(fn($c) => $c->puedeRenovarse())->count(),
            'contratos_renovacion' => $contratos->filter(fn($c) => $c->esRenovacion())->count(),
        ];
    }

    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        $inicio = $contrato->fecha_inicio_contrato->format('d/m/Y');
        $fin = $contrato->fecha_fin_contrato->format('d/m/Y');
        $duracion = $contrato->duracion_texto;
        
        return "{$duracion} (del {$inicio} al {$fin})";
    }

    private function generarNombreDescarga(Trabajador $trabajador, ContratoTrabajador $contrato): string
    {
        $nombreTrabajador = str_replace(' ', '_', $trabajador->nombre_completo);
        $fechaInicio = $contrato->fecha_inicio_contrato->format('Y-m-d');
        $estado = ucfirst($contrato->estado_final);
        
        return "Contrato_{$nombreTrabajador}_{$fechaInicio}_{$estado}.pdf";
    }
}