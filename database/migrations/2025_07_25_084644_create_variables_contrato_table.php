<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('variables_contrato', function (Blueprint $table) {
            $table->id('id_variable');
            
            // Información de la variable
            $table->string('nombre_variable', 100)->unique()->comment('Nombre de la variable sin {{}} ej: trabajador_nombre');
            $table->string('etiqueta', 150)->comment('Etiqueta descriptiva para mostrar en el editor');
            $table->text('descripcion')->nullable()->comment('Descripción de qué contiene la variable');
            
            // Categorización
            $table->enum('categoria', [
                'trabajador',
                'empresa', 
                'contrato',
                'fechas',
                'horarios',
                'salariales',
                'beneficiario',
                'legal'
            ])->comment('Categoría para organizar las variables');
            
            // Tipo de dato y formato
            $table->enum('tipo_dato', [
                'texto',
                'numero',
                'fecha',
                'hora',
                'booleano',
                'calculado'
            ])->default('texto')->comment('Tipo de dato de la variable');
            
            $table->string('formato_ejemplo', 200)->nullable()->comment('Ejemplo de cómo se ve la variable');
            
            // Origen del dato
            $table->string('origen_modelo', 100)->nullable()->comment('Modelo de donde viene el dato (Trabajador, ContratoTrabajador, etc.)');
            $table->string('origen_campo', 100)->nullable()->comment('Campo específico del modelo');
            $table->text('origen_codigo')->nullable()->comment('Código PHP para obtener el valor si es calculado');
            
            // Estado
            $table->boolean('activa')->default(true)->comment('Si la variable está disponible');
            $table->boolean('obligatoria')->default(false)->comment('Si debe estar presente en toda plantilla');
            
            $table->timestamps();
            
            // Índices
            $table->index(['categoria', 'activa'], 'idx_categoria_activa');
            $table->index('tipo_dato', 'idx_tipo_dato');
            $table->index('obligatoria', 'idx_obligatoria');
        });
    }

    public function down()
    {
        Schema::dropIfExists('variables_contrato');
    }
};