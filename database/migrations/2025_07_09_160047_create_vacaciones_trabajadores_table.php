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
        Schema::create('vacaciones_trabajadores', function (Blueprint $table) {
            $table->id('id_vacacion');
            $table->unsignedBigInteger('id_trabajador');
            $table->unsignedBigInteger('creado_por'); // Usuario que creó el registro

            // ✅ PERÍODO VACACIONAL - ENTRADA MANUAL
            $table->string('periodo_vacacional', 30)->comment('Período ingresado manualmente ej: "2025-2026", "2024-2025"');
            $table->integer('año_correspondiente')->comment('Año ingresado manualmente, puede ser cualquier año');

            // Días de vacaciones
            $table->integer('dias_correspondientes')->comment('Días que le corresponden según antigüedad');
            $table->integer('dias_solicitados')->comment('Días que solicita tomar');
            $table->integer('dias_disfrutados')->default(0)->comment('Días efectivamente disfrutados');
            $table->integer('dias_restantes')->comment('Días pendientes de disfrutar');

            // ✅ FECHAS - SIN RESTRICCIONES TEMPORALES
            $table->date('fecha_inicio')->comment('Fecha de inicio - puede ser pasada, presente o futura');
            $table->date('fecha_fin')->comment('Fecha de fin - puede ser pasada, presente o futura');
            $table->date('fecha_reintegro')->nullable()->comment('Fecha real de reintegro');

            // Estados de vacaciones
            $table->enum('estado', ['pendiente', 'activa', 'finalizada', 'cancelada'])->default('pendiente');

            // Observaciones y motivos
            $table->text('observaciones')->nullable();
            $table->text('motivo_finalizacion')->nullable();
            $table->text('motivo_cancelacion')->nullable()->comment('Motivo por el cual se canceló');
            $table->boolean('justificada_por_documento')
                  ->default(false)
                  ->comment('Indica si la vacación tiene documento de amortización');

            // Control de cancelación
            $table->unsignedBigInteger('cancelado_por')->nullable()->comment('Usuario que canceló las vacaciones');
            $table->timestamp('fecha_cancelacion')->nullable()->comment('Fecha cuando se canceló');

            // Metadatos
            $table->timestamps();

            // Índices y relaciones
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancelado_por')->references('id')->on('users')->onDelete('restrict');

            // ✅ ÍNDICES OPTIMIZADOS PARA CONSULTAS HISTÓRICAS
            $table->index(['id_trabajador', 'estado']);
            $table->index(['periodo_vacacional']); // Para filtrar por período
            $table->index(['año_correspondiente']); // Para filtrar por año
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['estado']);
            $table->index(['id_trabajador', 'año_correspondiente']); // Para estadísticas por trabajador y año
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacaciones_trabajadores');
    }
};