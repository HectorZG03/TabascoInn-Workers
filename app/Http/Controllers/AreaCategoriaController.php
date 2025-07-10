<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Categoria;

class AreaCategoriaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        
        $areas = Area::with('categorias')
            ->when($busqueda, function ($query, $busqueda) {
                return $query->where('nombre_area', 'LIKE', "%{$busqueda}%")
                    ->orWhereHas('categorias', function ($q) use ($busqueda) {
                        $q->where('nombre_categoria', 'LIKE', "%{$busqueda}%");
                    });
            })
            ->orderBy('nombre_area')
            ->paginate(2);

        $todasLasAreas = Area::orderBy('nombre_area')->get();

        return view('users.configuracion.areas_categorias', compact('areas', 'todasLasAreas', 'busqueda'));
    }

    public function storeArea(Request $request)
    {
        $request->validate([
            'nombre_area' => 'required|string|max:100|unique:area,nombre_area'
        ]);

        Area::create(['nombre_area' => $request->nombre_area]);

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

    public function updateArea(Request $request, Area $area)
    {
        $request->validate([
            'nombre_area' => 'required|string|max:100|unique:area,nombre_area,' . $area->id_area . ',id_area'
        ]);

        $area->update(['nombre_area' => $request->nombre_area]);

        return redirect()->back()->with('success', 'Área actualizada correctamente.');
    }

    // ✅ ELIMINACIÓN EN CASCADA: Al eliminar área se eliminan las categorías
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

    // ✅ NUEVO: Eliminar múltiples categorías
    public function destroyMultipleCategories(Request $request)
    {
        $request->validate([
            'categorias' => 'required|array|min:1',
            'categorias.*' => 'exists:categoria,id_categoria'
        ]);

        $count = Categoria::whereIn('id_categoria', $request->categorias)->delete();

        return redirect()->back()->with('success', "Se eliminaron {$count} categoría(s) correctamente.");
    }
}