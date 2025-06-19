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
            $table->date('fecha_inicio_contrato');
            $table->date('fecha_fin_contrato');
            
            // ✅ CAMPOS PARA MANEJAR DÍAS Y MESES
            $table->enum('tipo_duracion', ['dias', 'meses'])->default('meses');
            $table->integer('duracion')->comment('Duración en días o meses según tipo_duracion');
            
            // ✅ NUEVO: Campo estatus físico para mejor control
            $table->enum('estatus', [
                'activo',       // Contrato vigente y activo
                'terminado',    // Completado naturalmente al llegar a fecha fin
                'revocado',     // Cancelado/terminado antes de tiempo
                'renovado'      // Reemplazado por una renovación
            ])->default('activo');
            
            // ✅ NUEVO: Referencias para renovaciones
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
            
            // ✅ ÍNDICES OPTIMIZADOS CON NOMBRES CORTOS
            $table->index('id_trabajador', 'idx_trabajador');
            $table->index('estatus', 'idx_estatus');
            $table->index(['tipo_duracion', 'duracion'], 'idx_tipo_duracion');
            $table->index(['fecha_inicio_contrato', 'fecha_fin_contrato'], 'idx_fechas'); // ✅ CORREGIDO: Nombre corto
            $table->index('contrato_anterior_id', 'idx_anterior');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratos_trabajadores');
    }
};