<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariablesContratoSeeder extends Seeder
{
    public function run()
    {
        // ✅ LIMPIAR VARIABLES EXISTENTES PARA EVITAR DUPLICADOS
        DB::table('variables_contrato')->truncate();
        
        // ✅ VARIABLES ACTUALIZADAS CON DÍAS LABORALES INCLUIDOS + NUEVAS VARIABLES
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
            // ✅ NUEVA VARIABLE: ESTADO CIVIL
            [
                'nombre_variable' => 'trabajador_estado_civil',
                'etiqueta' => 'Estado Civil del Trabajador',
                'descripcion' => 'Estado civil del trabajador (soltero, casado, etc.)',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Casado(a)',
                'origen_codigo' => '$trabajador->estado_civil ? (\App\Models\Trabajador::ESTADOS_CIVILES[$trabajador->estado_civil] ?? ucfirst($trabajador->estado_civil)) : "No especificado"',
                'obligatoria' => false
            ],
            // ✅ NUEVA VARIABLE: CÓDIGO POSTAL
            [
                'nombre_variable' => 'trabajador_codigo_postal',
                'etiqueta' => 'Código Postal del Trabajador',
                'descripcion' => 'Código postal del domicilio actual del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '86100',
                'origen_codigo' => '$trabajador->codigo_postal ?? "No especificado"',
                'obligatoria' => false
            ],

            // ✅ NUEVA VARIABLE: ESTADO ACTUAL
            [
                'nombre_variable' => 'trabajador_estado_actual',
                'etiqueta' => 'Estado Actual del Trabajador',
                'descripcion' => 'Estado donde vive actualmente el trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Tabasco',
                'origen_codigo' => '$trabajador->estado_actual ?? "No especificado"',
                'obligatoria' => false
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
                'origen_codigo' => '$fechaNac = \Carbon\Carbon::parse($trabajador->fecha_nacimiento); $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; $meses[(int)$fechaNac->format("n")]'
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
            // ✅ NUEVA VARIABLE - FECHA DE NACIMIENTO COMPLETA FORMATEADA
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_completa',
                'etiqueta' => 'Fecha de Nacimiento Completa',
                'descripcion' => 'Fecha de nacimiento en formato legal: "nacido el día XX del mes de XXX del año XXXX"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'nacido el día 07 del mes de mayo del año 2002',
                'origen_codigo' => '$fechaNac = \Carbon\Carbon::parse($trabajador->fecha_nacimiento); $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; "nacido el día " . $fechaNac->format("d") . " del mes de " . $meses[(int)$fechaNac->format("n")] . " del año " . $fechaNac->format("Y")',
                'obligatoria' => false
            ],
            // ✅ VERSIÓN ALTERNATIVA - SOLO LA FECHA (SIN "NACIDO EL DÍA")
            [
                'nombre_variable' => 'fecha_nacimiento_legal',
                'etiqueta' => 'Fecha Nacimiento Formato Legal',
                'descripcion' => 'Fecha en formato legal sin prefijo: "07 del mes de mayo del año 2002"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '07 del mes de mayo del año 2002',
                'origen_codigo' => '$fechaNac = \Carbon\Carbon::parse($trabajador->fecha_nacimiento); $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; $fechaNac->format("d") . " del mes de " . $meses[(int)$fechaNac->format("n")] . " del año " . $fechaNac->format("Y")',
                'obligatoria' => false
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

            // ===== HORARIOS Y DÍAS LABORALES ===== ✅ SECCIÓN AMPLIADA
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
            
            // ✅ NUEVAS VARIABLES DE DÍAS LABORALES Y DESCANSO
            [
                'nombre_variable' => 'dias_laborables',
                'etiqueta' => 'Días Laborables',
                'descripcion' => 'Días de la semana que trabaja (formato texto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Lunes, Martes, Miércoles, Jueves, Viernes',
                'origen_codigo' => '$trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables ? collect($trabajador->fichaTecnica->dias_laborables)->map(fn($dia) => ucfirst($dia))->join(", ") : "Lunes, Martes, Miércoles, Jueves, Viernes"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'dias_descanso',
                'etiqueta' => 'Días de Descanso',
                'descripcion' => 'Días de la semana de descanso (formato texto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Sábado, Domingo',
                'origen_codigo' => '$trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_descanso ? collect($trabajador->fichaTecnica->dias_descanso)->map(fn($dia) => ucfirst($dia))->join(", ") : "Sábado, Domingo"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'texto_descanso_plural',
                'etiqueta' => 'Texto Descanso (Artículo)',
                'descripcion' => 'Artículo para días de descanso (el día/los días)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'los días',
                'origen_codigo' => '($trabajador->fichaTecnica && count($trabajador->fichaTecnica->dias_descanso ?? []) === 1) ? "el día" : "los días"'
            ],
            [
                'nombre_variable' => 'dias_descanso_minuscula',
                'etiqueta' => 'Días de Descanso (minúsculas)',
                'descripcion' => 'Días de descanso en minúsculas para textos legales',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'sábado, domingo',
                'origen_codigo' => '$trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_descanso ? collect($trabajador->fichaTecnica->dias_descanso)->join(", ") : "sábado, domingo"'
            ],
            [
                'nombre_variable' => 'turno_trabajador',
                'etiqueta' => 'Turno del Trabajador',
                'descripcion' => 'Tipo de turno (diurno, nocturno, mixto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'diurno',
                'origen_codigo' => '$trabajador->fichaTecnica->turno_calculado ?? $trabajador->fichaTecnica->turno ?? "diurno"'
            ],
            [
                'nombre_variable' => 'descripcion_turno',
                'etiqueta' => 'Descripción del Turno',
                'descripcion' => 'Descripción del turno para texto legal',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'por tratarse de jornada Diurna',
                'origen_codigo' => 'match($trabajador->fichaTecnica->turno_calculado ?? $trabajador->fichaTecnica->turno ?? "diurno") { "diurno" => "por tratarse de jornada Diurna", "nocturno" => "por tratarse de jornada Nocturna", "mixto" => "por tratarse de jornada Mixta", default => "por tratarse de jornada indefinida" }'
            ],
            [
                'nombre_variable' => 'horario_descanso',
                'etiqueta' => 'Horario de Descanso',
                'descripcion' => 'Horario de descanso según el turno',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '12:30 horas a las 13:00 horas',
                'origen_codigo' => '($trabajador->fichaTecnica->turno_calculado ?? $trabajador->fichaTecnica->turno ?? "diurno") === "nocturno" ? "02:00 horas a las 02:30 horas" : "12:30 horas a las 13:00 horas"'
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
                'origen_codigo' => '$fechaIngreso = \Carbon\Carbon::parse($trabajador->fecha_ingreso); $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; $meses[(int)$fechaIngreso->format("n")]'
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
            // ✅ NUEVA VARIABLE: FECHA DE INGRESO FORMATEADA COMPLETA
            [
                'nombre_variable' => 'fecha_ingreso_legal',
                'etiqueta' => 'Fecha de Ingreso Formato Legal',
                'descripcion' => 'Fecha de ingreso en formato legal: "08 de marzo del año 2025"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '08 de marzo del año 2025',
                'origen_codigo' => '$fechaIngreso = \Carbon\Carbon::parse($trabajador->fecha_ingreso); $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; $fechaIngreso->format("d") . " de " . $meses[(int)$fechaIngreso->format("n")] . " del año " . $fechaIngreso->format("Y")',
                'obligatoria' => false
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
            [
                'nombre_variable' => 'beneficiario_parentesco',
                'etiqueta' => 'Parentesco del Beneficiario',
                'descripcion' => 'Relación familiar con el trabajador',
                'categoria' => 'beneficiario',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'esposa',
                'origen_codigo' => '$trabajador->fichaTecnica->beneficiario_parentesco ?? "parentesco por especificar"'
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
            // ✅ VARIABLES DE FECHAS DEL CONTRATO CORREGIDAS
            [
                'nombre_variable' => 'contrato_fecha_inicio',
                'etiqueta' => 'Fecha de Inicio del Contrato',
                'descripcion' => 'Fecha de inicio del contrato en formato legal',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '30 de julio del 2025',
                'origen_codigo' => 'if (!isset($fecha_inicio) || !$fecha_inicio) return "fecha a determinar"; $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; return $fecha_inicio->format("d") . " de " . $meses[(int)$fecha_inicio->format("n")] . " del " . $fecha_inicio->format("Y");',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'contrato_fecha_fin',
                'etiqueta' => 'Fecha de Fin del Contrato',
                'descripcion' => 'Fecha de terminación del contrato (solo para determinados)',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '16 de julio del 2026',
                'origen_codigo' => 'if (!isset($fecha_fin) || !$fecha_fin) return ($datosContrato["tipo_contrato"] ?? "determinado") === "indeterminado" ? "Sin fecha de terminación" : "fecha a determinar"; $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"]; return $fecha_fin->format("d") . " de " . $meses[(int)$fecha_fin->format("n")] . " del " . $fecha_fin->format("Y");'
            ]
        ];

        // ✅ INSERTAR VARIABLES CON MANEJO DE ERRORES
        foreach ($variables as $variable) {
            try {
                DB::table('variables_contrato')->insert(array_merge($variable, [
                    'activa' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            } catch (\Exception $e) {
                echo "❌ Error insertando variable {$variable['nombre_variable']}: " . $e->getMessage() . "\n";
            }
        }

        echo "✅ " . count($variables) . " variables de contrato insertadas correctamente\n";
        echo "🎯 Nuevas variables agregadas:\n";
        echo "   • {{trabajador_estado_civil}} - Estado civil del trabajador\n";
        echo "   • {{trabajador_codigo_postal}} - Código postal del domicilio\n";
        echo "   • {{fecha_ingreso_legal}} - Fecha de ingreso formato: '08 de marzo del año 2025'\n";
        echo "   • {{trabajador_fecha_nacimiento_completa}} - Formato completo con 'nacido el día'\n";
        echo "   • {{fecha_nacimiento_legal}} - Solo la fecha sin prefijo\n";
    }
}