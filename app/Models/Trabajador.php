<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Trabajador\{TieneRelaciones, TieneAccessors, TieneMutators, TieneLogicaEstados, TieneHelpersTemporales };

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
        // ✅ NUEVOS: Campos de ubicación
        'lugar_nacimiento',
        'estado_actual',
        'ciudad_actual',
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

    // ✅ NUEVAS: Constantes para estados de México
    public const ESTADOS_MEXICO = [
        'Aguascalientes' => 'Aguascalientes',
        'Baja California' => 'Baja California',
        'Baja California Sur' => 'Baja California Sur',
        'Campeche' => 'Campeche',
        'Chiapas' => 'Chiapas',
        'Chihuahua' => 'Chihuahua',
        'Ciudad de México' => 'Ciudad de México',
        'Coahuila' => 'Coahuila',
        'Colima' => 'Colima',
        'Durango' => 'Durango',
        'Estado de México' => 'Estado de México',
        'Guanajuato' => 'Guanajuato',
        'Guerrero' => 'Guerrero',
        'Hidalgo' => 'Hidalgo',
        'Jalisco' => 'Jalisco',
        'Michoacán' => 'Michoacán',
        'Morelos' => 'Morelos',
        'Nayarit' => 'Nayarit',
        'Nuevo León' => 'Nuevo León',
        'Oaxaca' => 'Oaxaca',
        'Puebla' => 'Puebla',
        'Querétaro' => 'Querétaro',
        'Quintana Roo' => 'Quintana Roo',
        'San Luis Potosí' => 'San Luis Potosí',
        'Sinaloa' => 'Sinaloa',
        'Sonora' => 'Sonora',
        'Tabasco' => 'Tabasco',
        'Tamaulipas' => 'Tamaulipas',
        'Tlaxcala' => 'Tlaxcala',
        'Veracruz' => 'Veracruz',
        'Yucatán' => 'Yucatán',
        'Zacatecas' => 'Zacatecas',
    ];

}