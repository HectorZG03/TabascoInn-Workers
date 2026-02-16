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
        Schema::create('documento_vacacion_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_vacacion_id');
            $table->unsignedBigInteger('vacacion_id');
            $table->timestamps();
            
            // Crear índices
            $table->index(['documento_vacacion_id']);
            $table->index(['vacacion_id']);
            
            // Constraint único para evitar duplicados
            $table->unique(['documento_vacacion_id', 'vacacion_id'], 'unique_documento_vacacion');
            
            // Foreign keys
            $table->foreign('documento_vacacion_id', 'fk_documento_vacacion')
                  ->references('id')
                  ->on('documento_vacaciones')
                  ->onDelete('cascade');
                  
            $table->foreign('vacacion_id', 'fk_vacacion')
                  ->references('id_vacacion')
                  ->on('vacaciones_trabajadores')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_vacacion_vacaciones');
    }
};