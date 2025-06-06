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
     * Mostrar lista de trabajadores (INDEX) - ✅ OPTIMIZADO
     */
    public function index(Request $request)
    {
        $query = Trabajador::with(['fichaTecnica.categoria.area'])
                          ->where('estatus', '!=', 'inactivo'); // Excluir inactivos por defecto

        // ✅ FILTRO POR ESTADO
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        // Filtros existentes
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

        // Ordenamiento
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        
        $allowedOrderFields = ['nombre_trabajador', 'ape_pat', 'fecha_ingreso', 'created_at', 'updated_at', 'estatus'];
        if (!in_array($orderBy, $allowedOrderFields)) {
            $orderBy = 'created_at';
        }

        $trabajadores = $query->orderBy($orderBy, $orderDirection)
                             ->paginate(12)
                             ->withQueryString();

        // Para los filtros
        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        
        if ($request->filled('area')) {
            $categorias = Categoria::where('id_area', $request->area)
                                 ->orderBy('nombre_categoria')
                                 ->get();
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

        $estados = Trabajador::TODOS_ESTADOS;
        return view('trabajadores.lista_trabajadores', compact(
            'trabajadores', 
            'areas', 
            'categorias', 
            'stats',
            'estados'
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
     * Guardar nuevo trabajador (STORE) - ✅ SIN DOCUMENTOS
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIONES SIN DOCUMENTOS
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

            // ✅ CONTACTO DE EMERGENCIA - CAMPOS CORRECTOS DEL FORMULARIO
            'contacto_nombre' => 'nullable|string|max:50',
            'contacto_apellido_paterno' => 'nullable|string|max:50',
            'contacto_apellido_materno' => 'nullable|string|max:50',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_correo' => 'nullable|email|max:100',
            'contacto_direccion' => 'nullable|string|max:500',
            'contacto_notas' => 'nullable|string|max:1000',
        ], [
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
            'contacto_telefono_principal.size' => 'El teléfono debe tener 10 dígitos',
            'contacto_telefono_secundario.size' => 'El teléfono debe tener 10 dígitos',
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
                'antiguedad' => (int) Carbon::parse($validated['fecha_ingreso'])->diffInYears(now()),
                'estatus' => $validated['estatus'] ?? 'activo',
            ]);

            Log::info('✅ Trabajador creado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'estatus' => $trabajador->estatus
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

            // 3️⃣ CREAR CONTACTO DE EMERGENCIA (SI SE PROPORCIONÓ)
            // ✅ VERIFICAR SI AL MENOS EL NOMBRE ESTÁ PRESENTE
            if ($request->filled('contacto_nombre')) {
                // ✅ CONSTRUIR NOMBRE COMPLETO
                $nombreCompleto = trim($validated['contacto_nombre']);
                if ($validated['contacto_apellido_paterno']) {
                    $nombreCompleto .= ' ' . trim($validated['contacto_apellido_paterno']);
                }
                if ($validated['contacto_apellido_materno']) {
                    $nombreCompleto .= ' ' . trim($validated['contacto_apellido_materno']);
                }

                $contacto = ContactoEmergencia::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'nombre_completo' => $nombreCompleto,
                    'parentesco' => $validated['contacto_parentesco'],
                    'telefono_principal' => $validated['contacto_telefono_principal'],
                    'telefono_secundario' => $validated['contacto_telefono_secundario'],
                    'correo' => $validated['contacto_correo'],
                    'direccion' => $validated['contacto_direccion'],
                    'notas' => $validated['contacto_notas'],
                ]);
                
                Log::info('✅ Contacto de emergencia creado', [
                    'trabajador_id' => $trabajador->id_trabajador,
                    'contacto_id' => $contacto->id_contacto,
                    'nombre_completo' => $nombreCompleto
                ]);
            }

            DB::commit();

            $mensaje = "Trabajador {$trabajador->nombre_completo} creado exitosamente con estado: {$trabajador->estatus_texto}";

            Log::info('🎉 Trabajador creado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'estatus' => $trabajador->estatus
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