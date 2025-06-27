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
            $table->enum('tipo_baja', ['definitiva', 'temporal']);
            $table->unsignedBigInteger('id_trabajador')->nullable();
            $table->dateTime('fecha_baja')->nullable();
            $table->date('fecha_reintegro')->nullable(); // 🔁 sin ->after()
            $table->string('motivo', 150)->nullable();
            $table->string('condicion_salida', 150)->nullable();
            $table->string('observaciones', 150)->nullable();
            $table->enum('estado', ['activo', 'cancelado'])->default('activo');
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->string('motivo_cancelacion', 255)->nullable();
            $table->string('cancelado_por', 100)->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users');
            $table->foreignId('actualizado_por')->nullable()->constrained('users');
            $table->timestamps();

            
            // Foreign key hacia trabajadores
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')
                  ->onDelete('restrict')->onUpdate('restrict');
                  
            // ✅ ÍNDICES para performance
            $table->index(['id_trabajador', 'estado']);
            $table->index('fecha_baja');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despidos');
    }
};