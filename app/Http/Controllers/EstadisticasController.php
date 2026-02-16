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
    // Obtener todos los trabajadores (incluye inactivos para total)
    $total = Trabajador::count();
    
    // Trabajadores inactivos (dados de baja definitivamente)
    $inactivos = Trabajador::where('estatus', 'inactivo')->count();
    
    // Trabajadores en vacaciones
    $enVacaciones = Trabajador::where('estatus', 'vacaciones')->count();
    
    // Trabajadores activos (estado activo normal)
    $activos = Trabajador::where('estatus', 'activo')->count();
    
    // Trabajadores en prueba
    $enPrueba = Trabajador::where('estatus', 'prueba')->count();
    
    // Trabajadores suspendidos
    $suspendidos = Trabajador::where('estatus', 'suspendido')->count();

    return [
        'total' => $total,
        'inactivos' => $inactivos,
        'en_vacaciones' => $enVacaciones,
        'activos' => $activos,
        'en_prueba' => $enPrueba,
        'suspendidos' => $suspendidos
    ];
}

// TAMBIÉN REEMPLAZAR la sección 'trabajadores' en obtenerConfiguracionTarjetas() con esta:

public static function obtenerConfiguracionTarjetas($tipo)
{
    $configuraciones = [
        'trabajadores' => [
            'total' => [
                'titulo' => 'Total Trabajadores',
                'icono' => 'bi-people-fill',
                'color' => 'info',
                'descripcion' => 'Todos los trabajadores'
            ],
            'inactivos' => [
                'titulo' => 'Inactivos',
                'icono' => 'bi-person-x',
                'color' => 'secondary',
                'descripcion' => 'Trabajadores dados de baja'
            ],
            'en_vacaciones' => [
                'titulo' => 'En Vacaciones',
                'icono' => 'bi-calendar-check',
                'color' => 'success',
                'descripcion' => 'Trabajadores de vacaciones'
            ],
            'activos' => [
                'titulo' => 'Activos',
                'icono' => 'bi-person-check',
                'color' => 'primary',
                'descripcion' => 'Trabajadores activos'
            ],
            'en_prueba' => [
                'titulo' => 'En Prueba',
                'icono' => 'bi-person-plus',
                'color' => 'warning',
                'descripcion' => 'Período de prueba'
            ],
            'suspendidos' => [
                'titulo' => 'Suspendidos',
                'icono' => 'bi-person-dash',
                'color' => 'danger',
                'descripcion' => 'Trabajadores suspendidos'
            ]
        ],
        
        // ... resto de configuraciones permanece igual
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

}