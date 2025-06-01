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
    ];

    protected $dates = [];

    protected $casts = [
        'sueldo_diarios' => 'decimal:2',
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
        return $query->where('sueldo_diarios', '>', $cantidad); // ✅ Corregir nombre
    }


    public function scopePorGradoEstudios($query, $grado)
    {
        return $query->where('grado_estudios', $grado);
    }

    public function scopePorFormacion($query, $formacion)
    {
        return $query->where('formacion', $formacion);
    }

    public function getSueldoMensualAttribute()
    {
        return $this->sueldo_diarios * 30;
    }

    public function getSueldoAnualAttribute()
    {
        return $this->sueldo_diarios * 365;
    }

    public function estaCompleta()
    {
        return !empty($this->sueldo_diarios) &&
               !empty($this->formacion) &&
               !empty($this->grado_estudios) &&
               !empty($this->id_categoria);
    }

    public function getResumenAttribute()
    {
        return [
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin asignar',
            'categoria' => $this->categoria->nombre_categoria ?? 'Sin categoría',
            'area' => $this->categoria->area->nombre_area ?? 'Sin área',
            'sueldo_diario' => $this->sueldo_diarios, // ✅ Corregir aquí también
            'sueldo_mensual' => $this->sueldo_mensual,
            'formacion' => $this->formacion,
            'estudios' => $this->grado_estudios,
            'completa' => $this->estaCompleta()
        ];
    }
}
