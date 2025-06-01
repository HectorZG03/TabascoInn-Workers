<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Despidos extends Model
{
    use HasFactory;

    protected $table = 'despidos';

    protected $primaryKey = 'id_baja';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_trabajador',
        'fecha_baja',
        'motivo',
        'condicion_salida',
        'observaciones',
    ];

    protected $dates = [
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_baja' => 'datetime',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_baja', $fecha);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_baja', [$fechaInicio, $fechaFin]);
    }

    public function scopePorMotivo($query, $motivo)
    {
        return $query->where('motivo', 'LIKE', "%{$motivo}%");
    }

    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha_baja', Carbon::now()->month)
                     ->whereYear('fecha_baja', Carbon::now()->year);
    }

    public function scopeDelAnoActual($query)
    {
        return $query->whereYear('fecha_baja', Carbon::now()->year);
    }

    public function getDiasDesdeEjecutadoAttribute()
    {
        if (!$this->fecha_baja) {
            return null;
        }
        return Carbon::parse($this->fecha_baja)->diffInDays(Carbon::now());
    }

    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_baja,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'fecha_baja' => $this->fecha_baja?->format('d/m/Y'),
            'motivo' => $this->motivo,
            'condicion_salida' => $this->condicion_salida,
            'observaciones' => $this->observaciones,
            'dias_transcurridos' => $this->dias_desde_ejecutado,
        ];
    }

    public function esBajaVoluntaria()
    {
        $motivosVoluntarios = [
            'renuncia', 'dimisión', 'voluntaria', 'personal',
            'mejor oferta', 'cambio de trabajo'
        ];

        foreach ($motivosVoluntarios as $motivo) {
            if (stripos($this->motivo, $motivo) !== false) {
                return true;
            }
        }

        return false;
    }

    public function esDespidoJustificado()
    {
        $motivosJustificados = [
            'falta grave', 'indisciplina', 'robo', 'fraude',
            'abandono', 'incumplimiento', 'negligencia'
        ];

        foreach ($motivosJustificados as $motivo) {
            if (stripos($this->motivo, $motivo) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getTipoBajaAttribute()
    {
        if ($this->esBajaVoluntaria()) {
            return 'Voluntaria';
        } elseif ($this->esDespidoJustificado()) {
            return 'Despido Justificado';
        } else {
            return 'Otro';
        }
    }

    public static function estadisticasPorMes($año = null)
    {
        $año = $año ?? Carbon::now()->year;

        return static::selectRaw('MONTH(fecha_baja) as mes, COUNT(*) as total')
                    ->whereYear('fecha_baja', $año)
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
    }

    public static function estadisticasPorMotivo($año = null)
    {
        $año = $año ?? Carbon::now()->year;

        return static::selectRaw('motivo, COUNT(*) as total')
                    ->whereYear('fecha_baja', $año)
                    ->groupBy('motivo')
                    ->orderBy('total', 'desc')
                    ->get();
    }
}
