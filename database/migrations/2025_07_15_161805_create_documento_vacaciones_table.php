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
        Schema::create('documento_vacaciones', function (Blueprint $table) {
            $table->id(); // UNSIGNED BIGINT AUTO_INCREMENT
            $table->unsignedBigInteger('trabajador_id');
            $table->string('nombre_original', 255)->comment('Nombre original del archivo');
            $table->string('ruta', 500)->comment('Ruta del archivo en storage');
            $table->timestamps();
            
            // Relación con trabajadores
            $table->foreign('trabajador_id')
                  ->references('id_trabajador')
                  ->on('trabajadores')
                  ->onDelete('cascade');
            
            // Índices
            $table->index(['trabajador_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_vacaciones');
    }
};