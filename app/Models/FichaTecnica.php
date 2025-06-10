<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaTecnica extends Model
{
    use HasFactory;

    protected $table = 'ficha_tecnica';

    protected $primaryKey = 'id_ficha';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_trabajador',
        'id_categoria',
        'sueldo_diarios',
        'formacion',
        'grado_estudios',
        // ✅ NUEVOS: Campos laborales
        'horas_trabajo',
        'turno',
    ];

    protected $dates = [];

    protected $casts = [
        'sueldo_diarios' => 'decimal:2',
        'horas_trabajo' => 'decimal:2', // ✅ NUEVO: Cast para horas de trabajo
    ];

    // ✅ NUEVAS: Constantes para turnos
    public const TURNOS_DISPONIBLES = [
        'diurno' => 'Diurno',
        'nocturno' => 'Nocturno',
        'mixto' => 'Mixto/Rotativo',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function area()
    {
        return $this->hasOneThrough(
            Area::class,
            Categoria::class,
            'id_categoria',
            'id_area',
            'id_categoria',
            'id_area'
        );
    }

    public function scopeSueldoMayorA($query, $cantidad)
    {
        return $query->where('sueldo_diarios', '>', $cantidad);
    }

    public function scopePorGradoEstudios($query, $grado)
    {
        return $query->where('grado_estudios', $grado);
    }

    public function scopePorFormacion($query, $formacion)
    {
        return $query->where('formacion', $formacion);
    }

    // ✅ NUEVOS: Scopes para campos laborales
    public function scopePorTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }

    public function scopePorHorasTrabajo($query, $horas)
    {
        return $query->where('horas_trabajo', $horas);
    }



    // ✅ NUEVO: Obtener texto del turno
    public function getTurnoTextoAttribute()
    {
        return self::TURNOS_DISPONIBLES[$this->turno] ?? 'No especificado';
    }

    // ✅ NUEVO: Obtener horas formateadas
    public function getHorasTrabajoFormateadasAttribute()
    {
        if (!$this->horas_trabajo) {
            return 'No especificadas';
        }
        
        // Convertir decimal a horas y minutos
        $horas = floor($this->horas_trabajo);
        $minutos = ($this->horas_trabajo - $horas) * 60;
        
        if ($minutos > 0) {
            return sprintf('%d:%02d hrs', $horas, $minutos);
        }
        
        return $horas . ' hrs';
    }

    public function estaCompleta()
    {
        return !empty($this->sueldo_diarios) &&
               !empty($this->formacion) &&
               !empty($this->grado_estudios) &&
               !empty($this->id_categoria) &&
               !empty($this->horas_trabajo) && // ✅ NUEVO: Incluir horas de trabajo
               !empty($this->turno);           // ✅ NUEVO: Incluir turno
    }

    public function getResumenAttribute()
    {
        return [
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin asignar',
            'categoria' => $this->categoria->nombre_categoria ?? 'Sin categoría',
            'area' => $this->categoria->area->nombre_area ?? 'Sin área',
            'sueldo_diario' => $this->sueldo_diarios, // ✅ Solo sueldo diario
            'formacion' => $this->formacion,
            'estudios' => $this->grado_estudios,
            'horas_trabajo' => $this->horas_trabajo_formateadas, // ✅ NUEVO
            'turno' => $this->turno_texto, // ✅ NUEVO
            'completa' => $this->estaCompleta()
        ];
    }
}