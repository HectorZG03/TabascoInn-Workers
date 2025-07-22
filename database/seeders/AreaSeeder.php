<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            // DEPARTAMENTO DE ALIMENTOS Y BEBIDAS (id_departamento = 1)
            ['id_area' => 1, 'id_departamento' => 1, 'nombre_area' => 'Meseros'],
            ['id_area' => 2, 'id_departamento' => 1, 'nombre_area' => 'Cocina'],
            ['id_area' => 3, 'id_departamento' => 1, 'nombre_area' => 'Stewards'],
            
            // DEPARTAMENTO DE RECEPCIÓN Y HOSPEDAJE (id_departamento = 2)
            ['id_area' => 4, 'id_departamento' => 2, 'nombre_area' => 'Recepción'],
            ['id_area' => 5, 'id_departamento' => 2, 'nombre_area' => 'Hospedaje'],
            
            // DEPARTAMENTO DE SERVICIOS GENERALES (id_departamento = 3)
            ['id_area' => 6, 'id_departamento' => 3, 'nombre_area' => 'Áreas Públicas'],
            
            // DEPARTAMENTO DE SEGURIDAD (id_departamento = 4)
            ['id_area' => 7, 'id_departamento' => 4, 'nombre_area' => 'Vigilancia'],
            
            // DEPARTAMENTO COMERCIAL (id_departamento = 5)
            ['id_area' => 8, 'id_departamento' => 5, 'nombre_area' => 'Ventas'],
            
            // DEPARTAMENTO DE ABASTECIMIENTO (id_departamento = 6)
            ['id_area' => 9, 'id_departamento' => 6, 'nombre_area' => 'Almacén'],
            
            // DEPARTAMENTO ADMINISTRATIVO (id_departamento = 7)
            ['id_area' => 10, 'id_departamento' => 7, 'nombre_area' => 'Gerencia Administrativa'],
            
            // DEPARTAMENTO TÉCNICO (id_departamento = 8)
            ['id_area' => 11, 'id_departamento' => 8, 'nombre_area' => 'Mantenimiento'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}