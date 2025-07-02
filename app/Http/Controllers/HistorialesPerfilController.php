<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class HistorialesPerfilController extends Controller
{
    public function permisos(Trabajador $trabajador, Request $request)
    {
        $query = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->with('trabajador')
            ->orderBy('fecha_inicio', 'desc');

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo_permiso', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('estatus_permiso', $request->estado);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->hasta);
        }

        $permisos = $query->paginate(10);

        return response()->json([
            'html' => view('trabajadores.secciones_perfil.historial_permisos', [
                'permisos' => $permisos,
                'tiposPermisos' => PermisosLaborales::getTiposDisponibles()
            ])->render()
        ]);
    }
}