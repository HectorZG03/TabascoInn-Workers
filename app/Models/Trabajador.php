<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Trabajador\{TieneRelaciones, TieneAccessors, TieneMutators, TieneLogicaEstados, TieneHelpersTemporales};

class Trabajador extends Model
{
    use HasFactory,
        TieneRelaciones,
        TieneAccessors,
        TieneMutators,
        TieneLogicaEstados,
        TieneHelpersTemporales;

    protected $table = 'trabajadores';
    protected $primaryKey = 'id_trabajador';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'id_baja',
        'nombre_trabajador',
        'ape_pat',
        'ape_mat',
        'fecha_nacimiento',
        'curp',
        'rfc',
        'no_nss',
        'telefono',
        'correo',
        'direccion',
        'fecha_ingreso',
        'antiguedad',
        'estatus',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'antiguedad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const TODOS_ESTADOS = [
        'activo' => 'Activo',
        'permiso' => 'Con Permiso Temporal',
        'suspendido' => 'Suspendido',
        'prueba' => 'Período de Prueba',
        'inactivo' => 'Inactivo'
    ];
}
