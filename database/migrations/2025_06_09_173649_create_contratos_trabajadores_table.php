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
            
            // ✅ NUEVOS CAMPOS PARA MANEJAR DÍAS Y MESES
            $table->enum('tipo_duracion', ['dias', 'meses'])->default('meses');
            $table->integer('duracion')->comment('Duración en días o meses según tipo_duracion');
            
            // ✅ MANTENER COMPATIBILIDAD (opcional, se puede eliminar después)
            $table->integer('duracion_meses')->nullable()->comment('Campo legacy - usar duracion + tipo_duracion');
            
            $table->string('ruta_archivo', 255);
            $table->timestamps();
            
            $table->index('id_trabajador');
            $table->index(['tipo_duracion', 'duracion']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratos_trabajadores');
    }
};