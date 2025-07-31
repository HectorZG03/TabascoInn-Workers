<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categoria', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->unsignedBigInteger('id_area');
            $table->string('nombre_categoria', 50); 

            $table->foreign('id_area')->references('id_area')->on('area')
                  ->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('categoria');
    }
};