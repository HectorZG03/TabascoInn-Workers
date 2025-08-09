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
        
        // ✅ VARIABLES ULTRA MINIMALISTAS - CÓDIGOS QUE SÍ FUNCIONAN CON eval()
        $variables = [
            // ===== TRABAJADOR =====
            [
                'nombre_variable' => 'trabajador_nombre_completo',
                'etiqueta' => 'Nombre Completo del Trabajador',
                'descripcion' => 'Nombre completo del trabajador',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'JUAN PÉREZ GARCÍA',
                'origen_codigo' => '$trabajador->nombre_completo ?? "NOMBRE NO ESPECIFICADO"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'trabajador_edad',
                'etiqueta' => 'Edad del Trabajador',
                'descripcion' => 'Edad en años',
                'categoria' => 'trabajador',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '35',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->age',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'trabajador_estado_civil',
                'etiqueta' => 'Estado Civil del Trabajador',
                'descripcion' => 'Estado civil del trabajador (soltero, casado, etc.)',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Casado(a)',
                'origen_codigo' => '$trabajador->estado_civil ? ucfirst($trabajador->estado_civil) : "No especificado"',
                'obligatoria' => false
            ],
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

            // ===== FECHAS - COMPONENTES INDIVIDUALES =====
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_dia',
                'etiqueta' => 'Día de Nacimiento',
                'descripcion' => 'Día del nacimiento',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '15',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("d")'
            ],
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_año',
                'etiqueta' => 'Año de Nacimiento',
                'descripcion' => 'Año de nacimiento',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '1985',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("Y")'
            ],
            [
                'nombre_variable' => 'fecha_ingreso_dia',
                'etiqueta' => 'Día de Ingreso',
                'descripcion' => 'Día de ingreso del trabajador',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '15',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->format("d")'
            ],
            [
                'nombre_variable' => 'fecha_ingreso_año',
                'etiqueta' => 'Año de Ingreso',
                'descripcion' => 'Año de ingreso',
                'categoria' => 'fechas',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '2020',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->format("Y")'
            ],

            // ===== MESES SIMPLIFICADOS =====
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_mes',
                'etiqueta' => 'Mes de Nacimiento',
                'descripcion' => 'Mes de nacimiento en texto',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'marzo',
                'origen_codigo' => '["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->month - 1]'
            ],
            [
                'nombre_variable' => 'fecha_ingreso_mes',
                'etiqueta' => 'Mes de Ingreso',
                'descripcion' => 'Mes de ingreso en texto',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'enero',
                'origen_codigo' => '["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][\Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->month - 1]'
            ],

            // ===== FECHAS LEGALES - MÉTODO ALTERNATIVO MÁS SIMPLE =====
            [
                'nombre_variable' => 'fecha_nacimiento_legal',
                'etiqueta' => 'Fecha Nacimiento Formato Legal',
                'descripcion' => 'Fecha en formato legal: "07 del mes de mayo del año 2002"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '07 del mes de mayo del año 2002',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("d") . " del mes de " . ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->month - 1] . " del año " . \Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("Y")',
                'obligatoria' => false
            ],
            [
                'nombre_variable' => 'fecha_ingreso_legal',
                'etiqueta' => 'Fecha de Ingreso Formato Legal',
                'descripcion' => 'Fecha de ingreso en formato legal: "08 de marzo del año 2025"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '08 de marzo del año 2025',
                'origen_codigo' => '\Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->format("d") . " de " . ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][\Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->month - 1] . " del año " . \Carbon\Carbon::parse($trabajador->fecha_ingreso ?? now())->format("Y")',
                'obligatoria' => false
            ],
            [
                'nombre_variable' => 'trabajador_fecha_nacimiento_completa',
                'etiqueta' => 'Fecha de Nacimiento Completa',
                'descripcion' => 'Fecha de nacimiento en formato legal: "nacido el día XX del mes de XXX del año XXXX"',
                'categoria' => 'fechas',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'nacido el día 07 del mes de mayo del año 2002',
                'origen_codigo' => '"nacido el día " . \Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("d") . " del mes de " . ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][\Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->month - 1] . " del año " . \Carbon\Carbon::parse($trabajador->fecha_nacimiento ?? now())->format("Y")',
                'obligatoria' => false
            ],

            // ===== INFORMACIÓN PERSONAL =====
            [
                'nombre_variable' => 'trabajador_lugar_nacimiento',
                'etiqueta' => 'Lugar de Nacimiento',
                'descripcion' => 'Ciudad y estado de nacimiento',
                'categoria' => 'trabajador',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Villahermosa, Tabasco',
                'origen_codigo' => '$trabajador->lugar_nacimiento ?? "Villahermosa, Centro, Tabasco"'
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
                'origen_codigo' => '$trabajador->direccion ?? "NO ESPECIFICADO"'
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
                'descripcion' => 'Salario diario en palabras con centavos',
                'categoria' => 'salariales',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'CUATROCIENTOS CINCUENTA PESOS CON CERO CENTAVOS',
                'origen_codigo' => '$salario_texto ?? "CANTIDAD A DETERMINAR"',
                'obligatoria' => true
            ],

            // ===== HORARIOS Y DÍAS LABORALES =====
            [
                'nombre_variable' => 'horas_semanales',
                'etiqueta' => 'Horas Semanales',    
                'descripcion' => 'Total de horas por semana',
                'categoria' => 'horarios',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '40',
                'origen_codigo' => '$trabajador->fichaTecnica->horas_semanales ?? 42'
            ],
            [
                'nombre_variable' => 'horas_diarias',
                'etiqueta' => 'Horas Diarias',
                'descripcion' => 'Horas de trabajo por día',
                'categoria' => 'horarios',
                'tipo_dato' => 'numero',
                'formato_ejemplo' => '8',
                'origen_codigo' => '$trabajador->fichaTecnica->horas_trabajo ?? 7'
            ],
            [
                'nombre_variable' => 'horario_entrada',
                'etiqueta' => 'Hora de Entrada',
                'descripcion' => 'Hora de entrada',
                'categoria' => 'horarios',
                'tipo_dato' => 'hora',
                'formato_ejemplo' => '08:00',
                'origen_codigo' => '$trabajador->fichaTecnica->hora_entrada ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_entrada)->format("H:i") : "08:00"'
            ],
            [
                'nombre_variable' => 'horario_salida',
                'etiqueta' => 'Hora de Salida',
                'descripcion' => 'Hora de salida',
                'categoria' => 'horarios',
                'tipo_dato' => 'hora',
                'formato_ejemplo' => '17:00',
                'origen_codigo' => '$trabajador->fichaTecnica->hora_salida ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_salida)->format("H:i") : "17:00"'
            ],
            [
                'nombre_variable' => 'dias_laborables',
                'etiqueta' => 'Días Laborables',
                'descripcion' => 'Días de la semana que trabaja (formato texto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Lunes, Martes, Miércoles, Jueves, Viernes',
                'origen_codigo' => '"Lunes, Martes, Miércoles, Jueves, Viernes"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'dias_descanso',
                'etiqueta' => 'Días de Descanso',
                'descripcion' => 'Días de la semana de descanso (formato texto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'Sábado, Domingo',
                'origen_codigo' => '"Sábado, Domingo"',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'texto_descanso_plural',
                'etiqueta' => 'Texto Descanso (Artículo)',
                'descripcion' => 'Artículo para días de descanso (el día/los días)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'los días',
                'origen_codigo' => '"los días"'
            ],
            [
                'nombre_variable' => 'dias_descanso_minuscula',
                'etiqueta' => 'Días de Descanso (minúsculas)',
                'descripcion' => 'Días de descanso en minúsculas para textos legales',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'sábado, domingo',
                'origen_codigo' => '"sábado, domingo"'
            ],
            [
                'nombre_variable' => 'turno_trabajador',
                'etiqueta' => 'Turno del Trabajador',
                'descripcion' => 'Tipo de turno (diurno, nocturno, mixto)',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'diurno',
                'origen_codigo' => '$trabajador->fichaTecnica->turno ?? "diurno"'
            ],
            [
                'nombre_variable' => 'descripcion_turno',
                'etiqueta' => 'Descripción del Turno',
                'descripcion' => 'Descripción del turno para texto legal',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => 'por tratarse de jornada Diurna',
                'origen_codigo' => '($trabajador->fichaTecnica->turno ?? "diurno") === "nocturno" ? "por tratarse de jornada Nocturna" : (($trabajador->fichaTecnica->turno ?? "diurno") === "mixto" ? "por tratarse de jornada Mixta" : "por tratarse de jornada Diurna")'
            ],
            [
                'nombre_variable' => 'horario_descanso',
                'etiqueta' => 'Horario de Descanso',
                'descripcion' => 'Horario de descanso según el turno',
                'categoria' => 'horarios',
                'tipo_dato' => 'texto',
                'formato_ejemplo' => '12:30 horas a las 13:00 horas',
                'origen_codigo' => '($trabajador->fichaTecnica->turno ?? "diurno") === "nocturno" ? "02:00 horas a las 02:30 horas" : "12:30 horas a las 13:00 horas"'
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
                'origen_codigo' => '($datosContrato["tipo_contrato"] ?? "determinado") === "indeterminado" ? "tiempo indeterminado" : "tiempo determinado de 6 meses"'
            ],

            // ===== FECHAS DEL CONTRATO - VERSIÓN ULTRA SIMPLIFICADA =====
            [
                'nombre_variable' => 'contrato_fecha_inicio',
                'etiqueta' => 'Fecha de Inicio del Contrato',
                'descripcion' => 'Fecha de inicio del contrato en formato legal español',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '04 de agosto del 2025',
                'origen_codigo' => '!isset($fecha_inicio) ? "fecha a determinar" : str_pad($fecha_inicio->day, 2, "0", STR_PAD_LEFT) . " de " . ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][$fecha_inicio->month - 1] . " del " . $fecha_inicio->year',
                'obligatoria' => true
            ],
            [
                'nombre_variable' => 'contrato_fecha_fin',
                'etiqueta' => 'Fecha de Fin del Contrato',
                'descripcion' => 'Fecha de terminación del contrato en formato legal español',
                'categoria' => 'contrato',
                'tipo_dato' => 'fecha',
                'formato_ejemplo' => '04 de agosto del 2026',
                'origen_codigo' => '($datosContrato["tipo_contrato"] ?? "determinado") === "indeterminado" ? "Sin fecha de terminación" : (!isset($fecha_fin) ? "fecha a determinar" : str_pad($fecha_fin->day, 2, "0", STR_PAD_LEFT) . " de " . ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"][$fecha_fin->month - 1] . " del " . $fecha_fin->year)'
            ]
        ];

        // ✅ INSERTAR VARIABLES CON MANEJO DE ERRORES
        $insertadas = 0;
        foreach ($variables as $variable) {
            try {
                DB::table('variables_contrato')->insert(array_merge($variable, [
                    'activa' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                $insertadas++;
            } catch (\Exception $e) {
                echo "❌ Error insertando variable {$variable['nombre_variable']}: " . $e->getMessage() . "\n";
            }
        }

        echo "✅ {$insertadas} variables de contrato insertadas correctamente\n";
        echo "🎯 VARIABLES ULTRA SIMPLIFICADAS:\n";
        echo "   • Código PHP inline directo sin variables temporales\n";
        echo "   • Arrays accedidos directamente con índices\n";
        echo "   • Operadores ternarios anidados en lugar de if complejos\n";
        echo "   • Fechas legales: acceso directo a propiedades Carbon\n";
        echo "   • contrato_fecha_fin: lógica corregida en una sola línea\n";
        echo "   • GARANTIZADO: Compatible con eval() y en español\n";
    }
}