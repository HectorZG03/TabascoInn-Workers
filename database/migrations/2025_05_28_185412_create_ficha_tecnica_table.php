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
            
            // ✅ DATOS SALARIALES
            $table->decimal('sueldo_diarios', 8, 2)->nullable();
            
            // ✅ DATOS ACADÉMICOS
            $table->string('formacion', 50)->nullable();
            $table->string('grado_estudios', 50)->nullable();
            
            // ✅ NUEVOS: Datos laborales específicos
            $table->decimal('horas_trabajo', 4, 2)->nullable()->comment('Horas de trabajo por día (ej: 8.00, 8.50)');
            $table->enum('turno', [
                'diurno',   // Turno diurno
                'nocturno', // Turno nocturno  
                'mixto'     // Turno mixto/rotativo
            ])->nullable()->comment('Turno de trabajo del empleado');
            
            // ✅ ÍNDICES PARA CONSULTAS
            $table->index('turno', 'idx_turno');
            $table->index('horas_trabajo', 'idx_horas_trabajo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ficha_tecnica');
    }
};