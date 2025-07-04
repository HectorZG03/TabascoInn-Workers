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
     * Mostrar formulario para crear nuevo trabajador (CREATE)
     */
    public function create()
    {
        $areas = Area::orderBy('nombre_area')->get();
        
        return view('trabajadores.crear_trabajador', compact('areas'));
    }

    /**
     * ✅ STORE ACTUALIZADO - Manejar formato DD/MM/YYYY
     */
    public function store(Request $request)
    {
        Log::info('🚀 Iniciando creación de trabajador con formato controlado', [
            'usuario' => Auth::user()->email ?? 'Sistema',
            'datos_basicos' => [
                'nombre' => $request->nombre_trabajador,
                'estatus' => $request->estatus,
                'area' => $request->id_area,
                'categoria' => $request->id_categoria,
            ]
        ]);

        // ✅ VALIDACIONES UNIFICADAS CON FORMATO PERSONALIZADO
        $validated = $request->validate([
            // Datos personales
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de nacimiento no es válida.');
                    }
                    
                    $fechaNacimiento = $this->convertirFechaACarbon($value);
                    if ($fechaNacimiento && $fechaNacimiento->gt(now()->subYears(18))) {
                        $fail('El trabajador debe ser mayor de 18 años.');
                    }
                }
            ],
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            'curp' => 'required|string|size:18|unique:trabajadores,curp',
            'rfc' => 'required|string|size:13|unique:trabajadores,rfc',
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => 'nullable|email|max:55|unique:trabajadores,correo',
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de ingreso no es válida.');
                    }
                    
                    $fechaIngreso = $this->convertirFechaACarbon($value);
                    if ($fechaIngreso && $fechaIngreso->gt(now())) {
                        $fail('La fecha de ingreso no puede ser futura.');
                    }
                }
            ],
            
            // Datos laborales
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            
            // Horarios
            'hora_entrada' => [
                'required',
                'string',
                'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
            ],
            'hora_salida' => [
                'required',
                'string',
                'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('hora_entrada') && $request->filled('hora_salida')) {
                        $entrada = Carbon::parse($request->hora_entrada);
                        $salida = Carbon::parse($request->hora_salida);
                        
                        if ($salida->lte($entrada)) {
                            $salida->addDay();
                        }
                        
                        $horas = $entrada->diffInMinutes($salida) / 60;
                        
                        if ($horas < 1 || $horas > 16) {
                            $fail('El horario debe estar entre 1 y 16 horas. Calculado: ' . round($horas, 2) . ' horas.');
                        }
                    }
                }
            ],
            
            // Días laborables
            'dias_laborables' => 'required|array|min:1|max:7',
            'dias_laborables.*' => 'string|in:' . implode(',', array_keys(FichaTecnica::DIAS_SEMANA)),
            
            // Beneficiario
            'beneficiario_nombre' => 'nullable|string|max:150',
            'beneficiario_parentesco' => [
                'nullable',
                'string',
                'in:' . implode(',', array_keys(FichaTecnica::PARENTESCOS_BENEFICIARIO)),
                'required_with:beneficiario_nombre'
            ],
            
            // ✅ ESTADO Y CONTRATO
            'estatus' => 'required|in:activo,prueba',
            'fecha_inicio_contrato' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de inicio del contrato no es válida.');
                    }
                    
                    $fechaInicio = $this->convertirFechaACarbon($value);
                    if ($fechaInicio && $fechaInicio->lt(now()->startOfDay())) {
                        $fail('La fecha de inicio del contrato no puede ser anterior a hoy.');
                    }
                }
            ],
            'fecha_fin_contrato' => [
                'required',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$this->validarFechaPersonalizada($value)) {
                        $fail('La fecha de fin del contrato no es válida.');
                    }
                    
                    if ($request->filled('fecha_inicio_contrato')) {
                        $fechaInicio = $this->convertirFechaACarbon($request->fecha_inicio_contrato);
                        $fechaFin = $this->convertirFechaACarbon($value);
                        
                        if ($fechaInicio && $fechaFin && $fechaFin->lte($fechaInicio)) {
                            $fail('La fecha de fin debe ser posterior a la fecha de inicio.');
                        }
                    }
                }
            ],
            
            // Contacto de emergencia
            'contacto_nombre_completo' => 'nullable|string|max:150',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_direccion' => 'nullable|string|max:500',
        ], [
            // Mensajes de error
            'nombre_trabajador.required' => 'El nombre es obligatorio.',
            'ape_pat.required' => 'El apellido paterno es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.regex' => 'La fecha de nacimiento debe tener el formato DD/MM/YYYY.',
            'curp.required' => 'La CURP es obligatoria.',
            'curp.unique' => 'Esta CURP ya está registrada.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.regex' => 'La fecha de ingreso debe tener el formato DD/MM/YYYY.',
            'id_area.required' => 'Debe seleccionar un área.',
            'id_categoria.required' => 'Debe seleccionar una categoría.',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio.',
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'hora_entrada.regex' => 'La hora de entrada debe tener el formato HH:MM.',
            'hora_salida.required' => 'La hora de salida es obligatoria.',
            'hora_salida.regex' => 'La hora de salida debe tener el formato HH:MM.',
            'dias_laborables.required' => 'Debe seleccionar al menos un día laborable.',
            'estatus.required' => 'El estado inicial es obligatorio.',
            'estatus.in' => 'El estado debe ser: activo o prueba.',
            'fecha_inicio_contrato.required' => 'La fecha de inicio del contrato es obligatoria.',
            'fecha_inicio_contrato.regex' => 'La fecha de inicio del contrato debe tener el formato DD/MM/YYYY.',
            'fecha_fin_contrato.required' => 'La fecha de fin del contrato es obligatoria.',
            'fecha_fin_contrato.regex' => 'La fecha de fin del contrato debe tener el formato DD/MM/YYYY.',
        ]);

        // ✅ VALIDACIONES ADICIONALES
        
        // Validar relación área-categoría
        $categoria = Categoria::where('id_categoria', $validated['id_categoria'])
                            ->where('id_area', $validated['id_area'])
                            ->first();
                            
        if (!$categoria) {
            return back()->withErrors(['id_categoria' => 'La categoría no pertenece al área seleccionada'])
                        ->withInput();
        }

        // Validar días laborables únicos
        $diasLaborables = $validated['dias_laborables'];
        if (count($diasLaborables) !== count(array_unique($diasLaborables))) {
            return back()->withErrors(['dias_laborables' => 'No puede seleccionar el mismo día más de una vez'])
                        ->withInput();
        }

        // ✅ CONVERTIR FECHAS A FORMATO MYSQL
        $fechaNacimiento = $this->convertirFechaACarbon($validated['fecha_nacimiento']);
        $fechaIngreso = $this->convertirFechaACarbon($validated['fecha_ingreso']);
        $fechaInicioContrato = $this->convertirFechaACarbon($validated['fecha_inicio_contrato']);
        $fechaFinContrato = $this->convertirFechaACarbon($validated['fecha_fin_contrato']);

        // ✅ CALCULAR TIPO DE DURACIÓN AUTOMÁTICAMENTE
        $diasTotales = $fechaInicioContrato->diffInDays($fechaFinContrato);
        $tipoDuracion = $diasTotales > 30 ? 'meses' : 'dias';

        Log::info('✅ Validación completada con formato controlado', [
            'estatus' => $validated['estatus'],
            'duracion_contrato' => "$diasTotales días ($tipoDuracion)",
            'dias_laborables' => count($diasLaborables),
        ]);

        // ✅ CREAR TRABAJADOR Y RELACIONADOS
        DB::beginTransaction();
        
        try {
            // 1️⃣ Crear trabajador
            $antiguedadCalculada = (int) $fechaIngreso->diffInYears(now());

            $trabajador = Trabajador::create([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $fechaNacimiento->format('Y-m-d'),
                'lugar_nacimiento' => $validated['lugar_nacimiento'],
                'estado_actual' => $validated['estado_actual'],
                'ciudad_actual' => $validated['ciudad_actual'],
                'curp' => strtoupper($validated['curp']),
                'rfc' => strtoupper($validated['rfc']),
                'no_nss' => $validated['no_nss'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'fecha_ingreso' => $fechaIngreso->format('Y-m-d'),
                'antiguedad' => $antiguedadCalculada,
                'estatus' => $validated['estatus'],
            ]);

            // 2️⃣ Crear ficha técnica
            $entrada = Carbon::parse($validated['hora_entrada']);
            $salida = Carbon::parse($validated['hora_salida']);
            
            if ($salida->lte($entrada)) {
                $salida->addDay();
            }
            
            $horasCalculadas = round($entrada->diffInMinutes($salida) / 60, 2);
            $diasDescanso = FichaTecnica::calcularDiasDescanso($diasLaborables);
            $horasSemanales = round($horasCalculadas * count($diasLaborables), 2);
            
            // Calcular turno
            $horaEntradaStr = $entrada->format('H:i');
            $horaSalidaOriginal = Carbon::parse($validated['hora_salida'])->format('H:i');
            
            $turnoCalculado = 'mixto';
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
                'dias_laborables' => $diasLaborables,
                'dias_descanso' => $diasDescanso,
                'horas_semanales' => $horasSemanales,
                'beneficiario_nombre' => $validated['beneficiario_nombre'],
                'beneficiario_parentesco' => $validated['beneficiario_parentesco'],
            ]);

            // 3️⃣ Crear contacto de emergencia (si se proporcionó)
            if ($request->filled('contacto_nombre_completo')) {
                ContactoEmergencia::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'nombre_completo' => trim($validated['contacto_nombre_completo']),
                    'parentesco' => $validated['contacto_parentesco'],
                    'telefono_principal' => $validated['contacto_telefono_principal'],
                    'telefono_secundario' => $validated['contacto_telefono_secundario'],
                    'direccion' => $validated['contacto_direccion'],
                ]);
            }

            // 4️⃣ Generar contrato
            $contratoController = new ContratoController();
            $contrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $fechaInicioContrato->format('Y-m-d'),
                'fecha_fin_contrato' => $fechaFinContrato->format('Y-m-d'),
                'tipo_duracion' => $tipoDuracion,
            ]);

            // 5️⃣ Limpiar archivos temporales
            $contratoController->limpiarArchivosTemporales();

            DB::commit();

            // ✅ MENSAJE DE ÉXITO DETALLADO
            $duracionTexto = $this->calcularDuracionTexto($fechaInicioContrato, $fechaFinContrato, $tipoDuracion);
            $estadoTexto = $validated['estatus'] === 'activo' ? 'Activo' : 'En Prueba';
            
            $mensaje = "✅ Trabajador {$trabajador->nombre_completo} creado exitosamente";
            $mensaje .= " • Estado: {$estadoTexto}";
            $mensaje .= " • Horario: {$validated['hora_entrada']} - {$validated['hora_salida']} ({$horasCalculadas}h/día)";
            $mensaje .= " • Contrato: {$duracionTexto}";
            
            if ($validated['beneficiario_nombre']) {
                $mensaje .= " • Beneficiario: {$validated['beneficiario_nombre']}";
            }
            
            if ($request->filled('contacto_nombre_completo')) {
                $mensaje .= " • Contacto de emergencia incluido";
            }

            Log::info('✅ Trabajador creado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre' => $trabajador->nombre_completo,
                'estatus' => $trabajador->estatus,
                'contrato_generado' => true,
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('❌ Error al crear trabajador', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'usuario' => Auth::user()->email ?? 'Sistema',
            ]);

            return back()->withErrors(['error' => 'Error al crear el trabajador: ' . $e->getMessage()])
                        ->withInput();
        }
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
        
        // Validar rangos
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
            return null;
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

    /**
     * ✅ HELPER: Calcular texto de duración
     */
    private function calcularDuracionTexto($fechaInicio, $fechaFin, $tipoDuracion)
    {
        if ($tipoDuracion === 'dias') {
            $dias = $fechaInicio->diffInDays($fechaFin);
            return $dias . ' ' . ($dias === 1 ? 'día' : 'días');
        } else {
            $meses = $fechaInicio->diffInMonths($fechaFin);
            if ($fechaInicio->copy()->addMonths($meses)->lt($fechaFin)) {
                $meses++;
            }
            return $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
        }
    }

    /**
     * ✅ HELPER: Calcular texto de antigüedad
     */
    private function calcularAntiguedadTexto(int $antiguedad): string
    {
        return match($antiguedad) {
            0 => 'Nuevo',
            1 => '1 año',
            default => "$antiguedad años"
        };
    }
}