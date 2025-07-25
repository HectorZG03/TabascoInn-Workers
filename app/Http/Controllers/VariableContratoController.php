<?php

namespace App\Http\Controllers;

use App\Models\VariableContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ✅ Controlador para gestión avanzada de variables de contrato
 */
class VariableContratoController extends Controller
{
    /**
     * Mostrar lista de variables
     */
    public function index(Request $request)
    {
        $query = VariableContrato::query();
        
        // Filtros
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        
        if ($request->filled('tipo_dato')) {
            $query->where('tipo_dato', $request->tipo_dato);
        }
        
        if ($request->filled('activa')) {
            $query->where('activa', $request->boolean('activa'));
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_variable', 'LIKE', "%{$search}%")
                  ->orWhere('etiqueta', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }
        
        $variables = $query->ordenadas()->paginate(20)->withQueryString();
        
        return view('users.configuracion.variables_contrato', compact('variables'));
    }

    /**
     * Crear nueva variable
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_variable' => 'required|string|max:100|unique:variables_contrato,nombre_variable|regex:/^[a-z0-9_]+$/',
            'etiqueta' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'categoria' => 'required|in:' . implode(',', array_keys(VariableContrato::CATEGORIAS)),
            'tipo_dato' => 'required|in:' . implode(',', array_keys(VariableContrato::TIPOS_DATO)),
            'formato_ejemplo' => 'nullable|string|max:200',
            'origen_modelo' => 'nullable|string|max:100',
            'origen_campo' => 'nullable|string|max:100',
            'origen_codigo' => 'nullable|string|max:1000',
            'obligatoria' => 'boolean'
        ], [
            'nombre_variable.regex' => 'El nombre de la variable solo puede contener letras minúsculas, números y guiones bajos',
            'nombre_variable.unique' => 'Ya existe una variable con ese nombre'
        ]);

        try {
            $variable = VariableContrato::create(array_merge($validated, [
                'activa' => true
            ]));

            Log::info('Variable de contrato creada', [
                'variable' => $variable->nombre_variable,
                'usuario' => Auth::user()->email
            ]);

            return back()->with('success', "Variable '{$variable->etiqueta}' creada exitosamente");

        } catch (\Exception $e) {
            Log::error('Error creando variable de contrato', [
                'error' => $e->getMessage(),
                'datos' => $validated
            ]);
            
            return back()->withErrors(['error' => 'Error al crear la variable: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Actualizar variable existente
     */
    public function update(Request $request, VariableContrato $variable)
    {
        $validated = $request->validate([
            'etiqueta' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'categoria' => 'required|in:' . implode(',', array_keys(VariableContrato::CATEGORIAS)),
            'tipo_dato' => 'required|in:' . implode(',', array_keys(VariableContrato::TIPOS_DATO)),
            'formato_ejemplo' => 'nullable|string|max:200',
            'origen_modelo' => 'nullable|string|max:100',
            'origen_campo' => 'nullable|string|max:100',
            'origen_codigo' => 'nullable|string|max:1000',
            'obligatoria' => 'boolean'
        ]);

        try {
            $variable->update($validated);

            Log::info('Variable de contrato actualizada', [
                'variable' => $variable->nombre_variable,
                'usuario' => Auth::user()->email
            ]);

            return back()->with('success', "Variable '{$variable->etiqueta}' actualizada exitosamente");

        } catch (\Exception $e) {
            Log::error('Error actualizando variable de contrato', [
                'variable' => $variable->nombre_variable,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al actualizar la variable: ' . $e->getMessage()]);
        }
    }

    /**
     * Activar/Desactivar variable
     */
    public function toggleEstado(VariableContrato $variable)
    {
        try {
            $nuevoEstado = !$variable->activa;
            
            // Validar que no se desactive una variable obligatoria
            if (!$nuevoEstado && $variable->obligatoria) {
                return back()->withErrors(['error' => 'No se puede desactivar una variable obligatoria']);
            }
            
            $variable->update(['activa' => $nuevoEstado]);

            Log::info('Estado de variable cambiado', [
                'variable' => $variable->nombre_variable,
                'nuevo_estado' => $nuevoEstado ? 'activa' : 'inactiva',
                'usuario' => Auth::user()->email
            ]);

            $mensaje = $nuevoEstado ? 'Variable activada' : 'Variable desactivada';
            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error cambiando estado de variable', [
                'variable' => $variable->nombre_variable,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al cambiar el estado de la variable']);
        }
    }

    /**
     * Eliminar variable (solo si no es obligatoria y no está en uso)
     */
    public function destroy(VariableContrato $variable)
    {
        try {
            // Validar que no sea obligatoria
            if ($variable->obligatoria) {
                return back()->withErrors(['error' => 'No se puede eliminar una variable obligatoria']);
            }
            
            // Validar que no esté en uso en plantillas activas
            $plantillasEnUso = \App\Models\PlantillaContrato::activa()
                ->whereJsonContains('variables_utilizadas', $variable->nombre_variable)
                ->count();
            
            if ($plantillasEnUso > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar una variable que está en uso en plantillas activas']);
            }

            $nombreVariable = $variable->etiqueta;
            $variable->delete();

            Log::info('Variable de contrato eliminada', [
                'variable' => $variable->nombre_variable,
                'usuario' => Auth::user()->email
            ]);

            return back()->with('success', "Variable '{$nombreVariable}' eliminada exitosamente");

        } catch (\Exception $e) {
            Log::error('Error eliminando variable', [
                'variable' => $variable->nombre_variable,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Error al eliminar la variable: ' . $e->getMessage()]);
        }
    }
}