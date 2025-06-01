<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            ['id_area' => 1, 'nombre_area' => 'Recepción'],
            ['id_area' => 2, 'nombre_area' => 'Limpieza'],
            ['id_area' => 3, 'nombre_area' => 'Cocina'],
            ['id_area' => 4, 'nombre_area' => 'Restaurante'],
            ['id_area' => 5, 'nombre_area' => 'Bar'],
            ['id_area' => 6, 'nombre_area' => 'Mantenimiento'],
            ['id_area' => 7, 'nombre_area' => 'Administración'],
            ['id_area' => 8, 'nombre_area' => 'Seguridad'],
            ['id_area' => 9, 'nombre_area' => 'Lavandería'],
            ['id_area' => 10, 'nombre_area' => 'Spa y Wellness'],
            ['id_area' => 11, 'nombre_area' => 'Eventos y Banquetes'],
            ['id_area' => 12, 'nombre_area' => 'Recursos Humanos'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}