<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PermisosLaborales extends Model
{
    use HasFactory;
    
    protected $table = 'permisos_laborales';
    protected $primaryKey = 'id_permiso';
    public $timestamps = true;
    
    protected $fillable = [
        'id_trabajador',
        'tipo_permiso',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];
    
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }
    
    public function getDiasDePermisoAttribute()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) return null;
        return Carbon::parse($this->fecha_inicio)->diffInDays(Carbon::parse($this->fecha_fin)) + 1;
    }
    
    // ✅ NUEVOS MÉTODOS SIN DECIMALES
    public function getDiasRestantesAttribute(): int
    {
        if ($this->estaVencido()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }
    
    public function getDiasVencidosAttribute(): int
    {
        if (!$this->estaVencido()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }
    
    public function getDiasDesdeFinAttribute(): int
    {
        return (int) $this->fecha_fin->startOfDay()->diffInDays(now()->startOfDay());
    }
    
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_permiso', $tipo);
    }
    
    public function scopeActivos($query)
    {
        return $query->where('fecha_fin', '>=', now());
    }
    
    public function scopeVencidos($query)
    {
        return $query->where('fecha_fin', '<', now());
    }
    
    public function scopeDelMes($query, $año = null, $mes = null)
    {
        $año = $año ?? now()->year;
        $mes = $mes ?? now()->month;
        
        return $query->whereYear('fecha_inicio', $año)
                    ->whereMonth('fecha_inicio', $mes);
    }
    
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
    }
    
    public function estaActivo()
    {
        return $this->fecha_fin >= now()->startOfDay();
    }
    
    public function estaVencido()
    {
        return $this->fecha_fin < now()->startOfDay();
    }
    
    // ✅ MÉTODO ACTUALIZADO
    public function diasRestantes()
    {
        return $this->dias_restantes; // Usa el accessor
    }
    
    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_permiso,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'tipo_permiso' => $this->tipo_permiso,
            'fecha_inicio' => $this->fecha_inicio->format('d/m/Y'),
            'fecha_fin' => $this->fecha_fin->format('d/m/Y'),
            'dias_permiso' => $this->dias_de_permiso,
            'esta_activo' => $this->estaActivo(),
            'dias_restantes' => $this->dias_restantes, // ✅ Sin decimales
            'observaciones' => $this->observaciones,
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }
}