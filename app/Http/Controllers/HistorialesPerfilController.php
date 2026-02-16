<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use App\Models\Despidos;
use Illuminate\Http\Request;

class HistorialesPerfilController extends Controller
{
    /**
     * ✅ HISTORIAL DE PERMISOS - VISTA COMPLETA
     */
    public function permisos(Trabajador $trabajador, Request $request)
    {
        $query = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->with('trabajador')
            ->orderBy('fecha_inicio', 'desc');

        // Aplicar filtros si existen
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

        $permisos = $query->paginate(10)->appends($request->query());

        // ✅ CORREGIDO: Apunta a la vista correcta
        return view('trabajadores.secciones_perfil.historial_permisos', [
            'trabajador' => $trabajador,
            'permisos' => $permisos,
            'tiposPermisos' => PermisosLaborales::getTiposDisponibles(),
            'filtros' => $request->all()
        ]);
    }

    /**
     * ✅ HISTORIAL DE BAJAS - VISTA COMPLETA
     */
    public function bajas(Trabajador $trabajador, Request $request)
    {
        $query = Despidos::where('id_trabajador', $trabajador->id_trabajador)
            ->with(['trabajador', 'usuarioCancelacion'])
            ->orderBy('fecha_baja', 'desc');

        // Aplicar filtros si existen
        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->activos();
            } elseif ($request->estado === 'cancelado') {
                $query->cancelados();
            }
        }

        if ($request->filled('condicion')) {
            $query->where('condicion_salida', $request->condicion);
        }

        if ($request->filled('tipo_baja')) {
            $query->where('tipo_baja', $request->tipo_baja);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_baja', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_baja', '<=', $request->hasta);
        }

        $bajas = $query->paginate(10)->appends($request->query());

        // Obtener condiciones únicas para el filtro
        $condiciones = Despidos::where('id_trabajador', $trabajador->id_trabajador)
            ->distinct()
            ->pluck('condicion_salida')
            ->sort()
            ->values();

        // ✅ CORREGIDO: Apunta a la vista correcta
        return view('trabajadores.secciones_perfil.historial_bajas', [
            'trabajador' => $trabajador,
            'bajas' => $bajas,
            'condiciones' => $condiciones,
            'tiposBaja' => Despidos::TIPOS_BAJA,
            'filtros' => $request->all()
        ]);
    }

    /**
     * ✅ DETALLE DE PERMISO - PÁGINA COMPLETA
     */
    public function detallePermiso(PermisosLaborales $permiso)
    {
        $permiso->load(['trabajador']);

        // ✅ CORREGIDO: Apunta a la vista correcta
        return view('trabajadores.secciones_perfil.detalle_permiso', [
            'permiso' => $permiso,
            'trabajador' => $permiso->trabajador
        ]);
    }

    /**
     * ✅ DETALLE DE BAJA - PÁGINA COMPLETA
     */
    public function detalleBaja(Despidos $despido)
    {
        $despido->load(['trabajador', 'usuarioCancelacion']);

        // ✅ CORREGIDO: Apunta a la vista correcta
        return view('trabajadores.secciones_perfil.detalle_baja', [
            'baja' => $despido,
            'trabajador' => $despido->trabajador
        ]);
    }
}