<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\DocumentoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    /**
     * Descargar plantilla Excel para importación masiva
     */
    public function descargarPlantilla()
    {
        try {
            $areas = Area::with('categorias')->orderBy('nombre_area')->get();
            
            // Crear datos de ejemplo
            $datosEjemplo = [
                [
                    'nombre_trabajador' => 'Juan Carlos',
                    'ape_pat' => 'García',
                    'ape_mat' => 'López',
                    'fecha_nacimiento' => '1990-05-15',
                    'curp' => 'GALJ900515HDFGPN09',
                    'rfc' => 'GALJ900515123',
                    'no_nss' => '12345678901',
                    'telefono' => '9934567890',
                    'correo' => 'juan.garcia@hotel.com',
                    'direccion' => 'Calle Principal #123, Centro, Villahermosa',
                    'fecha_ingreso' => '2024-01-15',
                    'estatus' => 'activo',
                    'nombre_area' => 'Recepción',
                    'nombre_categoria' => 'Recepcionista',
                    'sueldo_diarios' => '450.00',
                    'formacion' => 'Técnico en Turismo',
                    'grado_estudios' => 'Técnico Superior'
                ],
                [
                    'nombre_trabajador' => 'María Fernanda',
                    'ape_pat' => 'Martínez',
                    'ape_mat' => 'Hernández',
                    'fecha_nacimiento' => '1988-12-03',
                    'curp' => 'MAHF881203MDFRNR08',
                    'rfc' => 'MAHF881203456',
                    'no_nss' => '98765432109',
                    'telefono' => '9934567891',
                    'correo' => 'maria.martinez@hotel.com',
                    'direccion' => 'Av. Ruiz Cortines #456, Atasta, Villahermosa',
                    'fecha_ingreso' => '2024-02-01',
                    'estatus' => 'activo',
                    'nombre_area' => 'Limpieza',
                    'nombre_categoria' => 'Camarista',
                    'sueldo_diarios' => '380.00',
                    'formacion' => 'Curso de Hotelería',
                    'grado_estudios' => 'Secundaria'
                ]
            ];

            // Crear hoja de instrucciones
            $instrucciones = [
                ['INSTRUCCIONES PARA IMPORTACIÓN MASIVA DE TRABAJADORES'],
                [''],
                ['1. Complete TODOS los campos obligatorios marcados con *'],
                ['2. Respete el formato de fechas: YYYY-MM-DD (ej: 2024-01-15)'],
                ['3. El CURP debe tener exactamente 18 caracteres'],
                ['4. El RFC debe tener exactamente 13 caracteres'],
                ['5. El teléfono debe tener exactamente 10 dígitos'],
                ['6. Los nombres de área y categoría deben existir en el sistema'],
                ['7. El sueldo debe ser un número decimal (ej: 450.00)'],
                ['8. No modifique el orden de las columnas'],
                ['9. Puede agregar más filas con trabajadores adicionales'],
                ['10. Guarde el archivo en formato Excel (.xlsx)'],
                [''],
                ['ESTADOS VÁLIDOS:'],
                ['- activo (recomendado para nuevos trabajadores)'],
                ['- vacaciones'],
                ['- incapacidad_medica'],
                ['- licencia_maternidad'],
                ['- licencia_paternidad'],
                ['- licencia_sin_goce'],
                ['- permiso_especial'],
                ['- suspendido'],
                ['- inactivo'],
                [''],
                ['ÁREAS Y CATEGORÍAS DISPONIBLES:']
            ];

            // Agregar áreas y categorías disponibles
            foreach ($areas as $area) {
                $instrucciones[] = ["ÁREA: {$area->nombre_area}"];
                foreach ($area->categorias as $categoria) {
                    $instrucciones[] = ["  - {$categoria->nombre_categoria}"];
                }
                $instrucciones[] = [''];
            }

            // Crear el archivo Excel
            return Excel::download(new class($datosEjemplo, $instrucciones) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                private $datos;
                private $instrucciones;

                public function __construct($datos, $instrucciones)
                {
                    $this->datos = $datos;
                    $this->instrucciones = $instrucciones;
                }

                public function collection()
                {
                    return collect($this->datos);
                }

                public function sheets(): array
                {
                    return [
                        'Datos_Trabajadores' => new class($this->datos) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithColumnWidths {
                            private $datos;

                            public function __construct($datos)
                            {
                                $this->datos = $datos;
                            }

                            public function collection()
                            {
                                return collect($this->datos);
                            }

                            public function headings(): array
                            {
                                return [
                                    'nombre_trabajador *',
                                    'ape_pat *',
                                    'ape_mat',
                                    'fecha_nacimiento *',
                                    'curp *',
                                    'rfc *',
                                    'no_nss',
                                    'telefono *',
                                    'correo',
                                    'direccion',
                                    'fecha_ingreso *',
                                    'estatus *',
                                    'nombre_area *',
                                    'nombre_categoria *',
                                    'sueldo_diarios *',
                                    'formacion',
                                    'grado_estudios'
                                ];
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 20, 'B' => 20, 'C' => 20, 'D' => 15, 'E' => 20,
                                    'F' => 15, 'G' => 15, 'H' => 12, 'I' => 25, 'J' => 30,
                                    'K' => 15, 'L' => 15, 'M' => 20, 'N' => 25, 'O' => 15,
                                    'P' => 20, 'Q' => 20
                                ];
                            }
                        },
                        'Instrucciones' => new class($this->instrucciones) implements \Maatwebsite\Excel\Concerns\FromCollection {
                            private $instrucciones;

                            public function __construct($instrucciones)
                            {
                                $this->instrucciones = $instrucciones;
                            }

                            public function collection()
                            {
                                return collect($this->instrucciones);
                            }
                        }
                    ];
                }
            }, 'plantilla_importacion_trabajadores.xlsx');

        } catch (\Exception $e) {
            Log::error('Error al generar plantilla de importación', [
                'error' => $e->getMessage(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al generar la plantilla: ' . $e->getMessage()]);
        }
    }

    /**
     * Procesar importación masiva desde Excel
     */
    public function importarTrabajadores(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB máximo
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel',
            'archivo_excel.mimes' => 'El archivo debe ser de tipo Excel (.xlsx o .xls)',
            'archivo_excel.max' => 'El archivo no puede superar los 10MB'
        ]);

        try {
            // Leer el archivo Excel
            $archivo = $request->file('archivo_excel');
            $datos = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array)
                {
                    return $array;
                }
            }, $archivo);

            // Obtener la primera hoja (datos de trabajadores)
            $filasTrabajadores = $datos[0] ?? [];
            
            if (empty($filasTrabajadores)) {
                return back()->withErrors(['error' => 'El archivo Excel está vacío o no tiene el formato correcto']);
            }

            // Obtener encabezados (primera fila)
            $encabezados = array_shift($filasTrabajadores);
            
            // Mapear encabezados
            $camposEsperados = [
                'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'curp', 'rfc',
                'no_nss', 'telefono', 'correo', 'direccion', 'fecha_ingreso', 'estatus',
                'nombre_area', 'nombre_categoria', 'sueldo_diarios', 'formacion', 'grado_estudios'
            ];

            // Limpiar encabezados (quitar asteriscos y espacios)
            $encabezadosLimpios = array_map(function($encabezado) {
                return trim(str_replace('*', '', $encabezado));
            }, $encabezados);

            // Verificar que todos los campos estén presentes
            $camposFaltantes = array_diff($camposEsperados, $encabezadosLimpios);
            if (!empty($camposFaltantes)) {
                return back()->withErrors(['error' => 'Faltan columnas en el Excel: ' . implode(', ', $camposFaltantes)]);
            }

            // Procesar cada fila
            $resultados = $this->procesarFilasTrabajadores($filasTrabajadores, $encabezadosLimpios);

            $mensaje = "Importación completada. ";
            $mensaje .= "Éxito: {$resultados['exitosos']}, ";
            $mensaje .= "Errores: {$resultados['errores']}, ";
            $mensaje .= "Omitidos: {$resultados['omitidos']}";

            if ($resultados['errores'] > 0) {
                return back()->with('warning', $mensaje)
                           ->with('errores_detalle', $resultados['detalles_errores']);
            }

            return redirect()->route('trabajadores.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error en importación masiva', [
                'error' => $e->getMessage(),
                'archivo' => $archivo->getClientOriginalName(),
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->withErrors(['error' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Procesar filas de trabajadores del Excel
     */
    private function procesarFilasTrabajadores(array $filas, array $encabezados): array
    {
        $exitosos = 0;
        $errores = 0;
        $omitidos = 0;
        $detallesErrores = [];

        // Cache de áreas y categorías para optimizar
        $areasCache = Area::pluck('id_area', 'nombre_area')->toArray();
        $categoriasCache = Categoria::with('area')
                                   ->get()
                                   ->groupBy('nombre_categoria')
                                   ->map(function($categorias) {
                                       return $categorias->first();
                                   })
                                   ->toArray();

        DB::beginTransaction();

        try {
            foreach ($filas as $indice => $fila) {
                $numeroFila = $indice + 2; // +2 porque empezamos en fila 2 del Excel

                // Saltar filas vacías
                if (empty(array_filter($fila))) {
                    $omitidos++;
                    continue;
                }

                // Mapear datos de la fila
                $datosTrabajador = [];
                foreach ($encabezados as $colIndice => $campo) {
                    $datosTrabajador[$campo] = $fila[$colIndice] ?? null;
                }

                // Procesar trabajador individual
                $resultado = $this->procesarTrabajadorIndividual($datosTrabajador, $numeroFila, $areasCache, $categoriasCache);

                if ($resultado['exito']) {
                    $exitosos++;
                } else {
                    $errores++;
                    $detallesErrores[] = "Fila {$numeroFila}: " . implode(', ', $resultado['errores']);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return [
            'exitosos' => $exitosos,
            'errores' => $errores,
            'omitidos' => $omitidos,
            'detalles_errores' => $detallesErrores
        ];
    }

    /**
     * Procesar un trabajador individual
     */
    private function procesarTrabajadorIndividual(array $datos, int $numeroFila, array $areasCache, array $categoriasCache): array
    {
        try {
            // Validar datos básicos
            $erroresValidacion = $this->validarDatosTrabajador($datos, $numeroFila);
            if (!empty($erroresValidacion)) {
                return ['exito' => false, 'errores' => $erroresValidacion];
            }

            // Buscar área y categoría
            $nombreArea = trim($datos['nombre_area']);
            $nombreCategoria = trim($datos['nombre_categoria']);

            if (!isset($areasCache[$nombreArea])) {
                return ['exito' => false, 'errores' => ["Área '{$nombreArea}' no encontrada"]];
            }

            if (!isset($categoriasCache[$nombreCategoria])) {
                return ['exito' => false, 'errores' => ["Categoría '{$nombreCategoria}' no encontrada"]];
            }

            $categoria = $categoriasCache[$nombreCategoria];
            if ($categoria['id_area'] != $areasCache[$nombreArea]) {
                return ['exito' => false, 'errores' => ["La categoría '{$nombreCategoria}' no pertenece al área '{$nombreArea}'"]];
            }

            // Verificar duplicados
            $curpExiste = Trabajador::where('curp', strtoupper(trim($datos['curp'])))->exists();
            if ($curpExiste) {
                return ['exito' => false, 'errores' => ["CURP {$datos['curp']} ya existe"]];
            }

            $rfcExiste = Trabajador::where('rfc', strtoupper(trim($datos['rfc'])))->exists();
            if ($rfcExiste) {
                return ['exito' => false, 'errores' => ["RFC {$datos['rfc']} ya existe"]];
            }

            if (!empty($datos['correo'])) {
                $correoExiste = Trabajador::where('correo', strtolower(trim($datos['correo'])))->exists();
                if ($correoExiste) {
                    return ['exito' => false, 'errores' => ["Correo {$datos['correo']} ya existe"]];
                }
            }

            // Crear trabajador
            $trabajador = Trabajador::create([
                'nombre_trabajador' => trim($datos['nombre_trabajador']),
                'ape_pat' => trim($datos['ape_pat']),
                'ape_mat' => trim($datos['ape_mat']) ?: null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'curp' => strtoupper(trim($datos['curp'])),
                'rfc' => strtoupper(trim($datos['rfc'])),
                'no_nss' => trim($datos['no_nss']) ?: null,
                'telefono' => trim($datos['telefono']),
                'correo' => !empty($datos['correo']) ? strtolower(trim($datos['correo'])) : null,
                'direccion' => trim($datos['direccion']) ?: null,
                'fecha_ingreso' => $datos['fecha_ingreso'],
                'antiguedad' => (int) Carbon::parse($datos['fecha_ingreso'])->diffInYears(now()),
                'estatus' => trim($datos['estatus']) ?: 'activo',
            ]);

            // Crear ficha técnica
            FichaTecnica::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'id_categoria' => $categoria['id_categoria'],
                'sueldo_diarios' => floatval($datos['sueldo_diarios']),
                'formacion' => trim($datos['formacion']) ?: null,
                'grado_estudios' => trim($datos['grado_estudios']) ?: null,
            ]);

            // Crear registro de documentos básico
            DocumentoTrabajador::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'porcentaje_completado' => 0.00,
                'documentos_basicos_completos' => false,
                'estado' => 'incompleto',
                'fecha_ultima_actualizacion' => now()
            ]);

            return ['exito' => true, 'errores' => []];

        } catch (\Exception $e) {
            Log::error("Error procesando trabajador en fila {$numeroFila}", [
                'error' => $e->getMessage(),
                'datos' => $datos
            ]);

            return ['exito' => false, 'errores' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    /**
     * Validar datos de un trabajador
     */
    private function validarDatosTrabajador(array $datos, int $numeroFila): array
    {
        $errores = [];

        // Campos obligatorios
        $camposObligatorios = [
            'nombre_trabajador' => 'Nombre',
            'ape_pat' => 'Apellido paterno',
            'fecha_nacimiento' => 'Fecha de nacimiento',
            'curp' => 'CURP',

            'fecha_ingreso' => 'Fecha de ingreso',
            'estatus' => 'Estado',
            'nombre_area' => 'Área',
            'nombre_categoria' => 'Categoría',
            'sueldo_diarios' => 'Sueldo diario'
        ];

        foreach ($camposObligatorios as $campo => $nombre) {
            if (empty(trim($datos[$campo] ?? ''))) {
                $errores[] = "{$nombre} es obligatorio";
            }
        }

        // Validaciones específicas
        if (!empty($datos['curp']) && strlen(trim($datos['curp'])) !== 18) {
            $errores[] = "CURP debe tener 18 caracteres";
        }




        if (!empty($datos['correo']) && !filter_var(trim($datos['correo']), FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Correo electrónico no válido";
        }

        // Validar fechas
        if (!empty($datos['fecha_nacimiento'])) {
            try {
                $fechaNacimiento = Carbon::parse($datos['fecha_nacimiento']);
                if ($fechaNacimiento->diffInYears(now()) < 18) {
                    $errores[] = "El trabajador debe ser mayor de 18 años";
                }
            } catch (\Exception $e) {
                $errores[] = "Fecha de nacimiento no válida";
            }
        }

        if (!empty($datos['fecha_ingreso'])) {
            try {
                $fechaIngreso = Carbon::parse($datos['fecha_ingreso']);
                if ($fechaIngreso->isFuture()) {
                    $errores[] = "Fecha de ingreso no puede ser futura";
                }
            } catch (\Exception $e) {
                $errores[] = "Fecha de ingreso no válida";
            }
        }

        // Validar estado
        if (!empty($datos['estatus']) && !array_key_exists(trim($datos['estatus']), Trabajador::TODOS_ESTADOS)) {
            $errores[] = "Estado '{$datos['estatus']}' no es válido";
        }

        // Validar sueldo
        if (!empty($datos['sueldo_diarios'])) {
            $sueldo = floatval($datos['sueldo_diarios']);
            if ($sueldo <= 0 || $sueldo > 99999.99) {
                $errores[] = "Sueldo debe ser entre 0.01 y 99999.99";
            }
        }

        return $errores;
    }
}