<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use App\Models\DiaAntiguedad; // ✅ NUEVO IMPORT

class VacacionesTrabajador extends Model
{
    use HasFactory;

    protected $table = 'vacaciones_trabajadores';
    protected $primaryKey = 'id_vacacion';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador', 'creado_por', 'periodo_vacacional', 'año_correspondiente',
        'dias_correspondientes', 'dias_solicitados', 'dias_disfrutados', 'dias_restantes',
        'fecha_inicio', 'fecha_fin', 'fecha_reintegro', 'estado', 'observaciones',
        'motivo_finalizacion', 'motivo_cancelacion', 'justificada_por_documento',
        'cancelado_por', 'fecha_cancelacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date', 
        'fecha_reintegro' => 'date',
        'fecha_cancelacion' => 'datetime',
        'año_correspondiente' => 'integer',
        'dias_correspondientes' => 'integer',
        'dias_solicitados' => 'integer',
        'dias_disfrutados' => 'integer',
        'dias_restantes' => 'integer',
        'justificada_por_documento' => 'boolean',
    ];

    // ✅ CONSTANTES CONSOLIDADAS
    public const ESTADOS = [
        'pendiente' => ['texto' => 'Pendiente', 'color' => 'warning', 'icono' => 'bi-clock-history'],
        'activa' => ['texto' => 'Activa', 'color' => 'success', 'icono' => 'bi-calendar-check'],
        'finalizada' => ['texto' => 'Finalizada', 'color' => 'secondary', 'icono' => 'bi-check-circle'],
        'cancelada' => ['texto' => 'Cancelada', 'color' => 'danger', 'icono' => 'bi-x-circle']
    ];

    // ❌ CONSTANTE ELIMINADA: DIAS_POR_ANTIGUEDAD

    // ✅ RELACIONES SIMPLIFICADAS
    public function trabajador(): BelongsTo { return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador'); }
    public function creadoPor(): BelongsTo { return $this->belongsTo(User::class, 'creado_por'); }
    public function canceladoPor(): BelongsTo { return $this->belongsTo(User::class, 'cancelado_por'); }

    // ✅ ACCESSORS SIMPLIFICADOS
    public function getEstadoInfoAttribute(): array { return self::ESTADOS[$this->estado] ?? self::ESTADOS['pendiente']; }
    public function getEstadoTextoAttribute(): string { return $this->estado_info['texto']; }
    public function getEstadoColorAttribute(): string { return $this->estado_info['color']; }
    public function getEstadoIconoAttribute(): string { return $this->estado_info['icono']; }
    public function getDuracionDiasAttribute(): int { return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1; }

    // ✅ MÉTODOS DE ESTADO CONSOLIDADOS
    public function esPendiente(): bool { return $this->estado === 'pendiente'; }
    public function esActiva(): bool { return $this->estado === 'activa'; }
    public function esFinalizada(): bool { return $this->estado === 'finalizada'; }
    public function esCancelada(): bool { return $this->estado === 'cancelada'; }

    public function puedeIniciar(): bool { return $this->esPendiente() && !$this->trabajador->tieneVacacionesActivas(); }
    public function puedeFinalizarse(): bool { return $this->esActiva() && Carbon::today()->gte($this->fecha_fin); }
    public function puedeCancelarse(): bool { return in_array($this->estado, ['pendiente', 'activa']); }

    // ✅ SCOPES SIMPLIFICADOS
    public function scopeEstado($query, string $estado) { return $query->where('estado', $estado); }
    public function scopeActivas($query) { return $this->scopeEstado($query, 'activa'); }
    public function scopePendientes($query) { return $this->scopeEstado($query, 'pendiente'); }
    public function scopeFinalizadas($query) { return $this->scopeEstado($query, 'finalizada'); }
    public function scopeCanceladas($query) { return $this->scopeEstado($query, 'cancelada'); }

    // ✅ MÉTODOS DE ACCIÓN SIMPLIFICADOS
    public function iniciar(int $usuarioId = null): bool
    {
        if (!$this->puedeIniciar()) return false;
        
        $this->trabajador->update(['estatus' => 'vacaciones']);
        $this->update(['estado' => 'activa', 'fecha_inicio' => Carbon::today()]);
        return true;
    }

    public function finalizar(?string $motivo = null, int $usuarioId = null): bool
    {
        if (!$this->puedeFinalizarse()) return false;
        
        $this->trabajador->update(['estatus' => 'activo']);
        $this->update([
            'estado' => 'finalizada',
            'dias_disfrutados' => min($this->dias_solicitados, Carbon::today()->diffInDays($this->fecha_inicio) + 1),
            'fecha_reintegro' => Carbon::today(),
            'motivo_finalizacion' => $motivo ?? 'Finalización automática'
        ]);
        return true;
    }

    public function cancelar(string $motivo, int $usuarioId): bool
    {
        if (!$this->puedeCancelarse()) return false;
        
        if ($this->esActiva()) $this->trabajador->update(['estatus' => 'activo']);
        
        $this->update([
            'estado' => 'cancelada',
            'motivo_cancelacion' => $motivo,
            'cancelado_por' => $usuarioId,
            'fecha_cancelacion' => Carbon::now(),
            'dias_disfrutados' => 0,
            'dias_restantes' => $this->dias_solicitados,
            'fecha_reintegro' => $this->esActiva() ? Carbon::today() : null
        ]);
        return true;
    }

    // ✅ MÉTODO ACTUALIZADO: Usa la tabla de configuración
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
            ->orderBy('antiguedad_min', 'desc')
            ->first();

        return $rango ? $rango->dias : 6;
    }

    // ✅ RELACIÓN CON DOCUMENTOS
    public function documentos(): BelongsToMany
    {
        return $this->belongsToMany(DocumentoVacaciones::class, 'documento_vacacion_vacaciones', 'vacacion_id', 'documento_vacacion_id', 'id_vacacion', 'id');
    }

    public static function generarPeriodoVacacional(int $año): string
    {
        return $año . '-' . ($año + 1);
    }
}