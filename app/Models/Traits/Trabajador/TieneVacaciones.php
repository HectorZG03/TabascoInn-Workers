<?php

namespace App\Models\Traits\Trabajador;

use App\Models\VacacionesTrabajador;
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

    public function getDiasVacacionesRestantesEsteAñoAttribute(): int
    {
        $añoActual = Carbon::now()->year;
        $diasUsados = $this->vacaciones()
            ->where('año_correspondiente', $añoActual)
            ->whereNotIn('estado', ['cancelada'])
            ->sum('dias_solicitados');

        return max(0, $this->dias_vacaciones_correspondientes - $diasUsados);
    }

    // ✅ MÉTODOS DE GESTIÓN SIMPLIFICADOS
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

    // ✅ VALIDACIONES CONSOLIDADAS
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
}