<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plantillas_contratos', function (Blueprint $table) {
            $table->id('id_plantilla');
            
            // Información básica
            $table->string('nombre_plantilla', 100)->comment('Nombre descriptivo de la plantilla');
            $table->enum('tipo_contrato', ['determinado', 'indeterminado', 'ambos'])->default('ambos')->comment('Tipo de contrato al que aplica');
            
            // Contenido de la plantilla
            $table->longText('contenido_html')->comment('HTML completo de la plantilla con variables {{variable}}');
            $table->json('variables_utilizadas')->nullable()->comment('Array de variables usadas en esta plantilla');
            
            // Control de versiones
            $table->integer('version')->default(1)->comment('Versión de la plantilla');
            $table->boolean('activa')->default(false)->comment('Si esta versión está activa');
            
            // Metadatos
            $table->text('descripcion')->nullable()->comment('Descripción de los cambios');
            $table->unsignedBigInteger('creado_por')->nullable()->comment('Usuario que creó la plantilla');
            $table->unsignedBigInteger('modificado_por')->nullable()->comment('Usuario que modificó la plantilla');
            
            $table->timestamps();
            
            // Índices
            $table->index(['activa', 'tipo_contrato'], 'idx_plantilla_activa');
            $table->index('version', 'idx_version');
            $table->index('creado_por', 'idx_creado_por');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plantillas_contratos');
    }
};