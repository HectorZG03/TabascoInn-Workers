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
     * ✅ STORE ACTUALIZADO Y CORREGIDO: Crear trabajador CON contrato
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIONES CORREGIDAS - Coinciden con el frontend
        $validated = $request->validate([
            // Datos personales básicos
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:-18 years',
            
            // ✅ NUEVOS: Datos de ubicación
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            
            // Identificadores oficiales
            'curp' => 'required|string|size:18|unique:trabajadores,curp',
            'rfc' => 'required|string|size:13|unique:trabajadores,rfc',
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => 'nullable|email|max:55|unique:trabajadores,correo',
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => 'required|date|before_or_equal:today',
            
            // Datos laborales
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            'estatus' => 'nullable|in:' . implode(',', array_keys(Trabajador::TODOS_ESTADOS)),
            
            // ✅ NUEVOS: Datos laborales específicos
            'horas_trabajo' => 'nullable|numeric|min:1|max:24',
            'turno' => 'nullable|in:diurno,nocturno,mixto',

            // ✅ CORREGIDO: Datos del contrato que coinciden con el frontend
            'fecha_inicio_contrato' => 'required|date|after_or_equal:today',
            'fecha_fin_contrato' => 'required|date|after:fecha_inicio_contrato',
            'tipo_duracion' => 'required|in:dias,meses',
            // ✅ ELIMINADO: 'duracion_meses' => 'required|integer|min:1|max:120',

            // Contacto (sin cambios)
            'contacto_nombre_completo' => 'nullable|string|max:150',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_direccion' => 'nullable|string|max:500',
        ], [
            // Mensajes datos personales
            'nombre_trabajador.required' => 'El nombre es obligatorio',
            'ape_pat.required' => 'El apellido paterno es obligatorio',
            'fecha_nacimiento.before' => 'El trabajador debe ser mayor de 18 años',
            
            // ✅ NUEVOS: Mensajes para ubicación
            'lugar_nacimiento.max' => 'El lugar de nacimiento no debe exceder 100 caracteres',
            'estado_actual.max' => 'El estado actual no debe exceder 50 caracteres',
            'ciudad_actual.max' => 'La ciudad actual no debe exceder 50 caracteres',
            
            // Mensajes identificadores
            'curp.size' => 'El CURP debe tener exactamente 18 caracteres',
            'curp.unique' => 'Este CURP ya está registrado',
            'rfc.size' => 'El RFC debe tener exactamente 13 caracteres',
            'rfc.unique' => 'Este RFC ya está registrado',
            'telefono.size' => 'El teléfono debe tener exactamente 10 dígitos',
            'correo.unique' => 'Este correo ya está registrado',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            
            // Mensajes datos laborales
            'id_categoria.required' => 'Debe seleccionar una categoría',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio',
            'sueldo_diarios.min' => 'El sueldo debe ser mayor a 0',
            
            // ✅ NUEVOS: Mensajes para campos laborales específicos
            'horas_trabajo.numeric' => 'Las horas de trabajo deben ser un número',
            'horas_trabajo.min' => 'Las horas de trabajo deben ser al menos 1',
            'horas_trabajo.max' => 'Las horas de trabajo no pueden exceder 24',
            'turno.in' => 'El turno debe ser: diurno, nocturno o mixto',
            
            // ✅ CORREGIDO: Mensajes para contrato que coinciden con el frontend
            'fecha_inicio_contrato.required' => 'La fecha de inicio del contrato es obligatoria',
            'fecha_inicio_contrato.after_or_equal' => 'La fecha de inicio del contrato no puede ser pasada',
            'fecha_fin_contrato.required' => 'La fecha de fin del contrato es obligatoria',
            'fecha_fin_contrato.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'tipo_duracion.required' => 'El tipo de duración del contrato es obligatorio',
            'tipo_duracion.in' => 'El tipo de duración debe ser "dias" o "meses"',
            // ✅ ELIMINADO: Mensajes de duracion_meses
            
            // Contacto (sin cambios)
            'contacto_nombre_completo.max' => 'El nombre completo no debe exceder 150 caracteres',
            'contacto_telefono_principal.size' => 'El teléfono principal debe tener 10 dígitos',
            'contacto_telefono_secundario.size' => 'El teléfono secundario debe tener 10 dígitos',
        ]);

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

            // 1️⃣ CREAR TRABAJADOR (sin cambios)
            $trabajador = Trabajador::create([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                // ✅ NUEVOS: Campos de ubicación
                'lugar_nacimiento' => $validated['lugar_nacimiento'],
                'estado_actual' => $validated['estado_actual'],
                'ciudad_actual' => $validated['ciudad_actual'],
                // Identificadores oficiales
                'curp' => strtoupper($validated['curp']),
                'rfc' => strtoupper($validated['rfc']),
                'no_nss' => $validated['no_nss'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'antiguedad' => $antiguedadCalculada,
                'estatus' => $validated['estatus'] ?? 'activo',
            ]);

            Log::info('✅ Trabajador creado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'estatus' => $trabajador->estatus
            ]);

            // 2️⃣ CREAR FICHA TÉCNICA (sin cambios)
            $fichaTecnica = FichaTecnica::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'id_categoria' => $validated['id_categoria'],
                'sueldo_diarios' => $validated['sueldo_diarios'],
                'formacion' => $validated['formacion'],
                'grado_estudios' => $validated['grado_estudios'],
                // ✅ NUEVOS: Campos laborales específicos
                'horas_trabajo' => $validated['horas_trabajo'],
                'turno' => $validated['turno'],
            ]);

            Log::info('✅ Ficha técnica creada', ['ficha_id' => $fichaTecnica->id]);

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

            // 4️⃣ ✅ CORREGIDO: GENERAR CONTRATO DEFINITIVO con datos correctos
            $contratoController = new ContratoController();
            $contrato = $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $validated['fecha_inicio_contrato'],
                'fecha_fin_contrato' => $validated['fecha_fin_contrato'],
                'tipo_duracion' => $validated['tipo_duracion'],
            ]);

            Log::info('✅ Contrato generado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_duracion' => $validated['tipo_duracion']
            ]);

            // 5️⃣ ✅ LIMPIAR ARCHIVOS TEMPORALES (opcional)
            $contratoController->limpiarArchivosTemporales();

            DB::commit();

            // ✅ MENSAJE ACTUALIZADO con duración calculada automáticamente
            $fechaInicio = Carbon::parse($validated['fecha_inicio_contrato']);
            $fechaFin = Carbon::parse($validated['fecha_fin_contrato']);
            
            // Calcular duración para el mensaje
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

            $mensaje = "Trabajador {$trabajador->nombre_completo} creado exitosamente";
            if ($request->filled('contacto_nombre_completo')) {
                $mensaje .= " con contacto de emergencia";
            }
            $mensaje .= " y contrato generado (duración: {$duracionTexto} hasta {$fechaFin->format('d/m/Y')})";

            Log::info('🎉 Trabajador y contrato creados exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'contrato_id' => $contrato->id_contrato,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'estatus' => $trabajador->estatus,
                'duracion_contrato' => $duracionTexto
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error crítico al crear trabajador y contrato', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'usuario' => Auth::user()->email ?? 'Sistema',
                'request_data' => $request->except(['_token'])
            ]);

            $mensajeError = 'Error al crear el trabajador y su contrato: ' . $e->getMessage();

            return back()->withErrors(['error' => $mensajeError])
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