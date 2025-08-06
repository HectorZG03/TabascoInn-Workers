<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'tipo',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con permisos
     */
    public function permisos()
    {
        return $this->hasMany(PermisoUsuario::class);
    }

    /**
     * Verificar si el usuario es de Gerencia
     */
    public function esGerencia(): bool
    {
        return $this->tipo === 'Gerencia';
    }

    /**
     * Verificar si el usuario es de Recursos Humanos
     */
    public function esRecursosHumanos(): bool
    {
        return $this->tipo === 'Recursos_Humanos';
    }

    /**
     * Verificar si el usuario es Operativo
     */
    public function esOperativo(): bool
    {
        return $this->tipo === 'Operativo';
    }

    /**
     * Verificar si es administrador (Gerencia o RRHH)
     */
    public function esAdministrador(): bool
    {
        return in_array($this->tipo, ['Gerencia', 'Recursos_Humanos']);
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public function tienePermiso($modulo, $accion): bool
    {
        // Los administradores tienen todos los permisos
        if ($this->esAdministrador()) {
            return true;
        }

        // Para operativos, verificar en la tabla de permisos
        $permiso = $this->permisos()->where('modulo', $modulo)->first();
        
        if (!$permiso) {
            return false;
        }

        return $permiso->$accion ?? false;
    }

    /**
     * Verificar si puede acceder a un módulo
     */
    public function puedeAcceder($modulo): bool
    {
        return $this->tienePermiso($modulo, 'ver');
    }

    /**
     * Obtener los módulos a los que tiene acceso
     */
    public function modulosConAcceso(): array
    {
        // Los administradores tienen acceso a todo
        if ($this->esAdministrador()) {
            return array_keys(PermisoUsuario::getModulosDisponibles());
        }

        // Para operativos, filtrar solo los módulos donde tienen permiso de ver
        return $this->permisos()
            ->where('ver', true)
            ->pluck('modulo')
            ->toArray();
    }

    /**
     * Verificar si el usuario está activo
     */
    public function estaActivo(): bool
    {
        // Los administradores siempre están activos
        if ($this->esAdministrador()) {
            return true;
        }

        return $this->activo ?? true;
    }
}