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
        // ✅ DATOS REALES DEL HOTEL TABASCO INN EN PRODUCCIÓN
        $areas = [
            // DEPARTAMENTO DE ALIMENTOS Y BEBIDAS (id_departamento = 9)
            ['id_area' => 12, 'id_departamento' => 9, 'nombre_area' => 'RESTAURANTE'],
            ['id_area' => 13, 'id_departamento' => 9, 'nombre_area' => 'COCINA'],
            ['id_area' => 14, 'id_departamento' => 9, 'nombre_area' => 'INDIGO'],
            ['id_area' => 25, 'id_departamento' => 9, 'nombre_area' => 'ALIMENTOS Y BEBIDAS'],
            
            // DEPARTAMENTO DE DIVISIÓN CUARTOS (id_departamento = 10)
            ['id_area' => 15, 'id_departamento' => 10, 'nombre_area' => 'RECEPCION'],
            ['id_area' => 16, 'id_departamento' => 10, 'nombre_area' => 'RESTAURANTE-RECEPCION'],
            ['id_area' => 17, 'id_departamento' => 10, 'nombre_area' => 'AREAS PUBLICAS'],
            ['id_area' => 18, 'id_departamento' => 10, 'nombre_area' => 'HOSPEDAJE'],
            ['id_area' => 19, 'id_departamento' => 10, 'nombre_area' => 'LAVANDERIA'],
            
            // DEPARTAMENTO DE MANTENIMIENTO (id_departamento = 11)
            ['id_area' => 20, 'id_departamento' => 11, 'nombre_area' => 'MANTENIMIENTO'],
            
            // DEPARTAMENTO DE VENTAS (id_departamento = 12)
            ['id_area' => 21, 'id_departamento' => 12, 'nombre_area' => 'VENTAS'],
            
            // DEPARTAMENTO DE SERVICIOS (id_departamento = 13)
            ['id_area' => 22, 'id_departamento' => 13, 'nombre_area' => 'SEGURIDAD'],
            
            // DEPARTAMENTO DE COMPRAS (id_departamento = 14)
            ['id_area' => 23, 'id_departamento' => 14, 'nombre_area' => 'ALMACEN'],
            
            // DEPARTAMENTO ADMINISTRATIVO (id_departamento = 15)
            ['id_area' => 24, 'id_departamento' => 15, 'nombre_area' => 'GERENCIA ADMINISTRATIVA'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}