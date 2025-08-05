<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ReinicioVacaciones extends Model
{
    use HasFactory;

    protected $table = 'reinicios_vacaciones';
    protected $primaryKey = 'id_reinicio';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'reiniciado_por',
        'año_reiniciado',
        'dias_correspondientes_restaurados',
        'antiguedad_al_reinicio',
        'vacaciones_pendientes_canceladas',
        'vacaciones_activas_canceladas',
        'vacaciones_finalizadas_ignoradas',
        'total_dias_usados_antes_reinicio',
        'observaciones',
        'tipo_reinicio',
        'fecha_reinicio'
    ];

    protected $casts = [
        'fecha_reinicio' => 'datetime',
        'año_reiniciado' => 'integer',
        'dias_correspondientes_restaurados' => 'integer',
        'antiguedad_al_reinicio' => 'integer',
        'vacaciones_pendientes_canceladas' => 'integer',
        'vacaciones_activas_canceladas' => 'integer',
        'vacaciones_finalizadas_ignoradas' => 'integer',
        'total_dias_usados_antes_reinicio' => 'integer',
    ];

    // ✅ RELACIONES
    public function trabajador(): BelongsTo 
    { 
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador'); 
    }

    public function reiniciadoPor(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'reiniciado_por'); 
    }

    // ✅ SCOPES
    public function scopeDelAño($query, int $año)
    {
        return $query->where('año_reiniciado', $año);
    }

    public function scopeRecientes($query, int $dias = 30)
    {
        return $query->where('fecha_reinicio', '>=', Carbon::now()->subDays($dias));
    }

    // ✅ ACCESSORS
    public function getFechaReinicioFormattedAttribute(): string
    {
        return $this->fecha_reinicio->format('d/m/Y H:i');
    }

    public function getResumenEstadisticasAttribute(): string
    {
        $partes = [];
        
        if ($this->vacaciones_pendientes_canceladas > 0) {
            $partes[] = "{$this->vacaciones_pendientes_canceladas} pendientes canceladas";
        }
        
        if ($this->vacaciones_activas_canceladas > 0) {
            $partes[] = "{$this->vacaciones_activas_canceladas} activas canceladas";
        }
        
        if ($this->vacaciones_finalizadas_ignoradas > 0) {
            $partes[] = "{$this->vacaciones_finalizadas_ignoradas} finalizadas ignoradas";
        }
        
        return implode(', ', $partes);
    }

    // ✅ MÉTODOS ESTÁTICOS ÚTILES
    public static function obtenerUltimoReinicio(int $trabajadorId, int $año): ?self
    {
        return static::where('id_trabajador', $trabajadorId)
            ->where('año_reiniciado', $año)
            ->orderBy('fecha_reinicio', 'desc')
            ->first();
    }

    public static function trabajadorTieneReinicioEnAño(int $trabajadorId, int $año): bool
    {
        return static::where('id_trabajador', $trabajadorId)
            ->where('año_reiniciado', $año)
            ->exists();
    }

    public static function crearRegistroReinicio(array $datos): self
    {
        return static::create($datos);
    }
}