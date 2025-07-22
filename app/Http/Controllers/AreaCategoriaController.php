<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\Departamento; // ✅ Nueva importación

class AreaCategoriaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        
        // ✅ Cargar departamentos en las áreas
        $areas = Area::with(['categorias', 'departamento'])
            ->when($busqueda, function ($query, $busqueda) {
                return $query->where('nombre_area', 'LIKE', "%{$busqueda}%")
                    ->orWhereHas('categorias', function ($q) use ($busqueda) {
                        $q->where('nombre_categoria', 'LIKE', "%{$busqueda}%");
                    })
                    ->orWhereHas('departamento', function ($q) use ($busqueda) {
                        $q->where('nombre_departamento', 'LIKE', "%{$busqueda}%");
                    });
            })
            ->orderBy('nombre_area')
            ->paginate(2);

        // ✅ Cargar áreas con departamentos
        $todasLasAreas = Area::with('departamento')->orderBy('nombre_area')->get();
        
        // ✅ Cargar todos los departamentos
        $departamentos = Departamento::orderBy('nombre_departamento')->get();

        return view('users.configuracion.areas_categorias', compact('areas', 'todasLasAreas', 'departamentos', 'busqueda'));
    }

    // ✅ NUEVO: Crear departamento
    public function storeDepartamento(Request $request)
    {
        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamentos,nombre_departamento',
            'descripcion' => 'nullable|string|max:255'
        ]);

        Departamento::create([
            'nombre_departamento' => $request->nombre_departamento,
            'descripcion' => $request->descripcion
        ]);

        return redirect()->back()->with('success', 'Departamento creado correctamente.');
    }

    // ✅ ACTUALIZADO: Incluir departamento
    public function storeArea(Request $request)
    {
        $request->validate([
            'id_departamento' => 'required|exists:departamentos,id_departamento',
            'nombre_area' => 'required|string|max:100|unique:area,nombre_area'
        ]);

        Area::create([
            'id_departamento' => $request->id_departamento,
            'nombre_area' => $request->nombre_area
        ]);

        return redirect()->back()->with('success', 'Área creada correctamente.');
    }

    public function storeCategoria(Request $request)
    {
        $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'nombre_categoria' => 'required|string|max:100|unique:categoria,nombre_categoria'
        ]);

        Categoria::create([
            'id_area' => $request->id_area,
            'nombre_categoria' => $request->nombre_categoria,
        ]);

        return redirect()->back()->with('success', 'Categoría creada correctamente.');
    }

    // ✅ NUEVO: Actualizar departamento
    public function updateDepartamento(Request $request, Departamento $departamento)
    {
        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamentos,nombre_departamento,' . $departamento->id_departamento . ',id_departamento',
            'descripcion' => 'nullable|string|max:255'
        ]);

        $departamento->update([
            'nombre_departamento' => $request->nombre_departamento,
            'descripcion' => $request->descripcion
        ]);

        return redirect()->back()->with('success', 'Departamento actualizado correctamente.');
    }

    // ✅ ACTUALIZADO: Incluir departamento
    public function updateArea(Request $request, Area $area)
    {
        $request->validate([
            'id_departamento' => 'required|exists:departamentos,id_departamento',
            'nombre_area' => 'required|string|max:100|unique:area,nombre_area,' . $area->id_area . ',id_area'
        ]);

        $area->update([
            'id_departamento' => $request->id_departamento,
            'nombre_area' => $request->nombre_area
        ]);

        return redirect()->back()->with('success', 'Área actualizada correctamente.');
    }

    // ✅ NUEVO: Eliminar departamento (elimina áreas y categorías en cascada)
    public function destroyDepartamento(Departamento $departamento)
    {
        $areasCount = $departamento->areas()->count();
        $categoriasCount = $departamento->categorias()->count();
        
        // Eliminar primero las categorías de todas las áreas del departamento
        foreach ($departamento->areas as $area) {
            $area->categorias()->delete();
        }
        
        // Luego eliminar las áreas del departamento
        $departamento->areas()->delete();
        
        // Finalmente eliminar el departamento
        $departamento->delete();

        $mensaje = "Departamento eliminado correctamente";
        if ($areasCount > 0) {
            $mensaje .= " junto con {$areasCount} área(s)";
        }
        if ($categoriasCount > 0) {
            $mensaje .= " y {$categoriasCount} categoría(s)";
        }
        $mensaje .= ".";

        return redirect()->back()->with('success', $mensaje);
    }

    // ✅ ACTUALIZADO: Eliminar área y sus categorías
    public function destroyArea(Area $area)
    {
        $categoriasCount = $area->categorias()->count();
        
        // Eliminar primero las categorías asociadas
        $area->categorias()->delete();
        
        // Luego eliminar el área
        $area->delete();

        $mensaje = $categoriasCount > 0 
            ? "Área eliminada correctamente junto con {$categoriasCount} categoría(s)."
            : "Área eliminada correctamente.";

        return redirect()->back()->with('success', $mensaje);
    }

    public function updateCategoria(Request $request, Categoria $categoria)
    {
        $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'nombre_categoria' => 'required|string|max:100|unique:categoria,nombre_categoria,' . $categoria->id_categoria . ',id_categoria'
        ]);

        $categoria->update([
            'id_area' => $request->id_area,
            'nombre_categoria' => $request->nombre_categoria,
        ]);

        return redirect()->back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroyCategoria(Categoria $categoria)
    {
        $categoria->delete();
        return redirect()->back()->with('success', 'Categoría eliminada correctamente.');
    }

    public function destroyMultipleCategories(Request $request)
    {
        $request->validate([
            'categorias' => 'required|array|min:1',
            'categorias.*' => 'exists:categoria,id_categoria'
        ]);

        $count = Categoria::whereIn('id_categoria', $request->categorias)->delete();

        return redirect()->back()->with('success', "Se eliminaron {$count} categoría(s) correctamente.");
    }

    // ✅ NUEVO: API para obtener áreas por departamento
    public function getAreasPorDepartamento(Departamento $departamento)
    {
        $areas = $departamento->areas()->select('id_area', 'nombre_area')->orderBy('nombre_area')->get();
        return response()->json($areas);
    }
}