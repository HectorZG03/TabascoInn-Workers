<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ AGREGAR ESTA LÍNEA

return new class extends Migration
{
    public function up()
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            // ✅ Primary key auto-increment
            $table->id('id_trabajador');
            $table->unsignedBigInteger('id_baja')->nullable();  // Referencia a despidos
            
            // ✅ DATOS PERSONALES BÁSICOS
            $table->string('nombre_trabajador', 50)->nullable();
            $table->string('ape_pat', 50)->nullable();
            $table->string('ape_mat', 50)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            
            // ✅ IDENTIFICADORES OFICIALES
            $table->string('curp', 18)->nullable()->unique();
            $table->string('rfc', 13)->nullable()->unique();
            $table->string('no_nss', 11)->nullable();
            
            // ✅ DATOS DE CONTACTO
            $table->string('telefono', 10)->nullable();
            $table->string('correo', 55)->nullable()->unique();
            $table->string('direccion', 255)->nullable();
            
            // ✅ DATOS LABORALES
            $table->date('fecha_ingreso')->nullable();
            $table->integer('antiguedad')->default(0); // Años de antigüedad (entero)
            
            // ✅ ESTADO DEL TRABAJADOR (ENUM)
            $table->enum('estatus', [
                // Estados laborales principales
                'activo',
                'inactivo',
                // Ausencias temporales
                'vacaciones',
                'incapacidad_medica',
                'licencia_maternidad',
                'licencia_paternidad',
                'licencia_sin_goce',
                'permiso_especial',
                // Situaciones administrativas
                'suspendido'
            ])->default('activo')->comment('Estado laboral del trabajador');
            
            // ✅ TIMESTAMPS
            $table->timestamps();
            
            // ✅ ÍNDICES PARA PERFORMANCE
            $table->index(['estatus', 'created_at'], 'idx_estatus_fecha');
            $table->index('fecha_ingreso', 'idx_fecha_ingreso');
            $table->index(['nombre_trabajador', 'ape_pat'], 'idx_nombres');
            $table->index('antiguedad', 'idx_antiguedad');
        });
        
        // ✅ COMENTARIO DE LA TABLA
        DB::statement("ALTER TABLE trabajadores COMMENT = 'Tabla principal de trabajadores con estados laborales'");
    }

    public function down()
    {
        Schema::dropIfExists('trabajadores');
    }
};