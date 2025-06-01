<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documentos_trabajador', function (Blueprint $table) {
            // ✅ Primary key auto-increment
            $table->id('id_documento');
            $table->unsignedBigInteger('id_trabajador');
            
            // ✅ DOCUMENTOS OFICIALES BÁSICOS
            $table->string('ine', 255)->nullable();
            $table->string('acta_nacimiento', 255)->nullable();
            $table->string('comprobante_domicilio', 255)->nullable();
            $table->string('acta_residencia', 255)->nullable();
            $table->string('nss', 255)->nullable();
            
            // ✅ DOCUMENTOS ADICIONALES
            $table->string('curp_documento', 255)->nullable();
            $table->string('rfc_documento', 255)->nullable();
            $table->string('contrato_trabajo', 255)->nullable();
            $table->string('carta_recomendacion', 255)->nullable();
            $table->string('certificados_estudios', 255)->nullable();
            $table->string('examenes_medicos', 255)->nullable();
            $table->string('fotos', 255)->nullable();
            
            // ✅ METADATOS Y CONTROL
            $table->decimal('porcentaje_completado', 5, 2)->default(0.00);
            $table->datetime('fecha_ultima_actualizacion')->nullable();
            $table->text('observaciones')->nullable(); // ✅ Cambiado a TEXT para más espacio
            
            // ✅ CAMPOS ADICIONALES PARA MEJOR CONTROL
            $table->boolean('documentos_basicos_completos')->default(false);
            $table->enum('estado', ['incompleto', 'parcial', 'revision', 'completo', 'aprobado', 'rechazado'])->default('incompleto');
            
            // ✅ TIMESTAMPS para auditoría
            $table->timestamps();
            
            // ✅ FOREIGN KEY
            $table->foreign('id_trabajador')
                  ->references('id_trabajador')
                  ->on('trabajadores')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // ✅ ÍNDICES PARA PERFORMANCE
            $table->index('porcentaje_completado');
            $table->index('documentos_basicos_completos');
            $table->index('estado');
            $table->unique('id_trabajador'); // Un trabajador solo tiene un registro de documentos
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_trabajador');
    }
};