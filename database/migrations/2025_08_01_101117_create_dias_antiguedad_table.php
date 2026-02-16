// database/migrations/2024_06_20_000000_create_dias_antiguedad_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDiasAntiguedadTable extends Migration
{
    public function up()
    {
        Schema::create('dias_antiguedad', function (Blueprint $table) {
            $table->id();
            $table->integer('antiguedad_min'); // Años mínimos
            $table->integer('antiguedad_max')->nullable(); // Años máximos (opcional)
            $table->integer('dias'); // Días correspondientes
            $table->timestamps();
        });

        // Datos iniciales completos según los requerimientos
        DB::table('dias_antiguedad')->insert([
            ['antiguedad_min' => 1, 'antiguedad_max' => 1, 'dias' => 12],
            ['antiguedad_min' => 2, 'antiguedad_max' => 2, 'dias' => 14],
            ['antiguedad_min' => 3, 'antiguedad_max' => 3, 'dias' => 16],
            ['antiguedad_min' => 4, 'antiguedad_max' => 4, 'dias' => 18],
            ['antiguedad_min' => 5, 'antiguedad_max' => 5, 'dias' => 20],
            ['antiguedad_min' => 6, 'antiguedad_max' => 10, 'dias' => 22],
            ['antiguedad_min' => 11, 'antiguedad_max' => 15, 'dias' => 24],
            ['antiguedad_min' => 16, 'antiguedad_max' => 20, 'dias' => 26],
            ['antiguedad_min' => 21, 'antiguedad_max' => 25, 'dias' => 28],
            ['antiguedad_min' => 26, 'antiguedad_max' => 30, 'dias' => 30]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('dias_antiguedad');
    }
}