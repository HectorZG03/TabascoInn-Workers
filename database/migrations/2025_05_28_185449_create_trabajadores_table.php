<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            
            // ✅ NUEVOS: Datos de nacimiento y ubicación actual
            $table->string('lugar_nacimiento', 100)->nullable()->comment('Ciudad y estado de nacimiento');
            $table->string('estado_actual', 50)->nullable()->comment('Estado donde vive actualmente');
            $table->string('ciudad_actual', 50)->nullable()->comment('Ciudad donde vive actualmente');
            
            // ✅ IDENTIFICADORES OFICIALES
            $table->string('curp', 18)->nullable()->unique();
            $table->string('rfc', 13)->nullable()->unique();
            $table->string('no_nss', 11)->nullable();
            
            // ✅ DATOS DE CONTACTO
            $table->string('telefono', 10)->nullable();
            $table->string('correo', 55)->nullable()->unique();
            $table->string('direccion', 255)->nullable()->comment('Dirección actual completa');
            
            // ✅ DATOS LABORALES
            $table->date('fecha_ingreso')->nullable();
            $table->integer('antiguedad')->default(0); // Años de antigüedad (entero)
            
            // ✅ ESTADO DEL TRABAJADOR - 5 ESTADOS ÚNICAMENTE
            $table->enum('estatus', [
                'activo',      // Trabajador activo
                'inactivo',    // Trabajador dado de baja
                'permiso',     // Con permiso temporal
                'vacaciones',  // Trabajador en vacaciones  
                'suspendido',  // Suspendido (requiere acción manual)
                'prueba',      // En período de prueba
            ])->default('activo')->comment('Estado laboral del trabajador');
            
            // ✅ TIMESTAMPS
            $table->timestamps();
            
            // ✅ ÍNDICES PARA PERFORMANCE
            $table->index(['estatus', 'created_at'], 'idx_estatus_fecha');
            $table->index('fecha_ingreso', 'idx_fecha_ingreso');
            $table->index(['nombre_trabajador', 'ape_pat'], 'idx_nombres');
            $table->index('antiguedad', 'idx_antiguedad');
            $table->index('curp', 'idx_curp');
            $table->index('rfc', 'idx_rfc');
            $table->index('correo', 'idx_correo');
            $table->index(['estado_actual', 'ciudad_actual'], 'idx_ubicacion_actual');
        });
        
        // ✅ COMENTARIO DE LA TABLA
        DB::statement("ALTER TABLE trabajadores COMMENT = 'Tabla principal de trabajadores con 5 estados laborales definidos y datos de ubicación'");
    }

    public function down()
    {
        Schema::dropIfExists('trabajadores');
    }
};