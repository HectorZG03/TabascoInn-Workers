<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use App\Models\Despidos;
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

    /**
     * ✅ HISTORIAL DE BAJAS/DESPIDOS
     */
    public function bajas(Trabajador $trabajador, Request $request)
    {
        $query = Despidos::where('id_trabajador', $trabajador->id_trabajador)
            ->with(['trabajador', 'usuarioCancelacion'])
            ->orderBy('fecha_baja', 'desc');

        // Filtros
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

        $bajas = $query->paginate(10);

        // Obtener condiciones únicas para el filtro
        $condiciones = Despidos::where('id_trabajador', $trabajador->id_trabajador)
            ->distinct()
            ->pluck('condicion_salida')
            ->sort()
            ->values();

        return response()->json([
            'html' => view('trabajadores.secciones_perfil.historial_bajas', [
                'bajas' => $bajas,
                'condiciones' => $condiciones,
                'tiposBaja' => Despidos::TIPOS_BAJA
            ])->render()
        ]);
    }

    /**
     * ✅ NUEVO: Obtener detalle de un permiso específico
     */
    public function detallePermiso(PermisosLaborales $permiso)
    {
        // Cargar relaciones necesarias
        $permiso->load(['trabajador']);

        return response()->json([
            'permiso' => [
                'id' => $permiso->id_permiso,
                'trabajador' => $permiso->trabajador->nombre_completo,
                'tipo' => $permiso->tipo_permiso_texto,
                'motivo' => $permiso->motivo,
                'fecha_inicio' => $permiso->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $permiso->fecha_fin->format('d/m/Y'),
                'dias_de_permiso' => $permiso->dias_de_permiso,
                'estado' => $permiso->estatus_permiso_texto,
                'observaciones' => $permiso->observaciones,
                'fecha_solicitud' => $permiso->created_at->format('d/m/Y H:i'),
                'estado_clase' => $this->getClaseEstadoPermiso($permiso->estatus_permiso)
            ]
        ]);
    }

    /**
     * ✅ NUEVO: Obtener detalle de una baja específica
     */
    public function detalleBaja(Despidos $despido)
    {
        // Cargar relaciones necesarias
        $despido->load(['trabajador', 'usuarioCancelacion']);

        return response()->json([
            'baja' => [
                'id' => $despido->id_baja,
                'trabajador' => $despido->trabajador->nombre_completo,
                'fecha_baja' => $despido->fecha_baja->format('d/m/Y'),
                'fecha_baja_relativa' => $despido->fecha_baja->diffForHumans(),
                'condicion_salida' => $despido->condicion_salida,
                'tipo_baja' => $despido->tipo_baja_texto,
                'motivo' => $despido->motivo,
                'observaciones' => $despido->observaciones,
                'estado' => $despido->estado_texto,
                'estado_clase' => $despido->es_activo ? 'danger' : 'success',
                'fecha_reintegro' => $despido->fecha_reintegro ? $despido->fecha_reintegro->format('d/m/Y') : null,
                'fecha_reintegro_relativa' => $despido->fecha_reintegro ? 
                    ($despido->fecha_reintegro->isPast() ? 
                        'Venció ' . $despido->fecha_reintegro->diffForHumans() : 
                        $despido->fecha_reintegro->diffForHumans()) : null,
                'fecha_cancelacion' => $despido->fecha_cancelacion ? $despido->fecha_cancelacion->format('d/m/Y H:i') : null,
                'motivo_cancelacion' => $despido->motivo_cancelacion,
                'fecha_creacion' => $despido->created_at->format('d/m/Y H:i')
            ]
        ]);
    }

    /**
     * Helper para obtener clase CSS del estado del permiso
     */
    private function getClaseEstadoPermiso($estado)
    {
        return match($estado) {
            'activo' => 'success',
            'finalizado' => 'info',
            'cancelado' => 'secondary',
            default => 'secondary'
        };
    }
}