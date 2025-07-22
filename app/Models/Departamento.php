<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    /** nombre de la tabla dentro de la bd */
    protected $table = 'departamentos';

    /** nombre de la columna que es la PK */
    protected $primaryKey = 'id_departamento';

    /** Usar incrementing */
    public $incrementing = true;

     /**
     * Tipo de dato de la clave primaria
     */
    protected $keyType = 'int';

    /**
     * Indica si el modelo debe manejar timestamps automáticamente
     */
    public $timestamps = true;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre_departamento',
        'descripcion',
    ];

    /**
     * Campos que deben ser tratados como fechas
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un departamento puede tener muchas áreas
     */
    public function areas()
    {
        return $this->hasMany(Area::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Obtener trabajadores de este departamento a través de las áreas
     */
    public function trabajadores()
    {
        return Trabajador::whereHas('fichaTecnica.categoria.area', function($query) {
            $query->where('id_departamento', $this->id_departamento);
        });
    }

    /**
     * Contar trabajadores activos en este departamento
     */
    public function contarTrabajadoresActivos()
    {
        return $this->trabajadores()->where('estatus', 'activo')->count();
    }

    /**
     * Contar áreas en este departamento
     */
    public function contarAreas()
    {
        return $this->areas()->count();
    }

    /**
     * Obtener todas las categorías del departamento a través de las áreas
     */
    public function categorias()
    {
        return Categoria::whereHas('area', function($query) {
            $query->where('id_departamento', $this->id_departamento);
        });
    }

    /**
     * Contar categorías en este departamento
     */
    public function contarCategorias()
    {
        return $this->categorias()->count();
    }

    /**
     * Scope para departamentos activos
     */
    public function scopeActivos($query)
    {
        return $query->whereNotNull('nombre_departamento');
    }
}