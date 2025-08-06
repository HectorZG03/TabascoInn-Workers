<?php

namespace Database\Seeders;

use App\Models\PlantillaContrato;
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
        // ✅ PRIMERO: Crear estructura real del Hotel Tabasco Inn (EN ORDEN CORRECTO)
        $this->call([
            DepartamentoSeeder::class,      // 1️⃣ Departamentos reales
            AreaSeeder::class,              // 2️⃣ Áreas reales (necesitan departamentos)
            CategoriaSeeder::class,         // 3️⃣ Categorías reales (necesitan áreas)
            VariablesContratoSeeder::class, // 4️⃣ Variables de contrato corregidas
        ]);

        // ✅ DESPUÉS: Crear Usuarios de prueba
        // Usuario de Recursos Humanos
        User::create([
            'nombre' => 'Cecilia del Carmen Velazquez del Valle',
            'email' => 'recursos_humanos@tabascoinn.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Recursos_Humanos',
        ]);

        // Usuario de Gerencia
        User::create([  
            'nombre' => 'Gerencia',
            'email' => 'gerencias@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Gerencia',
        ]);

        // ✅ Usuario Administrador adicional
        User::create([
            'nombre' => 'Administrador',
            'email' => 'admis@hotel.com',
            'password' => Hash::make('password123'),
            'tipo' => 'Gerencia',
        ]);

        // ✅ INFORMACIÓN ACTUALIZADA CON DATOS REALES DEL HOTEL TABASCO INN
        $this->command->info('🏨 Datos del Hotel TABASCO INN creados exitosamente:');
        $this->command->info('🏢 7 Departamentos reales creados');
        $this->command->info('📍 14 Áreas operativas creadas');
        $this->command->info('👥 45 Categorías de trabajo reales creadas');
        $this->command->info('🔑 3 Usuarios de prueba creados');
        $this->command->line('');
        
        // ✅ ESTRUCTURA REAL DEL HOTEL TABASCO INN
        $this->command->info('🏢 Estructura Real del Hotel:');
        $this->command->line('• ALIMENTOS Y BEBIDAS → Restaurante, Cocina, Índigo, A&B General');
        $this->command->line('• DIVISIÓN CUARTOS → Recepción, Rest-Recepción, Áreas Públicas, Hospedaje, Lavandería');
        $this->command->line('• MANTENIMIENTO → Mantenimiento General');
        $this->command->line('• VENTAS → Ventas y Eventos');
        $this->command->line('• SERVICIOS → Seguridad');
        $this->command->line('• COMPRAS → Almacén');
        $this->command->line('• ADMINISTRATIVO → Gerencia Administrativa');
        $this->command->line('');
        
        $this->command->info('');
        $this->command->info('✅ Sistema inicializado con estructura REAL del Hotel Tabasco Inn:');
        $this->command->info('   Departamentos → Áreas → Categorías → Variables de Contrato');
        $this->command->info('🎯 Listo para recibir trabajadores reales del hotel');
    }
}