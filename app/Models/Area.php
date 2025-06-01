<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    /** nombre de la tabla dentro de la bd */
    protected $table = 'area';

    /** nombre de la columna que es la PK */
    protected $primaryKey = 'id_area';

    /** ✅ CORREGIDO: Usar $incrementing en lugar de $autoIncrement */
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
        'nombre_area',
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
     * Relación: Un área puede tener muchas categorías
     */
    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'id_area', 'id_area');
    }

    /**
     * Obtener trabajadores de esta área a través de las categorías
     */
    public function trabajadores()
    {
        return Trabajador::whereHas('fichaTecnica.categoria', function($query) {
            $query->where('id_area', $this->id_area);
        });
    }

    /**
     * Contar trabajadores activos en esta área
     */
    public function contarTrabajadoresActivos()
    {
        return $this->trabajadores()->where('estatus', 1)->count();
    }

    /**
     * Scope para áreas activas (si necesitas filtrar)
     */
    public function scopeActivas($query)
    {
        return $query->whereNotNull('nombre_area');
    }
}