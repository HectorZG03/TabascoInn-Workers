<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrabajadorController extends Controller
{
    /**
     * ✅ SOLUCIÓN AL ERROR: Calcular antiguedad en el controlador
     */
    public function index(Request $request)
    {
        // ✅ QUERY OPTIMIZADA con cálculo de antigüedad en base de datos
        $query = Trabajador::select([
                'trabajadores.*',
                // ✅ Calcular antigüedad directamente en SQL (evita errores de tipo)
                DB::raw('COALESCE(TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()), 0) as antiguedad_calculada')
            ])
            ->with(['fichaTecnica.categoria.area'])
            ->where('estatus', '!=', 'inactivo');

        // Filtros existentes
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('area')) {
            $query->whereHas('fichaTecnica.categoria.area', function($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }

        if ($request->filled('categoria')) {
            $query->whereHas('fichaTecnica.categoria', function($q) use ($request) {
                $q->where('id_categoria', $request->categoria);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                  ->orWhere('curp', 'LIKE', "%{$search}%")
                  ->orWhere('rfc', 'LIKE', "%{$search}%");
            });
        }

        $trabajadores = $query->orderBy('created_at', 'desc')
                             ->paginate(12)
                             ->withQueryString();

        // ✅ PROCESAR DATOS DESPUÉS DE LA CONSULTA para evitar errores
        foreach ($trabajadores as $trabajador) {
            // ✅ Asegurar que antiguedad_calculada sea entero
            $trabajador->antiguedad_calculada = (int) ($trabajador->antiguedad_calculada ?? 0);
            
            // ✅ Calcular texto de antigüedad en el controlador
            $trabajador->antiguedad_texto = $this->calcularAntiguedadTexto($trabajador->antiguedad_calculada);
        }

        // ✅ ESTADÍSTICAS OPTIMIZADAS
        $stats = [
            'activos' => Trabajador::where('estatus', 'activo')->count(),
            'total' => Trabajador::where('estatus', '!=', 'inactivo')->count(),
            'con_permiso' => Trabajador::where('estatus', 'permiso')->count(),
            'suspendidos' => Trabajador::where('estatus', 'suspendido')->count(),    
            'en_prueba' => Trabajador::where('estatus', 'prueba')->count(),
            'por_estado' => [
                'inactivo' => Trabajador::where('estatus', 'inactivo')->count(),
            ]
        ];

        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        $estados = Trabajador::TODOS_ESTADOS;

        return view('trabajadores.lista_trabajadores', compact(
            'trabajadores', 'areas', 'categorias', 'stats', 'estados'
        ));
    }

    /**
     * ✅ HELPER: Calcular texto de antigüedad de forma segura
     */
    private function calcularAntiguedadTexto(int $antiguedad): string
    {
        return match($antiguedad) {
            0 => 'Nuevo',
            1 => '1 año',
            default => "$antiguedad años"
        };
    }
    
    /**
     * Mostrar formulario para crear nuevo trabajador (CREATE)
     */
    public function create()
    {
        $areas = Area::orderBy('nombre_area')->get();
        
        return view('trabajadores.crear_trabajador', compact('areas'));
    }

    /**
     * ✅ STORE ACTUALIZADO: Crear trabajador CON horarios, días laborables y beneficiario
     */
    public function store(Request $request)
    {
        // ✅ LOG PARA DEBUGGING - Ver qué datos llegan
        Log::info('🔍 DEBUGGING - Datos recibidos en store:', [
            'estatus_recibido' => $request->estatus,
            'todos_los_datos' => $request->except(['_token', 'password']),
            'metodo_http' => $request->method()
        ]);

        // ✅ VALIDACIONES ACTUALIZADAS - CORREGIDO EL ERROR DEL ESPACIO
        $validated = $request->validate([
            // Datos personales básicos (sin cambios)
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:-18 years',
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            'curp' => 'required|string|size:18|unique:trabajadores,curp',
            'rfc' => 'required|string|size:13|unique:trabajadores,rfc',
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => 'nullable|email|max:55|unique:trabajadores,correo',
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => 'required|date|before_or_equal:today',
            
            // Datos laborales básicos (sin cambios)
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            
            // ✅ CORREGIDO: Eliminado el espacio extra que causaba el error
            'estatus' => 'required|in:activo,prueba',
            
            // ✅ CORREGIDO: Validación de horarios para Laravel 12
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('hora_entrada') && $request->filled('hora_salida')) {
                        $entrada = Carbon::parse($request->hora_entrada);
                        $salida = Carbon::parse($request->hora_salida);
                        
                        if ($salida->lte($entrada)) {
                            $salida->addDay();
                        }
                        
                        $diferencia = $entrada->diffInMinutes($salida);
                        $horas = $diferencia / 60;
                        
                        if ($horas < 1 || $horas > 16) {
                            $fail('El horario debe estar entre 1 y 16 horas. Calculado: ' . round($horas, 2) . ' horas.');
                        }
                    }
                }
            ],
            
            // ✅ NUEVOS: Validación de días laborables
            'dias_laborables' => [
                'required',
                'array',
                'min:1',
                'max:7'
            ],
            'dias_laborables.*' => [
                'string',
                'in:' . implode(',', array_keys(FichaTecnica::DIAS_SEMANA))
            ],
            
            // ✅ NUEVOS: Validación de beneficiario (opcional, simplificado)
            'beneficiario_nombre' => 'nullable|string|max:150',
            'beneficiario_parentesco' => [
                'nullable',
                'string',
                'in:' . implode(',', array_keys(FichaTecnica::PARENTESCOS_BENEFICIARIO)),
                'required_with:beneficiario_nombre'
            ],
            
            // Datos del contrato (sin cambios)
            'fecha_inicio_contrato' => 'required|date|after_or_equal:today',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
            
            // Contacto (sin cambios)
            'contacto_nombre_completo' => 'nullable|string|max:150',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_direccion' => 'nullable|string|max:500',
        ], [
            // Mensajes básicos (mantener los existentes)
            'nombre_trabajador.required' => 'El nombre es obligatorio.',
            'ape_pat.required' => 'El apellido paterno es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'El trabajador debe ser mayor de 18 años.',
            'curp.required' => 'La CURP es obligatoria.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'Esta CURP ya está registrada.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.size' => 'El RFC debe tener exactamente 13 caracteres.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.size' => 'El teléfono debe tener exactamente 10 dígitos.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'id_area.required' => 'Debe seleccionar un área.',
            'id_categoria.required' => 'Debe seleccionar una categoría.',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio.',
            'sueldo_diarios.min' => 'El sueldo debe ser mayor a 0.',
            
            // Horarios
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'hora_entrada.date_format' => 'La hora de entrada debe tener el formato HH:MM (ej: 08:00)',
            'hora_salida.required' => 'La hora de salida es obligatoria.',
            'hora_salida.date_format' => 'La hora de salida debe tener el formato HH:MM (ej: 17:00)',
            
            // ✅ NUEVOS: Mensajes para días laborables
            'dias_laborables.required' => 'Debe seleccionar al menos un día laborable.',
            'dias_laborables.min' => 'Debe seleccionar al menos un día laborable.',
            'dias_laborables.max' => 'No puede seleccionar más de 7 días.',
            'dias_laborables.*.in' => 'Día laborable no válido.',
            
            // ✅ NUEVOS: Mensajes para beneficiario (simplificado)
            'beneficiario_nombre.max' => 'El nombre del beneficiario no puede exceder 150 caracteres.',
            'beneficiario_parentesco.required_with' => 'El parentesco es obligatorio cuando se especifica un beneficiario.',
            'beneficiario_parentesco.in' => 'Parentesco no válido.',

            // ✅ CORREGIDO: Mensaje para estatus
            'estatus.required' => 'El estado inicial del trabajador es obligatorio.',
            'estatus.in' => 'El estado inicial debe ser: activo o prueba.',
            
            // Contratos
            'fecha_inicio_contrato.required' => 'La fecha de inicio del contrato es obligatoria.',
            'fecha_inicio_contrato.after_or_equal' => 'El contrato no puede iniciar antes de hoy.',
            'fecha_fin_contrato.required' => 'La fecha de fin del contrato es obligatoria.',
            'fecha_fin_contrato.after' => 'La fecha de fin debe ser posterior al inicio.',
            'tipo_duracion.required' => 'Debe especificar el tipo de duración.',
        ]);

        // ✅ LOG DESPUÉS DE VALIDACIÓN
        Log::info('✅ VALIDACIÓN EXITOSA - Estado del trabajador:', [
            'estatus_validado' => $validated['estatus'],
            'usuario' => Auth::user()->email ?? 'Sistema'
        ]);

        // ✅ Validación adicional: días laborables únicos
        $diasLaborables = $validated['dias_laborables'];
        if (count($diasLaborables) !== count(array_unique($diasLaborables))) {
            return back()->withErrors(['dias_laborables' => 'No puede seleccionar el mismo día más de una vez'])
                        ->withInput();
        }

        // Validar relación área-categoría (sin cambios)
        $categoria = Categoria::where('id_categoria', $validated['id_categoria'])
                            ->where('id_area', $validated['id_area'])
                            ->first();
                            
        if (!$categoria) {
            return back()->withErrors(['id_categoria' => 'La categoría no pertenece al área seleccionada'])
                        ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // ✅ CALCULAR ANTIGÜEDAD
            $antiguedadCalculada = (int) Carbon::parse($validated['fecha_ingreso'])->diffInYears(now());

            // 1️⃣ CREAR TRABAJADOR CON ESTADO DESDE EL FORMULARIO
            $trabajador = Trabajador::create([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'lugar_nacimiento' => $validated['lugar_nacimiento'],
                'estado_actual' => $validated['estado_actual'],
                'ciudad_actual' => $validated['ciudad_actual'],
                'rfc' => strtoupper($validated['rfc']),
                'no_nss' => $validated['no_nss'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'antiguedad' => $antiguedadCalculada,
                'estatus' => $validated['estatus'], // ✅ ESTADO DESDE EL MODAL
            ]);

            Log::info('✅ Trabajador creado con estado del modal', [
                'trabajador_id' => $trabajador->id_trabajador,
                'estatus_asignado' => $trabajador->estatus,
                'estatus_fuente' => 'modal_contrato',
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            // 2️⃣ ✅ CREAR FICHA TÉCNICA CON NUEVOS CAMPOS
            $entrada = Carbon::parse($validated['hora_entrada']);
            $salida = Carbon::parse($validated['hora_salida']);
            
            // Si cruza medianoche
            if ($salida->lte($entrada)) {
                $salida->addDay();
            }
            
            // Calcular horas trabajadas con mayor precisión
            $horasCalculadas = round($entrada->diffInMinutes($salida) / 60, 2);
            
            // ✅ NUEVO: Calcular días de descanso automáticamente
            $diasDescanso = FichaTecnica::calcularDiasDescanso($diasLaborables);
            
            // ✅ NUEVO: Calcular horas semanales
            $horasSemanales = round($horasCalculadas * count($diasLaborables), 2);
            
            // Calcular turno automáticamente
            $horaEntradaStr = $entrada->format('H:i');
            $horaSalidaOriginal = Carbon::parse($validated['hora_salida'])->format('H:i');
            
            $turnoCalculado = 'mixto'; // Default
            
            if ($horaEntradaStr >= FichaTecnica::HORARIO_DIURNO_INICIO && 
                $horaSalidaOriginal <= FichaTecnica::HORARIO_DIURNO_FIN) {
                $turnoCalculado = 'diurno';
            } elseif ($horaEntradaStr >= FichaTecnica::HORARIO_NOCTURNO_INICIO || 
                    $horaSalidaOriginal <= FichaTecnica::HORARIO_NOCTURNO_FIN) {
                $turnoCalculado = 'nocturno';
            }
            
            $fichaTecnica = FichaTecnica::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'id_categoria' => $validated['id_categoria'],
                'sueldo_diarios' => $validated['sueldo_diarios'],
                'formacion' => $validated['formacion'],
                'grado_estudios' => $validated['grado_estudios'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'horas_trabajo' => $horasCalculadas,
                'turno' => $turnoCalculado,
                // ✅ NUEVOS: Días laborables y descanso
                'dias_laborables' => $diasLaborables,
                'dias_descanso' => $diasDescanso,
                'horas_semanales' => $horasSemanales,
                // ✅ NUEVOS: Beneficiario (si se proporcionó, simplificado)
                'beneficiario_nombre' => $validated['beneficiario_nombre'],
                'beneficiario_parentesco' => $validated['beneficiario_parentesco'],
            ]);

            Log::info('✅ Ficha técnica creada con horarios completos', [
                'ficha_id' => $fichaTecnica->id,
                'entrada' => $validated['hora_entrada'],
                'salida' => $validated['hora_salida'],
                'horas_calculadas' => $horasCalculadas,
                'turno_calculado' => $turnoCalculado,
                'dias_laborables' => $diasLaborables,
                'dias_descanso' => $diasDescanso,
                'horas_semanales' => $horasSemanales,
                'beneficiario' => $validated['beneficiario_nombre'] ? 
                            "{$validated['beneficiario_nombre']} ({$validated['beneficiario_parentesco']})" : 
                            'Sin beneficiario'
            ]);

            // 3️⃣ CREAR CONTACTO DE EMERGENCIA (sin cambios)
            if ($request->filled('contacto_nombre_completo') && !empty(trim($validated['contacto_nombre_completo']))) {
                $contacto = ContactoEmergencia::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'nombre_completo' => trim($validated['contacto_nombre_completo']),
                    'parentesco' => $validated['contacto_parentesco'],
                    'telefono_principal' => $validated['contacto_telefono_principal'],
                    'telefono_secundario' => $validated['contacto_telefono_secundario'],
                    'direccion' => $validated['contacto_direccion'],
                ]);
                
                Log::info('✅ Contacto de emergencia creado', ['contacto_id' => $contacto->id_contacto]);
            }

            // 4️⃣ GENERAR CONTRATO (sin cambios)
            $contratoController = new ContratoController();
            $contrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio_contrato'],
                'fecha_fin_contrato' => $validated['fecha_fin_contrato'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            // 5️⃣ LIMPIAR ARCHIVOS TEMPORALES
            $contratoController->limpiarArchivosTemporales();

            DB::commit();

            // ✅ MENSAJE MEJORADO CON NUEVA INFORMACIÓN
            $fechaInicio = Carbon::parse($validated['fecha_inicio_contrato']);
            $fechaFin = Carbon::parse($validated['fecha_fin_contrato']);
            
            if ($validated['tipo_duracion'] === 'dias') {
                $duracion = $fechaInicio->diffInDays($fechaFin);
                $duracionTexto = $duracion . ' ' . ($duracion === 1 ? 'día' : 'días');
            } else {
                $duracion = $fechaInicio->diffInMonths($fechaFin);
                if ($fechaInicio->copy()->addMonths($duracion)->lt($fechaFin)) {
                    $duracion++;
                }
                $duracionTexto = $duracion . ' ' . ($duracion === 1 ? 'mes' : 'meses');
            }

            // ✅ INCLUIR ESTADO EN EL MENSAJE
            $estadoTexto = $validated['estatus'] === 'activo' ? 'Activo' : 'En Prueba';
            $mensaje = "Trabajador {$trabajador->nombre_completo} creado exitosamente con estado: {$estadoTexto}";
            
            if ($request->filled('contacto_nombre_completo')) {
                $mensaje .= " con contacto de emergencia";
            }
            
            // ✅ NUEVO: Incluir información de horarios completos
            $diasTexto = collect($diasLaborables)->map(function($dia) {
                return FichaTecnica::DIAS_SEMANA[$dia];
            })->join(', ');
            
            $mensaje .= " con horario {$validated['hora_entrada']} - {$validated['hora_salida']}";
            $mensaje .= " ({$horasCalculadas}h/día, {$horasSemanales}h/semana)";
            $mensaje .= " trabajando: {$diasTexto}";
            $mensaje .= " (turno {$turnoCalculado})";
            
            if ($validated['beneficiario_nombre']) {
                $parentesco = $validated['beneficiario_parentesco'] ? 
                            " ({$validated['beneficiario_parentesco']})" : '';
                $mensaje .= " y beneficiario: {$validated['beneficiario_nombre']}{$parentesco}";
            }
            
            $mensaje .= ". Contrato generado (duración: {$duracionTexto} hasta {$fechaFin->format('d/m/Y')})";

            return redirect()->route('trabajadores.index')
                        ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error crítico al crear trabajador completo', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'usuario' => Auth::user()->email ?? 'Sistema',
                'request_data' => $request->except(['_token'])
            ]);

            return back()->withErrors(['error' => 'Error al crear el trabajador: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Mostrar un trabajador específico (SHOW)
     */
    public function show(Trabajador $trabajador)
    {
        $trabajador->load(['fichaTecnica.categoria.area', 'contactosEmergencia']);
        
        return redirect()->route('trabajadores.perfil.show', $trabajador);
    }

    /**
     * API: Obtener categorías por área (para AJAX)
     */
    public function getCategoriasPorArea(Area $area)
    {
        $categorias = $area->categorias()
                          ->select('id_categoria', 'nombre_categoria')
                          ->orderBy('nombre_categoria')
                          ->get();

        return response()->json($categorias);
    }
}