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
 * ✅ ACTUALIZADO: AdminContratosController con validaciones para formato DD/MM/YYYY
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

    /**
     * ✅ MÉTODO STORE ACTUALIZADO - Con validaciones para formato DD/MM/YYYY
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // ✅ VALIDACIÓN BASE CON FORMATO DD/MM/YYYY
        $baseRules = [
            'tipo_contrato' => 'required|in:determinado,indeterminado',
            'fecha_inicio_contrato' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/', function ($attribute, $value, $fail) {
                if (!$this->validarFechaPersonalizada($value)) {
                    $fail('La fecha de inicio del contrato no es válida. Use formato DD/MM/YYYY');
                }
            }],
            'observaciones' => 'nullable|string|max:500'
        ];
        
        // ✅ VALIDACIÓN CONDICIONAL SEGÚN TIPO DE CONTRATO
        $conditionalRules = [];
        
        if ($request->tipo_contrato === 'determinado') {
            $conditionalRules = [
                'fecha_fin_contrato' => [
                    'required', 
                    'string', 
                    'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$this->validarFechaPersonalizada($value)) {
                            $fail('La fecha de fin del contrato no es válida. Use formato DD/MM/YYYY');
                        }
                        
                        // Validar que sea posterior al inicio
                        if ($request->filled('fecha_inicio_contrato')) {
                            $fechaInicio = $this->convertirFechaACarbon($request->fecha_inicio_contrato);
                            $fechaFin = $this->convertirFechaACarbon($value);
                            
                            if ($fechaInicio && $fechaFin && $fechaFin->lte($fechaInicio)) {
                                $fail('La fecha de fin debe ser posterior a la fecha de inicio.');
                            }
                        }
                    }
                ],
                'tipo_duracion' => 'nullable|in:dias,meses' // Se calcula automáticamente
            ];
        } elseif ($request->tipo_contrato === 'indeterminado') {
            // Para indeterminados: campos opcionales que se forzarán a null
            $conditionalRules = [
                'fecha_fin_contrato' => 'nullable|string',
                'tipo_duracion' => 'nullable'
            ];
        }
        
        $allRules = array_merge($baseRules, $conditionalRules);
        
        $validated = $request->validate($allRules, [
            'tipo_contrato.required' => 'El tipo de contrato es obligatorio',
            'tipo_contrato.in' => 'Tipo de contrato no válido',
            'fecha_inicio_contrato.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio_contrato.regex' => 'La fecha de inicio debe tener el formato DD/MM/YYYY',
            'fecha_fin_contrato.required' => 'La fecha de fin es obligatoria para contratos determinados',
            'fecha_fin_contrato.regex' => 'La fecha de fin debe tener el formato DD/MM/YYYY',
            'tipo_duracion.in' => 'Tipo de duración no válido'
        ]);

        // ✅ PROCESAMIENTO ESPECÍFICO SEGÚN TIPO DE CONTRATO
        if ($validated['tipo_contrato'] === 'indeterminado') {
            // Forzar campos a null para indeterminados
            $validated['fecha_fin_contrato'] = null;
            $validated['tipo_duracion'] = null;
        } else {
            // Para determinados: calcular tipo_duracion automáticamente si no se proporcionó
            if (empty($validated['tipo_duracion'])) {
                $fechaInicio = $this->convertirFechaACarbon($validated['fecha_inicio_contrato']);
                $fechaFin = $this->convertirFechaACarbon($validated['fecha_fin_contrato']);
                $diasTotales = $fechaInicio->diffInDays($fechaFin);
                
                // LÓGICA AUTOMÁTICA: > 30 días = meses, <= 30 días = días
                $validated['tipo_duracion'] = $diasTotales > 30 ? 'meses' : 'dias';
            }
        }

        DB::beginTransaction();
        
        try {
            $fechaInicio = $this->convertirFechaACarbon($validated['fecha_inicio_contrato']);
            
            // ✅ PREPARAR DATOS PARA EL CONTROLADOR DE CONTRATOS
            $datosContrato = [
                'tipo_contrato' => $validated['tipo_contrato'],
                'fecha_inicio_contrato' => $fechaInicio->format('Y-m-d'), // Convertir a formato ISO para el controlador
            ];

            // Solo añadir datos de fin para contratos determinados
            if ($validated['tipo_contrato'] === 'determinado') {
                $fechaFin = $this->convertirFechaACarbon($validated['fecha_fin_contrato']);
                $datosContrato['fecha_fin_contrato'] = $fechaFin->format('Y-m-d');
                $datosContrato['tipo_duracion'] = $validated['tipo_duracion'];
            }

            // DELEGAR generación a ContratoController
            $contrato = $this->contratoController->generarDefinitivo($trabajador, $datosContrato);

            // AGREGAR OBSERVACIONES SI EXISTEN
            if (!empty($validated['observaciones'])) {
                $contrato->update(['observaciones' => $validated['observaciones']]);
            }

            // ✅ GENERAR MENSAJE ESPECÍFICO SEGÚN TIPO
            if ($validated['tipo_contrato'] === 'determinado') {
                $fechaFin = $this->convertirFechaACarbon($validated['fecha_fin_contrato']);
                $tipoDuracionCalculado = $validated['tipo_duracion'];
                
                $mensaje = "Contrato determinado creado exitosamente para {$trabajador->nombre_completo}. ";
                $mensaje .= "Vigencia: del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}. ";
                $mensaje .= "Duración: " . ($tipoDuracionCalculado === 'meses' ? 'Por meses' : 'Por días') . ". ";
                $mensaje .= "Estado: ACTIVO.";
            } else {
                $mensaje = "Contrato indeterminado creado exitosamente para {$trabajador->nombre_completo}. ";
                $mensaje .= "Fecha de inicio: {$fechaInicio->format('d/m/Y')}. ";
                $mensaje .= "Sin fecha de terminación. Estado: ACTIVO.";
            }

            DB::commit();

            Log::info('Contrato creado exitosamente', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_contrato' => $validated['tipo_contrato'],
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $validated['tipo_contrato'] === 'determinado' 
                    ? $this->convertirFechaACarbon($validated['fecha_fin_contrato'])->format('Y-m-d') 
                    : null,
                'formato_entrada' => 'DD/MM/YYYY',
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje)
                        ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear contrato', [
                'error' => $e->getMessage(),
                'tipo_contrato' => $validated['tipo_contrato'] ?? 'No especificado',
                'trabajador_id' => $trabajador->id_trabajador,
                'fecha_inicio' => $validated['fecha_inicio_contrato'] ?? 'No especificada'
            ]);
            return back()->withErrors(['error' => 'Error al crear el contrato: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * ✅ RENOVAR contrato existente - ACTUALIZADO para formato DD/MM/YYYY
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
                'string', 
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($contrato) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de inicio no es válida. Use formato DD/MM/YYYY');
                        return;
                    }
                    
                    $fechaInicio = $this->convertirFechaACarbon($value);
                    $fechaFinContratoActual = $contrato->fecha_fin_contrato->copy()->addDay();
                    
                    if ($fechaInicio->lt($fechaFinContratoActual)) {
                        $fail('La fecha de inicio debe ser posterior al vencimiento del contrato actual (' . $fechaFinContratoActual->format('d/m/Y') . ')');
                    }
                }
            ],
            'fecha_fin' => [
                'required', 
                'string', 
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de fin no es válida. Use formato DD/MM/YYYY');
                        return;
                    }
                    
                    if ($request->filled('fecha_inicio')) {
                        $fechaInicio = $this->convertirFechaACarbon($request->fecha_inicio);
                        $fechaFin = $this->convertirFechaACarbon($value);
                        
                        if ($fechaInicio && $fechaFin && $fechaFin->lte($fechaInicio)) {
                            $fail('La fecha de fin debe ser posterior a la fecha de inicio');
                        }
                    }
                }
            ],
            'tipo_duracion' => 'required|in:dias,meses',
            'observaciones_renovacion' => 'nullable|string|max:500'
        ], [
            'fecha_inicio.regex' => 'La fecha de inicio debe tener el formato DD/MM/YYYY',
            'fecha_fin.regex' => 'La fecha de fin debe tener el formato DD/MM/YYYY',
            'tipo_duracion.required' => 'El tipo de duración es obligatorio',
            'tipo_duracion.in' => 'El tipo de duración no es válido'
        ]);

        DB::beginTransaction();
        
        try {
            // ✅ CONVERTIR FECHAS DD/MM/YYYY A CARBON
            $fechaInicio = $this->convertirFechaACarbon($validated['fecha_inicio']);
            $fechaFin = $this->convertirFechaACarbon($validated['fecha_fin']);
            
            // DELEGAR generación del nuevo contrato
            $nuevoContrato = $this->contratoController->generarDefinitivo($trabajador, [
                'tipo_contrato' => 'determinado', // Las renovaciones siempre son determinadas
                'fecha_inicio_contrato' => $fechaInicio->format('Y-m-d'),
                'fecha_fin_contrato' => $fechaFin->format('Y-m-d'),
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
            
            Log::info('Contrato renovado exitosamente', [
                'contrato_anterior_id' => $contrato->id_contrato,
                'contrato_nuevo_id' => $nuevoContrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'formato_fechas' => 'DD/MM/YYYY',
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
     * ✅ ELIMINAR contrato permanentemente (sin cambios)
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
                'fecha_fin' => $contrato->fecha_fin_contrato
                    ? $contrato->fecha_fin_contrato->format('Y-m-d')
                    : 'Sin fecha de vencimiento',
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

    // ========================================
    // ✅ MÉTODOS AUXILIARES ACTUALIZADOS PARA FORMATO DD/MM/YYYY
    // ========================================

    /**
     * ✅ NUEVO: Validar fecha personalizada DD/MM/YYYY
     */
    private function validarFechaPersonalizada($fecha)
    {
        if (!$fecha) return false;
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) return false;
        
        $dia = (int)$matches[1];
        $mes = (int)$matches[2];
        $año = (int)$matches[3];
        
        return checkdate($mes, $dia, $año);
    }

    /**
     * ✅ NUEVO: Convertir fecha DD/MM/YYYY a Carbon
     */
    private function convertirFechaACarbon($fecha)
    {
        if (!$this->validarFechaPersonalizada($fecha)) return null;
        
        [$dia, $mes, $año] = explode('/', $fecha);
        try {
            return Carbon::create((int)$año, (int)$mes, (int)$dia);
        } catch (\Exception $e) {
            return null;
        }
    }

    // ========================================
    // 🔄 RESTO DE MÉTODOS SIN CAMBIOS
    // ========================================

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
    // MÉTODOS HELPER PRIVADOS (SIN CAMBIOS)
    // ========================================

    private function calcularEstadisticasContratos($contratos): array
    {
        $vigentes = $contratos->filter(fn($c) => $c->estaVigente())->count();
        $terminados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_TERMINADO)->count();
        $renovados = $contratos->filter(fn($c) => $c->estado_final === ContratoTrabajador::ESTADO_RENOVADO)->count();
        $proximosVencer = $contratos->filter(fn($c) => $c->estaVigente() && $c->estaProximoAVencer())->count();
        $expirados = $contratos->filter(fn($c) => $c->estaVigente() && $c->yaExpiro())->count();
        
        $contratoActual = $contratos->filter(fn($c) => $c->estaVigente())->first();
        $tieneContratoVigente = $vigentes > 0 || $contratos->contains('tipo_contrato', 'indeterminado');

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'terminados' => $terminados,
            'renovados' => $renovados,
            'proximos_vencer' => $proximosVencer,
            'expirados_pendientes' => $expirados,
            'contrato_actual' => $contratoActual,
            'tiene_contrato_vigente' => $tieneContratoVigente,
            'renovables' => $contratos->filter(fn($c) => $c->puedeRenovarse())->count(),
            'contratos_renovacion' => $contratos->filter(fn($c) => $c->esRenovacion())->count(),
        ];
    }

    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        if($contrato->tipo_contrato === 'indeterminado'){
            return 'Tiempo Indeterminado (sin fecha fin)';  
        }

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