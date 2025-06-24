<?php

namespace App\Http\Controllers;

use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FormatoPermisosController extends Controller
{
    /**
     * ✅ GENERAR Y DESCARGAR PDF DEL PERMISO - FORMATO ACTUALIZADO
     */
    public function generarPDF(PermisosLaborales $permiso)
    {
        try {
            // ✅ CARGAR RELACIONES NECESARIAS
            $permiso->load([
                'trabajador.fichaTecnica.categoria.area'
            ]);

            // ✅ VERIFICAR QUE EL TRABAJADOR EXISTE
            if (!$permiso->trabajador) {
                return redirect()->back()->withErrors([
                    'error' => 'No se encontraron datos del trabajador para este permiso'
                ]);
            }

            // ✅ PREPARAR DATOS DEL TRABAJADOR
            $trabajador = [
                'id_trabajador' => $permiso->trabajador->id_trabajador,
                'nombre_completo' => $permiso->trabajador->nombre_completo,
                'area' => $permiso->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área asignada',
                'categoria' => $permiso->trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría asignada',
                'fecha_ingreso' => $permiso->trabajador->fecha_ingreso->format('d/m/Y'),
                'telefono' => $permiso->trabajador->telefono ?? 'No disponible',
                'correo' => $permiso->trabajador->correo ?? 'No disponible',
            ];

            // ✅ PREPARAR DATOS DEL PERMISO CON FECHAS ESPECÍFICAS
            $fechaInicio = Carbon::parse($permiso->fecha_inicio);
            $fechaFin = Carbon::parse($permiso->fecha_fin);
            $fechaRegreso = $fechaFin->copy()->addDay();
            $diasTotales = $permiso->dias_de_permiso;

            // ✅ GENERAR FECHAS ESPECÍFICAS POR MES
            $fechasPorMes = [];
            $fechaActual = $fechaInicio->copy();
            
            while ($fechaActual->lte($fechaFin)) {
                $mesAño = $fechaActual->locale('es')->translatedFormat('F Y');
                if (!isset($fechasPorMes[$mesAño])) {
                    $fechasPorMes[$mesAño] = [];
                }
                $fechasPorMes[$mesAño][] = $fechaActual->day;
                $fechaActual->addDay();
            }

            // ✅ CONSTRUIR CADENA DE FECHAS COMO EN EL EJEMPLO
            $fechasTexto = '';
            $contador = 0;
            foreach ($fechasPorMes as $mesAño => $dias) {
                if ($contador > 0) {
                    $fechasTexto .= ' y ';
                }
                $fechasTexto .= implode(', ', $dias) . ' de ' . $mesAño;
                $contador++;
            }

            $permisoData = [
                'id' => $permiso->id_permiso,
                'tipo_permiso' => $permiso->tipo_permiso, // permiso o suspendido
                'motivo_raw' => $permiso->motivo, // motivo sin procesar
                'motivo_texto' => $permiso->motivo_texto, // motivo legible
                'fecha_inicio' => $fechaInicio->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'fecha_fin' => $fechaFin->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'fecha_regreso' => $fechaRegreso->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'dias_totales' => $diasTotales,
                'dias_texto' => $this->numeroATextoMejorado($diasTotales),
                'fechas_especificas' => $fechasTexto, // ✅ NUEVO: fechas como en el ejemplo
                'observaciones' => $permiso->observaciones ?? '',
                'estatus' => $permiso->estatus_permiso_texto,
            ];

            // ✅ DATOS FIJOS DE LA EMPRESA
            $lugar = 'Villahermosa, Tabasco';
            $fechaActual = Carbon::now()->locale('es')->translatedFormat('d \d\e F Y');

            // ✅ FIRMAS
            $firmas = [
                'trabajador' => $trabajador['nombre_completo'],
                'director' => 'L.F.C.P. Alberto Zurita del Rivero',
            ];



            $watermarkData = null;
            $watermarkPath = public_path('image/estaticas/watermark.jpg'); // o tu imagen
            if (file_exists($watermarkPath)) {
                $watermarkContent = base64_encode(file_get_contents($watermarkPath));
                $watermarkMimeType = mime_content_type($watermarkPath);
                $watermarkData = 'data:' . $watermarkMimeType . ';base64,' . $watermarkContent; 
            }

            // ✅ GENERAR PDF
            $pdf = PDF::loadView('formatos.formato_permisos', [
                'trabajador' => $trabajador,
                'permiso' => $permisoData,
                'lugar' => $lugar,
                'fecha_actual' => $fechaActual,
                'firmas' => $firmas,
                'watermark' => $watermarkData,
            ]);

            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'dpi' => 300, // ✅ Aumentar DPI de 150 a 300 para mejor calidad
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'fontSubsetting' => false,
                'chroot' => public_path(), // ✅ Permite mejor acceso a imágenes
            ]);

            // ✅ CONFIGURAR PDF
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            // ✅ GENERAR NOMBRE DEL ARCHIVO
            $nombreArchivo = $this->generarNombreArchivo($permiso);
            
            // ✅ CREAR DIRECTORIO SI NO EXISTE
            $directorioPermisos = 'permisos_laborales/' . date('Y') . '/' . date('m');
            Storage::disk('public')->makeDirectory($directorioPermisos);

            // ✅ RUTA RELATIVA AL DISCO 'public'
            $rutaArchivo = $directorioPermisos . '/' . $nombreArchivo;

            // ✅ GUARDAR PDF EN storage/app/public/permisos_laborales/...
            Storage::disk('public')->put($rutaArchivo, $pdf->output());

            // ✅ GUARDAR SOLO LA RUTA RELATIVA EN BD
            $permiso->update([
                'ruta_pdf' => $rutaArchivo
            ]);

            Log::info('PDF de permiso generado exitosamente', [
                'permiso_id' => $permiso->id_permiso,
                'trabajador' => $trabajador['nombre_completo'],
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'tamaño_bytes' => strlen($pdf->output()),
            ]);

            // ✅ DESCARGAR PDF DIRECTAMENTE
            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Error al generar el PDF: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ DESCARGAR PDF EXISTENTE
     */
    public function descargarPDF(PermisosLaborales $permiso)
    {
        try {
            // ✅ VERIFICAR SI EXISTE PDF GENERADO
            if (!$permiso->ruta_pdf || !Storage::exists('public/' . $permiso->ruta_pdf)) {
                // Si no existe, generar uno nuevo
                return $this->generarPDF($permiso);
            }

            // ✅ DESCARGAR PDF EXISTENTE
            $nombreArchivo = basename($permiso->ruta_pdf);
            
            return Storage::download('public/' . $permiso->ruta_pdf, $nombreArchivo, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descargar PDF de permiso', [
                'permiso_id' => $permiso->id_permiso,
                'ruta_pdf' => $permiso->ruta_pdf,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Error al descargar el PDF: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ REGENERAR PDF (ÚTIL SI SE ACTUALIZAN DATOS)
     */
    public function regenerarPDF(PermisosLaborales $permiso)
    {
        try {
            // ✅ ELIMINAR PDF ANTERIOR SI EXISTE
            if ($permiso->ruta_pdf && Storage::exists('public/' . $permiso->ruta_pdf)) {
                Storage::delete('public/' . $permiso->ruta_pdf);
            }

            // ✅ LIMPIAR RUTA EN BD
            $permiso->update(['ruta_pdf' => null]);

            // ✅ GENERAR NUEVO PDF
            return $this->generarPDF($permiso);

        } catch (\Exception $e) {
            Log::error('Error al regenerar PDF de permiso', [
                'permiso_id' => $permiso->id_permiso,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Error al regenerar el PDF: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ HELPER: GENERAR NOMBRE DEL ARCHIVO
     */
    private function generarNombreArchivo(PermisosLaborales $permiso): string
    {
        $trabajador = $permiso->trabajador;
        $tipoPermiso = $permiso->tipo_permiso === 'permiso' ? 'PERMISO' : 'SUSPENSION';
        
        // ✅ LIMPIAR NOMBRE DEL TRABAJADOR
        $nombreLimpio = $this->limpiarNombreArchivo($trabajador->nombre_completo);
        
        // ✅ FORMATO: PERMISO_JUAN_PEREZ_20250624_ID123.pdf
        return sprintf(
            '%s_%s_%s_ID%d.pdf',
            $tipoPermiso,
            $nombreLimpio,
            $permiso->fecha_inicio->format('Ymd'),
            $permiso->id_permiso
        );
    }

    /**
     * ✅ HELPER: LIMPIAR NOMBRE PARA ARCHIVO
     */
    private function limpiarNombreArchivo(string $nombre): string
    {
        // ✅ CONVERTIR A MAYÚSCULAS Y REEMPLAZAR CARACTERES ESPECIALES
        $nombre = strtoupper($nombre);
        $nombre = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'N'], $nombre);
        $nombre = preg_replace('/[^A-Z0-9\s]/', '', $nombre);
        $nombre = preg_replace('/\s+/', '_', trim($nombre));
        
        return $nombre;
    }

    /**
     * ✅ HELPER: GENERAR MOTIVO DESCRIPTIVO
     */
    private function generarMotivoDescriptivo(string $tipoPermiso, string $motivoTexto): string
    {
        $frases = [
            'permiso' => [
                'vacaciones' => 'por concepto de vacaciones correspondientes',
                'incapacidad_medica' => 'por incapacidad médica',
                'licencia_maternidad' => 'por licencia de maternidad',
                'licencia_paternidad' => 'por licencia de paternidad',
                'asuntos_personales' => 'por asuntos personales de índole familiar',
                'emergencia_familiar' => 'por emergencia familiar',
                'estudios' => 'por motivos académicos y de capacitación',
                'cita_medica' => 'para asistir a cita médica',
                'tramites_oficiales' => 'para realizar trámites oficiales',
            ],
            'suspendido' => [
                'falta_disciplinaria' => 'por falta disciplinaria según reglamento interno',
                'incumplimiento_normas' => 'por incumplimiento de normas establecidas',
                'investigacion_interna' => 'durante proceso de investigación interna',
                'ausencia_injustificada' => 'por ausencia injustificada al trabajo',
                'bajo_rendimiento' => 'por bajo rendimiento laboral',
                'conducta_inapropiada' => 'por conducta inapropiada en el lugar de trabajo',
            ]
        ];

        return $frases[$tipoPermiso][strtolower(str_replace(' ', '_', $motivoTexto))] 
               ?? "por motivo de: $motivoTexto";
    }

    /**
     * ✅ HELPER: CONVERTIR NÚMERO A TEXTO MEJORADO (sin "días")
     */
    private function numeroATextoMejorado(int $numero): string
    {
        $numeros = [
            1 => 'Un día', 2 => 'Dos días', 3 => 'Tres días', 4 => 'Cuatro días', 5 => 'Cinco días',
            6 => 'Seis días', 7 => 'Siete días', 8 => 'Ocho días', 9 => 'Nueve días', 10 => 'Diez días',
            11 => 'Once días', 12 => 'Doce días', 13 => 'Trece días', 14 => 'Catorce días', 15 => 'Quince días',
            16 => 'Dieciséis días', 17 => 'Diecisiete días', 18 => 'Dieciocho días', 19 => 'Diecinueve días', 20 => 'Veinte días',
            21 => 'Veintiún días', 22 => 'Veintidós días', 23 => 'Veintitrés días', 24 => 'Veinticuatro días', 25 => 'Veinticinco días',
            26 => 'Veintiséis días', 27 => 'Veintisiete días', 28 => 'Veintiocho días', 29 => 'Veintinueve días', 30 => 'Treinta días'
        ];

        return $numeros[$numero] ?? ucfirst($this->numeroEnLetras($numero)) . ' días';
    }

    /**
     * ✅ HELPER: CONVERTIR NÚMEROS GRANDES A LETRAS
     */
    private function numeroEnLetras(int $numero): string
    {
        if ($numero < 31) {
            $numerosBase = [
                1 => 'un', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
                6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
                11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
                16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve', 20 => 'veinte',
                21 => 'veintiún', 22 => 'veintidós', 23 => 'veintitrés', 24 => 'veinticuatro', 25 => 'veinticinco',
                26 => 'veintiséis', 27 => 'veintisiete', 28 => 'veintiocho', 29 => 'veintinueve', 30 => 'treinta'
            ];
            return $numerosBase[$numero] ?? (string)$numero;
        }
        
        // Para números mayores a 30, usar lógica básica
        $decenas = [
            30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta', 
            60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
        ];
        
        $unidades = [
            1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve'
        ];
        
        if ($numero < 100) {
            $decena = floor($numero / 10) * 10;
            $unidad = $numero % 10;
            
            if ($unidad == 0) {
                return $decenas[$decena] ?? (string)$numero;
            } else {
                return ($decenas[$decena] ?? (string)$decena) . ' y ' . ($unidades[$unidad] ?? (string)$unidad);
            }
        }
        
        return (string)$numero; // Fallback para números muy grandes
    }
}