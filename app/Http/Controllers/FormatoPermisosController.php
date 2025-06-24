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
     * ✅ GENERAR Y DESCARGAR PDF DEL PERMISO
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

            // ✅ PREPARAR DATOS DEL PERMISO
            $fechaInicio = Carbon::parse($permiso->fecha_inicio);
            $fechaFin = Carbon::parse($permiso->fecha_fin);
            $fechaRegreso = $fechaFin->addDay();
            $diasTotales = $permiso->dias_de_permiso;

            // ✅ GENERAR LISTA DE FECHAS (si son pocos días)
            $fechasDetalle = [];
            if ($diasTotales <= 10) {
                $fechaActual = Carbon::parse($permiso->fecha_inicio);
                while ($fechaActual->lte(Carbon::parse($permiso->fecha_fin))) {
                    $fechasDetalle[] = $fechaActual->locale('es')->translatedFormat('l d \d\e F \d\e Y');
                    $fechaActual->addDay();
                }
            }

            // ✅ PREPARAR MOTIVO DESCRIPTIVO
            $motivoDescriptivo = $this->generarMotivoDescriptivo($permiso->tipo_permiso, $permiso->motivo_texto);

            $permisoData = [
                'id' => $permiso->id_permiso,
                'tipo' => strtoupper($permiso->tipo_permiso_texto),
                'motivo' => $motivoDescriptivo,
                'fecha_inicio' => $fechaInicio->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'fecha_fin' => Carbon::parse($permiso->fecha_fin)->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'fecha_regreso' => $fechaRegreso->locale('es')->translatedFormat('l d \d\e F \d\e Y'),
                'dias_totales' => $diasTotales,
                'dias_texto' => $this->numeroATexto($diasTotales),
                'fechas_detalle' => $fechasDetalle,
                'observaciones' => $permiso->observaciones ?? '',
                'estatus' => $permiso->estatus_permiso_texto,
            ];

            // ✅ DATOS FIJOS DE LA EMPRESA
            $lugar = 'Villahermosa, Tabasco';
            $fechaActual = Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y');

            // ✅ FIRMAS (puedes personalizar esto según tus necesidades)
            $firmas = [
                'trabajador' => strtoupper($trabajador['nombre_completo']),
                'director' => 'LIC. JORGE ANTONIO ZURITA SILVA', // Personalizar según tu empresa
            ];

            // ✅ GENERAR PDF
            $pdf = PDF::loadView('formatos.formato_permisos', [
                'trabajador' => $trabajador,
                'permiso' => $permisoData,
                'lugar' => $lugar,
                'fecha_actual' => $fechaActual,
                'firmas' => $firmas,
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
     * ✅ HELPER: CONVERTIR NÚMERO A TEXTO
     */
    private function numeroATexto(int $numero): string
    {
        $numeros = [
            1 => 'un día', 2 => 'dos días', 3 => 'tres días', 4 => 'cuatro días', 5 => 'cinco días',
            6 => 'seis días', 7 => 'siete días', 8 => 'ocho días', 9 => 'nueve días', 10 => 'diez días',
            11 => 'once días', 12 => 'doce días', 13 => 'trece días', 14 => 'catorce días', 15 => 'quince días',
            16 => 'dieciséis días', 17 => 'diecisiete días', 18 => 'dieciocho días', 19 => 'diecinueve días', 20 => 'veinte días',
            21 => 'veintiún días', 22 => 'veintidós días', 23 => 'veintitrés días', 24 => 'veinticuatro días', 25 => 'veinticinco días',
            26 => 'veintiséis días', 27 => 'veintisiete días', 28 => 'veintiocho días', 29 => 'veintinueve días', 30 => 'treinta días'
        ];

        return $numeros[$numero] ?? "$numero días";
    }
}