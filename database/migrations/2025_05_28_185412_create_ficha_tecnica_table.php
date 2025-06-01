<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ficha_tecnica', function (Blueprint $table) {
            // ✅ CORREGIDO: Auto-increment
            $table->id('id_ficha');
            
            // ✅ CAMBIO: id_trabajador es NOT NULL y ÚNICO (relación 1:1)
            $table->unsignedBigInteger('id_trabajador')->unique();
            $table->unsignedBigInteger('id_categoria')->nullable();
            
            $table->decimal('sueldo_diarios', 8, 2)->nullable();
            $table->string('formacion', 50)->nullable();
            $table->string('grado_estudios', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ficha_tecnica');
    }
};