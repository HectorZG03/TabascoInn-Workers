<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gerente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido_paterno', 
        'apellido_materno',
        'cargo',
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
     * Accessor para obtener nombre completo con cargo
     */
    public function getNombreCompletoConCargoAttribute()
    {
        return $this->nombre_completo . ' - ' . $this->cargo;
    }

    /**
     * Scope para gerentes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para gerentes por cargo
     */
    public function scopePorCargo($query, $cargo)
    {
        return $query->where('cargo', 'LIKE', "%{$cargo}%");
    }

    /**
     * Buscar gerentes por nombre, apellidos o cargo
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('apellido_paterno', 'LIKE', "%{$termino}%")
              ->orWhere('apellido_materno', 'LIKE', "%{$termino}%")
              ->orWhere('cargo', 'LIKE', "%{$termino}%");
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

    /**
     * Obtener gerentes para firmas de documentos (excluyendo al Gerente General)
     */
    public static function paraFirmasDocumentos()
    {
        return self::activos()
                   ->where('cargo', '<>', 'Gerente General')
                   ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'cargo')
                   ->orderBy('cargo')
                   ->orderBy('apellido_paterno')
                   ->get()
                   ->map(function ($gerente) {
                       return [
                           'id' => $gerente->id,
                           'nombre_completo' => $gerente->nombre_completo,
                           'cargo' => $gerente->cargo,
                           'para_firma' => $gerente->nombre_completo_con_cargo
                       ];
                   });
    }

    /**
     * Obtener el gerente general activo
     */
    public static function getGerenteGeneral()
    {
        return self::activos()
            ->where('cargo', 'Gerente General')
            ->first();
    }

    /**
     * Verificar si es gerente general
     */
    public function esGerenteGeneral()
    {
        return $this->cargo === 'Gerente General';
    }
}