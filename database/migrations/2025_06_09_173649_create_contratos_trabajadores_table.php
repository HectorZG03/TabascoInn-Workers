<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('contratos_trabajadores', function (Blueprint $table) {
            $table->id('id_contrato');
            $table->foreignId('id_trabajador')->constrained('trabajadores', 'id_trabajador');
            
            // ✅ NUEVO: Tipo de contrato
            $table->enum('tipo_contrato', ['determinado', 'indeterminado'])->default('determinado');
            
            $table->date('fecha_inicio_contrato');
            
            // ✅ MODIFICADO: Fecha fin nullable para contratos indeterminados
            $table->date('fecha_fin_contrato')->nullable()->comment('NULL para contratos indeterminados');
            
            // ✅ CAMPOS PARA MANEJAR DÍAS Y MESES (solo para determinados)
            $table->enum('tipo_duracion', ['dias', 'meses'])->nullable()->comment('NULL para indeterminados');
            $table->integer('duracion')->nullable()->comment('Duración en días o meses, NULL para indeterminados');
            
            // ✅ CAMPO ESTATUS FÍSICO
            $table->enum('estatus', [
                'activo',       // Contrato vigente y activo
                'terminado',    // Completado naturalmente al llegar a fecha fin
                'revocado',     // Cancelado/terminado antes de tiempo
                'renovado'      // Reemplazado por una renovación
            ])->default('activo');
            
            // ✅ REFERENCIAS PARA RENOVACIONES
            $table->foreignId('contrato_anterior_id')
                ->nullable()
                ->constrained('contratos_trabajadores', 'id_contrato')
                ->comment('ID del contrato que se renovó');
            
            // ✅ CAMPOS ADICIONALES
            $table->text('observaciones')->nullable()->comment('Observaciones o motivos especiales');
            $table->string('ruta_archivo', 255);
            
            // ✅ MANTENER COMPATIBILIDAD (se puede eliminar después)
            $table->integer('duracion_meses')->nullable()->comment('Campo legacy');
            
            $table->timestamps();
            
            // ✅ ÍNDICES OPTIMIZADOS
            $table->index('id_trabajador', 'idx_trabajador');
            $table->index('estatus', 'idx_estatus');
            $table->index('tipo_contrato', 'idx_tipo_contrato'); // ✅ NUEVO
            $table->index(['tipo_duracion', 'duracion'], 'idx_tipo_duracion');
            $table->index(['fecha_inicio_contrato', 'fecha_fin_contrato'], 'idx_fechas');
            $table->index('contrato_anterior_id', 'idx_anterior');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratos_trabajadores');
    }
};