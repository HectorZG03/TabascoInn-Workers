<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'categoria';

    /**
     * Clave primaria de la tabla
     */
    protected $primaryKey = 'id_categoria';

    /**
     * Indica si la clave primaria es auto-incremental
     */
    public $incrementing = true;

    /**
     * Tipo de dato de la clave primaria
     */
    protected $keyType = 'int';

    /**
     * Indica si el modelo debe manejar timestamps automáticamente
     */
    public $timestamps = false;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'id_area',
        'nombre_categoria',
    ];

    /**
     * Campos que deben ser tratados como fechas
     */
    protected $dates = [];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [];

    /**
     * Relación: Una categoría pertenece a un área
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }


    /**
     * Relación: Una categoría puede tener muchas fichas técnicas
     * (si la relación es uno a muchos en lugar de uno a uno)
     */
    public function fichasTecnicas()
    {
        return $this->hasMany(FichaTecnica::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Obtener trabajadores de esta categoría
     */
    public function trabajadores()
    {
        return $this->hasManyThrough(
            Trabajador::class,
            FichaTecnica::class,
            'id_categoria',     // ✅ FK en ficha_tecnica
            'id_trabajador',    // ✅ FK en ficha_tecnica que apunta a trabajador  
            'id_categoria',     // ✅ Local key en categorias
            'id_trabajador'     // ✅ Local key en ficha_tecnica
        );
    }

    /**
     * Scope para categorías de un área específica
     */
    public function scopePorArea($query, $idArea)
    {
        return $query->where('id_area', $idArea);
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActivas($query)
    {
        return $query->whereNotNull('nombre_categoria');
    }

    /**
     * Contar trabajadores en esta categoría
     */
    public function contarTrabajadores()
    {
        return $this->trabajadores()->count();
    }

    /**
     * Obtener nombre completo (área - categoría)
     */
    public function getNombreCompletoAttribute()
    {
        return $this->area->nombre_area . ' - ' . $this->nombre_categoria;
    }
}