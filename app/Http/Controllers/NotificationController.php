<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\VacacionesTrabajador;
use App\Models\ContratoTrabajador;
use App\Models\PermisosLaborales;
use App\Models\Despidos;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function eventosProximos()
    {
        $now = Carbon::now()->startOfDay(); // ✅ INICIAR AL PRINCIPIO DEL DÍA
        $threeDaysLater = $now->copy()->addDays(3);
        $sevenDaysLater = $now->copy()->addDays(7); // Para contratos, usar 7 días

        $notifications = [];

        try {
            // ✅ VACACIONES - MEJORADO
            $this->procesarVacaciones($notifications, $now, $threeDaysLater);

            // ✅ CONTRATOS - MEJORADO 
            $this->procesarContratos($notifications, $now, $sevenDaysLater);

            // ✅ PERMISOS - MEJORADO
            $this->procesarPermisos($notifications, $now, $threeDaysLater);

            // ✅ DESPIDOS TEMPORALES - MEJORADO
            $this->procesarDespidos($notifications, $now, $threeDaysLater);

        } catch (\Exception $e) {
            Log::error('Error en notificaciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Log para debugging
        Log::info('Notificaciones generadas', [
            'total' => count($notifications),
            'tipos' => array_count_values(array_column($notifications, 'tipo'))
        ]);

        return response()->json($notifications);
    }

    /**
     * ✅ PROCESAR VACACIONES - CORREGIDO DECIMALES Y RANGO
     */
    private function procesarVacaciones(&$notifications, $now, $threeDaysLater)
    {
        // ✅ VACACIONES PRÓXIMAS A INICIAR (estado pendiente) - INCLUIR HOY
        $vacacionesPorIniciar = VacacionesTrabajador::with('trabajador')
            ->where('estado', 'pendiente')
            ->where('fecha_inicio', '>=', $now->toDateString()) // ✅ DESDE HOY EN ADELANTE
            ->where('fecha_inicio', '<=', $threeDaysLater->toDateString())
            ->get();

        Log::info('Vacaciones por iniciar encontradas', ['count' => $vacacionesPorIniciar->count()]);

        foreach ($vacacionesPorIniciar as $vacacion) {
            // ✅ CÁLCULO CORREGIDO - SIN DECIMALES
            $fechaInicio = Carbon::parse($vacacion->fecha_inicio)->startOfDay();
            $diasRestantes = $now->diffInDays($fechaInicio, false);
            
            // ✅ ASEGURAR QUE SEA ENTERO Y NO NEGATIVO
            $diasRestantes = max(0, intval($diasRestantes));
            
            $notifications[] = [
                'id' => 'vacacion_inicio_' . $vacacion->id_vacacion,
                'tipo' => 'vacacion_inicio',
                'trabajador' => $vacacion->trabajador->nombre_completo,
                'mensaje' => $diasRestantes == 0 
                    ? "Vacaciones inician HOY" 
                    : "Vacaciones inician en {$diasRestantes} día(s) - {$vacacion->fecha_inicio->format('d/m/Y')}",
                'fecha_evento' => $vacacion->fecha_inicio->format('Y-m-d'),
                'urgencia' => $diasRestantes <= 1 ? 'alta' : 'media',
                'datos' => [
                    'trabajador_id' => $vacacion->trabajador->id_trabajador,
                    'vacacion_id' => $vacacion->id_vacacion,
                    'accion_requerida' => 'iniciar_vacaciones'
                ]
            ];
        }

        // ✅ VACACIONES PRÓXIMAS A FINALIZAR (estado activa) - INCLUIR HOY
        $vacacionesPorFinalizar = VacacionesTrabajador::with('trabajador')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>=', $now->toDateString()) // ✅ DESDE HOY EN ADELANTE
            ->where('fecha_fin', '<=', $threeDaysLater->toDateString())
            ->get();

        Log::info('Vacaciones por finalizar encontradas', ['count' => $vacacionesPorFinalizar->count()]);

        foreach ($vacacionesPorFinalizar as $vacacion) {
            // ✅ CÁLCULO CORREGIDO - SIN DECIMALES
            $fechaFin = Carbon::parse($vacacion->fecha_fin)->startOfDay();
            $diasRestantes = $now->diffInDays($fechaFin, false);
            
            // ✅ ASEGURAR QUE SEA ENTERO Y NO NEGATIVO
            $diasRestantes = max(0, intval($diasRestantes));
            
            $notifications[] = [
                'id' => 'vacacion_fin_' . $vacacion->id_vacacion,
                'tipo' => 'vacacion_fin',
                'trabajador' => $vacacion->trabajador->nombre_completo,
                'mensaje' => $diasRestantes == 0 
                    ? "Vacaciones finalizan HOY" 
                    : "Vacaciones finalizan en {$diasRestantes} día(s) - {$vacacion->fecha_fin->format('d/m/Y')}",
                'fecha_evento' => $vacacion->fecha_fin->format('Y-m-d'),
                'urgencia' => $diasRestantes <= 1 ? 'alta' : 'media',
                'datos' => [
                    'trabajador_id' => $vacacion->trabajador->id_trabajador,
                    'vacacion_id' => $vacacion->id_vacacion,
                    'accion_requerida' => 'finalizar_vacaciones'
                ]
            ];
        }

        // ✅ VACACIONES VENCIDAS (deberían haber terminado pero siguen activas)
        $vacacionesVencidas = VacacionesTrabajador::with('trabajador')
            ->where('estado', 'activa')
            ->where('fecha_fin', '<', $now->toDateString())
            ->get();

        foreach ($vacacionesVencidas as $vacacion) {
            $fechaFin = Carbon::parse($vacacion->fecha_fin)->startOfDay();
            $diasVencidos = intval($fechaFin->diffInDays($now)); // ✅ ENTERO
            
            $notifications[] = [
                'id' => 'vacacion_vencida_' . $vacacion->id_vacacion,
                'tipo' => 'vacacion_vencida',
                'trabajador' => $vacacion->trabajador->nombre_completo,
                'mensaje' => "Vacaciones VENCIDAS desde hace {$diasVencidos} día(s) - Finalizar urgente",
                'fecha_evento' => $vacacion->fecha_fin->format('Y-m-d'),
                'urgencia' => 'critica',
                'datos' => [
                    'trabajador_id' => $vacacion->trabajador->id_trabajador,
                    'vacacion_id' => $vacacion->id_vacacion,
                    'accion_requerida' => 'finalizar_vacaciones_urgente'
                ]
            ];
        }
    }

    /**
     * ✅ PROCESAR CONTRATOS - CORREGIDO DECIMALES Y RANGO
     */
    private function procesarContratos(&$notifications, $now, $sevenDaysLater)
    {
        // ✅ Solo contratos DETERMINADOS que están ACTIVOS - INCLUIR HOY
        $contratos = ContratoTrabajador::with('trabajador')
            ->where('tipo_contrato', 'determinado') // Solo determinados
            ->where('estatus', ContratoTrabajador::ESTATUS_ACTIVO)
            ->whereNotNull('fecha_fin_contrato') // Asegurar que tiene fecha fin
            ->where('fecha_fin_contrato', '>=', $now->toDateString()) // ✅ DESDE HOY EN ADELANTE
            ->where('fecha_fin_contrato', '<=', $sevenDaysLater->toDateString())
            ->get();

        Log::info('Contratos por vencer encontrados', [
            'count' => $contratos->count(),
            'fecha_consulta' => $now->toDateString(),
            'hasta_fecha' => $sevenDaysLater->toDateString()
        ]);

        foreach ($contratos as $contrato) {
            // ✅ CÁLCULO CORREGIDO - SIN DECIMALES
            $fechaFin = Carbon::parse($contrato->fecha_fin_contrato)->startOfDay();
            $diasRestantes = $now->diffInDays($fechaFin, false);
            
            // ✅ ASEGURAR QUE SEA ENTERO Y NO NEGATIVO
            $diasRestantes = max(0, intval($diasRestantes));
            
            $urgencia = 'media';
            if ($diasRestantes <= 1) {
                $urgencia = 'critica';
            } elseif ($diasRestantes <= 3) {
                $urgencia = 'alta';
            }

            $notifications[] = [
                'id' => 'contrato_expiracion_' . $contrato->id_contrato,
                'tipo' => 'contrato_expiracion',
                'trabajador' => $contrato->trabajador->nombre_completo,
                'mensaje' => $diasRestantes == 0 
                    ? "Contrato EXPIRA HOY - Acción requerida" 
                    : "Contrato expira en {$diasRestantes} día(s) - {$contrato->fecha_fin_contrato->format('d/m/Y')}",
                'fecha_evento' => $contrato->fecha_fin_contrato->format('Y-m-d'),
                'urgencia' => $urgencia,
                'datos' => [
                    'trabajador_id' => $contrato->trabajador->id_trabajador,
                    'contrato_id' => $contrato->id_contrato,
                    'accion_requerida' => 'renovar_o_terminar_contrato'
                ]
            ];
        }

        // ✅ Contratos VENCIDOS (ya pasó la fecha pero siguen activos)
        $contratosVencidos = ContratoTrabajador::with('trabajador')
            ->where('tipo_contrato', 'determinado')
            ->where('estatus', ContratoTrabajador::ESTATUS_ACTIVO)
            ->where('fecha_fin_contrato', '<', $now->toDateString())
            ->get();

        foreach ($contratosVencidos as $contrato) {
            $fechaFin = Carbon::parse($contrato->fecha_fin_contrato)->startOfDay();
            $diasVencidos = intval($fechaFin->diffInDays($now)); // ✅ ENTERO
            
            $notifications[] = [
                'id' => 'contrato_vencido_' . $contrato->id_contrato,
                'tipo' => 'contrato_vencido',
                'trabajador' => $contrato->trabajador->nombre_completo,
                'mensaje' => "Contrato VENCIDO desde hace {$diasVencidos} día(s) - Acción urgente requerida",
                'fecha_evento' => $contrato->fecha_fin_contrato->format('Y-m-d'),
                'urgencia' => 'critica',
                'datos' => [
                    'trabajador_id' => $contrato->trabajador->id_trabajador,
                    'contrato_id' => $contrato->id_contrato,
                    'accion_requerida' => 'terminar_contrato_urgente'
                ]
            ];
        }
    }

    /**
     * ✅ PROCESAR PERMISOS - CORREGIDO DECIMALES Y RANGO
     */
    private function procesarPermisos(&$notifications, $now, $threeDaysLater)
    {
        // ✅ INCLUIR HOY EN LA CONSULTA
        $permisos = PermisosLaborales::with('trabajador')
            ->where('estatus_permiso', 'activo')
            ->where('fecha_fin', '>=', $now->toDateString()) // ✅ DESDE HOY EN ADELANTE
            ->where('fecha_fin', '<=', $threeDaysLater->toDateString())
            ->get();

        foreach ($permisos as $permiso) {
            // ✅ CÁLCULO CORREGIDO - SIN DECIMALES
            $fechaFin = Carbon::parse($permiso->fecha_fin)->startOfDay();
            $diasRestantes = $now->diffInDays($fechaFin, false);
            
            // ✅ ASEGURAR QUE SEA ENTERO Y NO NEGATIVO
            $diasRestantes = max(0, intval($diasRestantes));
            
            $notifications[] = [
                'id' => 'permiso_fin_' . $permiso->id_permiso,
                'tipo' => 'permiso_fin',
                'trabajador' => $permiso->trabajador->nombre_completo,
                'mensaje' => $diasRestantes == 0 
                    ? "Permiso finaliza HOY" 
                    : "Permiso finaliza en {$diasRestantes} día(s) - {$permiso->fecha_fin->format('d/m/Y')}",
                'fecha_evento' => $permiso->fecha_fin->format('Y-m-d'),
                'urgencia' => $diasRestantes <= 1 ? 'alta' : 'media',
                'datos' => [
                    'trabajador_id' => $permiso->trabajador->id_trabajador,
                    'permiso_id' => $permiso->id_permiso,
                    'accion_requerida' => 'finalizar_permiso'
                ]
            ];
        }
    }

    /**
     * ✅ PROCESAR DESPIDOS TEMPORALES - CORREGIDO DECIMALES Y RANGO
     */
    private function procesarDespidos(&$notifications, $now, $threeDaysLater)
    {
        // ✅ INCLUIR HOY EN LA CONSULTA
        $despidos = Despidos::with('trabajador')
            ->where('tipo_baja', 'temporal')
            ->where('estado', 'activo')
            ->whereNotNull('fecha_reintegro')
            ->where('fecha_reintegro', '>=', $now->toDateString()) // ✅ DESDE HOY EN ADELANTE
            ->where('fecha_reintegro', '<=', $threeDaysLater->toDateString())
            ->get();

        foreach ($despidos as $despido) {
            // ✅ CÁLCULO CORREGIDO - SIN DECIMALES
            $fechaReintegro = Carbon::parse($despido->fecha_reintegro)->startOfDay();
            $diasRestantes = $now->diffInDays($fechaReintegro, false);
            
            // ✅ ASEGURAR QUE SEA ENTERO Y NO NEGATIVO
            $diasRestantes = max(0, intval($diasRestantes));
            
            $notifications[] = [
                'id' => 'despido_reintegro_' . $despido->id_baja,
                'tipo' => 'despido_reintegro',
                'trabajador' => $despido->trabajador->nombre_completo,
                'mensaje' => $diasRestantes == 0 
                    ? "Reintegro HOY" 
                    : "Reintegro en {$diasRestantes} día(s) - {$despido->fecha_reintegro->format('d/m/Y')}",
                'fecha_evento' => $despido->fecha_reintegro->format('Y-m-d'),
                'urgencia' => $diasRestantes <= 1 ? 'alta' : 'media',
                'datos' => [
                    'trabajador_id' => $despido->trabajador->id_trabajador,
                    'despido_id' => $despido->id_baja,
                    'accion_requerida' => 'reintegrar_trabajador'
                ]
            ];
        }
    }

    /**
     * ✅ MARCAR NOTIFICACIÓN COMO PROCESADA (para el frontend)
     */
    public function marcarComoProcesada(Request $request)
    {
        $notificationId = $request->input('notification_id');
        
        // Aquí podrías guardar en caché que esta notificación fue procesada
        // Por ahora solo devolvemos success
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como procesada'
        ]);
    }
}