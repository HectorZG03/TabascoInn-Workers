<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documentos_horas_extra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_horas_extra');
            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('tipo_mime');
            $table->integer('tamaño_archivo');
            $table->string('subido_por');
            $table->timestamps();

            $table->foreign('id_horas_extra')->references('id')->on('horas_extra')->onDelete('cascade');
            $table->index(['id_horas_extra']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_horas_extra');
    }
};