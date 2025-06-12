<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ficha_tecnica', function (Blueprint $table) {
            // ✅ PRIMARY KEY: Auto-increment
            $table->id('id_ficha');
            
            // ✅ FOREIGN KEYS: Relaciones con otras tablas (SIN CONSTRAINTS POR AHORA)
            $table->unsignedBigInteger('id_trabajador')->unique()->comment('Relación 1:1 con trabajadores');
            $table->unsignedBigInteger('id_categoria')->nullable()->comment('Categoría del trabajador');
            
            // ✅ DATOS SALARIALES
            $table->decimal('sueldo_diarios', 8, 2)->nullable()->comment('Sueldo diario del trabajador');
            
            // ✅ DATOS ACADÉMICOS
            $table->string('formacion', 50)->nullable()->comment('Nivel de formación académica');
            $table->string('grado_estudios', 50)->nullable()->comment('Grado específico de estudios');
            
            // ✅ CAMPOS DE HORARIO
            $table->time('hora_entrada')->nullable()->comment('Hora de entrada al trabajo');
            $table->time('hora_salida')->nullable()->comment('Hora de salida del trabajo');
            
            // ✅ DATOS LABORALES CALCULADOS
            $table->decimal('horas_trabajo', 4, 2)->nullable()->comment('Horas de trabajo por día (ej: 8.00, 8.50)');
            $table->enum('turno', [
                'diurno',   // Turno diurno (06:00 - 18:00)
                'nocturno', // Turno nocturno (18:00 - 06:00)
                'mixto'     // Turno mixto/rotativo
            ])->nullable()->comment('Turno de trabajo del empleado');
            
            // ✅ NUEVOS: DÍAS LABORABLES Y DESCANSO
            $table->json('dias_laborables')->nullable()->comment('Días de la semana que trabaja: ["lunes","martes","miercoles","jueves","viernes"]');
            $table->json('dias_descanso')->nullable()->comment('Días de descanso: ["sabado","domingo"]');
            $table->decimal('horas_semanales', 5, 2)->nullable()->comment('Total de horas por semana (calculado automáticamente)');
            
            // ✅ NUEVOS: BENEFICIARIO PRINCIPAL (PARA CONTRATO) - SIMPLIFICADO
            $table->string('beneficiario_nombre', 150)->nullable()->comment('Nombre completo del beneficiario de su seguro');
            $table->string('beneficiario_parentesco', 50)->nullable()->comment('Parentesco del beneficiario (esposa, hijo, padre, etc.)');
            
            // ✅ TIMESTAMPS: OBLIGATORIOS PARA LARAVEL
            $table->timestamps();
            
            // ✅ ÍNDICES PARA OPTIMIZACIÓN DE CONSULTAS (sin foreign keys por ahora)
            $table->index('id_trabajador', 'idx_ficha_trabajador');
            $table->index('id_categoria', 'idx_ficha_categoria');
            $table->index('turno', 'idx_ficha_turno');
            $table->index('horas_trabajo', 'idx_ficha_horas_trabajo');
            $table->index(['turno', 'horas_trabajo'], 'idx_ficha_turno_horas');
            $table->index('beneficiario_parentesco', 'idx_ficha_beneficiario');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ficha_tecnica');
    }
};