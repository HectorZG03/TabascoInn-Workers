<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VacacionesTrabajador extends Model
{
    use HasFactory;

    protected $table = 'vacaciones_trabajadores';
    protected $primaryKey = 'id_vacacion';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'creado_por',
        'periodo_vacacional',
        'año_correspondiente',
        'dias_correspondientes',
        'dias_solicitados',
        'dias_disfrutados',
        'dias_restantes',
        'fecha_inicio',
        'fecha_fin',
        'fecha_reintegro',
        'estado',
        'observaciones',
        'motivo_finalizacion'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_reintegro' => 'date',
        'año_correspondiente' => 'integer',
        'dias_correspondientes' => 'integer',
        'dias_solicitados' => 'integer',
        'dias_disfrutados' => 'integer',
        'dias_restantes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'activa' => 'Activa',
        'finalizada' => 'Finalizada'
    ];

    public const ESTADOS_COLORES = [
        'pendiente' => 'warning',
        'activa' => 'success',
        'finalizada' => 'secondary'
    ];

    public const ESTADOS_ICONOS = [
        'pendiente' => 'bi-clock-history',
        'activa' => 'bi-calendar-check',
        'finalizada' => 'bi-check-circle'
    ];

    // ✅ DÍAS DE VACACIONES SEGÚN LFT MÉXICO
    public const DIAS_POR_ANTIGUEDAD = [
        0 => 6,   // Menos de 1 año
        1 => 6,   // 1 año
        2 => 8,   // 2 años
        3 => 10,  // 3 años
        4 => 12,  // 4 años
        5 => 14,  // 5-9 años
        10 => 16, // 10-14 años
        15 => 18, // 15-19 años
        20 => 20, // 20-24 años
        25 => 22, // 25-29 años
        30 => 24  // 30+ años
    ];

    // ✅ RELACIONES
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ✅ ACCESSORS
    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }

    public function getEstadoColorAttribute(): string
    {
        return self::ESTADOS_COLORES[$this->estado] ?? 'secondary';
    }

    public function getEstadoIconoAttribute(): string
    {
        return self::ESTADOS_ICONOS[$this->estado] ?? 'bi-question';
    }

    public function getDuracionDiasAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }

    public function getDiasTranscurridosAttribute(): int
    {
        if ($this->estado !== 'activa') {
            return 0;
        }
        
        $hoy = Carbon::today();
        if ($hoy->lt($this->fecha_inicio)) {
            return 0;
        }
        
        $fechaFin = $hoy->gt($this->fecha_fin) ? $this->fecha_fin : $hoy;
        return $this->fecha_inicio->diffInDays($fechaFin) + 1;
    }

    public function getPorcentajeCompletadoAttribute(): float
    {
        if ($this->dias_solicitados === 0) {
            return 0;
        }
        
        return ($this->dias_disfrutados / $this->dias_solicitados) * 100;
    }

    // ✅ MÉTODOS DE ESTADO
    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function esActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function esFinalizada(): bool
    {
        return $this->estado === 'finalizada';
    }

    public function puedeIniciar(): bool
    {
        return $this->esPendiente() && 
               Carbon::today()->gte($this->fecha_inicio) &&
               !$this->trabajador->tieneVacacionesActivas();
    }

    public function puedeFinalizarse(): bool
    {
        return $this->esActiva();
    }

    // ✅ MÉTODOS ESTÁTICOS
    public static function calcularDiasCorrespondientes(int $antiguedadAños): int
    {
        foreach (array_reverse(self::DIAS_POR_ANTIGUEDAD, true) as $años => $dias) {
            if ($antiguedadAños >= $años) {
                return $dias;
            }
        }
        
        return 6; // Por defecto, 6 días
    }

    public static function generarPeriodoVacacional(int $año): string
    {
        return $año . '-' . ($año + 1);
    }

    // ✅ SCOPES
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'finalizada');
    }

    public function scopePorTrabajador($query, int $idTrabajador)
    {
        return $query->where('id_trabajador', $idTrabajador);
    }

    public function scopePorPeriodo($query, string $periodo)
    {
        return $query->where('periodo_vacacional', $periodo);
    }

    // ✅ MÉTODOS DE ACCIÓN
    public function iniciar(int $usuarioId = null): bool
    {
        if (!$this->puedeIniciar()) {
            return false;
        }

        // Actualizar estado del trabajador
        $this->trabajador->update(['estatus' => 'vacaciones']);

        // Actualizar vacación
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => Carbon::today() // Ajustar si es necesario
        ]);

        return true;
    }

    public function finalizar(?string $motivo = null, int $usuarioId = null): bool
    {
        if (!$this->puedeFinalizarse()) {
            return false;
        }

        // Calcular días efectivamente disfrutados
        $diasDisfrutados = $this->dias_transcurridos;

        // Actualizar estado del trabajador a activo
        $this->trabajador->update(['estatus' => 'activo']);

        // Actualizar vacación
        $this->update([
            'estado' => 'finalizada',
            'dias_disfrutados' => $diasDisfrutados,
            'dias_restantes' => $this->dias_solicitados - $diasDisfrutados,
            'fecha_reintegro' => Carbon::today(),
            'motivo_finalizacion' => $motivo
        ]);

        return true;
    }
}