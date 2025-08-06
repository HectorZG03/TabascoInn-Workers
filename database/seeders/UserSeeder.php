<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PermisoUsuario;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario de Gerencia
        User::create([
            'nombre' => 'Administrador Gerencia',
            'email' => 'gerencia@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Gerencia',
            'activo' => true
        ]);

        // Crear usuario de Recursos Humanos
        User::create([
            'nombre' => 'Administrador RRHH',
            'email' => 'rrhh@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Recursos_Humanos',
            'activo' => true
        ]);

        // Crear un usuario operativo de ejemplo con permisos limitados
        $operativo = User::create([
            'nombre' => 'Usuario Operativo',
            'email' => 'operativo@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Operativo',
            'activo' => true
        ]);

        // Asignar permisos al usuario operativo
        PermisoUsuario::create([
            'user_id' => $operativo->id,
            'modulo' => 'trabajadores',
            'ver' => true,
            'crear' => true,
            'editar' => true,
            'eliminar' => false
        ]);

        PermisoUsuario::create([
            'user_id' => $operativo->id,
            'modulo' => 'contratos',
            'ver' => true,
            'crear' => false,
            'editar' => false,
            'eliminar' => false
        ]);
    }
}