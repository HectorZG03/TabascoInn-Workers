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
            
            // ✅ NUEVO: Estado civil
            $table->enum('estado_civil', [
                'soltero', 
                'casado', 
                'union_libre', 
                'divorciado', 
                'viudo', 
                'separado'
            ])->nullable()->comment('Estado civil del trabajador');
            
            // ✅ DATOS DE NACIMIENTO Y UBICACIÓN ACTUAL
            $table->string('lugar_nacimiento', 100)->nullable()->comment('Ciudad y estado de nacimiento');
            $table->string('estado_actual', 50)->nullable()->comment('Estado donde vive actualmente (texto libre)');
            $table->string('ciudad_actual', 50)->nullable()->comment('Ciudad donde vive actualmente');
            // ✅ CÓDIGO POSTAL
            $table->string('codigo_postal', 5)->nullable()->comment('Código postal del domicilio actual');
            
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
            $table->index('curp', 'idx_curp');
            $table->index('rfc', 'idx_rfc');
            $table->index('correo', 'idx_correo');
            $table->index(['estado_actual', 'ciudad_actual'], 'idx_ubicacion_actual');
            $table->index('codigo_postal', 'idx_codigo_postal');
            // ✅ NUEVO: Índice para estado civil
            $table->index('estado_civil', 'idx_estado_civil');
        });
        
        // ✅ COMENTARIO DE LA TABLA
        DB::statement("ALTER TABLE trabajadores COMMENT = 'Tabla principal de trabajadores con estado civil, ubicación de texto libre y código postal'");
    }

    public function down()
    {
        Schema::dropIfExists('trabajadores');
    }
};