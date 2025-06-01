<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ PRIMERO: Crear Áreas y Categorías
        $this->call([
            AreaSeeder::class,
            CategoriaSeeder::class,
        ]);

        // ✅ DESPUÉS: Crear Usuarios de prueba
        // Usuario de Recursos Humanos
        User::create([
            'nombre' => 'Recursos Humanos',
            'email' => 'rh@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Recursos_Humanos',
        ]);

        // Usuario de Gerencia
        User::create([
            'nombre' => 'Gerencia',
            'email' => 'gerencia@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Gerencia',
        ]);

        // ✅ OPCIONAL: Usuario Administrador adicional
        User::create([
            'nombre' => 'Administrador',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Gerencia', // O crear un tipo 'Admin' si lo necesitas
        ]);

        // ✅ Mostrar información en consola
        $this->command->info('🏨 Datos del Hotel TABASCO INN creados exitosamente:');
        $this->command->info('📍 12 Áreas creadas');
        $this->command->info('👥 47 Categorías de trabajo creadas');
        $this->command->info('🔑 3 Usuarios de prueba creados');
        $this->command->line('');
        $this->command->info('Usuarios de prueba:');
        $this->command->line('• RH: rh@hotel.com / password123');
        $this->command->line('• Gerencia: gerencia@hotel.com / password123');
        $this->command->line('• Admin: admin@hotel.com / password123');
    }
}