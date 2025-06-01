<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categoria', function (Blueprint $table) {
            // ✅ CORREGIDO: Auto-increment
            $table->id('id_categoria');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->string('nombre_categoria', 50)->nullable();
            
            // Foreign key
            $table->foreign('id_area')->references('id_area')->on('area')
                  ->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('categoria');
    }
};