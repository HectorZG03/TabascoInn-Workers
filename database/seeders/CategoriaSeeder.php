<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            // RECEPCIÓN (id_area = 1)
            ['id_categoria' => 1, 'id_area' => 1, 'nombre_categoria' => 'Recepcionista'],
            ['id_categoria' => 2, 'id_area' => 1, 'nombre_categoria' => 'Conserje'],
            ['id_categoria' => 3, 'id_area' => 1, 'nombre_categoria' => 'Jefe de Recepción'],
            ['id_categoria' => 4, 'id_area' => 1, 'nombre_categoria' => 'Botones'],

            // LIMPIEZA (id_area = 2)
            ['id_categoria' => 5, 'id_area' => 2, 'nombre_categoria' => 'Camarista'],
            ['id_categoria' => 6, 'id_area' => 2, 'nombre_categoria' => 'Supervisor de Limpieza'],
            ['id_categoria' => 7, 'id_area' => 2, 'nombre_categoria' => 'Auxiliar de Limpieza'],
            ['id_categoria' => 8, 'id_area' => 2, 'nombre_categoria' => 'Encargado de Áreas Públicas'],

            // COCINA (id_area = 3)
            ['id_categoria' => 9, 'id_area' => 3, 'nombre_categoria' => 'Chef Ejecutivo'],
            ['id_categoria' => 10, 'id_area' => 3, 'nombre_categoria' => 'Sous Chef'],
            ['id_categoria' => 11, 'id_area' => 3, 'nombre_categoria' => 'Cocinero'],
            ['id_categoria' => 12, 'id_area' => 3, 'nombre_categoria' => 'Ayudante de Cocina'],
            ['id_categoria' => 13, 'id_area' => 3, 'nombre_categoria' => 'Panadero'],
            ['id_categoria' => 14, 'id_area' => 3, 'nombre_categoria' => 'Pastelero'],

            // RESTAURANTE (id_area = 4)
            ['id_categoria' => 15, 'id_area' => 4, 'nombre_categoria' => 'Mesero'],
            ['id_categoria' => 16, 'id_area' => 4, 'nombre_categoria' => 'Capitán de Meseros'],
            ['id_categoria' => 17, 'id_area' => 4, 'nombre_categoria' => 'Hostess'],
            ['id_categoria' => 18, 'id_area' => 4, 'nombre_categoria' => 'Auxiliar de Restaurante'],

            // BAR (id_area = 5)
            ['id_categoria' => 19, 'id_area' => 5, 'nombre_categoria' => 'Barman'],
            ['id_categoria' => 20, 'id_area' => 5, 'nombre_categoria' => 'Bartender'],
            ['id_categoria' => 21, 'id_area' => 5, 'nombre_categoria' => 'Auxiliar de Bar'],

            // MANTENIMIENTO (id_area = 6)
            ['id_categoria' => 22, 'id_area' => 6, 'nombre_categoria' => 'Jefe de Mantenimiento'],
            ['id_categoria' => 23, 'id_area' => 6, 'nombre_categoria' => 'Técnico Eléctrico'],
            ['id_categoria' => 24, 'id_area' => 6, 'nombre_categoria' => 'Técnico en Plomería'],
            ['id_categoria' => 25, 'id_area' => 6, 'nombre_categoria' => 'Técnico en Aires Acondicionados'],
            ['id_categoria' => 26, 'id_area' => 6, 'nombre_categoria' => 'Jardinero'],
            ['id_categoria' => 27, 'id_area' => 6, 'nombre_categoria' => 'Pintor'],

            // ADMINISTRACIÓN (id_area = 7)
            ['id_categoria' => 28, 'id_area' => 7, 'nombre_categoria' => 'Gerente General'],
            ['id_categoria' => 29, 'id_area' => 7, 'nombre_categoria' => 'Subgerente'],
            ['id_categoria' => 30, 'id_area' => 7, 'nombre_categoria' => 'Contador'],
            ['id_categoria' => 31, 'id_area' => 7, 'nombre_categoria' => 'Auxiliar Contable'],
            ['id_categoria' => 32, 'id_area' => 7, 'nombre_categoria' => 'Secretaria'],

            // SEGURIDAD (id_area = 8)
            ['id_categoria' => 33, 'id_area' => 8, 'nombre_categoria' => 'Jefe de Seguridad'],
            ['id_categoria' => 34, 'id_area' => 8, 'nombre_categoria' => 'Guardia de Seguridad'],
            ['id_categoria' => 35, 'id_area' => 8, 'nombre_categoria' => 'Vigilante Nocturno'],

            // LAVANDERÍA (id_area = 9)
            ['id_categoria' => 36, 'id_area' => 9, 'nombre_categoria' => 'Supervisor de Lavandería'],
            ['id_categoria' => 37, 'id_area' => 9, 'nombre_categoria' => 'Operador de Lavandería'],
            ['id_categoria' => 38, 'id_area' => 9, 'nombre_categoria' => 'Planchador'],

            // SPA Y WELLNESS (id_area = 10)
            ['id_categoria' => 39, 'id_area' => 10, 'nombre_categoria' => 'Masajista'],
            ['id_categoria' => 40, 'id_area' => 10, 'nombre_categoria' => 'Terapeuta'],
            ['id_categoria' => 41, 'id_area' => 10, 'nombre_categoria' => 'Recepcionista de Spa'],

            // EVENTOS Y BANQUETES (id_area = 11)
            ['id_categoria' => 42, 'id_area' => 11, 'nombre_categoria' => 'Coordinador de Eventos'],
            ['id_categoria' => 43, 'id_area' => 11, 'nombre_categoria' => 'Mesero de Banquetes'],
            ['id_categoria' => 44, 'id_area' => 11, 'nombre_categoria' => 'Montajista'],

            // RECURSOS HUMANOS (id_area = 12)
            ['id_categoria' => 45, 'id_area' => 12, 'nombre_categoria' => 'Gerente de RH'],
            ['id_categoria' => 46, 'id_area' => 12, 'nombre_categoria' => 'Especialista en RH'],
            ['id_categoria' => 47, 'id_area' => 12, 'nombre_categoria' => 'Reclutador'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}