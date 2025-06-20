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
        'estatus',
        'contrato_anterior_id',
        'observaciones',
        'ruta_archivo',
        'duracion_meses', // Legacy
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

    // ✅ CONSTANTES DE ESTATUS MEJORADAS
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

    // ✅ ESTADOS SIMPLIFICADOS - SOLO 3 ESTADOS ESENCIALES
    public const ESTADO_VIGENTE = 'vigente';
    public const ESTADO_TERMINADO = 'terminado';
    public const ESTADO_RENOVADO = 'renovado';

    // ===== RELACIONES =====
    
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador');
    }

    public function contratoAnterior(): BelongsTo
    {
        return $this->belongsTo(ContratoTrabajador::class, 'contrato_anterior_id', 'id_contrato');
    }

    public function renovaciones()
    {
        return $this->hasMany(ContratoTrabajador::class, 'contrato_anterior_id', 'id_contrato');
    }

    // ===== MÉTODOS PRINCIPALES ACTUALIZADOS =====

    /**
     * ✅ SIMPLIFICADO: Estado unificado - Solo 3 estados esenciales
     */
    public function getEstadoFinalAttribute(): string
    {
        // Si está renovado, siempre mostrarlo
        if ($this->estatus === self::ESTATUS_RENOVADO) {
            return self::ESTADO_RENOVADO;
        }

        // Si está marcado como terminado, mostrarlo
        if ($this->estatus === self::ESTATUS_TERMINADO) {
            return self::ESTADO_TERMINADO;
        }

        // ✅ SIMPLIFICADO: Si está activo, siempre es VIGENTE
        // Sin importar fechas - el usuario decide cuándo terminar/renovar
        if ($this->estatus === self::ESTATUS_ACTIVO) {
            return self::ESTADO_VIGENTE;
        }

        // Fallback para estados legacy
        return self::ESTADO_TERMINADO;
    }

    /**
     * ✅ SIMPLIFICADO: Verifica si está vigente
     */
    public function estaVigente(): bool
    {
        return $this->estado_final === self::ESTADO_VIGENTE;
    }

    /**
     * ✅ SIMPLIFICADO: Días restantes o información de estado
     */
    public function diasRestantes(): int
    {
        if (!$this->estaVigente()) {
            return 0;
        }

        $hoy = Carbon::today();
        
        // Si aún no inicia, mostrar días hasta inicio
        if ($hoy->isBefore($this->fecha_inicio_contrato)) {
            return $hoy->diffInDays($this->fecha_inicio_contrato);
        }

        // Si ya está en período vigente o pasó, mostrar días hasta/desde fin
        return $hoy->diffInDays($this->fecha_fin_contrato, false);
    }

    /**
     * ✅ SIMPLIFICADO: Información adicional para mostrar en vista
     */
    public function getInfoEstadoAttribute(): string
    {
        if (!$this->estaVigente()) {
            return ucfirst($this->estado_final);
        }

        $hoy = Carbon::today();
        $diasRestantes = $this->diasRestantes();

        // Aún no inicia
        if ($hoy->isBefore($this->fecha_inicio_contrato)) {
            return "Inicia en {$diasRestantes} días";
        }

        // Ya expiró pero sigue marcado como vigente
        if ($hoy->isAfter($this->fecha_fin_contrato)) {
            $diasPasados = abs($diasRestantes);
            return "Expiró hace {$diasPasados} días";
        }

        // En período vigente normal
        return "{$diasRestantes} días restantes";
    }

    /**
     * ✅ SIMPLIFICADO: Verifica si está próximo a vencer (solo para vigentes)
     */
    public function estaProximoAVencer(int $dias = 30): bool
    {
        if (!$this->estaVigente()) {
            return false;
        }

        $hoy = Carbon::today();
        
        // Solo considera próximo a vencer si ya está en período vigente
        if ($hoy->isBefore($this->fecha_inicio_contrato)) {
            return false;
        }

        return $this->diasRestantes() <= $dias && $this->diasRestantes() >= 0;
    }

    /**
     * ✅ SIMPLIFICADO: Verifica si puede renovarse
     */
    public function puedeRenovarse(): bool
    {
        // Solo contratos vigentes pueden renovarse
        if (!$this->estaVigente()) {
            return false;
        }

        // Y deben estar próximos a vencer
        return $this->estaProximoAVencer(30);
    }

    /**
     * ✅ SIMPLIFICADO: Verifica si ya expiró (para marcado automático)
     */
    public function yaExpiro(): bool
    {
        if (!$this->estaVigente()) {
            return false;
        }

        return Carbon::today()->isAfter($this->fecha_fin_contrato);
    }

    /**
     * ✅ ACTUALIZADO: Marcar contrato como terminado por vencimiento
     */
    public function marcarComoTerminado(string $motivo = null): bool
    {
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return false;
        }

        $motivoDefault = $motivo ?? 'Contrato terminado por vencimiento automático';
        $observacion = "[" . now()->format('Y-m-d H:i') . "] {$motivoDefault}";

        return $this->update([
            'estatus' => self::ESTATUS_TERMINADO,
            'observaciones' => ($this->observaciones ? $this->observaciones . "\n" : '') . $observacion
        ]);
    }

    /**
     * ✅ MEJORADO: Marcar como renovado con mejor logging
     */
    public function marcarComoRenovado(int $nuevoContratoId = null): bool
    {
        if ($this->estatus !== self::ESTATUS_ACTIVO) {
            return false;
        }

        $observacion = "[" . now()->format('Y-m-d H:i') . "] Contrato renovado";
        if ($nuevoContratoId) {
            $observacion .= " (nuevo contrato #{$nuevoContratoId})";
        }

        return $this->update([
            'estatus' => self::ESTATUS_RENOVADO,
            'observaciones' => ($this->observaciones ? $this->observaciones . "\n" : '') . $observacion
        ]);
    }

    /**
     * ✅ SIMPLIFICADO: Obtener color para badge según estado final
     */
    public function getColorEstadoFinalAttribute(): string
    {
        return match($this->estado_final) {
            self::ESTADO_VIGENTE => 'success',      // Verde
            self::ESTADO_TERMINADO => 'secondary',  // Gris
            self::ESTADO_RENOVADO => 'info',        // Azul
            default => 'secondary'
        };
    }

    /**
     * ✅ SIMPLIFICADO: Obtener texto del estado final
     */
    public function getTextoEstadoFinalAttribute(): string
    {
        return match($this->estado_final) {
            self::ESTADO_VIGENTE => 'Vigente',
            self::ESTADO_TERMINADO => 'Terminado', 
            self::ESTADO_RENOVADO => 'Renovado',
            default => 'Desconocido'
        };
    }

    // ===== MÉTODOS EXISTENTES =====

    public function getDuracionTextoAttribute(): string
    {
        if ($this->tipo_duracion === 'dias') {
            return $this->duracion . ' ' . ($this->duracion === 1 ? 'día' : 'días');
        } else {
            return $this->duracion . ' ' . ($this->duracion === 1 ? 'mes' : 'meses');
        }
    }

    public function esPorDias(): bool
    {
        return $this->tipo_duracion === 'dias';
    }

    public function esPorMeses(): bool
    {
        return $this->tipo_duracion === 'meses';
    }

    public function esRenovacion(): bool
    {
        return !is_null($this->contrato_anterior_id);
    }

    public function getTextoEstatusAttribute(): string
    {
        return self::TODOS_ESTATUS[$this->estatus] ?? 'Desconocido';
    }

    public function getColorEstatusAttribute(): string
    {
        return self::COLORES_ESTATUS[$this->estatus] ?? 'secondary';
    }

    // ===== SCOPES SIMPLIFICADOS =====

    /**
     * ✅ SIMPLIFICADO: Solo contratos vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('estatus', self::ESTATUS_ACTIVO);
    }

    /**
     * ✅ SIMPLIFICADO: Contratos que ya expiraron (para marcado automático opcional)
     */
    public function scopeExpirados($query)
    {
        $hoy = Carbon::today();
        return $query->where('estatus', self::ESTATUS_ACTIVO)
                    ->where('fecha_fin_contrato', '<', $hoy);
    }

    /**
     * ✅ SIMPLIFICADO: Próximos a vencer
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        $hoy = Carbon::today();
        $fechaLimite = $hoy->copy()->addDays($dias);
        
        return $query->where('estatus', self::ESTATUS_ACTIVO)
                    ->where('fecha_inicio_contrato', '<=', $hoy) // Ya iniciaron
                    ->where('fecha_fin_contrato', '<=', $fechaLimite)
                    ->where('fecha_fin_contrato', '>=', $hoy);
    }

    public function scopePorEstatus($query, string $estatus)
    {
        return $query->where('estatus', $estatus);
    }

    public function scopeRenovaciones($query)
    {
        return $query->whereNotNull('contrato_anterior_id');
    }

    public function scopeOriginales($query)
    {
        return $query->whereNull('contrato_anterior_id');
    }
}