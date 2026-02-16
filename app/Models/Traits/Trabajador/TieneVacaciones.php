<?php

namespace App\Models\Traits\Trabajador;
use App\Models\VacacionesTrabajador;
use App\Models\DiaAntiguedad;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait TieneVacaciones
{
    // ✅ ESTADOS BÁSICOS
    public function estaEnVacaciones(): bool { return $this->estatus === 'vacaciones'; }
    public function puedeTomarVacaciones(): bool { return $this->estaActivo() && !$this->tieneVacacionesActivas(); }

    // ✅ CÁLCULOS PRINCIPALES CONSOLIDADOS
    public function getDiasVacacionesCorrespondientesAttribute(): int
    {
        return VacacionesTrabajador::calcularDiasCorrespondientes($this->antiguedad);
    }


    public static function calcularDiasCorrespondientes(int $antiguedadAños): int
    {
        // Valor por defecto para 0 años
        if ($antiguedadAños === 0) {
            return 6;
        }

        $rango = DiaAntiguedad::where('antiguedad_min', '<=', $antiguedadAños)
            ->where(function($query) use ($antiguedadAños) {
                $query->where('antiguedad_max', '>=', $antiguedadAños)
                      ->orWhereNull('antiguedad_max');
            })
            ->orderByDesc('antiguedad_min')
            ->first();

        return $rango ? $rango->dias : 6;
    }

    public function getDiasVacacionesRestantesEsteAñoAttribute(): int
    {
        $añoActual = Carbon::now()->year;
        $diasUsados = $this->vacaciones()
            ->where('año_correspondiente', $añoActual)
            ->whereNotIn('estado', ['cancelada'])
            ->sum('dias_solicitados');
            
        return max(0, $this->dias_vacaciones_correspondientes - $diasUsados);
    }

    // En App\Models\Traits\Trabajador\TieneVacaciones.php

public function asignarVacacionesRefactorizado(array $datos, int $usuarioId): VacacionesTrabajador
{
    // ✅ CALCULAR DÍAS DISFRUTADOS DEL MISMO PERIODO
    $diasDisfrutadosPeriodo = $this->vacaciones()
        ->where('periodo_vacacional', $datos['periodo_vacacional'])
        ->whereIn('estado', ['pendiente', 'activa', 'finalizada'])
        ->sum('dias_solicitados');
    
    // ✅ CALCULAR DÍAS RESTANTES CORRECTAMENTE
    $diasRestantesPeriodo = $datos['dias_correspondientes'] - $diasDisfrutadosPeriodo - $datos['dias_solicitados'];
    $diasRestantesPeriodo = max(0, $diasRestantesPeriodo);
    
    // ✅ NUEVO: CALCULAR FECHA DE REINTEGRO CONSIDERANDO DÍAS DE DESCANSO Y FESTIVOS
    // (esto asegura que use los festivos del año de la fecha fin)
    $fechaFin = Carbon::parse($datos['fecha_fin']);
    $fechaReintegroInicial = $fechaFin->copy()->addDay();
    
    // Obtener días de descanso del trabajador
    $diasDescanso = [];
    if ($this->fichaTecnica) {
        $diasDescanso = $this->fichaTecnica->dias_descanso ?? [];
    }
    
    // Calcular el siguiente día hábil (considera festivos del año correcto)
    $fechaReintegroHabil = \App\Models\DiaFestivo::siguienteDiaHabil($fechaReintegroInicial, $diasDescanso);
    $datos['fecha_reintegro'] = $fechaReintegroHabil->format('Y-m-d');

    return $this->vacaciones()->create([
        'creado_por' => $usuarioId,
        
        // ✅ CAMPOS MANUALES
        'periodo_vacacional' => $datos['periodo_vacacional'],
        'año_correspondiente' => $datos['año_correspondiente'],
        'dias_correspondientes' => $datos['dias_correspondientes'],
        
        // ✅ CAMPOS CALCULADOS CORRECTAMENTE
        'dias_solicitados' => $datos['dias_solicitados'],
        'dias_disfrutados' => $diasDisfrutadosPeriodo,
        'dias_restantes' => $diasRestantesPeriodo,
        
        // ✅ FECHAS Y ESTADO
        'fecha_inicio' => $datos['fecha_inicio'],
        'fecha_fin' => $datos['fecha_fin'],
        'fecha_reintegro' => $datos['fecha_reintegro'], // ✅ NUEVO: fecha de reintegro calculada
        'estado' => 'pendiente',
        'observaciones' => $datos['observaciones'] ?? null
    ]);
}

    // ✅ MÉTODO ORIGINAL MANTENIDO PARA COMPATIBILIDAD
    public function asignarVacaciones(array $datos, int $usuarioId): VacacionesTrabajador
    {
        return $this->vacaciones()->create([
            'creado_por' => $usuarioId,
            'periodo_vacacional' => $datos['periodo_vacacional'] ?? VacacionesTrabajador::generarPeriodoVacacional($datos['año_correspondiente'] ?? Carbon::now()->year),
            'año_correspondiente' => $datos['año_correspondiente'] ?? Carbon::now()->year,
            'dias_correspondientes' => $this->dias_vacaciones_correspondientes,
            'dias_solicitados' => $datos['dias_solicitados'],
            'dias_disfrutados' => 0,
            'dias_restantes' => $datos['dias_solicitados'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'estado' => 'pendiente',
            'observaciones' => $datos['observaciones'] ?? null
        ]);
    }

    // ✅ ESTADÍSTICAS SIMPLIFICADAS
    public function getEstadisticasVacaciones(): array
    {
        $vacaciones = $this->vacaciones;
        
        return [
            'total_vacaciones' => $vacaciones->count(),
            'vacaciones_activas' => $vacaciones->where('estado', 'activa')->count(),
            'vacaciones_pendientes' => $vacaciones->where('estado', 'pendiente')->count(),
            'vacaciones_finalizadas' => $vacaciones->where('estado', 'finalizada')->count(),
            'vacaciones_canceladas' => $vacaciones->where('estado', 'cancelada')->count(),
            'total_dias_tomados' => $vacaciones->where('estado', 'finalizada')->sum('dias_disfrutados'),
            'dias_correspondientes_año_actual' => $this->dias_vacaciones_correspondientes,
            'dias_restantes_año_actual' => $this->dias_vacaciones_restantes_este_año,
        ];
    }

    public function puedeAsignarVacacionesFlexible(array $datos): array
    {
        $errores = [];

        // ✅ VALIDACIÓN DE ESTADO
        if (!$this->estaActivo() && !in_array($this->estatus, ['permiso', 'prueba'])) {
            $errores[] = "El trabajador debe estar en un estado válido para asignar vacaciones (actual: {$this->estatus_texto}).";
        }

        // ✅ VALIDACIÓN DE VACACIONES ACTIVAS
        $vacacionesActivas = $this->tieneVacacionesActivas();
        if ($vacacionesActivas) {
            $vacacionActiva = $this->getVacacionActualAttribute();
            if ($vacacionActiva && $vacacionActiva->periodo_vacacional === $datos['periodo_vacacional']) {
                $errores[] = 'El trabajador ya tiene vacaciones activas para este período.';
            }
        }

        // ✅ VALIDACIÓN DE DÍAS POR PERIODO
        $periodoVacacional = $datos['periodo_vacacional'] ?? '';
        $diasSolicitados = $datos['dias_solicitados'] ?? 0;
        $diasCorrespondientes = $datos['dias_correspondientes'] ?? $this->dias_vacaciones_correspondientes;
        
        if (!empty($periodoVacacional)) {
            // Verificar disponibilidad en el período específico
            $erroresPeriodo = $this->puedeAsignarVacacionesEnPeriodoConDias(
                $periodoVacacional, 
                $diasSolicitados, 
                $diasCorrespondientes
            );
            $errores = array_merge($errores, $erroresPeriodo);
        }

        // ✅ VALIDACIÓN DE FECHAS - CORREGIDA PARA PERMITIR VACACIONES DE 1 DÍA
        if (isset($datos['fecha_inicio'], $datos['fecha_fin'])) {
            $fechaInicio = Carbon::parse($datos['fecha_inicio']);
            $fechaFin = Carbon::parse($datos['fecha_fin']);
            
            // 🔧 CAMBIADO: Permite fechas iguales (vacaciones de 1 día)
            if ($fechaFin->lt($fechaInicio)) {
                $errores[] = 'La fecha de fin debe ser igual o posterior a la fecha de inicio.';
            }
        }

        // ✅ VALIDACIÓN DE PERIODO
        if (empty($periodoVacacional) || strlen($periodoVacacional) < 3) {
            $errores[] = 'El período vacacional debe ser válido y descriptivo.';
        }

        return $errores;
    }

    // ✅ VALIDACIONES ORIGINALES MANTENIDAS PARA COMPATIBILIDAD - TAMBIÉN CORREGIDAS
    public function puedeAsignarVacaciones(array $datos): array
    {
        $errores = [];

        if (!$this->estaActivo()) {
            $errores[] = 'El trabajador debe estar activo para asignar vacaciones.';
        }

        if ($this->tieneVacacionesActivas()) {
            $errores[] = 'El trabajador ya tiene vacaciones activas.';
        }

        $diasDisponibles = $this->dias_vacaciones_restantes_este_año;
        $diasSolicitados = $datos['dias_solicitados'] ?? 0;
        
        if ($diasSolicitados > $diasDisponibles) {
            $errores[] = "Días solicitados ({$diasSolicitados}) exceden los disponibles ({$diasDisponibles}).";
        }

        if (isset($datos['fecha_inicio'], $datos['fecha_fin'])) {
            $fechaInicio = Carbon::parse($datos['fecha_inicio']);
            $fechaFin = Carbon::parse($datos['fecha_fin']);
            
            if ($fechaInicio->isPast()) {
                $errores[] = 'La fecha de inicio no puede ser en el pasado.';
            }

            // 🔧 CAMBIADO: Permite fechas iguales (vacaciones de 1 día)
            if ($fechaFin->lt($fechaInicio)) {
                $errores[] = 'La fecha de fin debe ser igual o posterior a la fecha de inicio.';
            }
        }

        return $errores;
    }

    // ✅ HELPERS ÚTILES CONSOLIDADOS
    public function tieneVacacionesSinDocumento(): bool
    {
        return $this->vacaciones()
            ->where('estado', 'pendiente')
            ->whereDoesntHave('documentos')
            ->exists();
    }

    public function getResumenVacacionesAñoAttribute(): array
    {
        $añoActual = Carbon::now()->year;
        $vacacionesDelAño = $this->vacaciones()->where('año_correspondiente', $añoActual)->get();

        return [
            'año' => $añoActual,
            'dias_correspondientes' => $this->dias_vacaciones_correspondientes,
            'dias_disfrutados' => $vacacionesDelAño->where('estado', 'finalizada')->sum('dias_disfrutados'),
            'dias_pendientes' => $vacacionesDelAño->where('estado', 'pendiente')->sum('dias_solicitados'),
            'dias_activos' => $vacacionesDelAño->where('estado', 'activa')->sum('dias_solicitados'),
            'dias_cancelados' => $vacacionesDelAño->where('estado', 'cancelada')->sum('dias_solicitados'),
            'dias_restantes_disponibles' => $this->dias_vacaciones_restantes_este_año,
        ];
    }

    // ✅ NUEVOS MÉTODOS PARA GESTIÓN FLEXIBLE
    
    /**
     * Obtener vacaciones por período específico
     */
    public function getVacacionesPorPeriodo(string $periodo)
    {
        return $this->vacaciones()
            ->where('periodo_vacacional', $periodo)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener estadísticas por año específico
     */
    public function getEstadisticasVacacionesPorAño(int $año): array
    {
        $vacacionesDelAño = $this->vacaciones()->where('año_correspondiente', $año)->get();

        return [
            'año' => $año,
            'total_vacaciones' => $vacacionesDelAño->count(),
            'dias_correspondientes_total' => $vacacionesDelAño->sum('dias_correspondientes'),
            'dias_solicitados_total' => $vacacionesDelAño->sum('dias_solicitados'),
            'dias_disfrutados_total' => $vacacionesDelAño->where('estado', 'finalizada')->sum('dias_disfrutados'),
            'vacaciones_por_estado' => [
                'pendientes' => $vacacionesDelAño->where('estado', 'pendiente')->count(),
                'activas' => $vacacionesDelAño->where('estado', 'activa')->count(),
                'finalizadas' => $vacacionesDelAño->where('estado', 'finalizada')->count(),
                'canceladas' => $vacacionesDelAño->where('estado', 'cancelada')->count(),
            ]
        ];
    }

    /**
     * Verificar si puede asignar vacaciones para un período específico
     */
    public function puedeAsignarVacacionesEnPeriodo(string $periodo): bool
    {
        $vacacionesActivasEnPeriodo = $this->vacaciones()
            ->where('periodo_vacacional', $periodo)
            ->whereIn('estado', ['activa', 'pendiente'])
            ->exists();

        return !$vacacionesActivasEnPeriodo;
    }

    /**
     * Obtener períodos vacacionales únicos del trabajador
     */
    public function getPeriodosVacacionales(): array
    {
        return $this->vacaciones()
            ->select('periodo_vacacional')
            ->distinct()
            ->orderBy('periodo_vacacional', 'desc')
            ->pluck('periodo_vacacional')
            ->toArray();
    }
    public function puedeAsignarVacacionesEnPeriodoConDias(string $periodo, int $diasSolicitados, int $diasCorrespondientes): array
    {
        $errores = [];
        
        // Obtener todas las vacaciones del periodo
        $vacacionesPeriodo = $this->vacaciones()
            ->where('periodo_vacacional', $periodo)
            ->whereIn('estado', ['pendiente', 'activa', 'finalizada'])
            ->get();
        
        $diasUsados = $vacacionesPeriodo->sum('dias_solicitados');
        $diasDisponibles = $diasCorrespondientes - $diasUsados;
        
        if ($diasSolicitados > $diasDisponibles) {
            $errores[] = "Solo quedan {$diasDisponibles} días disponibles para el periodo {$periodo}. Ya se han usado {$diasUsados} de {$diasCorrespondientes} días.";
        }
        
        // Log para debugging
        Log::info("Validación de vacaciones por periodo", [
            'trabajador' => $this->nombre_completo,
            'periodo' => $periodo,
            'dias_correspondientes' => $diasCorrespondientes,
            'dias_usados' => $diasUsados,
            'dias_disponibles' => $diasDisponibles,
            'dias_solicitados' => $diasSolicitados,
            'puede_asignar' => empty($errores)
        ]);
        
        return $errores;
    }
}