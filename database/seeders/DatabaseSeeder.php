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
        // ✅ PRIMERO: Crear Departamentos, Áreas y Categorías (EN ORDEN CORRECTO)
        $this->call([
            DepartamentoSeeder::class,  // 1️⃣ NUEVO: Primero los departamentos
            AreaSeeder::class,          // 2️⃣ Segundo las áreas (necesitan departamentos)
            CategoriaSeeder::class,     // 3️⃣ Tercero las categorías (necesitan áreas)
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

        // ✅ ACTUALIZADO: Mostrar información correcta en consola
        $this->command->info('🏨 Datos del Hotel TABASCO INN creados exitosamente:');
        $this->command->info('🏢 8 Departamentos creados');          // ✅ NUEVO
        $this->command->info('📍 11 Áreas creadas');                 // ✅ ACTUALIZADO
        $this->command->info('👥 43 Categorías de trabajo creadas'); // ✅ ACTUALIZADO
        $this->command->info('🔑 3 Usuarios de prueba creados');
        $this->command->line('');
        
        // ✅ NUEVO: Mostrar estructura de departamentos
        $this->command->info('🏢 Departamentos creados:');
        $this->command->line('• Alimentos y Bebidas → Meseros, Cocina, Stewards');
        $this->command->line('• Recepción y Hospedaje → Recepción, Hospedaje');
        $this->command->line('• Servicios Generales → Áreas Públicas');
        $this->command->line('• Seguridad → Vigilancia');
        $this->command->line('• Comercial → Ventas');
        $this->command->line('• Abastecimiento → Almacén');
        $this->command->line('• Administrativo → Gerencia Administrativa');
        $this->command->line('• Técnico → Mantenimiento');
        $this->command->line('');
        
        $this->command->info('Usuarios de prueba:');
        $this->command->line('• RH: rh@hotel.com / password123');
        $this->command->line('• Gerencia: gerencia@hotel.com / password123');
        $this->command->line('• Admin: admin@hotel.com / password123');
        
        // ✅ NUEVO: Mensaje final
        $this->command->info('');
        $this->command->info('✅ Sistema completo inicializado con estructura jerárquica:');
        $this->command->info('   Departamentos → Áreas → Categorías → Trabajadores');
    }
}