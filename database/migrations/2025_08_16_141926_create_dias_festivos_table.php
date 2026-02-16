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
        Schema::create('dias_festivos', function (Blueprint $table) {
            $table->id();
            $table->integer('año')->index()->comment('Año al que pertenece el día festivo');
            $table->date('fecha')->index()->comment('Fecha del día festivo');
            $table->string('nombre', 100)->comment('Nombre del día festivo (ej: Año Nuevo, Navidad)');
            $table->boolean('es_oficial')->default(true)->comment('Si es día festivo oficial');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();
            
            // Índices
            $table->unique(['año', 'fecha'], 'unique_año_fecha');
            $table->index(['año', 'es_oficial'], 'idx_año_oficial');
            
            // Foreign keys
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_festivos');
    }
};