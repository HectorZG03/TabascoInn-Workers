<?php

namespace App\Http\Controllers;
use App\Models\Trabajador;
use App\Models\Area;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusquedaTrabajadoresController extends Controller
{
    /**
     * Mostrar la página principal de búsqueda
     */
    public function index(Request $request)
    {
        // Obtener datos para los filtros
        $areas = Area::orderBy('nombre_area')->get();
        $categorias = Categoria::with('area')->orderBy('nombre_categoria')->get();
        
        $trabajadores = null;
        $stats = null;

        // ✅ CORRECCIÓN: Verificar parámetros de búsqueda INCLUYENDO 'page' cuando hay session
        $parametrosBusqueda = ['search', 'area', 'estatus', 'categoria', 'sexo', 'edad_min', 'edad_max', 'fecha_ingreso_desde', 'fecha_ingreso_hasta'];
        
        // Verificar si hay búsqueda activa
        $hayBusquedaActiva = $request->hasAny($parametrosBusqueda) || 
                            ($request->has('page') && session()->has('ultima_busqueda'));
        
        if ($hayBusquedaActiva) {
            // Si solo hay parámetro 'page', restaurar la última búsqueda desde la sesión
            if ($request->has('page') && !$request->hasAny($parametrosBusqueda) && session()->has('ultima_busqueda')) {
                $request->merge(session('ultima_busqueda'));
            }
            
            // Guardar parámetros de búsqueda en sesión (solo si no es solo 'page')
            if ($request->hasAny($parametrosBusqueda)) {
                session(['ultima_busqueda' => $request->only($parametrosBusqueda)]);
            }

            $trabajadores = $this->buscarTrabajadores($request);
            $stats = $this->calcularEstadisticas($trabajadores->getCollection());
        } else {
            // Limpiar sesión si no hay búsqueda
            session()->forget('ultima_busqueda');
        }

        return view('trabajadores.buscar', compact('areas', 'categorias', 'trabajadores', 'stats'));
    }

    /**
     * Realizar búsqueda de trabajadores con filtros
     */
    private function buscarTrabajadores(Request $request)
    {
        $query = Trabajador::with([
            'fichaTecnica.categoria.area'
        ]);

        // Filtro de búsqueda general (nombre, apellido, ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                  ->orWhere('id_trabajador', 'LIKE', "%{$search}%")
                  ->orWhereRaw("CONCAT(nombre_trabajador, ' ', ape_pat, ' ', ape_mat) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filtro por área
        if ($request->filled('area')) {
            $query->whereHas('fichaTecnica.categoria.area', function ($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }

        // Filtro por categoría/cargo
        if ($request->filled('categoria')) {
            $query->whereHas('fichaTecnica.categoria', function ($q) use ($request) {
                $q->where('id_categoria', $request->categoria);
            });
        }

        // Filtro por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        // Filtro por género
        if ($request->filled('sexo')) {
            $query->where('sexo', $request->sexo);
        }

        // Filtro por rango de edad
        if ($request->filled('edad_min') || $request->filled('edad_max')) {
            $fechaActual = Carbon::now();
            
            if ($request->filled('edad_min')) {
                $fechaMaxNacimiento = $fechaActual->copy()->subYears($request->edad_min)->endOfYear();
                $query->where('fecha_nacimiento', '<=', $fechaMaxNacimiento);
            }
            
            if ($request->filled('edad_max')) {
                $fechaMinNacimiento = $fechaActual->copy()->subYears($request->edad_max + 1)->startOfYear();
                $query->where('fecha_nacimiento', '>=', $fechaMinNacimiento);
            }
        }

        // Filtro por rango de fechas de ingreso
        if ($request->filled('fecha_ingreso_desde')) {
            $query->where('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
        }

        if ($request->filled('fecha_ingreso_hasta')) {
            $query->where('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
        }

        // Ordenar por nombre
        $query->orderBy('nombre_trabajador')->orderBy('ape_pat');

        // ✅ CORRECCIÓN: Asegurar que los parámetros se mantengan
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100]) ? $perPage : 15;

        return $query->orderBy('nombre_trabajador')
                     ->orderBy('ape_pat')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Calcular estadísticas de los resultados
     */
    private function calcularEstadisticas($trabajadores)
    {
            return [
                'activos' => $trabajadores->where('estatus', 'activo')->count(),
                'en_ausencia' => $trabajadores->whereIn('estatus', [
                    'permiso', 'suspendido', 'prueba'
                ])->count(),
                'inactivos' => $trabajadores->where('estatus', 'inactivo')->count()
            ];
    }

    /**
     * API para búsqueda rápida (AJAX)
     */
    public function busquedaRapida(Request $request)
    {
        if (!$request->filled('q')) {
            return response()->json([]);
        }

        $query = $request->q;
        
        $trabajadores = Trabajador::with(['fichaTecnica.categoria.area'])
            ->where(function ($q) use ($query) {
                $q->where('nombre_trabajador', 'LIKE', "%{$query}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$query}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$query}%")
                  ->orWhere('id_trabajador', 'LIKE', "%{$query}%")
                  ->orWhereRaw("CONCAT(nombre_trabajador, ' ', ape_pat, ' ', ape_mat) LIKE ?", ["%{$query}%"]);
            })
            ->limit(10)
            ->get()
            ->map(function ($trabajador) {
                return [
                    'id' => $trabajador->id_trabajador,
                    'nombre_completo' => $trabajador->nombre_completo,
                    'area' => $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área',
                    'cargo' => $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin cargo',
                    'estatus' => $trabajador->estatus,
                    'url' => route('trabajadores.show', $trabajador)
                ];
            });

        return response()->json($trabajadores);
    }

    /**
     * API para sugerencias de autocompletado
     */
    public function sugerencias(Request $request)
    {
        if (!$request->filled('campo') || !$request->filled('q')) {
            return response()->json([]);
        }

        $campo = $request->campo;
        $query = $request->q;
        $sugerencias = [];

        switch ($campo) {
            case 'nombre':
                $sugerencias = Trabajador::select(DB::raw("DISTINCT CONCAT(nombre_trabajador, ' ', ape_pat, ' ', ape_mat) as sugerencia"))
                    ->whereRaw("CONCAT(nombre_trabajador, ' ', ape_pat, ' ', ape_mat) LIKE ?", ["%{$query}%"])
                    ->limit(5)
                    ->pluck('sugerencia');
                break;

            case 'area':
                $sugerencias = Area::where('nombre_area', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->pluck('nombre_area');
                break;

            case 'categoria':
                $sugerencias = Categoria::where('nombre_categoria', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->pluck('nombre_categoria');
                break;
        }

        return response()->json($sugerencias->values());
    }

    /**
     * API para estadísticas generales
     */
    public function estadisticas()
    {
        $stats = [
            'total_empleados' => Trabajador::count(),
            'activos' => Trabajador::where('estatus', 'activo')->count(),
            'en_ausencia' => Trabajador::whereIn('estatus', [
                'vacaciones', 
                'incapacidad_medica', 
                'licencia_maternidad', 
                'licencia_paternidad', 
                'licencia_sin_goce', 
                'permiso_especial'
            ])->count(),
            'inactivos' => Trabajador::whereIn('estatus', ['despedido', 'retirado'])->count(),
            'por_area' => Area::withCount(['trabajadores' => function ($query) {
                $query->where('estatus', 'activo');
            }])->get()->map(function ($area) {
                return [
                    'nombre' => $area->nombre_area,
                    'cantidad' => $area->trabajadores_count
                ];
            }),
            'ingresos_mes_actual' => Trabajador::whereMonth('fecha_ingreso', Carbon::now()->month)
                ->whereYear('fecha_ingreso', Carbon::now()->year)
                ->count(),
            'proximos_cumpleanos' => Trabajador::whereMonth('fecha_nacimiento', Carbon::now()->month)
                ->whereDay('fecha_nacimiento', '>=', Carbon::now()->day)
                ->where('estatus', 'activo')
                ->count()
        ];

        return response()->json($stats);
    }


    /**
     * Búsqueda sin paginación para exportación
     */
    private function buscarTrabajadoresSinPaginacion(Request $request)
    {
        $query = Trabajador::with(['fichaTecnica.categoria.area']);

        // Aplicar los mismos filtros que en buscarTrabajadores()
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                  ->orWhere('id_trabajador', 'LIKE', "%{$search}%")
                  ->orWhereRaw("CONCAT(nombre_trabajador, ' ', ape_pat, ' ', ape_mat) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('area')) {
            $query->whereHas('fichaTecnica.categoria.area', function ($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }

        if ($request->filled('categoria')) {
            $query->whereHas('fichaTecnica.categoria', function ($q) use ($request) {
                $q->where('id_categoria', $request->categoria);
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('sexo')) {
            $query->where('sexo', $request->sexo);
        }

        if ($request->filled('edad_min') || $request->filled('edad_max')) {
            $fechaActual = Carbon::now();
            
            if ($request->filled('edad_min')) {
                $fechaMaxNacimiento = $fechaActual->copy()->subYears($request->edad_min)->endOfYear();
                $query->where('fecha_nacimiento', '<=', $fechaMaxNacimiento);
            }
            
            if ($request->filled('edad_max')) {
                $fechaMinNacimiento = $fechaActual->copy()->subYears($request->edad_max + 1)->startOfYear();
                $query->where('fecha_nacimiento', '>=', $fechaMinNacimiento);
            }
        }

        if ($request->filled('fecha_ingreso_desde')) {
            $query->where('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
        }

        if ($request->filled('fecha_ingreso_hasta')) {
            $query->where('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
        }

        return $query->orderBy('nombre_trabajador')->orderBy('ape_pat')->get();
    }

}