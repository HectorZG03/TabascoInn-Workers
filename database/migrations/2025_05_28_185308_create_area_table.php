<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->unsignedBigInteger('id_departamento'); // ✅ Nueva relación
            $table->string('nombre_area', 50)->unique();
            
            // ✅ Foreign key al departamento
            $table->foreign('id_departamento')->references('id_departamento')->on('departamentos')
                  ->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('area');
    }
};