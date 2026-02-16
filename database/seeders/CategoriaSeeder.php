<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ DATOS REALES DEL HOTEL TABASCO INN EN PRODUCCIÓN
        $categorias = [
            // DEPARTAMENTO DE ALIMENTOS Y BEBIDAS
            
            // Área: RESTAURANTE (id_area = 12)
            ['id_categoria' => 44, 'id_area' => 12, 'nombre_categoria' => 'CAPITAN DE MESEROS'],
            ['id_categoria' => 45, 'id_area' => 12, 'nombre_categoria' => 'MESERO'],
            ['id_categoria' => 47, 'id_area' => 12, 'nombre_categoria' => 'HOSTESS'],
            ['id_categoria' => 87, 'id_area' => 12, 'nombre_categoria' => 'MESERO'],
            ['id_categoria' => 88, 'id_area' => 12, 'nombre_categoria' => 'MESERA'],

            // Área: COCINA (id_area = 13)
            ['id_categoria' => 48, 'id_area' => 13, 'nombre_categoria' => 'CHEFF EJECUTIVO'],
            ['id_categoria' => 49, 'id_area' => 13, 'nombre_categoria' => 'COCINERO (A)'],
            ['id_categoria' => 50, 'id_area' => 13, 'nombre_categoria' => 'PANADERO'],
            ['id_categoria' => 51, 'id_area' => 13, 'nombre_categoria' => 'REPOSTERA (O)'],
            ['id_categoria' => 52, 'id_area' => 13, 'nombre_categoria' => 'CHEFF STEWARD'],
            ['id_categoria' => 53, 'id_area' => 13, 'nombre_categoria' => 'SUPERVISOR DE STEWARD'],
            ['id_categoria' => 54, 'id_area' => 13, 'nombre_categoria' => 'STEWARD'],

            // Área: INDIGO (id_area = 14)
            ['id_categoria' => 56, 'id_area' => 14, 'nombre_categoria' => 'BARMAN'],
            ['id_categoria' => 67, 'id_area' => 14, 'nombre_categoria' => 'MESERA'],

            // Área: ALIMENTOS Y BEBIDAS (id_area = 25)
            ['id_categoria' => 86, 'id_area' => 25, 'nombre_categoria' => 'GERENTE DE ALIMENTOS Y BEBIDAS'],

            // DEPARTAMENTO DE DIVISIÓN CUARTOS
            
            // Área: RECEPCION (id_area = 15)
            ['id_categoria' => 57, 'id_area' => 15, 'nombre_categoria' => 'ENCARGADO DE RECEPCION Y RESERVACIONES'],
            ['id_categoria' => 58, 'id_area' => 15, 'nombre_categoria' => 'AUDITOR NOCTURNO-RECEPCIONISTA'],
            ['id_categoria' => 59, 'id_area' => 15, 'nombre_categoria' => 'RECEPCIONISTA'],
            ['id_categoria' => 60, 'id_area' => 15, 'nombre_categoria' => 'BELL BOY'],

            // Área: RESTAURANTE-RECEPCION (id_area = 16)
            ['id_categoria' => 61, 'id_area' => 16, 'nombre_categoria' => 'CAJERO (A) RESTAURANTE'],

            // Área: AREAS PUBLICAS (id_area = 17)
            ['id_categoria' => 62, 'id_area' => 17, 'nombre_categoria' => 'SUPERVISOR DE AREAS PUBLICAS'],
            ['id_categoria' => 63, 'id_area' => 17, 'nombre_categoria' => 'MOZO DE AREAS PUBLICAS'],

            // Área: HOSPEDAJE (id_area = 18)
            ['id_categoria' => 64, 'id_area' => 18, 'nombre_categoria' => 'AMA DE LLAVES'],
            ['id_categoria' => 65, 'id_area' => 18, 'nombre_categoria' => 'SUPERVISORA DE AMA DE LLAVES'],
            ['id_categoria' => 66, 'id_area' => 18, 'nombre_categoria' => 'RECAMARERA'],

            // Área: LAVANDERIA (id_area = 19)
            ['id_categoria' => 68, 'id_area' => 19, 'nombre_categoria' => 'LAVANDERO'],

            // DEPARTAMENTO DE MANTENIMIENTO
            
            // Área: MANTENIMIENTO (id_area = 20)
            ['id_categoria' => 69, 'id_area' => 20, 'nombre_categoria' => 'GERENTE DE MANTENIMIENTO'],
            ['id_categoria' => 70, 'id_area' => 20, 'nombre_categoria' => 'OFICIAL ALBAÑIL B'],
            ['id_categoria' => 71, 'id_area' => 20, 'nombre_categoria' => 'AUX. DE ALBAÑIL'],
            ['id_categoria' => 72, 'id_area' => 20, 'nombre_categoria' => 'AUX. DE MANTTO. B'],
            ['id_categoria' => 73, 'id_area' => 20, 'nombre_categoria' => 'TEC. AIRE ACONDICIONADO'],

            // DEPARTAMENTO DE VENTAS
            
            // Área: VENTAS (id_area = 21)
            ['id_categoria' => 74, 'id_area' => 21, 'nombre_categoria' => 'GERENTE DE VENTAS'],
            ['id_categoria' => 75, 'id_area' => 21, 'nombre_categoria' => 'EJECUTIVO DE VENTAS B'],
            ['id_categoria' => 76, 'id_area' => 21, 'nombre_categoria' => 'EJECUTIVO DE VENTAS'],

            // DEPARTAMENTO DE SERVICIOS
            
            // Área: SEGURIDAD (id_area = 22)
            ['id_categoria' => 77, 'id_area' => 22, 'nombre_categoria' => 'SUPERVISOR DE SEGURIDAD'],
            ['id_categoria' => 78, 'id_area' => 22, 'nombre_categoria' => 'VIGILANTE'],

            // DEPARTAMENTO DE COMPRAS
            
            // Área: ALMACEN (id_area = 23)
            ['id_categoria' => 79, 'id_area' => 23, 'nombre_categoria' => 'ENCARGADO DE ALMACEN'],
            ['id_categoria' => 80, 'id_area' => 23, 'nombre_categoria' => 'CHOFER DE ALMACEN'],

            // DEPARTAMENTO ADMINISTRATIVO
            
            // Área: GERENCIA ADMINISTRATIVA (id_area = 24)
            ['id_categoria' => 81, 'id_area' => 24, 'nombre_categoria' => 'GERENTE GENERAL'],
            ['id_categoria' => 82, 'id_area' => 24, 'nombre_categoria' => 'SUBGERENTE ADMINISTRATIVO'],
            ['id_categoria' => 83, 'id_area' => 24, 'nombre_categoria' => 'JEFE DE RECURSOS HUMANOS'],
            ['id_categoria' => 84, 'id_area' => 24, 'nombre_categoria' => 'JEFE DE COSTOS'],
            ['id_categoria' => 85, 'id_area' => 24, 'nombre_categoria' => 'JEFE DE INGRESOS'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}