<?php

namespace App\Http\Controllers;

use App\Models\PlantillaContrato;
use App\Models\VariableContrato;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PlantillaContratoController extends Controller
{
    /**
     * Mostrar editor de plantillas
     */
    public function index()
    {
        $plantillas = PlantillaContrato::with(['creador', 'modificador'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        
        $plantillaActiva = PlantillaContrato::obtenerActiva();
        $variablesPorCategoria = VariableContrato::obtenerPorCategorias();
        
        return view('users.configuracion.edicion_contrato', compact(
            'plantillas',
            'plantillaActiva', 
            'variablesPorCategoria'
        ));
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function create(Request $request)
    {
        $plantillaBase = null;
        
        // Si se especifica una plantilla base, cargarla
        if ($request->filled('base')) {
            $plantillaBase = PlantillaContrato::find($request->base);
        }
        
        $variablesPorCategoria = VariableContrato::obtenerPorCategorias();
        
        return view('users.configuracion.crear_plantilla', compact(
            'plantillaBase',
            'variablesPorCategoria'
        ));
    }

    /**
     * Guardar nueva plantilla
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_plantilla' => 'required|string|max:100',
            'tipo_contrato' => 'required|in:determinado,indeterminado,ambos',
            'contenido_html' => 'required|string',
            'descripcion' => 'nullable|string|max:1000',
            'activar_inmediatamente' => 'boolean'
        ]);

        DB::beginTransaction();
        
        try {
            $plantilla = PlantillaContrato::crearNuevaVersion(
                $validated,
                Auth::id()
            );

            // Si no se debe activar inmediatamente, desactivar
            if (!$request->boolean('activar_inmediatamente')) {
                $plantilla->update(['activa' => false]);
            }

            DB::commit();

            Log::info('Plantilla de contrato creada', [
                'plantilla_id' => $plantilla->id_plantilla,
                'tipo' => $plantilla->tipo_contrato,
                'version' => $plantilla->version,
                'usuario' => Auth::user()->email
            ]);

            return redirect()->route('configuracion.plantillas.index')
                           ->with('success', "Plantilla '{$plantilla->nombre_plantilla}' creada exitosamente (Versión {$plantilla->version})");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creando plantilla de contrato', [
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email
            ]);
            
            return back()->withErrors(['error' => 'Error al crear la plantilla: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Mostrar plantilla específica
     */
    public function show(PlantillaContrato $plantilla)
    {
        $plantilla->load(['creador', 'modificador']);
        $variablesUtilizadas = VariableContrato::whereIn('nombre_variable', $plantilla->variables_utilizadas ?? [])
                                             ->activas()
                                             ->get();
        
        return view('users.configuracion.ver_plantilla', compact(
            'plantilla',
            'variablesUtilizadas'
        ));
    }

    /**
     * Editar plantilla existente
     */
    public function edit(PlantillaContrato $plantilla)
    {
        $variablesPorCategoria = VariableContrato::obtenerPorCategorias();
        
        return view('users.configuracion.editar_plantilla', compact(
            'plantilla',
            'variablesPorCategoria'
        ));
    }

    /**
     * Actualizar plantilla
     */
    public function update(Request $request, PlantillaContrato $plantilla)
    {
        $validated = $request->validate([
            'nombre_plantilla' => 'required|string|max:100',
            'tipo_contrato' => 'required|in:determinado,indeterminado,ambos',
            'contenido_html' => 'required|string',
            'descripcion' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        
        try {
            // Crear nueva versión en lugar de editar la existente
            $nuevaPlantilla = PlantillaContrato::crearNuevaVersion(
                array_merge($validated, [
                    'nombre_plantilla' => $plantilla->nombre_plantilla // Mantener el mismo nombre
                ]),
                Auth::id()
            );

            DB::commit();

            Log::info('Plantilla de contrato actualizada', [
                'plantilla_anterior' => $plantilla->id_plantilla,
                'plantilla_nueva' => $nuevaPlantilla->id_plantilla,
                'version_anterior' => $plantilla->version,
                'version_nueva' => $nuevaPlantilla->version,
                'usuario' => Auth::user()->email
            ]);

            return redirect()->route('configuracion.plantillas.index')
                           ->with('success', "Plantilla actualizada exitosamente. Nueva versión: {$nuevaPlantilla->version}");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error actualizando plantilla de contrato', [
                'plantilla_id' => $plantilla->id_plantilla,
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email
            ]);
            
            return back()->withErrors(['error' => 'Error al actualizar la plantilla: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Activar/Desactivar plantilla
     */
    public function toggleActivacion(PlantillaContrato $plantilla)
    {
        DB::beginTransaction();
        
        try {
            if (!$plantilla->activa) {
                // Activar esta plantilla y desactivar otras del mismo tipo
                PlantillaContrato::where('tipo_contrato', $plantilla->tipo_contrato)
                                ->orWhere('tipo_contrato', 'ambos')
                                ->where('id_plantilla', '!=', $plantilla->id_plantilla)
                                ->update(['activa' => false]);
                
                $plantilla->update(['activa' => true]);
                $mensaje = "Plantilla activada correctamente";
            } else {
                $plantilla->update(['activa' => false]);
                $mensaje = "Plantilla desactivada correctamente";
            }

            DB::commit();

            Log::info('Estado de plantilla cambiado', [
                'plantilla_id' => $plantilla->id_plantilla,
                'nuevo_estado' => $plantilla->fresh()->activa ? 'activa' : 'inactiva',
                'usuario' => Auth::user()->email
            ]);

            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error cambiando estado de plantilla', [
                'plantilla_id' => $plantilla->id_plantilla,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al cambiar el estado de la plantilla']);
        }
    }

    /**
     * Vista previa de contrato con datos reales
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'contenido_html' => 'required|string',
            'trabajador_id' => 'nullable|exists:trabajadores,id_trabajador',
            'tipo_contrato' => 'required|in:determinado,indeterminado'
        ]);

        try {
            // Obtener trabajador de muestra o crear datos de ejemplo
            if ($validated['trabajador_id']) {
                $trabajador = Trabajador::with(['fichaTecnica.categoria'])->find($validated['trabajador_id']);
            } else {
                $trabajador = $this->obtenerTrabajadorEjemplo();
            }

            // Datos de contrato de ejemplo
            $datosContrato = [
                'tipo_contrato' => $validated['tipo_contrato'],
                'fecha_inicio' => \Carbon\Carbon::now(),
                'fecha_fin' => $validated['tipo_contrato'] === 'determinado' ? \Carbon\Carbon::now()->addMonths(6) : null,
                'duracion_texto' => $validated['tipo_contrato'] === 'determinado' ? '6 meses' : 'Tiempo Indeterminado',
                'salario_texto' => 'CUATROCIENTOS CINCUENTA PESOS'
            ];

            // Obtener valores de todas las variables
            $valoresVariables = $this->obtenerValoresVariables($trabajador, $datosContrato);
            
            // Crear plantilla temporal para el preview
            $plantillaTemporal = new PlantillaContrato(['contenido_html' => $validated['contenido_html']]);
            
            // Reemplazar variables
            $contenidoFinal = $plantillaTemporal->reemplazarVariables($valoresVariables);

            return response()->json([
                'success' => true,
                'contenido_html' => $contenidoFinal,
                'trabajador_nombre' => $trabajador->nombre_completo ?? 'Trabajador de Ejemplo',
                'variables_utilizadas' => PlantillaContrato::extraerVariables($validated['contenido_html'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en preview de plantilla', [
                'error' => $e->getMessage(),
                'trabajador_id' => $validated['trabajador_id'] ?? 'ninguno'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error generando vista previa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de variables disponibles para AJAX
     */
    public function obtenerVariables(Request $request)
    {
        $categoria = $request->get('categoria');
        
        $query = VariableContrato::activas()->ordenadas();
        
        if ($categoria) {
            $query->porCategoria($categoria);
        }
        
        $variables = $query->get();
        
        return response()->json([
            'success' => true,
            'variables' => $variables->map(function ($variable) {
                return [
                    'id' => $variable->id_variable,
                    'nombre' => $variable->nombre_variable,
                    'etiqueta' => $variable->etiqueta,
                    'categoria' => $variable->categoria_text,
                    'tipo' => $variable->tipo_dato_text,
                    'ejemplo' => $variable->formato_ejemplo,
                    'variable_formateada' => $variable->variable_formateada,
                    'obligatoria' => $variable->obligatoria,
                    'descripcion' => $variable->descripcion
                ];
            })
        ]);
    }

    /**
     * Exportar plantilla activa
     */
    public function exportar($tipo = 'ambos')
    {
        $plantilla = PlantillaContrato::obtenerActiva($tipo);
        
        if (!$plantilla) {
            return back()->withErrors(['error' => 'No hay plantilla activa para exportar']);
        }
        
        $nombreArchivo = "plantilla_contrato_{$tipo}_v{$plantilla->version}.html";
        
        return Response::make($plantilla->contenido_html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"'
        ]);
    }

    // ===== MÉTODOS PRIVADOS =====
    
    /**
     * Obtener trabajador de ejemplo para preview
     */
    private function obtenerTrabajadorEjemplo()
    {
        return (object) [
            'nombre_completo' => 'JUAN PÉREZ GARCÍA',
            'fecha_nacimiento' => '1985-03-15',
            'curp' => 'PEGJ850315HTCRNS01',
            'rfc' => 'PEGJ850315ABC',
            'direccion' => 'Av. Siempre Viva 123, Col. Centro, Villahermosa, Tabasco',
            'lugar_nacimiento' => 'Villahermosa, Tabasco',
            'fecha_ingreso' => '2020-01-15',
            'fichaTecnica' => (object) [
                'categoria' => (object) ['nombre_categoria' => 'RECEPCIONISTA'],
                'sueldo_diarios' => 450.00,
                'hora_entrada' => '08:00:00',
                'hora_salida' => '17:00:00',
                'horas_semanales' => 40,
                'horas_trabajo' => 8,
                'turno' => 'diurno',
                'beneficiario_nombre' => 'MARÍA GONZÁLEZ LÓPEZ',
                'beneficiario_parentesco' => 'esposa'
            ]
        ];
    }

    /**
     * Obtener valores de todas las variables
     */
    private function obtenerValoresVariables($trabajador, array $datosContrato): array
    {
        $variables = VariableContrato::activas()->get();
        $valores = [];
        
        foreach ($variables as $variable) {
            $valores[$variable->nombre_variable] = $variable->obtenerValor($trabajador, $datosContrato);
        }
        
        return $valores;
    }
}