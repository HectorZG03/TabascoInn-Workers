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
        $table->id('id_permiso');

        $table->unsignedBigInteger('id_trabajador');
        $table->foreign('id_trabajador')
            ->references('id_trabajador')
            ->on('trabajadores')
            ->onDelete('cascade');

        $table->string('tipo_permiso', 100);
        $table->string('motivo', 100);

        $table->date('fecha_inicio');
        $table->date('fecha_fin');

        // 🔁 NUEVO: Campos para permisos por horas
        $table->time('hora_inicio')->nullable()->comment('Hora de inicio del permiso (si aplica)');
        $table->time('hora_fin')->nullable()->comment('Hora de fin del permiso (si aplica)');
        $table->boolean('es_por_horas')->default(false)->comment('Indica si el permiso es por horas específicas');

        $table->text('observaciones')->nullable();
        $table->enum('estatus_permiso', ['activo', 'finalizado', 'cancelado'])->default('activo');

        $table->string('ruta_pdf', 500)->nullable()->comment('Ruta del PDF generado del permiso');

        $table->timestamps();

        $table->index('tipo_permiso');
        $table->index('motivo');
        $table->index(['tipo_permiso', 'motivo']);
        $table->index(['fecha_inicio', 'fecha_fin']);
        $table->index(['id_trabajador', 'fecha_fin']);
        $table->index('fecha_fin');
        $table->index('estatus_permiso');
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