<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ContratoTrabajador extends Model
{
    protected $table = 'contratos_trabajadores';
    protected $primaryKey = 'id_contrato';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'fecha_inicio_contrato',
        'fecha_fin_contrato',
        'tipo_duracion',        // ✅ NUEVO: 'dias' o 'meses'
        'duracion',             // ✅ NUEVO: cantidad en días o meses
        'duracion_meses',       // ✅ MANTENER para compatibilidad
        'ruta_archivo'
    ];

    protected $casts = [
        'fecha_inicio_contrato' => 'date',
        'fecha_fin_contrato' => 'date',
        'tipo_duracion' => 'string',
        'duracion' => 'integer',
        'duracion_meses' => 'integer',
    ];

    // ===== RELACIONES =====
    
    /**
     * Relación con Trabajador
     */
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador');
    }

    // ===== MÉTODOS HELPER =====

    /**
     * ✅ NUEVO: Obtiene la duración formateada como texto
     */
    public function getDuracionTextoAttribute(): string
    {
        if ($this->tipo_duracion === 'dias') {
            return $this->duracion . ' ' . ($this->duracion === 1 ? 'día' : 'días');
        } else {
            return $this->duracion . ' ' . ($this->duracion === 1 ? 'mes' : 'meses');
        }
    }

    /**
     * ✅ NUEVO: Verifica si el contrato es por días
     */
    public function esPorDias(): bool
    {
        return $this->tipo_duracion === 'dias';
    }

    /**
     * ✅ NUEVO: Verifica si el contrato es por meses
     */
    public function esPorMeses(): bool
    {
        return $this->tipo_duracion === 'meses';
    }

    /**
     * ✅ NUEVO: Calcula la fecha de fin basada en inicio y duración
     */
    public function calcularFechaFin(Carbon $fechaInicio = null): Carbon
    {
        $fecha = $fechaInicio ?? $this->fecha_inicio_contrato;
        
        if ($this->tipo_duracion === 'dias') {
            return $fecha->copy()->addDays($this->duracion);
        } else {
            return $fecha->copy()->addMonths($this->duracion);
        }
    }

    /**
     * ✅ NUEVO: Método estático para calcular fecha fin sin instancia
     */
    public static function calcularFechaFinEstatico(Carbon $fechaInicio, int $duracion, string $tipo): Carbon
    {
        if ($tipo === 'dias') {
            return $fechaInicio->copy()->addDays($duracion);
        } else {
            return $fechaInicio->copy()->addMonths($duracion);
        }
    }

    /**
     * ✅ NUEVO: Verifica si el contrato está vigente
     */
    public function estaVigente(): bool
    {
        $hoy = Carbon::today();
        return $hoy->between($this->fecha_inicio_contrato, $this->fecha_fin_contrato);
    }

    /**
     * ✅ NUEVO: Días restantes del contrato
     */
    public function diasRestantes(): int
    {
        $hoy = Carbon::today();
        
        if ($hoy->isAfter($this->fecha_fin_contrato)) {
            return 0; // Contrato expirado
        }
        
        return $hoy->diffInDays($this->fecha_fin_contrato);
    }

    /**
     * ✅ NUEVO: Estado del contrato
     */
    public function getEstadoAttribute(): string
    {
        $hoy = Carbon::today();
        
        if ($hoy->isBefore($this->fecha_inicio_contrato)) {
            return 'pendiente';
        } elseif ($hoy->isAfter($this->fecha_fin_contrato)) {
            return 'expirado';
        } else {
            return 'vigente';
        }
    }

    // ===== SCOPES =====

    /**
     * ✅ NUEVO: Contratos vigentes
     */
    public function scopeVigentes($query)
    {
        $hoy = Carbon::today();
        return $query->where('fecha_inicio_contrato', '<=', $hoy)
                    ->where('fecha_fin_contrato', '>=', $hoy);
    }

    /**
     * ✅ NUEVO: Contratos por tipo de duración
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_duracion', $tipo);
    }

    /**
     * ✅ NUEVO: Contratos que expiran pronto
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        $fechaLimite = Carbon::today()->addDays($dias);
        return $query->where('fecha_fin_contrato', '<=', $fechaLimite)
                    ->where('fecha_fin_contrato', '>=', Carbon::today());
    }
}