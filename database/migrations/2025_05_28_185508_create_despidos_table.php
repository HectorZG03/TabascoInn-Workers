<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('despidos', function (Blueprint $table) {
            // ✅ CORREGIDO: Auto-increment
            $table->id('id_baja');
            $table->unsignedBigInteger('id_trabajador')->nullable();
            $table->dateTime('fecha_baja')->nullable();
            $table->string('motivo', 150)->nullable();
            $table->string('condicion_salida', 150)->nullable();
            $table->string('observaciones', 150)->nullable();
            
            // Foreign key hacia trabajadores
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')
                  ->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despidos');
    }
};