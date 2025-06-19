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
        'tipo_duracion',
        'duracion',
        'estatus',                  // ✅ NUEVO
        'contrato_anterior_id',     // ✅ NUEVO
        'observaciones',            // ✅ NUEVO
        'ruta_archivo',
        'duracion_meses',           // Legacy
    ];

    protected $casts = [
        'fecha_inicio_contrato' => 'date',
        'fecha_fin_contrato' => 'date',
        'tipo_duracion' => 'string',
        'estatus' => 'string',
        'duracion' => 'integer',
        'duracion_meses' => 'integer',
        'contrato_anterior_id' => 'integer',
    ];

    // ✅ NUEVAS CONSTANTES DE ESTATUS
    public const ESTATUS_ACTIVO = 'activo';
    public const ESTATUS_TERMINADO = 'terminado';
    public const ESTATUS_REVOCADO = 'revocado';
    public const ESTATUS_RENOVADO = 'renovado';

    public const TODOS_ESTATUS = [
        self::ESTATUS_ACTIVO => 'Activo',
        self::ESTATUS_TERMINADO => 'Terminado',
        self::ESTATUS_REVOCADO => 'Revocado',
        self::ESTATUS_RENOVADO => 'Renovado'
    ];

    public const COLORES_ESTATUS = [
        self::ESTATUS_ACTIVO => 'success',
        self::ESTATUS_TERMINADO => 'secondary',
        self::ESTATUS_REVOCADO => 'danger',
        self::ESTATUS_RENOVADO => 'info'
    ];

    // ===== RELACIONES =====
    
    /**
     * Relación con Trabajador
     */
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador');
    }

    /**
     * ✅ NUEVA: Relación con contrato anterior (renovaciones)
     */
    public function contratoAnterior(): BelongsTo
    {
        return $this->belongsTo(ContratoTrabajador::class, 'contrato_anterior_id', 'id_contrato');
    }

    /**
     * ✅ NUEVA: Relación con contratos posteriores (renovaciones)
     */
    public function renovaciones()
    {
        return $this->hasMany(ContratoTrabajador::class, 'contrato_anterior_id', 'id_contrato');
    }

    // ===== MÉTODOS HELPER =====

    /**
     * ✅ ACTUALIZADO: Estado calculado vs estatus físico
     */
    public function getEstadoCalculadoAttribute(): string
    {
        // Si el estatus físico es revocado o renovado, respetarlo
        if (in_array($this->estatus, [self::ESTATUS_REVOCADO, self::ESTATUS_RENOVADO])) {
            return $this->estatus;
        }

        // Para activo y terminado, calcular basado en fechas
        $hoy = Carbon::today();
        
        if ($hoy->isBefore($this->fecha_inicio_contrato)) {
            return 'pendiente';
        } elseif ($hoy->isAfter($this->fecha_fin_contrato)) {
            return $this->estatus === self::ESTATUS_ACTIVO ? 'expirado' : $this->estatus;
        } else {
            return 'vigente';
        }
    }

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
     * ✅ ACTUALIZADO: Verifica si el contrato está realmente vigente
     */
    public function estaVigente(): bool
    {
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return false;
        }

        $hoy = Carbon::today();
        return $hoy->between($this->fecha_inicio_contrato, $this->fecha_fin_contrato);
    }

    /**
     * ✅ NUEVO: Días restantes del contrato (solo si está activo)
     */
    public function diasRestantes(): int
    {
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return 0;
        }

        $hoy = Carbon::today();
        
        if ($hoy->isAfter($this->fecha_fin_contrato)) {
            return 0;
        }
        
        return $hoy->diffInDays($this->fecha_fin_contrato);
    }

    /**
     * ✅ NUEVO: Verifica si puede renovarse
     */
    public function puedeRenovarse(): bool
    {
        // Solo contratos activos pueden renovarse
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return false;
        }

        // Solo si está próximo a vencer (30 días) o ya venció
        $diasRestantes = $this->diasRestantes();
        return $diasRestantes <= 30;
    }

    /**
     * ✅ NUEVO: Marcar como renovado
     */
    public function marcarComoRenovado(): bool
    {
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return false;
        }

        return $this->update([
            'estatus' => self::ESTATUS_RENOVADO,
            'observaciones' => ($this->observaciones ?? '') . "\n[" . now()->format('Y-m-d H:i') . "] Contrato renovado automáticamente."
        ]);
    }

    /**
     * ✅ NUEVO: Revocar contrato
     */
    public function revocar(string $motivo = null): bool
    {
        if (!in_array($this->estatus, [self::ESTATUS_ACTIVO])) {
            return false;
        }

        $observacion = "[" . now()->format('Y-m-d H:i') . "] Contrato revocado.";
        if ($motivo) {
            $observacion .= " Motivo: {$motivo}";
        }

        return $this->update([
            'estatus' => self::ESTATUS_REVOCADO,
            'observaciones' => ($this->observaciones ?? '') . "\n" . $observacion
        ]);
    }

    /**
     * ✅ NUEVO: Obtener color del badge según el estatus
     */
    public function getColorEstatusAttribute(): string
    {
        return self::COLORES_ESTATUS[$this->estatus] ?? 'secondary';
    }

    /**
     * ✅ NUEVO: Obtener texto del estatus
     */
    public function getTextoEstatusAttribute(): string
    {
        return self::TODOS_ESTATUS[$this->estatus] ?? 'Desconocido';
    }

    /**
     * ✅ NUEVO: Verifica si es una renovación
     */
    public function esRenovacion(): bool
    {
        return !is_null($this->contrato_anterior_id);
    }

    // ===== MÉTODOS EXISTENTES ACTUALIZADOS =====

    public function esPorDias(): bool
    {
        return $this->tipo_duracion === 'dias';
    }

    public function esPorMeses(): bool
    {
        return $this->tipo_duracion === 'meses';
    }

    public function calcularFechaFin(Carbon $fechaInicio = null): Carbon
    {
        $fecha = $fechaInicio ?? $this->fecha_inicio_contrato;
        
        if ($this->tipo_duracion === 'dias') {
            return $fecha->copy()->addDays($this->duracion);
        } else {
            return $fecha->copy()->addMonths($this->duracion);
        }
    }

    public static function calcularFechaFinEstatico(Carbon $fechaInicio, int $duracion, string $tipo): Carbon
    {
        if ($tipo === 'dias') {
            return $fechaInicio->copy()->addDays($duracion);
        } else {
            return $fechaInicio->copy()->addMonths($duracion);
        }
    }

    // ===== SCOPES ACTUALIZADOS =====

    /**
     * ✅ ACTUALIZADO: Contratos realmente vigentes (activos + en período)
     */
    public function scopeVigentes($query)
    {
        $hoy = Carbon::today();
        return $query->where('estatus', self::ESTATUS_ACTIVO)
                    ->where('fecha_inicio_contrato', '<=', $hoy)
                    ->where('fecha_fin_contrato', '>=', $hoy);
    }

    /**
     * ✅ NUEVO: Por estatus específico
     */
    public function scopePorEstatus($query, string $estatus)
    {
        return $query->where('estatus', $estatus);
    }

    /**
     * ✅ ACTUALIZADO: Próximos a vencer (solo activos)
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        $fechaLimite = Carbon::today()->addDays($dias);
        return $query->where('estatus', self::ESTATUS_ACTIVO)
                    ->where('fecha_fin_contrato', '<=', $fechaLimite)
                    ->where('fecha_fin_contrato', '>=', Carbon::today());
    }

    /**
     * ✅ NUEVO: Solo renovaciones
     */
    public function scopeRenovaciones($query)
    {
        return $query->whereNotNull('contrato_anterior_id');
    }

    /**
     * ✅ NUEVO: Contratos originales (no renovaciones)
     */
    public function scopeOriginales($query)
    {
        return $query->whereNull('contrato_anterior_id');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_duracion', $tipo);
    }
}