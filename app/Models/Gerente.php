<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gerente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido_paterno', 
        'apellido_materno',
        'telefono',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Accessor para obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }

    /**
     * Accessor para obtener nombre completo formato apellidos, nombre
     */
    public function getNombreCompletoFormalAttribute()
    {
        $apellidos = trim($this->apellido_paterno . ' ' . $this->apellido_materno);
        return $apellidos . ', ' . $this->nombre;
    }

    /**
     * Scope para gerentes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Buscar gerentes por nombre o apellidos
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('apellido_paterno', 'LIKE', "%{$termino}%")
              ->orWhere('apellido_materno', 'LIKE', "%{$termino}%");
        });
    }

    /**
     * Formatear teléfono para mostrar
     */
    public function getTelefonoFormateadoAttribute()
    {
        if (!$this->telefono) return null;
        
        // Si tiene 10 dígitos, formato: (XXX) XXX-XXXX
        if (strlen($this->telefono) == 10) {
            return '(' . substr($this->telefono, 0, 3) . ') ' . 
                   substr($this->telefono, 3, 3) . '-' . 
                   substr($this->telefono, 6);
        }
        
        return $this->telefono;
    }
}