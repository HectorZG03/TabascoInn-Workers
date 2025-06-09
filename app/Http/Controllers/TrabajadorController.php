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
     * ✅ STORE CORREGIDO: Crear trabajador con contacto usando nombre_completo
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIONES CORREGIDAS PARA CONTACTO
        $validated = $request->validate([
            // Datos personales
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:-18 years',
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

            // ✅ CONTACTO CORREGIDO - Validación condicional
            'contacto_nombre_completo' => 'nullable|string|max:150',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_direccion' => 'nullable|string|max:500',
        ], [
            // Mensajes de validación existentes...
            'nombre_trabajador.required' => 'El nombre es obligatorio',
            'ape_pat.required' => 'El apellido paterno es obligatorio',
            'fecha_nacimiento.before' => 'El trabajador debe ser mayor de 18 años',
            'curp.size' => 'El CURP debe tener exactamente 18 caracteres',
            'curp.unique' => 'Este CURP ya está registrado',
            'rfc.size' => 'El RFC debe tener exactamente 13 caracteres',
            'rfc.unique' => 'Este RFC ya está registrado',
            'telefono.size' => 'El teléfono debe tener exactamente 10 dígitos',
            'correo.unique' => 'Este correo ya está registrado',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'id_categoria.required' => 'Debe seleccionar una categoría',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio',
            'sueldo_diarios.min' => 'El sueldo debe ser mayor a 0',
            
            // ✅ MENSAJES CORREGIDOS PARA CONTACTO
            'contacto_nombre_completo.max' => 'El nombre completo no debe exceder 150 caracteres',
            'contacto_telefono_principal.size' => 'El teléfono principal debe tener 10 dígitos',
            'contacto_telefono_secundario.size' => 'El teléfono secundario debe tener 10 dígitos',
        ]);

        // Validar relación área-categoría
        $categoria = Categoria::where('id_categoria', $validated['id_categoria'])
                             ->where('id_area', $validated['id_area'])
                             ->first();
                             
        if (!$categoria) {
            return back()->withErrors(['id_categoria' => 'La categoría no pertenece al área seleccionada'])
                        ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // ✅ CALCULAR ANTIGÜEDAD DE FORMA SEGURA
            $antiguedadCalculada = (int) Carbon::parse($validated['fecha_ingreso'])->diffInYears(now());

            // 1️⃣ CREAR TRABAJADOR
            $trabajador = Trabajador::create([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
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
                'estatus' => $trabajador->estatus,
                'antiguedad' => $antiguedadCalculada
            ]);

            // 2️⃣ CREAR FICHA TÉCNICA
            $fichaTecnica = FichaTecnica::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'id_categoria' => $validated['id_categoria'],
                'sueldo_diarios' => $validated['sueldo_diarios'],
                'formacion' => $validated['formacion'],
                'grado_estudios' => $validated['grado_estudios'],
            ]);

            Log::info('✅ Ficha técnica creada', ['ficha_id' => $fichaTecnica->id]);

            // 3️⃣ ✅ CREAR CONTACTO DE EMERGENCIA (VALIDACIÓN CORREGIDA)
            if ($request->filled('contacto_nombre_completo') && !empty(trim($validated['contacto_nombre_completo']))) {
                $contacto = ContactoEmergencia::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'nombre_completo' => trim($validated['contacto_nombre_completo']),
                    'parentesco' => $validated['contacto_parentesco'],
                    'telefono_principal' => $validated['contacto_telefono_principal'],
                    'telefono_secundario' => $validated['contacto_telefono_secundario'],
                    'direccion' => $validated['contacto_direccion'],
                ]);
                
                Log::info('✅ Contacto de emergencia creado', [
                    'trabajador_id' => $trabajador->id_trabajador,
                    'contacto_id' => $contacto->id_contacto,
                    'nombre_completo' => $contacto->nombre_completo
                ]);
            }

            DB::commit();

            $mensaje = "Trabajador {$trabajador->nombre_completo} creado exitosamente";
            if ($request->filled('contacto_nombre_completo')) {
                $mensaje .= " con contacto de emergencia";
            }
            $mensaje .= " con estado: {$trabajador->estatus_texto}";

            Log::info('🎉 Trabajador creado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'estatus' => $trabajador->estatus,
                'tiene_contacto' => $request->filled('contacto_nombre_completo')
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error crítico al crear trabajador', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'usuario' => Auth::user()->email ?? 'Sistema',
                'request_data' => $request->except(['_token'])
            ]);

            $mensajeError = 'Error al crear el trabajador: ' . $e->getMessage();

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