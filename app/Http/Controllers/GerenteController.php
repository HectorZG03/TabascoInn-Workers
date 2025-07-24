<?php

namespace App\Http\Controllers;

use App\Models\Gerente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GerenteController extends Controller
{
    /**
     * Mostrar lista de gerentes
     */
    public function index(Request $request)
    {
        $query = Gerente::query();
        
        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }
        
        // Filtro por estatus
        if ($request->filled('estatus')) {
            if ($request->estatus === 'activos') {
                $query->activos();
            } elseif ($request->estatus === 'inactivos') {
                $query->where('activo', false);
            }
        }
        
        $gerentes = $query->orderBy('apellido_paterno')
                         ->orderBy('apellido_materno')
                         ->orderBy('nombre')
                         ->paginate(15);
        
        return view('users.configuracion.gerentes_lista', compact('gerentes'));
    }

    /**
     * Crear nuevo gerente
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:15|regex:/^[0-9+\-\s()]*$/',
            'descripcion' => 'nullable|string|max:500'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, paréntesis, guiones y el símbolo +'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('error', 'Por favor corrige los errores en el formulario');
        }

        try {
            Gerente::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'telefono' => $request->telefono,
                'descripcion' => $request->descripcion,
                'activo' => true
            ]);

            return redirect()->route('gerentes.index')
                           ->with('success', 'Gerente registrado exitosamente');
                           
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al registrar el gerente: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar gerente
     */
    public function update(Request $request, Gerente $gerente)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:15|regex:/^[0-9+\-\s()]*$/',
            'descripcion' => 'nullable|string|max:500'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, paréntesis, guiones y el símbolo +'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('error', 'Por favor corrige los errores en el formulario');
        }

        try {
            $gerente->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'telefono' => $request->telefono,
                'descripcion' => $request->descripcion
            ]);

            return redirect()->route('gerentes.index')
                           ->with('success', 'Gerente actualizado exitosamente');
                           
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar el gerente: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estatus del gerente (activar/desactivar)
     */
    public function toggleEstatus(Gerente $gerente)
    {
        try {
            $gerente->update(['activo' => !$gerente->activo]);
            
            $mensaje = $gerente->activo ? 'Gerente activado' : 'Gerente desactivado';
            
            return redirect()->route('gerentes.index')
                           ->with('success', $mensaje . ' exitosamente');
                           
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error al cambiar el estatus: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar gerente permanentemente
     */
    public function destroy(Gerente $gerente)
    {
        try {
            $nombreCompleto = $gerente->nombre_completo;
            $gerente->delete();
            
            return redirect()->route('gerentes.index')
                           ->with('success', "Gerente '{$nombreCompleto}' eliminado exitosamente");
                           
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error al eliminar el gerente: ' . $e->getMessage());
        }
    }

    /**
     * API: Obtener lista de gerentes activos para selects
     */
    public function apiGerentes()
    {
        $gerentes = Gerente::activos()
                          ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')
                          ->orderBy('apellido_paterno')
                          ->get()
                          ->map(function ($gerente) {
                              return [
                                  'id' => $gerente->id,
                                  'nombre_completo' => $gerente->nombre_completo
                              ];
                          });
        
        return response()->json($gerentes);
    }
}