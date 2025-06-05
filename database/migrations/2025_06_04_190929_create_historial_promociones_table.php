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
        Schema::create('historial_promociones', function (Blueprint $table) {
            $table->id('id_promocion');
            
            // Relación con el trabajador
            $table->unsignedBigInteger('id_trabajador');
            $table->foreign('id_trabajador')->references('id_trabajador')->on('trabajadores')->onDelete('cascade');
            
            // Categoría anterior (puede ser null en el primer registro)
            $table->unsignedBigInteger('id_categoria_anterior')->nullable();
            $table->foreign('id_categoria_anterior')->references('id_categoria')->on('categoria')->onDelete('set null');
            
            // Categoría nueva
            $table->unsignedBigInteger('id_categoria_nueva');
            $table->foreign('id_categoria_nueva')->references('id_categoria')->on('categoria')->onDelete('cascade');
            
            // Sueldo anterior (puede ser null en el primer registro)
            $table->decimal('sueldo_anterior', 8, 2)->nullable();
            
            // Sueldo nuevo
            $table->decimal('sueldo_nuevo', 8, 2);
            
            // Información del cambio
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->enum('tipo_cambio', [
                'promocion',           // Cambio a mejor categoría/área
                'transferencia',       // Cambio de área sin promoción
                'aumento_sueldo',      // Solo aumento de sueldo
                'reclasificacion',     // Cambio de categoría
                'ajuste_salarial',     // Ajuste de sueldo
                'inicial'              // Registro inicial al crear ficha técnica
            ])->default('promocion');
            
            $table->string('motivo', 255)->nullable()->comment('Razón del cambio');
            $table->text('observaciones')->nullable();
            
            // Usuario que realizó el cambio
            $table->string('usuario_cambio', 100)->nullable()->comment('Email del usuario que hizo el cambio');
            
            // Metadatos adicionales
            $table->json('datos_adicionales')->nullable()->comment('Información extra como formación, grado_estudios, etc');
            
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['id_trabajador', 'fecha_cambio']);
            $table->index('tipo_cambio');
            $table->index('fecha_cambio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_promociones');
    }
};