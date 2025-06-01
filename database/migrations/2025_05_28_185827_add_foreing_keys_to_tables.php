<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ✅ AGREGAR: Foreign keys en ficha_tecnica
        Schema::table('ficha_tecnica', function (Blueprint $table) {
            // Ficha técnica pertenece a un trabajador (relación principal)
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')
                  ->onDelete('cascade')->onUpdate('cascade');
                  
            // Ficha técnica pertenece a una categoría
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria')
                  ->onDelete('restrict')->onUpdate('restrict');
        });

        // ✅ AGREGAR: Foreign key en trabajadores (solo para despidos)
        Schema::table('trabajadores', function (Blueprint $table) {
            
            // ✅ MANTENER: Solo la relación con despidos
            $table->foreign('id_baja')->references('id_baja')->on('despidos')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        // Eliminar foreign keys en orden inverso
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropForeign(['id_baja']);
        });

        Schema::table('ficha_tecnica', function (Blueprint $table) {
            $table->dropForeign(['id_trabajador']);
            $table->dropForeign(['id_categoria']);
        });
    }
};