<?php
// app/Models/PermisoLaboral.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PermisosLaborales extends Model
{
    use HasFactory;
    
    protected $table = 'permisos_laborales';
    protected $primaryKey = 'id_permiso';
    public $timestamps = true; // ✅ CAMBIADO: Ahora usa timestamps
    
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
        'created_at' => 'datetime', // ✅ AGREGADO
        'updated_at' => 'datetime', // ✅ AGREGADO
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
    
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_permiso', $tipo);
    }
    
    public function scopeActivos($query)
    {
        return $query->where('fecha_fin', '>=', now());
    }
    
    // ✅ AGREGADO: Scopes adicionales útiles
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
    
    // ✅ AGREGADO: Métodos útiles
    public function estaActivo()
    {
        return $this->fecha_fin >= now();
    }
    
    public function estaVencido()
    {
        return $this->fecha_fin < now();
    }
    
    public function diasRestantes()
    {
        if ($this->estaVencido()) return 0;
        return now()->diffInDays($this->fecha_fin);
    }
    
    // ✅ AGREGADO: Accessor para información completa
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
            'dias_restantes' => $this->diasRestantes(),
            'observaciones' => $this->observaciones,
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }
}