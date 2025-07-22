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
        $categorias = [
            // DEPARTAMENTO DE ALIMENTOS Y BEBIDAS
            
            // Área: Meseros (id_area = 1)
            ['id_categoria' => 1, 'id_area' => 1, 'nombre_categoria' => 'Mesero'],
            ['id_categoria' => 2, 'id_area' => 1, 'nombre_categoria' => 'Capitán de meseros'],
            ['id_categoria' => 3, 'id_area' => 1, 'nombre_categoria' => 'Hostess'],
            ['id_categoria' => 4, 'id_area' => 1, 'nombre_categoria' => 'Auxiliar de restaurante'],

            // Área: Cocina (id_area = 2)
            ['id_categoria' => 5, 'id_area' => 2, 'nombre_categoria' => 'Chef ejecutivo'],
            ['id_categoria' => 6, 'id_area' => 2, 'nombre_categoria' => 'Sous chef'],
            ['id_categoria' => 7, 'id_area' => 2, 'nombre_categoria' => 'Cocinero'],
            ['id_categoria' => 8, 'id_area' => 2, 'nombre_categoria' => 'Ayudante de cocina'],
            ['id_categoria' => 9, 'id_area' => 2, 'nombre_categoria' => 'Panadero'],
            ['id_categoria' => 10, 'id_area' => 2, 'nombre_categoria' => 'Pastelero'],

            // Área: Stewards (id_area = 3)
            ['id_categoria' => 11, 'id_area' => 3, 'nombre_categoria' => 'Steward general'],
            ['id_categoria' => 12, 'id_area' => 3, 'nombre_categoria' => 'Lavaloza'],
            ['id_categoria' => 13, 'id_area' => 3, 'nombre_categoria' => 'Encargado de limpieza de cocina'],

            // DEPARTAMENTO DE RECEPCIÓN Y HOSPEDAJE
            
            // Área: Recepción (id_area = 4)
            ['id_categoria' => 14, 'id_area' => 4, 'nombre_categoria' => 'Recepcionista'],
            ['id_categoria' => 15, 'id_area' => 4, 'nombre_categoria' => 'Conserje'],
            ['id_categoria' => 16, 'id_area' => 4, 'nombre_categoria' => 'Botones'],
            ['id_categoria' => 17, 'id_area' => 4, 'nombre_categoria' => 'Jefe de recepción'],

            // Área: Hospedaje (id_area = 5)
            ['id_categoria' => 18, 'id_area' => 5, 'nombre_categoria' => 'Camarista'],
            ['id_categoria' => 19, 'id_area' => 5, 'nombre_categoria' => 'Supervisor de pisos'],
            ['id_categoria' => 20, 'id_area' => 5, 'nombre_categoria' => 'Encargado de habitaciones'],

            // DEPARTAMENTO DE SERVICIOS GENERALES
            
            // Área: Áreas Públicas (id_area = 6)
            ['id_categoria' => 21, 'id_area' => 6, 'nombre_categoria' => 'Auxiliar de limpieza'],
            ['id_categoria' => 22, 'id_area' => 6, 'nombre_categoria' => 'Encargado de áreas públicas'],
            ['id_categoria' => 23, 'id_area' => 6, 'nombre_categoria' => 'Personal de limpieza nocturno'],

            // DEPARTAMENTO DE SEGURIDAD
            
            // Área: Vigilancia (id_area = 7)
            ['id_categoria' => 24, 'id_area' => 7, 'nombre_categoria' => 'Guardia de seguridad'],
            ['id_categoria' => 25, 'id_area' => 7, 'nombre_categoria' => 'Vigilante nocturno'],
            ['id_categoria' => 26, 'id_area' => 7, 'nombre_categoria' => 'Jefe de seguridad'],

            // DEPARTAMENTO COMERCIAL
            
            // Área: Ventas (id_area = 8)
            ['id_categoria' => 27, 'id_area' => 8, 'nombre_categoria' => 'Ejecutivo de ventas'],
            ['id_categoria' => 28, 'id_area' => 8, 'nombre_categoria' => 'Coordinador de eventos'],
            ['id_categoria' => 29, 'id_area' => 8, 'nombre_categoria' => 'Auxiliar administrativo de ventas'],

            // DEPARTAMENTO DE ABASTECIMIENTO
            
            // Área: Almacén (id_area = 9)
            ['id_categoria' => 30, 'id_area' => 9, 'nombre_categoria' => 'Almacenista'],
            ['id_categoria' => 31, 'id_area' => 9, 'nombre_categoria' => 'Encargado de inventarios'],
            ['id_categoria' => 32, 'id_area' => 9, 'nombre_categoria' => 'Repartidor interno'],

            // DEPARTAMENTO ADMINISTRATIVO
            
            // Área: Gerencia Administrativa (id_area = 10)
            ['id_categoria' => 33, 'id_area' => 10, 'nombre_categoria' => 'Gerente general'],
            ['id_categoria' => 34, 'id_area' => 10, 'nombre_categoria' => 'Subgerente'],
            ['id_categoria' => 35, 'id_area' => 10, 'nombre_categoria' => 'Auxiliar contable'],
            ['id_categoria' => 36, 'id_area' => 10, 'nombre_categoria' => 'Secretaria'],
            ['id_categoria' => 37, 'id_area' => 10, 'nombre_categoria' => 'Contador'],

            // DEPARTAMENTO TÉCNICO
            
            // Área: Mantenimiento (id_area = 11)
            ['id_categoria' => 38, 'id_area' => 11, 'nombre_categoria' => 'Técnico eléctrico'],
            ['id_categoria' => 39, 'id_area' => 11, 'nombre_categoria' => 'Técnico en plomería'],
            ['id_categoria' => 40, 'id_area' => 11, 'nombre_categoria' => 'Técnico en aires acondicionados'],
            ['id_categoria' => 41, 'id_area' => 11, 'nombre_categoria' => 'Jardinero'],
            ['id_categoria' => 42, 'id_area' => 11, 'nombre_categoria' => 'Pintor'],
            ['id_categoria' => 43, 'id_area' => 11, 'nombre_categoria' => 'Jefe de mantenimiento'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}