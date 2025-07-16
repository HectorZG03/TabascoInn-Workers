<?php

namespace App\Models\Traits\Trabajador;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\VacacionesTrabajador;
use Carbon\Carbon;

trait TieneVacaciones
{
    public function estaEnVacaciones(): bool
    {
        return $this->estatus === 'vacaciones';
    }

    public function puedeTomarVacaciones(): bool
    {
        return $this->estaActivo() && !$this->tieneVacacionesActivas();
    }
    public function getDiasVacacionesCorrespondientesAttribute(): int
    {
        return VacacionesTrabajador::calcularDiasCorrespondientes($this->antiguedad ?? 0);
    }

    public function getTotalDiasVacacionesTomadasAttribute(): int
    {
        return $this->vacacionesFinalizadas()->sum('dias_disfrutados');
    }

    public function getDiasVacacionesRestantesEsteAñoAttribute(): int
    {
        $añoActual = Carbon::now()->year;
        $vacacionesEsteAño = $this->vacaciones()
            ->where('año_correspondiente', $añoActual)
            ->whereNotIn('estado', ['cancelada'])
            ->get();

        $diasCorrespondientes = $this->dias_vacaciones_correspondientes;
        $diasUsados = $vacacionesEsteAño->sum('dias_solicitados');

        return max(0, $diasCorrespondientes - $diasUsados);
    }

       
    public function vacacionesCanceladas(): HasMany
    {
        return $this->vacaciones()->where('estado', 'cancelada');
    }

    public function asignarVacaciones(array $datos, int $usuarioId): VacacionesTrabajador
    {
        // Calcular días correspondientes automáticamente
        $diasCorrespondientes = $this->dias_vacaciones_correspondientes;
        
        // Generar período vacacional si no se proporciona
        $periodo = $datos['periodo_vacacional'] ?? VacacionesTrabajador::generarPeriodoVacacional(
            $datos['año_correspondiente'] ?? Carbon::now()->year
        );

        // Calcular días restantes
        $diasRestantes = $datos['dias_solicitados'];

        return $this->vacaciones()->create([
            'creado_por' => $usuarioId,
            'periodo_vacacional' => $periodo,
            'año_correspondiente' => $datos['año_correspondiente'] ?? Carbon::now()->year,
            'dias_correspondientes' => $diasCorrespondientes,
            'dias_solicitados' => $datos['dias_solicitados'],
            'dias_disfrutados' => 0,
            'dias_restantes' => $diasRestantes,
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'estado' => $datos['estado'] ?? 'pendiente',
            'observaciones' => $datos['observaciones'] ?? null
        ]);
    }

    public function iniciarVacaciones(?int $idVacacion = null, ?int $usuarioId = null): bool
    {
        $vacacion = $idVacacion 
            ? $this->vacaciones()->find($idVacacion)
            : $this->vacacionesPendientes()->first();

        if (!$vacacion || !$vacacion->puedeIniciar()) {
            return false;
        }

        return $vacacion->iniciar($usuarioId);
    }

    public function finalizarVacacionesActivas(?string $motivo = null, ?int $usuarioId = null): bool
    {
        $vacacionActiva = $this->vacacion_actual;

        if (!$vacacionActiva) {
            return false;
        }

        return $vacacionActiva->finalizar($motivo, $usuarioId);
    }

    // ✅ ESTADÍSTICAS DE VACACIONES
    public function getEstadisticasVacaciones(): array
    {
        $vacaciones = $this->vacaciones;
        
        return [
            'total_vacaciones' => $vacaciones->count(),
            'vacaciones_activas' => $vacaciones->where('estado', 'activa')->count(),
            'vacaciones_pendientes' => $vacaciones->where('estado', 'pendiente')->count(),
            'vacaciones_finalizadas' => $vacaciones->where('estado', 'finalizada')->count(),
            'vacaciones_canceladas' => $vacaciones->where('estado', 'cancelada')->count(), 
            'total_dias_tomados' => $vacaciones->whereNotIn('estado', ['cancelada'])->sum('dias_disfrutados'),
            'dias_correspondientes_año_actual' => $this->dias_vacaciones_correspondientes,
            'dias_restantes_año_actual' => $this->dias_vacaciones_restantes_este_año,
            'ultima_vacacion' => $vacaciones->first()?->created_at?->format('Y-m-d'),
            'proxima_vacacion' => $this->vacacionesPendientes()->first()?->fecha_inicio?->format('Y-m-d')
        ];
    }

   // ✅ VALIDACIONES ACTUALIZADAS
    public function puedeAsignarVacaciones(array $datos): array
    {
        $errores = [];

        // Validar que el trabajador esté activo
        if (!$this->estaActivo()) {
            $errores[] = 'El trabajador debe estar activo para asignar vacaciones.';
        }

        // Validar que no tenga vacaciones activas
        if ($this->tieneVacacionesActivas()) {
            $errores[] = 'El trabajador ya tiene vacaciones activas.';
        }

        // ✅ ACTUALIZADO: Validar días disponibles (excluyendo canceladas)
        $diasDisponibles = $this->dias_vacaciones_restantes_este_año;
        $diasSolicitados = $datos['dias_solicitados'] ?? 0;
        
        if ($diasSolicitados > $diasDisponibles) {
            $errores[] = "Días solicitados ($diasSolicitados) exceden los disponibles ($diasDisponibles).";
        }

        // Validar fechas
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
}