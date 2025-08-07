<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TrabajadoresGeneralesExport;
use App\Exports\TrabajadoresInactivosExport;
use App\Exports\TrabajadoresEnPermisosExport;
use App\Exports\TrabajadoresEnVacacionesExport; // ✅ NUEVO EXPORT
use App\Exports\TrabajadoresCumpleañosExport;

class ExportacionController extends Controller
{
    public function exportar(Request $request)
    {
        $tipo = $request->tipo;
        $mes = $request->mes;

        // ✅ VALIDAR EL TIPO DE EXPORTACIÓN
        $tiposValidos = ['generales', 'inactivos', 'permisos', 'vacaciones', 'cumpleaños'];
        
        if (!in_array($tipo, $tiposValidos)) {
            return back()->withErrors(['error' => 'Tipo de exportación no válido']);
        }

        // ✅ VALIDAR MES PARA CUMPLEAÑOS
        if ($tipo === 'cumpleaños') {
            if (!$mes || !is_numeric($mes) || $mes < 1 || $mes > 12) {
                return back()->withErrors(['error' => 'Debe seleccionar un mes válido para la exportación de cumpleaños']);
            }
        }

        try {
            switch ($tipo) {
                case 'generales':
                    return Excel::download(
                        new TrabajadoresGeneralesExport(), 
                        'trabajadores_generales_' . now()->format('Y-m-d') . '.xlsx'
                    );
                    
                case 'inactivos':
                    return Excel::download(
                        new TrabajadoresInactivosExport(), 
                        'trabajadores_inactivos_suspendidos_' . now()->format('Y-m-d') . '.xlsx'
                    );
                    
                case 'permisos':
                    // ✅ SOLO TRABAJADORES EN PERMISOS
                    return Excel::download(
                        new TrabajadoresEnPermisosExport(), 
                        'trabajadores_en_permisos_' . now()->format('Y-m-d') . '.xlsx'
                    );

                case 'vacaciones':
                    // ✅ NUEVA EXPORTACIÓN SOLO PARA VACACIONES
                    return Excel::download(
                        new TrabajadoresEnVacacionesExport(), 
                        'trabajadores_en_vacaciones_' . now()->format('Y-m-d') . '.xlsx'
                    );
                    
                case 'cumpleaños':
                    $nombreMes = $this->getNombreMes($mes);
                    return Excel::download(
                        new TrabajadoresCumpleañosExport($mes), 
                        'trabajadores_cumpleaños_' . $nombreMes . '_' . now()->format('Y') . '.xlsx'
                    );
                    
                default:
                    return back()->withErrors(['error' => 'Tipo de exportación no implementado']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar la exportación: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ MÉTODO HELPER PARA OBTENER NOMBRE DEL MES
     */
    private function getNombreMes($numeroMes): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        return $meses[$numeroMes] ?? 'mes_' . $numeroMes;
    }

    /**
     * ✅ NUEVO MÉTODO: Obtener estadísticas para el modal de exportación
     */
    public function estadisticasExportacion(): array
    {
        return [
            'total_trabajadores' => Trabajador::count(),
            'trabajadores_activos' => Trabajador::where('estatus', 'activo')->count(),
            'trabajadores_inactivos' => Trabajador::whereIn('estatus', ['inactivo', 'suspendido'])->count(),
            'trabajadores_permisos' => Trabajador::where('estatus', 'permiso')->count(),
            'trabajadores_vacaciones' => Trabajador::where('estatus', 'vacaciones')->count(),
            'trabajadores_prueba' => Trabajador::where('estatus', 'prueba')->count(),
        ];
    }
}