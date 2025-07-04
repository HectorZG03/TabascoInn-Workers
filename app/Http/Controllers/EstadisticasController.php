<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\Despidos;
use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    /**
     * ✅ ESTADÍSTICAS PARA TRABAJADORES
     */
    public function obtenerEstadisticasTrabajadores()
    {
        return [
            'activos' => Trabajador::where('estatus', 'activo')->count(),
            'total' => Trabajador::where('estatus', '!=', 'inactivo')->count(),
            'con_permiso' => Trabajador::where('estatus', 'permiso')->count(),
            'suspendidos' => Trabajador::where('estatus', 'suspendido')->count(),
            'en_prueba' => Trabajador::where('estatus', 'prueba')->count(),
            'por_estado' => [
                'inactivo' => Trabajador::where('estatus', 'inactivo')->count(),
            ]
        ];
    }

    /**
     * ✅ ESTADÍSTICAS PARA DESPIDOS/BAJAS
     */
    public function obtenerEstadisticasDespidos()
    {
        return [
            'total_activos' => Despidos::activos()->count(),
            'total_cancelados' => Despidos::cancelados()->count(),
            'este_mes' => Despidos::delMesActual()->count(),
            'este_año' => Despidos::delAnoActual()->count(),
            'voluntarias' => Despidos::activos()->where('condicion_salida', 'Voluntaria')->count(),
        ];
    }

    /**
     * ✅ ESTADÍSTICAS PARA PERMISOS LABORALES
     */
    public function obtenerEstadisticasPermisos()
    {
        return [
            'total' => PermisosLaborales::count(),
            'activos' => PermisosLaborales::where('estatus_permiso', 'activo')->count(),
            'este_mes' => PermisosLaborales::whereMonth('fecha_inicio', now()->month)
                                        ->whereYear('fecha_inicio', now()->year)
                                        ->count(),
            'finalizados' => PermisosLaborales::where('estatus_permiso', 'finalizado')->count(),
            'cancelados' => PermisosLaborales::where('estatus_permiso', 'cancelado')->count(),
            'vencidos' => PermisosLaborales::where('fecha_fin', '<', now())
                                        ->where('estatus_permiso', 'activo')
                                        ->count(),
        ];
    }

    /**
     * ✅ API ENDPOINT PARA OBTENER ESTADÍSTICAS VÍA AJAX
     */
    public function obtenerEstadisticas(Request $request)
    {
        $tipo = $request->get('tipo');
        
        $estadisticas = match($tipo) {
            'trabajadores' => $this->obtenerEstadisticasTrabajadores(),
            'despidos' => $this->obtenerEstadisticasDespidos(),
            'permisos' => $this->obtenerEstadisticasPermisos(),
            default => ['error' => 'Tipo de estadística no válido']
        };

        return response()->json($estadisticas);
    }

    /**
     * ✅ CONFIGURACIONES DE TARJETAS POR TIPO
     */
    public static function obtenerConfiguracionTarjetas($tipo)
    {
        $configuraciones = [
            'trabajadores' => [
                'activos' => [
                    'titulo' => 'Activos',
                    'icono' => 'bi-person-check',
                    'color' => 'success',
                    'descripcion' => 'Trabajadores activos'
                ],
                'con_permiso' => [
                    'titulo' => 'Con Permiso',
                    'icono' => 'bi-calendar-event',
                    'color' => 'info',
                    'descripcion' => 'Con permisos temporales'
                ],
                'suspendidos' => [
                    'titulo' => 'Suspendidos',
                    'icono' => 'bi-exclamation-triangle',
                    'color' => 'danger',
                    'descripcion' => 'Trabajadores suspendidos'
                ],
                'en_prueba' => [
                    'titulo' => 'En Prueba',
                    'icono' => 'bi-clock-history',
                    'color' => 'warning',
                    'descripcion' => 'Período de prueba'
                ],
                'total' => [
                    'titulo' => 'Total',
                    'icono' => 'bi-people',
                    'color' => 'primary',
                    'descripcion' => 'Total empleados'
                ],
                'por_estado.inactivo' => [
                    'titulo' => 'Inactivos',
                    'icono' => 'bi-person-x',
                    'color' => 'secondary',
                    'descripcion' => 'Empleados inactivos'
                ],
            ],
            
            'despidos' => [
                'total_activos' => [
                    'titulo' => 'Bajas Activas',
                    'icono' => 'bi-people-fill',
                    'color' => 'danger',
                    'descripcion' => 'Bajas vigentes'
                ],
                'este_mes' => [
                    'titulo' => 'Este Mes',
                    'icono' => 'bi-calendar-month',
                    'color' => 'warning',
                    'descripcion' => 'Bajas del mes actual'
                ],
                'este_año' => [
                    'titulo' => 'Este Año',
                    'icono' => 'bi-calendar-year',
                    'color' => 'info',
                    'descripcion' => 'Bajas del año actual'
                ],
                'total_cancelados' => [
                    'titulo' => 'Canceladas',
                    'icono' => 'bi-arrow-clockwise',
                    'color' => 'success',
                    'descripcion' => 'Bajas revertidas'
                ],
            ],
            
            'permisos' => [
                'activos' => [
                    'titulo' => 'Activos',
                    'icono' => 'bi-calendar-check',
                    'color' => 'info',
                    'descripcion' => 'Permisos vigentes'
                ],
                'total' => [
                    'titulo' => 'Total',
                    'icono' => 'bi-calendar-range',
                    'color' => 'primary',
                    'descripcion' => 'Total de permisos'
                ],
                'este_mes' => [
                    'titulo' => 'Este Mes',
                    'icono' => 'bi-calendar-month',
                    'color' => 'success',
                    'descripcion' => 'Permisos del mes'
                ],
                'finalizados' => [
                    'titulo' => 'Finalizados',
                    'icono' => 'bi-calendar-x',
                    'color' => 'warning',
                    'descripcion' => 'Permisos completados'
                ],
                'vencidos' => [
                    'titulo' => 'Vencidos',
                    'icono' => 'bi-exclamation-triangle',
                    'color' => 'danger',
                    'descripcion' => 'Permisos expirados'
                ],
            ]
        ];

        return $configuraciones[$tipo] ?? [];
    }
}