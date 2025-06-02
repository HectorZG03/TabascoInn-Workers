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
        Schema::create('permisos_laborales', function (Blueprint $table) {
            $table->id('id_permiso'); // Cambiar el nombre del ID

            $table->unsignedBigInteger('id_trabajador');
            $table->string('tipo_permiso', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('observaciones')->nullable();

            $table->foreign('id_trabajador')
                  ->references('id_trabajador')
                  ->on('trabajadores')
                  ->onDelete('cascade'); // Opcional pero recomendable

            // Si decides mantener timestamps:
            $table->timestamps(); // Si usas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_laborales');
    }
};
