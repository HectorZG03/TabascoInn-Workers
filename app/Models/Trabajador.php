<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Trabajador\{
    TieneRelaciones, 
    TieneAccessors, 
    TieneMutators, 
    TieneLogicaEstados, 
    TieneHelpersTemporales,
    TieneVacaciones
};

class Trabajador extends Model
{
    use HasFactory,
        TieneRelaciones,        // ✅ Debe ir PRIMERO (contiene las relaciones base)
        TieneAccessors,
        TieneMutators,
        TieneLogicaEstados,
        TieneHelpersTemporales,
        TieneVacaciones;        // ✅ Debe ir ÚLTIMO (usa métodos de TieneRelaciones)

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
        'estado_civil',       // ✅ NUEVO CAMPO
        'lugar_nacimiento',
        'estado_actual',
        'ciudad_actual',
        'codigo_postal',
        'curp',
        'rfc',
        'no_nss',
        'telefono',
        'correo',
        'direccion',
        'fecha_ingreso',
        'estatus',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'antiguedad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES ACTUALIZADAS
    public const TODOS_ESTADOS = [
        'activo' => 'Activo',
        'permiso' => 'Con Permiso Temporal',
        'vacaciones' => 'En Vacaciones',
        'suspendido' => 'Suspendido',
        'prueba' => 'Período de Prueba',
        'inactivo' => 'Inactivo'
    ];

    // ✅ NUEVA CONSTANTE: Estados civiles
    public const ESTADOS_CIVILES = [
        'soltero' => 'Soltero(a)',
        'casado' => 'Casado(a)',
        'union_libre' => 'Unión Libre',
        'divorciado' => 'Divorciado(a)',
        'viudo' => 'Viudo(a)',
        'separado' => 'Separado(a)'
    ];

    // ✅ CONSTANTE ELIMINADA: Ya no necesitamos ESTADOS_MEXICO
    // porque estado_actual ahora es texto libre
}