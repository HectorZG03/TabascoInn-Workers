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
        // ✅ DATOS REALES DEL HOTEL TABASCO INN EN PRODUCCIÓN
        $departamentos = [
            [
                'id_departamento' => 9,
                'nombre_departamento' => 'ALIMENTOS Y BEBIDAS',
                'descripcion' => 'ESTE DEPARTAMENTO SE ENCARGA DEL SERVICIO A COMENSALES EN RESTAURANTE Y PUNTOS DE VENTAS DE ALIMENTO Y BEBIDAS, ASI COMO LA PRODUCCION PARA BANQUETES'
            ],
            [
                'id_departamento' => 10,
                'nombre_departamento' => 'DIVISION CUARTOS',
                'descripcion' => 'ESTE DEPARTAMENTO ESTA ENCARGADO DE DAR CHECKING Y CHECKOUT A HUESPEDES, ASI COMO PROPORCIONAR ATENCION A CUALQUIER VISITANTE, MANTENER LIMPIA LAS AREAS DEL HOTEL'
            ],
            [
                'id_departamento' => 11,
                'nombre_departamento' => 'MANTENIMIENTO',
                'descripcion' => 'MANTENIMIENTO PREVENTIVO Y CORRECTIVO EN LAS AREAS DEL HOTEL'
            ],
            [
                'id_departamento' => 12,
                'nombre_departamento' => 'VENTAS',
                'descripcion' => 'VENTAS DE HOSPEDAJE Y EVENTOS CON O SIN BANQUETES'
            ],
            [
                'id_departamento' => 13,
                'nombre_departamento' => 'SERVICIOS',
                'descripcion' => 'ENCARGADO DE LA SEGURIDAD DEL HOTEL'
            ],
            [
                'id_departamento' => 14,
                'nombre_departamento' => 'COMPRAS',
                'descripcion' => 'SE ENCARGA DE LAS COMPRAS DE TODAS LAS AREAS'
            ],
            [
                'id_departamento' => 15,
                'nombre_departamento' => 'ADMINISTRATIVO',
                'descripcion' => 'SE ENCARGA DE LOS INGRESOS Y EGRESOS'
            ],
        ];

        foreach ($departamentos as $departamento) {
            Departamento::create($departamento);
        }
    }
}