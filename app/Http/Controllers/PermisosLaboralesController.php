<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PermisosLaboralesController extends Controller
{

    public function store(Request $request, Trabajador $trabajador)
    {
        if (!$trabajador->puedeAsignarPermiso()) {
            return back()->withErrors([
                'error' => 'Solo se pueden asignar permisos a trabajadores activos o sin permisos activos.'
            ]);
        }

        // ✅ TIPOS VÁLIDOS INCLUYENDO "OTRO"
        $tiposBasicos = array_keys(PermisosLaborales::getTiposDisponibles());
        $tiposValidos = implode(',', array_merge($tiposBasicos, ['OTRO']));

        // ✅ VALIDACIÓN ACTUALIZADA PARA FORMATO PERSONALIZADO
        $validated = $request->validate([
            // Tipo de permiso
            'tipo_permiso' => 'required|string|in:' . $tiposValidos,
            'tipo_personalizado' => 'nullable|required_if:tipo_permiso,OTRO|string|min:3|max:80',
            
            'motivo' => 'required|string|min:3|max:100',
            
            // ✅ FECHAS EN FORMATO DD/MM/YYYY (validación personalizada)
            'fecha_inicio' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de inicio no es válida.');
                    }
                    
                    $fechaInicio = $this->convertirFechaACarbon($value);
                    if ($fechaInicio && $fechaInicio->lt(now()->startOfDay())) {
                        $fail('La fecha de inicio no puede ser anterior a hoy.');
                    }
                }
            ],
            
            'fecha_fin' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de fin no es válida.');
                    }
                    
                    if ($request->filled('fecha_inicio')) {
                        $fechaInicio = $this->convertirFechaACarbon($request->fecha_inicio);
                        $fechaFin = $this->convertirFechaACarbon($value);
                        
                        if ($fechaInicio && $fechaFin && $fechaFin->lt($fechaInicio)) {
                            $fail('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
                        }
                    }
                }
            ],
            
            'observaciones' => 'nullable|string|max:500',
            'es_por_horas' => 'nullable|boolean',
            
            // ✅ HORAS EN FORMATO HH:MM (validación personalizada)
            'hora_inicio' => [
                'nullable',
                'required_if:es_por_horas,1',
                'string',
                'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
            ],
            
            'hora_fin' => [
                'nullable',
                'required_if:es_por_horas,1',
                'string',
                'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->boolean('es_por_horas') && $request->filled('hora_inicio') && $request->filled('hora_fin')) {
                        if ($value <= $request->hora_inicio) {
                            $fail('La hora de fin debe ser posterior a la hora de inicio.');
                        }
                    }
                }
            ],
        ], [
            // Mensajes básicos
            'tipo_permiso.required' => 'El tipo de permiso es obligatorio',
            'tipo_permiso.in' => 'El tipo de permiso seleccionado no es válido',
            
            // Mensajes para tipo personalizado
            'tipo_personalizado.required_if' => 'Debe especificar el tipo de permiso cuando selecciona "Otro"',
            'tipo_personalizado.min' => 'El tipo personalizado debe tener al menos 3 caracteres',
            'tipo_personalizado.max' => 'El tipo personalizado no puede exceder 80 caracteres',
            
            'motivo.required' => 'El motivo es obligatorio',
            'motivo.min' => 'El motivo debe tener al menos 3 caracteres',
            'motivo.max' => 'El motivo no puede exceder 100 caracteres',
            
            // ✅ MENSAJES PARA FECHAS PERSONALIZADAS
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.regex' => 'La fecha de inicio debe tener el formato DD/MM/YYYY',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.regex' => 'La fecha de fin debe tener el formato DD/MM/YYYY',
            
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
            
            // ✅ MENSAJES PARA HORAS PERSONALIZADAS
            'hora_inicio.required_if' => 'La hora de inicio es obligatoria para permisos por horas',
            'hora_inicio.regex' => 'La hora de inicio debe tener el formato HH:MM',
            'hora_fin.required_if' => 'La hora de fin es obligatoria para permisos por horas',
            'hora_fin.regex' => 'La hora de fin debe tener el formato HH:MM',
        ]);

        // ✅ CONVERTIR FECHAS A FORMATO MYSQL
        $fechaInicioCarbon = $this->convertirFechaACarbon($validated['fecha_inicio']);
        $fechaFinCarbon = $this->convertirFechaACarbon($validated['fecha_fin']);
        
        if (!$fechaInicioCarbon || !$fechaFinCarbon) {
            return back()->withErrors(['error' => 'Error al procesar las fechas'])->withInput();
        }

        // ✅ DETERMINAR EL TIPO FINAL A GUARDAR
        $tipoFinal = $validated['tipo_permiso'] === 'OTRO' 
            ? trim($validated['tipo_personalizado'])
            : $validated['tipo_permiso'];

        $esPorHoras = $request->boolean('es_por_horas');

        // ✅ VALIDAR CONFLICTOS CON PERMISOS EXISTENTES
        $conflicto = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->where('estatus_permiso', 'activo')
            ->where(function ($q) use ($fechaInicioCarbon, $fechaFinCarbon, $esPorHoras, $request) {
                $q->whereBetween('fecha_inicio', [$fechaInicioCarbon->format('Y-m-d'), $fechaFinCarbon->format('Y-m-d')])
                ->orWhereBetween('fecha_fin', [$fechaInicioCarbon->format('Y-m-d'), $fechaFinCarbon->format('Y-m-d')])
                ->orWhere(function ($sub) use ($fechaInicioCarbon, $fechaFinCarbon) {
                    $sub->where('fecha_inicio', '<=', $fechaInicioCarbon->format('Y-m-d'))
                        ->where('fecha_fin', '>=', $fechaFinCarbon->format('Y-m-d'));
                });
                
                // Si es por horas, también validar cruce de horas en la misma fecha
                if ($esPorHoras && $request->filled('hora_inicio') && $request->filled('hora_fin')) {
                    $q->where(function ($h) use ($fechaInicioCarbon, $request) {
                        $h->where('fecha_inicio', $fechaInicioCarbon->format('Y-m-d'))
                        ->where('hora_inicio', '<', $request->hora_fin)
                        ->where('hora_fin', '>', $request->hora_inicio);
                    });
                }
            })->exists();

        if ($conflicto) {
            return back()->withErrors([
                'fecha_inicio' => 'Ya existe un permiso activo en el rango (fecha y hora) seleccionado.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // ✅ CREAR PERMISO CON FECHAS CONVERTIDAS
            $permiso = PermisosLaborales::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_permiso' => $tipoFinal,
                'motivo' => $validated['motivo'],
                'fecha_inicio' => $fechaInicioCarbon->format('Y-m-d'), // ✅ Formato MySQL
                'fecha_fin' => $fechaFinCarbon->format('Y-m-d'),       // ✅ Formato MySQL
                'observaciones' => $validated['observaciones'],
                'estatus_permiso' => 'activo',
                'es_por_horas' => $esPorHoras,
                'hora_inicio' => $esPorHoras ? $validated['hora_inicio'] : null,
                'hora_fin' => $esPorHoras ? $validated['hora_fin'] : null,
            ]);

            $trabajador->update(['estatus' => 'permiso']);

            DB::commit();

            // ✅ LOG MEJORADO
            Log::info('Permiso asignado con formato personalizado', [
                'trabajador' => $trabajador->nombre_completo,
                'permiso_id' => $permiso->id_permiso,
                'tipo' => $tipoFinal,
                'tipo_fue_personalizado' => $validated['tipo_permiso'] === 'OTRO',
                'motivo' => $validated['motivo'],
                'es_por_horas' => $esPorHoras,
                'fecha_formato_original' => [
                    'inicio' => $validated['fecha_inicio'],
                    'fin' => $validated['fecha_fin']
                ],
                'fecha_formato_mysql' => [
                    'inicio' => $fechaInicioCarbon->format('Y-m-d'),
                    'fin' => $fechaFinCarbon->format('Y-m-d')
                ],
                'hora_inicio' => $validated['hora_inicio'] ?? null,
                'hora_fin' => $validated['hora_fin'] ?? null,
                'usuario' => Auth::user()->email ?? 'Sistema',
            ]);

            // ✅ MENSAJE DE ÉXITO CON INFORMACIÓN DETALLADA
            $duracionDias = $fechaInicioCarbon->diffInDays($fechaFinCarbon) + 1;
            $mensaje = "Permiso asignado exitosamente a {$trabajador->nombre_completo}";
            
            if ($validated['tipo_permiso'] === 'OTRO') {
                $mensaje .= " con tipo personalizado: \"{$tipoFinal}\"";
            }
            
            $mensaje .= " por {$duracionDias} día" . ($duracionDias > 1 ? 's' : '');
            
            if ($esPorHoras) {
                $mensaje .= " de {$validated['hora_inicio']} a {$validated['hora_fin']}";
            }

            return redirect()->route('trabajadores.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al asignar permiso con formato personalizado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_solicitado' => $validated['tipo_permiso'],
                'tipo_personalizado' => $validated['tipo_personalizado'] ?? null,
                'fechas_originales' => [
                    'inicio' => $validated['fecha_inicio'],
                    'fin' => $validated['fecha_fin']
                ],
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return back()->withErrors(['error' => 'Error al asignar el permiso: ' . $e->getMessage()]);
        }
    }
    
    /**
     * ✅ LISTAR PERMISOS - ÍNDICE LIMPIO CON ESTADÍSTICAS DELEGADAS
     */
    public function index(Request $request)
    {
        $query = PermisosLaborales::with([
            'trabajador.fichaTecnica.categoria.area'
        ]);

        // ✅ FILTROS
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trabajador', function($q) use ($search) {
                $q->where('nombre_trabajador', 'like', "%{$search}%")
                ->orWhere('ape_pat', 'like', "%{$search}%")
                ->orWhere('ape_mat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_permiso')) {
            $query->where('tipo_permiso', $request->tipo_permiso);
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', 'like', "%{$request->motivo}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('estatus_permiso', 'activo');
            } elseif ($request->estado === 'finalizados') {
                $query->where('estatus_permiso', 'finalizado');
            } elseif ($request->estado === 'cancelados') {
                $query->where('estatus_permiso', 'cancelado');
            } elseif ($request->estado === 'vencidos') {
                $query->where('fecha_fin', '<', now())
                    ->where('estatus_permiso', 'activo');
            }
        }

        $permisos = $query->orderBy('created_at', 'desc')->paginate(20);

        // ✅ OBTENER ESTADÍSTICAS DEL CONTROLADOR DEDICADO
        $estadisticasController = new EstadisticasController();
        $stats = $estadisticasController->obtenerEstadisticasPermisos();

        // ✅ TIPOS DINÁMICOS (básicos + personalizados existentes)
        $tiposBasicos = PermisosLaborales::getTiposDisponibles();
        
        // Obtener tipos personalizados que ya existen en BD
        $tiposPersonalizados = PermisosLaborales::select('tipo_permiso')
            ->whereNotIn('tipo_permiso', array_keys($tiposBasicos))
            ->distinct()
            ->pluck('tipo_permiso')
            ->toArray();

        // Combinar tipos básicos con personalizados
        $tiposCompletos = $tiposBasicos;
        foreach ($tiposPersonalizados as $tipoPersonalizado) {
            $tiposCompletos[$tipoPersonalizado] = $tipoPersonalizado;
        }
   
        $tiposPermisos = $tiposCompletos;

        
        // ✅ COLORES DINÁMICOS PARA LOS BADGES
        $coloresPermiso = [
            'Vacaciones' => 'success',
            'Licencia Médica' => 'danger',
            'Licencia por Maternidad' => 'info',
            'Licencia por Paternidad' => 'info', 
            'Permiso Personal' => 'warning',
            'Permiso por Estudios' => 'primary',
            'Permiso por Capacitación' => 'primary',
            'Licencia sin Goce de Sueldo' => 'secondary',
            'Permiso Especial' => 'dark',
            'Permiso por Duelo' => 'dark',
            'Permiso por Matrimonio' => 'success',
            'Incapacidad Temporal' => 'danger',
            'Licencia por Familiar Enfermo' => 'warning',
            'Permiso por Emergencia' => 'danger',
            'Licencia Sindical' => 'info',
        ];

        // ✅ ICONOS DINÁMICOS PARA LOS BADGES
        $iconosPermiso = [
            'Vacaciones' => 'bi-sun',
            'Licencia Médica' => 'bi-heart-pulse',
            'Licencia por Maternidad' => 'bi-person-hearts',
            'Licencia por Paternidad' => 'bi-person-hearts', 
            'Permiso Personal' => 'bi-person',
            'Permiso por Estudios' => 'bi-mortarboard',
            'Permiso por Capacitación' => 'bi-book',
            'Licencia sin Goce de Sueldo' => 'bi-dash-circle',
            'Permiso Especial' => 'bi-star',
            'Permiso por Duelo' => 'bi-heart',
            'Permiso por Matrimonio' => 'bi-suit-heart',
            'Incapacidad Temporal' => 'bi-bandaid',
            'Licencia por Familiar Enfermo' => 'bi-person-fill-exclamation',
            'Permiso por Emergencia' => 'bi-exclamation-triangle',
            'Licencia Sindical' => 'bi-people',
        ];

        // ✅ PASAR VARIABLES ACTUALIZADAS A LA VISTA
    return view('trabajadores.estatus.permisos_lista', compact(
        'permisos', 
        'stats', 
        'tiposPermisos', // ✅ Ahora usa el nombre correcto
        'coloresPermiso', 
        'iconosPermiso'
    ));
    }

    /** 
     * ✅ FINALIZAR PERMISO
     */
    public function finalizar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden finalizar permisos que estén activos'
            ]);
        }

        if ($trabajador->estatus !== 'permiso') {
            return back()->withErrors([
                'error' => 'El trabajador debe estar en estado de permiso'
            ]);
        }

        DB::beginTransaction();
        
        try {
            $permiso->update([
                'fecha_fin' => now()->format('Y-m-d'),
                'estatus_permiso' => 'finalizado',
                'observaciones' => $permiso->observaciones . 
                    "\n[FINALIZADO EL " . now()->format('d/m/Y') . " por " . (Auth::user()->email ?? 'Sistema') . "]"
            ]);

            $trabajador->update(['estatus' => 'activo']);

            DB::commit();

            Log::info('Permiso finalizado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'permiso_id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso,
                'motivo' => $permiso->motivo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return redirect()->route('permisos.index')
                           ->with('success', 
                               "Permiso finalizado. {$trabajador->nombre_completo} ha sido reactivado"
                           );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al finalizar permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al finalizar: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ CANCELAR PERMISO
     */
    public function cancelar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors([
                'error' => 'Solo se pueden cancelar permisos que estén activos'
            ]);
        }

        DB::beginTransaction();

        try {
            // Guardar datos para log
            $datosPermiso = [
                'permiso_id' => $permiso->id_permiso,
                'trabajador_id' => $trabajador->id_trabajador,
                'trabajador_nombre' => $trabajador->nombre_completo,
                'tipo_permiso' => $permiso->tipo_permiso,
                'motivo' => $permiso->motivo,
                'fecha_inicio' => $permiso->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $permiso->fecha_fin->format('d/m/Y'),
            ];

            // Reactivar trabajador
            $trabajador->update(['estatus' => 'activo']);

            // Eliminar registro
            $permiso->delete();

            DB::commit();

            Log::info('Permiso eliminado exitosamente', [
                'datos_permiso' => $datosPermiso,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'fecha_eliminacion' => now()->format('d/m/Y H:i:s'),
            ]);
            
            return redirect()->route('permisos.index')
                        ->with('success', 
                            "Permiso eliminado exitosamente. {$datosPermiso['trabajador_nombre']} ha sido reactivado"
                        );

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al eliminar permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors([
                'error' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ ESTADÍSTICAS DELEGADAS AL CONTROLADOR ESPECIALIZADO
     */
    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;
        
        $estadisticasController = new EstadisticasController();
        $estadisticasBasicas = $estadisticasController->obtenerEstadisticasPermisos();
        
        $estadisticas = array_merge($estadisticasBasicas, [
            'por_tipo' => PermisosLaborales::selectRaw('tipo_permiso, COUNT(*) as total')
                                          ->whereYear('fecha_inicio', $añoActual)
                                          ->groupBy('tipo_permiso')
                                          ->orderBy('total', 'desc')
                                          ->get(),
            'por_motivo' => PermisosLaborales::selectRaw('motivo, COUNT(*) as total')
                                            ->whereYear('fecha_inicio', $añoActual)
                                            ->groupBy('motivo')
                                            ->orderBy('total', 'desc')
                                            ->limit(10)
                                            ->get(),
            'por_mes' => PermisosLaborales::selectRaw('MONTH(fecha_inicio) as mes, COUNT(*) as total')
                                         ->whereYear('fecha_inicio', $añoActual)
                                         ->groupBy('mes')
                                         ->orderBy('mes')
                                         ->get(),
        ]);

        return response()->json($estadisticas);
    }

    /**
     * Mostrar detalles del permiso
     */
    public function show(PermisosLaborales $permiso)
    {
        $permiso->load('trabajador.fichaTecnica.categoria.area');
        
        return view('permisos.show', compact('permiso'));
    }

    /**
     * ✅ VALIDAR FECHA PERSONALIZADA DD/MM/YYYY
     */
    private function validarFechaPersonalizada($fecha)
    {
        if (!$fecha) return false;
        
        // Verificar formato
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
            return false;
        }
        
        $dia = (int) $matches[1];
        $mes = (int) $matches[2];
        $año = (int) $matches[3];
        
        // Validar rangos básicos
        if ($dia < 1 || $dia > 31 || $mes < 1 || $mes > 12 || $año < 1900 || $año > 2100) {
            return false;
        }
        
        // Verificar fecha válida
        return checkdate($mes, $dia, $año);
    }

    /**
     * ✅ CONVERTIR FECHA DD/MM/YYYY A CARBON
     */
    private function convertirFechaACarbon($fecha)
    {
        if (!$this->validarFechaPersonalizada($fecha)) {
            return null;
        }
        
        $partes = explode('/', $fecha);
        $dia = (int) $partes[0];
        $mes = (int) $partes[1];
        $año = (int) $partes[2];
        
        try {
            return Carbon::create($año, $mes, $dia);
        } catch (\Exception $e) {
            Log::error('Error al convertir fecha', [
                'fecha_original' => $fecha,
                'partes' => compact('dia', 'mes', 'año'),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}