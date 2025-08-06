<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoUsuario extends Model
{
    use HasFactory;

    protected $table = 'permisos_usuarios';

    protected $fillable = [
        'user_id',
        'modulo',
        'ver',
        'crear',
        'editar',
        'eliminar'
    ];

    protected $casts = [
        'ver' => 'boolean',
        'crear' => 'boolean',
        'editar' => 'boolean',
        'eliminar' => 'boolean',
    ];

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Módulos disponibles en el sistema
     */
    public static function getModulosDisponibles()
    {
        return [
            'trabajadores' => 'Trabajadores',
            'contratos' => 'Contratos',
            'permisos_laborales' => 'Permisos Laborales',
            'vacaciones' => 'Vacaciones',
            'despidos' => 'Despidos/Bajas',
            'horas_extra' => 'Horas Extra',
            'areas_categorias' => 'Áreas y Categorías',
            'gerentes' => 'Gerentes',
            'plantillas_contrato' => 'Plantillas de Contrato',
            'dias_antiguedad' => 'Días por Antigüedad',
            'configuracion' => 'Configuración del Sistema', // Nuevo módulo
            'gerentes' => 'Gestión de Gerentes', // Cambiar nombre para consistencia
        ];
    }

    /**
     * Obtener los permisos de un módulo específico para un usuario
     */
    public static function getPermisosModulo($userId, $modulo)
    {
        return self::where('user_id', $userId)
            ->where('modulo', $modulo)
            ->first();
    }

    /**
     * Crear permisos por defecto para un usuario
     */
    public static function crearPermisosDefecto($userId)
    {
        $modulos = self::getModulosDisponibles();
        
        foreach (array_keys($modulos) as $modulo) {
            self::create([
                'user_id' => $userId,
                'modulo' => $modulo,
                'ver' => false,
                'crear' => false,
                'editar' => false,
                'eliminar' => false
            ]);
        }
    }
}