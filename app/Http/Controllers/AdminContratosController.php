<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminContratosController extends Controller
{
    /**
     * ✅ NUEVO: Vista principal de administración de contratos
     */
    public function index(Request $request)
    {
        // ✅ Query optimizada con relaciones necesarias
        $query = ContratoTrabajador::with([
            'trabajador.fichaTecnica.categoria.area'
        ])->select([
            'contratos_trabajadores.*',
            // Calcular estado directamente en SQL para mejor rendimiento
            DB::raw('CASE 
                WHEN fecha_inicio_contrato > CURDATE() THEN "pendiente"
                WHEN fecha_fin_contrato < CURDATE() THEN "expirado" 
                ELSE "vigente"
            END as estado_calculado'),
            // Calcular días restantes
            DB::raw('CASE 
                WHEN fecha_fin_contrato < CURDATE() THEN 0
                ELSE DATEDIFF(fecha_fin_contrato, CURDATE())
            END as dias_restantes_calculados')
        ]);

        // ✅ FILTROS AVANZADOS
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'vigente') {
                $query->whereRaw('fecha_inicio_contrato <= CURDATE() AND fecha_fin_contrato >= CURDATE()');
            } elseif ($estado === 'expirado') {
                $query->whereRaw('fecha_fin_contrato < CURDATE()');
            } elseif ($estado === 'pendiente') {
                $query->whereRaw('fecha_inicio_contrato > CURDATE()');
            } elseif ($estado === 'proximo_vencer') {
                $query->whereRaw('fecha_fin_contrato >= CURDATE() AND fecha_fin_contrato <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
            }
        }

        if ($request->filled('area')) {
            $query->whereHas('trabajador.fichaTecnica.categoria.area', function($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }

        if ($request->filled('tipo_duracion')) {
            $query->where('tipo_duracion', $request->tipo_duracion);
        }

        if ($request->filled('trabajador')) {
            $search = $request->trabajador;
            $query->whereHas('trabajador', function($q) use ($search) {
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                  ->orWhere('curp', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_inicio_contrato', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_fin_contrato', '<=', $request->fecha_hasta);
        }

        // ✅ ORDENAMIENTO
        $sortBy = $request->get('sort', 'fecha_inicio_contrato');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['fecha_inicio_contrato', 'fecha_fin_contrato', 'estado_calculado', 'dias_restantes_calculados'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('fecha_inicio_contrato', 'desc');
        }

        // ✅ PAGINACIÓN
        $contratos = $query->paginate(20)->withQueryString();

        // ✅ PROCESAR DATOS ADICIONALES después de la consulta
        foreach ($contratos as $contrato) {
            // Asegurar datos calculados
            $contrato->estado_calculado = $contrato->estado_calculado ?? $contrato->estado;
            $contrato->dias_restantes_calculados = (int) ($contrato->dias_restantes_calculados ?? 0);
            
            // Información formateada
            $contrato->duracion_completa = $this->formatearDuracionCompleta($contrato);
            $contrato->color_estado = $this->obtenerColorEstado($contrato->estado_calculado);
            $contrato->archivo_existe = $contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo);
            
            // Información del trabajador si existe
            if ($contrato->trabajador) {
                $contrato->trabajador_nombre_completo = $contrato->trabajador->nombre_completo;
                $contrato->trabajador_area = $contrato->trabajador->fichaTecnica?->categoria?->area?->nombre_area ?? 'Sin área';
                $contrato->trabajador_categoria = $contrato->trabajador->fichaTecnica?->categoria?->nombre_categoria ?? 'Sin categoría';
                $contrato->trabajador_estatus = $contrato->trabajador->estatus;
            }
        }

        // ✅ ESTADÍSTICAS GENERALES
        $estadisticas = $this->calcularEstadisticasGenerales();

        // ✅ DATOS PARA FILTROS
        $areas = Area::orderBy('nombre_area')->get();
        $estados_filtro = [
            'vigente' => 'Vigentes',
            'expirado' => 'Expirados',
            'pendiente' => 'Pendientes',
            'proximo_vencer' => 'Próximos a vencer (30 días)'
        ];
        $tipos_duracion = [
            'dias' => 'Por días',
            'meses' => 'Por meses'
        ];

        Log::info('✅ Vista de administración de contratos consultada', [
            'total_contratos' => $contratos->total(),
            'filtros_aplicados' => $request->except(['page']),
        ]);

        return view('trabajadores.contratos.admin_contratos', compact(
            'contratos',
            'estadisticas',
            'areas',
            'estados_filtro',
            'tipos_duracion'
        ));
    }

    /**
     * ✅ NUEVO: Calcular estadísticas generales del sistema
     */
    private function calcularEstadisticasGenerales(): array
    {
        $hoy = Carbon::today();
        
        $total = ContratoTrabajador::count();
        
        $vigentes = ContratoTrabajador::whereRaw('fecha_inicio_contrato <= CURDATE() AND fecha_fin_contrato >= CURDATE()')->count();
        
        $expirados = ContratoTrabajador::whereRaw('fecha_fin_contrato < CURDATE()')->count();
        
        $pendientes = ContratoTrabajador::whereRaw('fecha_inicio_contrato > CURDATE()')->count();
        
        $proximosVencer = ContratoTrabajador::whereRaw('fecha_fin_contrato >= CURDATE() AND fecha_fin_contrato <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)')->count();
        
        $vencenEstaSeemana = ContratoTrabajador::whereRaw('fecha_fin_contrato >= CURDATE() AND fecha_fin_contrato <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)')->count();
        
        $porDias = ContratoTrabajador::where('tipo_duracion', 'dias')->count();
        $porMeses = ContratoTrabajador::where('tipo_duracion', 'meses')->count();

        return [
            'total' => $total,
            'vigentes' => $vigentes,
            'expirados' => $expirados,
            'pendientes' => $pendientes,
            'proximos_vencer' => $proximosVencer,
            'vencen_semana' => $vencenEstaSeemana,
            'por_dias' => $porDias,
            'por_meses' => $porMeses,
            'porcentaje_vigentes' => $total > 0 ? round(($vigentes / $total) * 100, 1) : 0,
            'trabajadores_con_contrato' => ContratoTrabajador::distinct('id_trabajador')->count(),
        ];
    }

    /**
     * Mostrar contratos del trabajador (vista principal)
     */
    public function show(Trabajador $trabajador)
    {
        // ✅ Cargar relaciones necesarias
        $trabajador->load([
            'fichaTecnica.categoria.area'
        ]);

        // ✅ Obtener contratos del trabajador ordenados por fecha de inicio (más reciente primero)
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        // ✅ Calcular estadísticas de contratos
        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        // ✅ Procesar información adicional para cada contrato
        $contratos = $contratos->map(function ($contrato) {
            // Calcular información adicional
            $contrato->dias_restantes_calculados = $contrato->diasRestantes();
            $contrato->esta_vigente_bool = $contrato->estaVigente();
            $contrato->estado_calculado = $contrato->estado;
            
            // Información de duración formateada
            $contrato->duracion_completa = $this->formatearDuracionCompleta($contrato);
            
            // Color del badge según el estado
            $contrato->color_estado = $this->obtenerColorEstado($contrato->estado_calculado);
            
            // Información de archivos
            $contrato->archivo_existe = $contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo);
            
            return $contrato;
        });

        // ✅ LOG SIMPLE - Sin datos de usuario
        Log::info('Contratos del trabajador consultados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'total_contratos' => $contratos->count(),
            'contratos_vigentes' => $estadisticas['vigentes']
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
            // Verificar que el contrato pertenece al trabajador
            if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
                abort(403, 'No autorizado para descargar este contrato');
            }

            // Verificar que el archivo existe
            if (!$contrato->ruta_archivo || !Storage::disk('public')->exists($contrato->ruta_archivo)) {
                Log::warning('Intento de descarga de archivo inexistente', [
                    'contrato_id' => $contrato->id_contrato,
                    'trabajador_id' => $trabajador->id_trabajador,
                    'ruta_archivo' => $contrato->ruta_archivo
                ]);
                
                return back()->withErrors(['error' => 'El archivo del contrato no existe o ha sido eliminado']);
            }

            $rutaCompleta = Storage::disk('public')->path($contrato->ruta_archivo);
            
            if (!file_exists($rutaCompleta)) {
                abort(404, 'Archivo físico no encontrado');
            }

            // Generar nombre descriptivo para la descarga
            $nombreDescarga = $this->generarNombreDescarga($trabajador, $contrato);

            // ✅ LOG SIMPLE - Sin datos de usuario
            Log::info('Contrato descargado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre_archivo' => $nombreDescarga
            ]);

            return Response::download($rutaCompleta, $nombreDescarga, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descargar contrato', [
                'error' => $e->getMessage(),
                'contrato_id' => $contrato->id_contrato ?? 'N/A',
                'trabajador_id' => $trabajador->id_trabajador
            ]);
            
            return back()->withErrors(['error' => 'Error al procesar la descarga del contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ Calcular estadísticas de contratos
     */
    private function calcularEstadisticasContratos($contratos): array
    {
        $hoy = Carbon::today();
        
        $vigentes = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estaVigente();
        })->count();
        
        $expirados = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estado === 'expirado';
        })->count();
        
        $pendientes = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estado === 'pendiente';
        })->count();
        
        // Contratos próximos a vencer (30 días)
        $proximosVencer = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estaVigente() && $contrato->diasRestantes() <= 30;
        })->count();
        
        // Contrato más reciente
        $contratoActual = $contratos->filter(function ($contrato) {
            return $contrato->estaVigente();
        })->first();
        
        // Duración total acumulada
        $duracionTotalDias = $contratos->sum(function ($contrato) {
            if ($contrato->tipo_duracion === 'dias') {
                return $contrato->duracion;
            } else {
                // Convertir meses a días aproximadamente
                return $contrato->duracion * 30;
            }
        });

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'expirados' => $expirados,
            'pendientes' => $pendientes,
            'proximos_vencer' => $proximosVencer,
            'contrato_actual' => $contratoActual,
            'duracion_total_dias' => $duracionTotalDias,
            'duracion_total_texto' => $this->convertirDiasATexto($duracionTotalDias),
            'tiene_contrato_vigente' => $vigentes > 0,
        ];
    }

    /**
     * ✅ Formatear duración completa del contrato
     */
    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        $inicio = $contrato->fecha_inicio_contrato->format('d/m/Y');
        $fin = $contrato->fecha_fin_contrato->format('d/m/Y');
        $duracion = $contrato->duracion_texto;
        
        return "{$duracion} (del {$inicio} al {$fin})";
    }

    /**
     * ✅ Obtener color del badge según el estado
     */
    private function obtenerColorEstado(string $estado): string
    {
        return match($estado) {
            'vigente' => 'success',
            'expirado' => 'danger',
            'pendiente' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * ✅ Generar nombre descriptivo para descarga
     */
    private function generarNombreDescarga(Trabajador $trabajador, ContratoTrabajador $contrato): string
    {
        $nombreTrabajador = str_replace(' ', '_', $trabajador->nombre_completo);
        $fechaInicio = $contrato->fecha_inicio_contrato->format('Y-m-d');
        $estado = ucfirst($contrato->estado);
        
        return "Contrato_{$nombreTrabajador}_{$fechaInicio}_{$estado}.pdf";
    }

    /**
     * ✅ Convertir días a texto legible
     */
    private function convertirDiasATexto(int $dias): string
    {
        if ($dias < 30) {
            return "{$dias} días";
        } elseif ($dias < 365) {
            $meses = round($dias / 30, 1);
            return "{$meses} meses";
        } else {
            $años = round($dias / 365, 1);
            return "{$años} años";
        }
    }

    /**
     * ✅ API: Obtener información resumida de contratos (para AJAX si se necesita)
     */
    public function obtenerResumen(Trabajador $trabajador)
    {
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get(['id_contrato', 'fecha_inicio_contrato', 'fecha_fin_contrato', 'tipo_duracion', 'duracion']);

        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        return response()->json([
            'success' => true,
            'data' => [
                'total_contratos' => $estadisticas['total'],
                'contrato_vigente' => $estadisticas['tiene_contrato_vigente'],
                'proximos_vencer' => $estadisticas['proximos_vencer'],
                'duracion_total' => $estadisticas['duracion_total_texto']
            ]
        ]);
    }

     /**
     * ✅ NUEVO: Mostrar formulario para crear contrato
     */
    public function create(Trabajador $trabajador)
    {
        // Verificar que el trabajador no tenga contratos vigentes
        $contratoVigente = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->where(function($query) {
                $hoy = Carbon::today();
                $query->where('fecha_inicio_contrato', '<=', $hoy)
                      ->where('fecha_fin_contrato', '>=', $hoy);
            })
            ->exists();

        if ($contratoVigente) {
            return back()->withErrors(['error' => 'El trabajador ya tiene un contrato vigente']);
        }

        // Cargar relaciones necesarias
        $trabajador->load(['fichaTecnica.categoria.area']);

        // Verificar que tenga ficha técnica completa
        if (!$trabajador->fichaTecnica) {
            return back()->withErrors(['error' => 'El trabajador debe tener una ficha técnica completa antes de crear un contrato']);
        }

        return view('trabajadores.secciones_perfil.crear_contrato', compact('trabajador'));
    }

    /**
     * ✅ NUEVO: Crear contrato para trabajador
     */
    public function store(Request $request, Trabajador $trabajador)
    {
        // ✅ Validaciones para Laravel 12
        $validated = $request->validate([
            'fecha_inicio_contrato' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'fecha_fin_contrato' => [
                'required',
                'date',
                'after:fecha_inicio_contrato'
            ],
            'tipo_duracion' => [
                'required',
                'in:dias,meses'
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:500'
            ]
        ], [
            'fecha_inicio_contrato.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio_contrato.after_or_equal' => 'El contrato no puede iniciar antes de hoy',
            'fecha_fin_contrato.required' => 'La fecha de fin es obligatoria',
            'fecha_fin_contrato.after' => 'La fecha de fin debe ser posterior al inicio',
            'tipo_duracion.required' => 'Debe especificar el tipo de duración',
            'tipo_duracion.in' => 'Tipo de duración no válido',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ]);

        // ✅ Verificaciones adicionales
        // 1. Verificar que no tenga contrato vigente
        $contratoVigente = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->where(function($query) {
                $hoy = Carbon::today();
                $query->where('fecha_inicio_contrato', '<=', $hoy)
                      ->where('fecha_fin_contrato', '>=', $hoy);
            })
            ->exists();

        if ($contratoVigente) {
            return back()->withErrors(['error' => 'El trabajador ya tiene un contrato vigente'])
                        ->withInput();
        }

        // 2. Verificar ficha técnica
        $trabajador->load(['fichaTecnica.categoria.area']);
        
        if (!$trabajador->fichaTecnica) {
            return back()->withErrors(['error' => 'El trabajador debe tener una ficha técnica completa'])
                        ->withInput();
        }

        // 3. Validación adicional de fechas
        $fechaInicio = Carbon::parse($validated['fecha_inicio_contrato']);
        $fechaFin = Carbon::parse($validated['fecha_fin_contrato']);
        
        $diferenciaDias = $fechaInicio->diffInDays($fechaFin);
        
        if ($diferenciaDias < 1) {
            return back()->withErrors(['fecha_fin_contrato' => 'El contrato debe durar al menos 1 día'])
                        ->withInput();
        }

        if ($diferenciaDias > 1095) { // Máximo 3 años
            return back()->withErrors(['fecha_fin_contrato' => 'El contrato no puede durar más de 3 años'])
                        ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // ✅ Generar contrato usando ContratoController
            $contratoController = new ContratoController();
            $contrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio_contrato'],
                'fecha_fin_contrato' => $validated['fecha_fin_contrato'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            // ✅ Agregar observaciones si se proporcionaron
            if (!empty($validated['observaciones'])) {
                $contrato->update([
                    'observaciones' => $validated['observaciones']
                ]);
            }

            // ✅ Limpiar archivos temporales
            $contratoController->limpiarArchivosTemporales();

            DB::commit();

            // ✅ Calcular duración para el mensaje
            if ($validated['tipo_duracion'] === 'dias') {
                $duracion = $diferenciaDias;
                $duracionTexto = $duracion . ' ' . ($duracion === 1 ? 'día' : 'días');
            } else {
                $duracion = $fechaInicio->diffInMonths($fechaFin);
                if ($fechaInicio->copy()->addMonths($duracion)->lt($fechaFin)) {
                    $duracion++;
                }
                $duracionTexto = $duracion . ' ' . ($duracion === 1 ? 'mes' : 'meses');
            }

            Log::info('✅ Contrato creado desde perfil', [
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'contrato_id' => $contrato->id_contrato,
                'duracion' => $duracionTexto,
                'fecha_inicio' => $validated['fecha_inicio_contrato'],
                'fecha_fin' => $validated['fecha_fin_contrato'],
                'tiene_observaciones' => !empty($validated['observaciones'])
            ]);

            $mensaje = "Contrato creado exitosamente para {$trabajador->nombre_completo}. ";
            $mensaje .= "Duración: {$duracionTexto} (del {$fechaInicio->format('d/m/Y')} al {$fechaFin->format('d/m/Y')}).";
            
            if (!empty($validated['observaciones'])) {
                $mensaje .= " Se han registrado observaciones especiales.";
            }

            // ✅ AHORA (corregido)
            return redirect()->route('trabajadores.perfil.show', $trabajador)
                        ->with('success', $mensaje)
                        ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error al crear contrato desde perfil', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trabajador_id' => $trabajador->id_trabajador,
                'request_data' => $request->except(['_token'])
            ]);

            return back()->withErrors(['error' => 'Error al crear el contrato: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * ✅ NUEVO: Renovar contrato existente
     */
    public function renovar(Request $request, Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        // Verificar que el contrato pertenece al trabajador
        if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
            abort(403, 'No autorizado para renovar este contrato');
        }

        // Verificar que el contrato está próximo a vencer o ya venció
        $diasRestantes = $contrato->diasRestantes();
        if ($diasRestantes > 30 && $contrato->estaVigente()) {
            return back()->withErrors(['error' => 'Solo se pueden renovar contratos próximos a vencer (30 días o menos)']);
        }

        $validated = $request->validate([
            'fecha_inicio' => [
                'required',
                'date',
                'after_or_equal:' . $contrato->fecha_fin_contrato->format('Y-m-d')
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio'
            ],
            'tipo_duracion' => [
                'required',
                'in:dias,meses'
            ],
            'observaciones_renovacion' => [
                'nullable',
                'string',
                'max:500'
            ]
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.after_or_equal' => 'La renovación debe iniciar después del contrato actual',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior al inicio',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear nuevo contrato (renovación)
            $contratoController = new ContratoController();
            $nuevoContrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio'],
                'fecha_fin_contrato' => $validated['fecha_fin'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            // Marcar como renovación del contrato anterior
            $nuevoContrato->update([
                'observaciones' => "Renovación del contrato #{$contrato->id_contrato}. " . 
                                 ($validated['observaciones_renovacion'] ?? ''),
                'contrato_anterior_id' => $contrato->id_contrato
            ]);

            DB::commit();

            Log::info('✅ Contrato renovado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'contrato_anterior_id' => $contrato->id_contrato,
                'nuevo_contrato_id' => $nuevoContrato->id_contrato
            ]);

        return redirect()->route('trabajadores.perfil.show', $trabajador)
                    ->with('success', 'Contrato renovado exitosamente')
                    ->with('activeTab', 'contratos');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error al renovar contrato', [
                'error' => $e->getMessage(),
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador
            ]);

            return back()->withErrors(['error' => 'Error al renovar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ NUEVO: API para verificar si puede crear contrato
     */
    public function verificarCreacion(Trabajador $trabajador)
    {
        $puedeCrear = true;
        $motivo = '';

        // Verificar ficha técnica
        if (!$trabajador->fichaTecnica) {
            $puedeCrear = false;
            $motivo = 'El trabajador debe tener una ficha técnica completa';
        }

        // Verificar contratos vigentes
        $contratoVigente = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->where(function($query) {
                $hoy = Carbon::today();
                $query->where('fecha_inicio_contrato', '<=', $hoy)
                      ->where('fecha_fin_contrato', '>=', $hoy);
            })
            ->exists();

        if ($contratoVigente) {
            $puedeCrear = false;
            $motivo = 'El trabajador ya tiene un contrato vigente';
        }

        return response()->json([
            'puede_crear' => $puedeCrear,
            'motivo' => $motivo,
            'trabajador' => [
                'nombre' => $trabajador->nombre_completo,
                'tiene_ficha' => (bool) $trabajador->fichaTecnica,
                'tiene_contrato_vigente' => $contratoVigente
            ]
        ]);
    }
}