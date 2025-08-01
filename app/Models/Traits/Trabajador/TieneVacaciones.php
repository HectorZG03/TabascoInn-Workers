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
        return VacacionesTrabajador::calcularDiasCorrespondientes($this->antiguedad ?? 0);
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

    // ✅ MÉTODO REFACTORIZADO - Asignar vacaciones con entrada manual
    public function asignarVacacionesRefactorizado(array $datos, int $usuarioId): VacacionesTrabajador
    {
        return $this->vacaciones()->create([
            'creado_por' => $usuarioId,
            
            // ✅ NUEVOS CAMPOS MANUALES
            'periodo_vacacional' => $datos['periodo_vacacional'], // Entrada manual
            'año_correspondiente' => $datos['año_correspondiente'], // Entrada manual
            'dias_correspondientes' => $datos['dias_correspondientes'], // Entrada manual
            
            // ✅ CAMPOS EXISTENTES
            'dias_solicitados' => $datos['dias_solicitados'],
            'dias_disfrutados' => 0,
            'dias_restantes' => $datos['dias_solicitados'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
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

    // ✅ VALIDACIONES FLEXIBLES - SIN RESTRICCIONES TEMPORALES ESTRICTAS
    public function puedeAsignarVacacionesFlexible(array $datos): array
    {
        $errores = [];

        // ✅ VALIDACIÓN RELAJADA: Solo verificar estado activo si es necesario
        if (!$this->estaActivo() && !in_array($this->estatus, ['permiso', 'prueba'])) {
            $errores[] = "El trabajador debe estar en un estado válido para asignar vacaciones (actual: {$this->estatus_texto}).";
        }

        // ✅ VALIDACIÓN FLEXIBLE: Permitir múltiples vacaciones si son de diferentes períodos
        $vacacionesActivas = $this->tieneVacacionesActivas();
        if ($vacacionesActivas) {
            $vacacionActiva = $this->getVacacionActualAttribute();
            if ($vacacionActiva && $vacacionActiva->periodo_vacacional === $datos['periodo_vacacional']) {
                $errores[] = 'El trabajador ya tiene vacaciones activas para este período.';
            }
        }

        // ✅ VALIDACIÓN DE DÍAS - MÁS FLEXIBLE
        $diasSolicitados = $datos['dias_solicitados'] ?? 0;
        $diasCorrespondientes = $datos['dias_correspondientes'] ?? $this->dias_vacaciones_correspondientes;
        
        if ($diasSolicitados > $diasCorrespondientes) {
            $errores[] = "Días solicitados ({$diasSolicitados}) exceden los correspondientes para este período ({$diasCorrespondientes}).";
        }

        // ✅ VALIDACIÓN DE FECHAS - SOLO LÓGICA BÁSICA
        if (isset($datos['fecha_inicio'], $datos['fecha_fin'])) {
            $fechaInicio = Carbon::parse($datos['fecha_inicio']);
            $fechaFin = Carbon::parse($datos['fecha_fin']);
            
            if ($fechaFin->lte($fechaInicio)) {
                $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
            }

            // ✅ VALIDACIÓN OPCIONAL: Advertir sobre fechas muy lejanas (no bloquear)
            $añoInicio = $fechaInicio->year;
            $añoCorrespondiente = $datos['año_correspondiente'] ?? Carbon::now()->year;
            
            if (abs($añoInicio - $añoCorrespondiente) > 2) {
                // Solo advertencia, no error
                Log::info("Asignación de vacación con diferencia de años significativa: Fecha inicio {$fechaInicio->format('Y-m-d')}, Año correspondiente {$añoCorrespondiente}");
            }
        }

        // ✅ VALIDACIÓN DE PERÍODO
        $periodoVacacional = $datos['periodo_vacacional'] ?? '';
        if (empty($periodoVacacional) || strlen($periodoVacacional) < 3) {
            $errores[] = 'El período vacacional debe ser válido y descriptivo.';
        }

        return $errores;
    }

    // ✅ VALIDACIONES ORIGINALES MANTENIDAS PARA COMPATIBILIDAD
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

            if ($fechaFin->lte($fechaInicio)) {
                $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
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
}