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
        Schema::create('horas_extra', function (Blueprint $table) {
            $table->id();
            
            // ✅ Relación con trabajador
            $table->unsignedBigInteger('id_trabajador');

            // ✅ Tipo: acumuladas o devueltas
            $table->enum('tipo', ['acumuladas', 'devueltas'])
                  ->comment('acumuladas: horas trabajadas extra | devueltas: horas compensadas');

            // ✅ MODIFICADO: Cantidad de horas (decimal con 2 decimales, máx. 999.99)
            $table->decimal('horas', 5, 2)->unsigned()
                  ->comment('Cantidad de horas con decimales (ej: 1.5, 2.25)');

            // ✅ Fecha del registro
            $table->date('fecha')
                  ->comment('Fecha en que se trabajaron o devolvieron las horas');

            // ✅ Descripción opcional
            $table->string('descripcion', 200)
                  ->nullable()
                  ->comment('Motivo o descripción del registro');

            // ✅ Quién autorizó
            $table->string('autorizado_por', 100)
                  ->nullable()
                  ->comment('Usuario que autorizó/registró las horas');

            // ✅ Timestamps
            $table->timestamps();

            // ✅ Restricciones y relaciones
            $table->foreign('id_trabajador')
                  ->references('id_trabajador')
                  ->on('trabajadores')
                  ->onDelete('cascade');

            // ✅ Índices útiles ACTUALIZADOS
            $table->index(['id_trabajador', 'tipo'], 'idx_trabajador_tipo');
            $table->index(['id_trabajador', 'fecha'], 'idx_trabajador_fecha');
            $table->index(['fecha'], 'idx_fecha');
            $table->index(['tipo', 'fecha'], 'idx_tipo_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horas_extra');
    }
};