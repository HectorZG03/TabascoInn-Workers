<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permisos_laborales', function (Blueprint $table) {
            // ✅ CAMPO ID PRINCIPAL
            $table->id('id_permiso');
            
            // ✅ RELACIÓN CON TRABAJADOR
            $table->unsignedBigInteger('id_trabajador');
            $table->foreign('id_trabajador')
                  ->references('id_trabajador')
                  ->on('trabajadores')
                  ->onDelete('cascade');
            
            // ✅ TIPO DE PERMISO (SOLO 2 OPCIONES)
            $table->enum('tipo_permiso', ['permiso', 'suspendido']);
            
            // ✅ MOTIVO ESPECÍFICO
            $table->string('motivo', 100);
            
            // ✅ FECHAS DEL PERMISO
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // ✅ OBSERVACIONES OPCIONALES
            $table->text('observaciones')->nullable();
            
            // ✅ ESTATUS DEL PERMISO
            $table->enum('estatus_permiso', ['activo', 'finalizado', 'cancelado'])->default('activo');
            
            // ✅ TIMESTAMPS
            $table->timestamps();
            
            // ✅ ÍNDICES PARA OPTIMIZACIÓN
            $table->index('tipo_permiso');
            $table->index('motivo');
            $table->index(['tipo_permiso', 'motivo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['id_trabajador', 'fecha_fin']); // Para permisos activos
            $table->index(['fecha_fin']); // Para verificar vencimientos
            $table->index('estatus_permiso'); // Nuevo índice útil
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_laborales');
    }
};
