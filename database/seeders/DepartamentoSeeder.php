<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departamento;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departamentos = [
            [
                'id_departamento' => 1,
                'nombre_departamento' => 'Alimentos y Bebidas',
                'descripcion' => 'Departamento encargado de la gestión de restaurantes, cocina y servicio de alimentos'
            ],
            [
                'id_departamento' => 2,
                'nombre_departamento' => 'Recepción y Hospedaje',
                'descripcion' => 'Departamento encargado del check-in/out de huéspedes y mantenimiento de habitaciones'
            ],
            [
                'id_departamento' => 3,
                'nombre_departamento' => 'Servicios Generales',
                'descripcion' => 'Departamento encargado de la limpieza y mantenimiento de áreas públicas'
            ],
            [
                'id_departamento' => 4,
                'nombre_departamento' => 'Seguridad',
                'descripcion' => 'Departamento encargado de la vigilancia y seguridad del establecimiento'
            ],
            [
                'id_departamento' => 5,
                'nombre_departamento' => 'Comercial',
                'descripcion' => 'Departamento encargado de ventas y coordinación de eventos'
            ],
            [
                'id_departamento' => 6,
                'nombre_departamento' => 'Abastecimiento',
                'descripcion' => 'Departamento encargado de almacén e inventarios'
            ],
            [
                'id_departamento' => 7,
                'nombre_departamento' => 'Administrativo',
                'descripcion' => 'Departamento encargado de la administración general y contabilidad'
            ],
            [
                'id_departamento' => 8,
                'nombre_departamento' => 'Técnico',
                'descripcion' => 'Departamento encargado del mantenimiento técnico y reparaciones'
            ],
        ];

        foreach ($departamentos as $departamento) {
            Departamento::create($departamento);
        }
    }
}