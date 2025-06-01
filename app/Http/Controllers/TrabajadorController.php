<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\DocumentoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TrabajadorController extends Controller
{
    /**
     * Mostrar lista de trabajadores (INDEX)
     */
    public function index(Request $request)
    {
        $query = Trabajador::with(['fichaTecnica.categoria.area', 'documentos'])
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

        // ✅ ESTADÍSTICAS ACTUALIZADAS PARA ENUM
        $stats = [
            // Trabajadores activos
            'activos' => Trabajador::where('estatus', 'activo')->count(),
            
            // Total de trabajadores (excluyendo inactivos)
            'total' => Trabajador::where('estatus', '!=', 'inactivo')->count(),
            
            // Nuevos trabajadores este mes
            'nuevos_este_mes' => Trabajador::where('estatus', '!=', 'inactivo')
                                         ->whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
            
            // En ausencia temporal
            'en_ausencia' => Trabajador::whereIn('estatus', Trabajador::ESTADOS_TEMPORALES)->count(),
            
            // Que requieren atención
            'requieren_atencion' => Trabajador::whereIn('estatus', Trabajador::ESTADOS_CRITICOS)->count(),
            
            // Documentos pendientes (solo activos)
            'documentos_pendientes' => Trabajador::where('estatus', 'activo')
                                                ->whereHas('documentos', function($q) {
                                                    $q->where('porcentaje_completado', '<', 100);
                                                })
                                                ->count(),
            
            // Sin documentos (solo activos)
            'sin_documentos' => Trabajador::where('estatus', 'activo')
                                        ->whereDoesntHave('documentos')
                                        ->count(),
        ];

        // ✅ ESTADOS PARA FILTROS
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
     * Mostrar formulario para crear nuevo trabajador (CREATE) - ✅ MÉTODO FALTANTE
     */
    public function create()
    {
        $areas = Area::orderBy('nombre_area')->get();
        
        return view('trabajadores.crear_trabajador', compact('areas'));
    }

    /**
     * Guardar nuevo trabajador (STORE)
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIONES ACTUALIZADAS
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
            
            // ✅ ESTADO OPCIONAL
            'estatus' => 'nullable|in:' . implode(',', array_keys(Trabajador::TODOS_ESTADOS)),
            
            // Documentos
            'ine' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'acta_nacimiento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nss' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'comprobante_domicilio' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'acta_residencia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'curp_documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'rfc_documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
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
            'estatus.in' => 'El estado seleccionado no es válido',
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
            // 1️⃣ CREAR TRABAJADOR CON ESTADO ENUM
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
                // ✅ ESTADO POR DEFECTO 'activo' O EL SELECCIONADO
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

            Log::info('✅ Ficha técnica creada', ['ficha_id' => $fichaTecnica->id ?? 'N/A']);

            // 3️⃣ PROCESAR DOCUMENTOS
            $documentosData = [
                'id_trabajador' => $trabajador->id_trabajador,
                'porcentaje_completado' => 0.00,
                'documentos_basicos_completos' => false,
                'estado' => 'incompleto',
                'fecha_ultima_actualizacion' => now()
            ];
            
            $documentosSubidos = [];
            $erroresDocumentos = [];

            $tiposDocumentos = [
                'ine', 'acta_nacimiento', 'nss', 'comprobante_domicilio',
                'acta_residencia', 'curp_documento', 'rfc_documento'
            ];
            
            foreach ($tiposDocumentos as $tipo) {
                if ($request->hasFile($tipo)) {
                    try {
                        $archivo = $request->file($tipo);
                        
                        if (!$archivo->isValid()) {
                            throw new \Exception("Archivo {$tipo} no es válido");
                        }
                        
                        $nombreArchivo = $this->generarNombreArchivo($trabajador, $tipo, $archivo);
                        $directorioDestino = "documentos/trabajadores/{$trabajador->id_trabajador}";
                        
                        if (!Storage::disk('public')->exists($directorioDestino)) {
                            Storage::disk('public')->makeDirectory($directorioDestino);
                        }
                        
                        $ruta = $archivo->storeAs($directorioDestino, $nombreArchivo, 'public');
                        
                        if (!$ruta) {
                            throw new \Exception("No se pudo guardar el archivo {$tipo}");
                        }
                        
                        if (!Storage::disk('public')->exists($ruta)) {
                            throw new \Exception("El archivo {$tipo} no existe después de guardarlo");
                        }
                        
                        $documentosData[$tipo] = $ruta;
                        $documentosSubidos[] = $tipo;
                        
                        Log::info("✅ Documento {$tipo} guardado", [
                            'trabajador_id' => $trabajador->id_trabajador,
                            'ruta' => $ruta,
                            'tamaño' => $archivo->getSize(),
                            'archivo_original' => $archivo->getClientOriginalName()
                        ]);
                        
                    } catch (\Exception $e) {
                        $erroresDocumentos[] = "Error en {$tipo}: " . $e->getMessage();
                        Log::error("❌ Error procesando documento {$tipo}", [
                            'trabajador_id' => $trabajador->id_trabajador,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            // 4️⃣ CREAR REGISTRO DE DOCUMENTOS
            $documentos = DocumentoTrabajador::create($documentosData);
            
            if (!$documentos) {
                throw new \Exception('No se pudo crear el registro de documentos');
            }

            Log::info('✅ Registro de documentos creado', [
                'documento_id' => $documentos->id_documento,
                'trabajador_id' => $trabajador->id_trabajador,
                'documentos_subidos' => $documentosSubidos,
                'errores_documentos' => $erroresDocumentos
            ]);

            DB::commit();

            $mensaje = "Trabajador {$trabajador->nombre_completo} creado exitosamente con estado: {$trabajador->estatus_texto}";
            
            if (count($documentosSubidos) > 0) {
                $mensaje .= ". Documentos subidos: " . count($documentosSubidos);
            }
            
            if (count($erroresDocumentos) > 0) {
                $mensaje .= ". Algunos documentos tuvieron errores (revisar logs)";
            }

            Log::info('🎉 Trabajador creado exitosamente', [
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema',
                'estatus' => $trabajador->estatus,
                'documentos_subidos' => count($documentosSubidos),
                'errores_documentos' => count($erroresDocumentos),
                'porcentaje_inicial' => $documentos->porcentaje_completado
            ]);

            return redirect()->route('trabajadores.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('💥 Error crítico al crear trabajador', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'usuario' => Auth::user()->email ?? 'Sistema',
                'request_data' => $request->except(['_token'] + $tiposDocumentos)
            ]);

            $mensajeError = 'Error al crear el trabajador: ' . $e->getMessage();
            
            if (isset($trabajador) && $trabajador->id_trabajador) {
                try {
                    Storage::disk('public')->deleteDirectory("documentos/trabajadores/{$trabajador->id_trabajador}");
                    Log::info('🧹 Directorio de documentos limpiado tras error');
                } catch (\Exception $cleanupError) {
                    Log::warning('⚠️ No se pudo limpiar directorio de documentos', [
                        'error' => $cleanupError->getMessage()
                    ]);
                }
            }

            return back()->withErrors(['error' => $mensajeError])
                        ->withInput();
        }
    }

    /**
     * Mostrar un trabajador específico (SHOW)
     */
    public function show(Trabajador $trabajador)
    {
        $trabajador->load(['fichaTecnica.categoria.area', 'documentos', 'despido']);
        
        return view('trabajadores.ver_trabajador', compact('trabajador'));
    }

    /**
     * Mostrar formulario para editar trabajador (EDIT) - ✅ MÉTODO FALTANTE
     */
    public function edit(Trabajador $trabajador)
    {
        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        
        // Si el trabajador tiene área, cargar sus categorías
        if ($trabajador->fichaTecnica && $trabajador->fichaTecnica->categoria) {
            $categorias = Categoria::where('id_area', $trabajador->fichaTecnica->categoria->id_area)
                                 ->orderBy('nombre_categoria')
                                 ->get();
        }
        
        return view('trabajadores.editar_trabajador', compact('trabajador', 'areas', 'categorias'));
    }

    /**
     * Actualizar trabajador (UPDATE) - ✅ MÉTODO FALTANTE
     */
    public function update(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            // Datos personales
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:-18 years',
            'curp' => ['required', 'string', 'size:18', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'rfc' => ['required', 'string', 'size:13', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => ['nullable', 'email', 'max:55', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => 'required|date|before_or_equal:today',
            
            // Datos laborales
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            
            // Estado
            'estatus' => 'required|in:' . implode(',', array_keys(Trabajador::TODOS_ESTADOS)),
        ]);

        DB::beginTransaction();
        
        try {
            // Actualizar trabajador
            $trabajador->update([
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
                'estatus' => $validated['estatus'],
            ]);

            // Actualizar ficha técnica
            if ($trabajador->fichaTecnica) {
                $trabajador->fichaTecnica->update([
                    'id_categoria' => $validated['id_categoria'],
                    'sueldo_diarios' => $validated['sueldo_diarios'],
                    'formacion' => $validated['formacion'],
                    'grado_estudios' => $validated['grado_estudios'],
                ]);
            }

            DB::commit();

            return redirect()->route('trabajadores.show', $trabajador)
                           ->with('success', 'Trabajador actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al actualizar trabajador', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al actualizar el trabajador'])
                        ->withInput();
        }
    }

    /**
     * Eliminar trabajador (DESTROY) - ✅ MÉTODO FALTANTE
     */
    public function destroy(Trabajador $trabajador)
    {
        DB::beginTransaction();
        
        try {
            // Cambiar estado a inactivo en lugar de eliminar
            $trabajador->update(['estatus' => 'inactivo']);
            
            DB::commit();

            return redirect()->route('trabajadores.index')
                           ->with('success', 'Trabajador dado de baja exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al dar de baja trabajador', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al dar de baja el trabajador']);
        }
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
     * Generar nombre único para archivo
     */
    private function generarNombreArchivo(Trabajador $trabajador, string $tipo, $archivo): string
    {
        $extension = $archivo->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $nombre = strtolower(str_replace(' ', '_', $trabajador->nombre_trabajador));
        
        return "{$tipo}_{$nombre}_{$timestamp}.{$extension}";
    }
}