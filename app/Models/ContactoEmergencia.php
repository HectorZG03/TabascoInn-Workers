<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoEmergencia extends Model
{
    use HasFactory;

    protected $table = 'contactos_emergencia';
    protected $primaryKey = 'id_contacto';

    protected $fillable = [
        'id_trabajador',
        'nombre_completo',
        'parentesco',
        'telefono_principal',
        'telefono_secundario',
        'direccion',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre_contacto . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }
}