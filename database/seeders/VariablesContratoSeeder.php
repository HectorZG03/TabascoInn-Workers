<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariablesContratoSeeder extends Seeder
{
    public function run()
    {
        // ✅ SOLO LAS VARIABLES QUE REALMENTE USA EL CONTRATO
        $variables = [
            // ===== TRABAJADOR =====
            [
                'nombre_variable' => 'trabajador_nombre_completo',
                'etiqueta' => 'Nombre Completo del Trabajador',
                'descripcion' => 'Nombre completo del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'JUAN PÉREZ GARCÍA',
                'origen_codigo' => '$trabajador->nombre_completo',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'trabajador_edad',
                'etiqueta' => 'Edad del Trabajador',
                'descripcion' => 'Edad en años',
                'categoria' => 'trabajador',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '35',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_dia',
                'etiqueta' => 'Día de Nacimiento',
                'descripcion' => 'Día del nacimiento',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '15',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format("d")'
            ],
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_mes',
                'etiqueta' => 'Mes de Nacimiento',
                'descripcion' => 'Mes de nacimiento en texto',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'marzo',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento)->locale("es")->monthName'
            ],
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_año',
                'etiqueta' => 'Año de Nacimiento',
                'descripcion' => 'Año de nacimiento',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '1985',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format("Y")'
            ],
            [
                'nombre_variable' => 'trabajador_lugar_nacimiento',
                'etiqueta' => 'Lugar de Nacimiento',
                'descripcion' => 'Ciudad y estado de nacimiento',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Villahermosa, Tabasco',
                'origen_codigo' => '$trabajador->lugar_nacimiento ?? ($trabajador->ciudad_actual && $trabajador->estado_actual ? $trabajador->ciudad_actual . ", " . $trabajador->estado_actual : "Villahermosa, Centro, Tabasco")'
            ],
            [
                'nombre_variable' => 'trabajador_curp',
                'etiqueta' => 'CURP',
                'descripcion' => 'CURP del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'PEGJ850315HTCRNS01',
                'origen_codigo' => '$trabajador->curp ?? "NO ESPECIFICADO"'
            ],
            [
                'nombre_variable' => 'trabajador_rfc',
                'etiqueta' => 'RFC',
                'descripcion' => 'RFC del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'PEGJ850315ABC',
                'origen_codigo' => '$trabajador->rfc ?? "NO ESPECIFICADO"'
            ],
            [
                'nombre_variable' => 'trabajador_direccion',
                'etiqueta' => 'Dirección del Trabajador',
                'descripcion' => 'Dirección actual completa',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Av. Siempre Viva 123, Col. Centro',
                'origen_codigo' => '$trabajador->direccion ?? ($trabajador->ciudad_actual && $trabajador->estado_actual ? $trabajador->ciudad_actual . ", " . $trabajador->estado_actual : "NO ESPECIFICADO")'
            ],

            // ===== CATEGORIA Y PUESTO =====
            [
                'nombre_variable' => 'categoria_puesto',
                'etiqueta' => 'Categoría/Puesto',
                'descripcion' => 'Categoría del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'RECEPCIONISTA',
                'origen_codigo' => '$trabajador->fichaTecnica->categoria->nombre_categoria ?? "CATEGORÍA A ASIGNAR"',
                'obligatoria' => true
            ],

            // ===== SALARIALES =====
            [
                'nombre_variable' => 'salario_diario_numero',
                'etiqueta' => 'Salario Diario (Número)',
                'descripcion' => 'Salario diario en formato numérico',
                'categoria' => 'salariales',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '450.00',
                'origen_codigo' => 'number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2)',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'salario_diario_texto',
                'etiqueta' => 'Salario Diario (Texto)',
                'descripcion' => 'Salario diario en palabras',
                'categoria' => 'salariales',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'CUATROCIENTOS CINCUENTA PESOS',
                'origen_codigo' => '$salario_texto ?? "CANTIDAD A DETERMINAR"',
                'obligatoria' => true
            ],

            // ===== HORARIOS =====
            [
                'nombre_variable' => 'horas_semanales',
                'etiqueta' => 'Horas Semanales',
                'descripcion' => 'Total de horas por semana',
                'categoria' => 'horarios',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '40',
                'origen_codigo' => '$trabajador->fichaTecnica->horas_semanales_calculadas ?? $trabajador->fichaTecnica->horas_semanales ?? 42'
            ],
            [
                'nombre_variable' => 'horas_diarias',
                'etiqueta' => 'Horas Diarias',
                'descripcion' => 'Horas de trabajo por día',
                'categoria' => 'horarios',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '8',
                'origen_codigo' => '$trabajador->fichaTecnica->horas_trabajadas_calculadas ?? $trabajador->fichaTecnica->horas_trabajo ?? 7'
            ],
            [
                'nombre_variable' => 'horario_entrada',
                'etiqueta' => 'Hora de Entrada',
                'descripcion' => 'Hora de entrada',
                'categoria' => 'horarios',
                'tipo_dato' => 'hora',
                'formato_ejemplo' => '08:00',
                'origen_codigo' => '$trabajador->fichaTecnica->hora_entrada ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_entrada)->format("H:i") : "22:00"'
            ],
            [
                'nombre_variable' => 'horario_salida',
                'etiqueta' => 'Hora de Salida',
                'descripcion' => 'Hora de salida',
                'categoria' => 'horarios',
                'tipo_dato' => 'hora',
                'formato_ejemplo' => '17:00',
                'origen_codigo' => '$trabajador->fichaTecnica->hora_salida ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_salida)->format("H:i") : "06:00"'
            ],

            // ===== FECHAS DE INGRESO =====
            [
                'nombre_variable' => 'fecha_ingreso_dia',
                'etiqueta' => 'Día de Ingreso',
                'descripcion' => 'Día de ingreso del trabajador',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '15',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso)->format("d")'
            ],
            [
                'nombre_variable' => 'fecha_ingreso_mes',
                'etiqueta' => 'Mes de Ingreso',
                'descripcion' => 'Mes de ingreso en texto',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'enero',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso)->locale("es")->monthName'
            ],
            [
                'nombre_variable' => 'fecha_ingreso_año',
                'etiqueta' => 'Año de Ingreso',
                'descripcion' => 'Año de ingreso',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '2020',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso)->format("Y")'
            ],

            // ===== BENEFICIARIO =====
            [
                'nombre_variable' => 'beneficiario_nombre',
                'etiqueta' => 'Nombre del Beneficiario',
                'descripcion' => 'Nombre del beneficiario',
                'categoria' => 'beneficiario',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'MARÍA GONZÁLEZ LÓPEZ',
                'origen_codigo' => '$trabajador->fichaTecnica->beneficiario_nombre ?? "BENEFICIARIO POR ESPECIFICAR"'
            ],

            // ===== CONTRATO =====
            [
                'nombre_variable' => 'contrato_tipo',
                'etiqueta' => 'Tipo de Contrato',
                'descripcion' => 'Tipo de contrato (determinado/indeterminado)',
                'categoria' => 'contrato',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'DETERMINADO',
                'origen_codigo' => 'strtoupper($datosContrato["tipo_contrato"] ?? "determinado")',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'contrato_duracion_texto',
                'etiqueta' => 'Duración del Contrato',
                'descripcion' => 'Duración en texto',
                'categoria' => 'contrato',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'tiempo determinado de 6 meses',
                'origen_codigo' => '$datosContrato["tipo_contrato"] === "indeterminado" ? "tiempo indeterminado" : ("tiempo determinado de " . ($duracion_texto ?? "duración a determinar"))'
            ],
            [
                'nombre_variable' => 'contrato_fecha_inicio',
                'etiqueta' => 'Fecha de Inicio Formateada',
                'descripcion' => 'Fecha de inicio del contrato formateada',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '15 de enero del 2024',
                'origen_codigo' => '$fecha_inicio ? $fecha_inicio->format("d \\d\\e F \\d\\e\\l Y") : "fecha a determinar"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'contrato_fecha_fin',
                'etiqueta' => 'Fecha de Fin Formateada',
                'descripcion' => 'Fecha de fin del contrato (solo determinados)',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '31 de diciembre del 2024',
                'origen_codigo' => '$fecha_fin ? $fecha_fin->format("d \\d\\e F \\d\\e\\l Y") : "fecha a determinar"'
            ]
        ];

        // Insertar variables
        foreach ($variables as $variable) {
            DB::table('variables_contrato')->insert(array_merge($variable, [
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}