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

            // Período vacacional
            $table->string('periodo_vacacional', 20); // ej: "2025-2026"
            $table->year('año_correspondiente'); // ej: 2025

            // Días de vacaciones
            $table->integer('dias_correspondientes')->comment('Días que le corresponden según antigüedad');
            $table->integer('dias_solicitados')->comment('Días que solicita tomar');
            $table->integer('dias_disfrutados')->default(0)->comment('Días efectivamente disfrutados');
            $table->integer('dias_restantes')->comment('Días pendientes de disfrutar');

            // Fechas
            $table->date('fecha_inicio')->comment('Fecha de inicio de vacaciones');
            $table->date('fecha_fin')->comment('Fecha de fin de vacaciones');
            $table->date('fecha_reintegro')->nullable()->comment('Fecha real de reintegro');

            // Estados: pendiente, activa, finalizada
            $table->enum('estado', ['pendiente', 'activa', 'finalizada'])->default('pendiente');

            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('motivo_finalizacion')->nullable();
            $table->boolean('justificada_por_documento')
                  ->default(false)
                  ->comment('Indica si la vacación tiene documento de amortización');

            // Metadatos
            $table->timestamps();

            // Índices y relaciones
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('restrict');

            // Índices para consultas frecuentes
            $table->index(['id_trabajador', 'estado']);
            $table->index(['periodo_vacacional']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['estado']);
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
