<?php

namespace App\Console\Commands;

use App\Models\PermisosLaborales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LimpiarPdfsHuerfanos extends Command
{
    /**
     * ✅ COMANDO SIMPLE PARA LIMPIAR PDFs SIN REGISTRO EN BD
     */
    protected $signature = 'permisos:limpiar-pdfs {--dry-run : Solo mostrar archivos sin eliminar}';
    protected $description = 'Elimina PDFs de permisos que ya no existen en la base de datos';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🧹 Iniciando limpieza de PDFs huérfanos...');
        
        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: Solo se mostrarán los archivos, no se eliminarán');
        }

        try {
            // ✅ OBTENER TODOS LOS ARCHIVOS PDF DEL DIRECTORIO
            $archivosEnStorage = collect();
            $directoriosAños = Storage::disk('public')->directories('permisos_laborales');
            
            foreach ($directoriosAños as $dirAño) {
                $directoriosMeses = Storage::disk('public')->directories($dirAño);
                foreach ($directoriosMeses as $dirMes) {
                    $archivos = Storage::disk('public')->files($dirMes);
                    foreach ($archivos as $archivo) {
                        if (str_ends_with($archivo, '.pdf')) {
                            $archivosEnStorage->push($archivo);
                        }
                    }
                }
            }

            $this->info("📁 Encontrados {$archivosEnStorage->count()} archivos PDF en storage");

            // ✅ OBTENER RUTAS DE PDFs REGISTRADOS EN BD
            $rutasEnBD = PermisosLaborales::whereNotNull('ruta_pdf')
                ->pluck('ruta_pdf')
                ->filter()
                ->toArray();

            $this->info("💾 Encontrados " . count($rutasEnBD) . " PDFs registrados en BD");

            // ✅ ENCONTRAR ARCHIVOS HUÉRFANOS
            $archivosHuerfanos = $archivosEnStorage->diff($rutasEnBD);

            if ($archivosHuerfanos->isEmpty()) {
                $this->info('✅ No se encontraron PDFs huérfanos');
                return 0;
            }

            $this->warn("🗑️  Encontrados {$archivosHuerfanos->count()} PDFs huérfanos:");

            $totalTamaño = 0;
            $eliminados = 0;

            foreach ($archivosHuerfanos as $archivo) {
                $tamaño = Storage::disk('public')->size($archivo);
                $totalTamaño += $tamaño;
                $tamañoFormateado = $this->formatearTamaño($tamaño);

                $this->line("  📄 {$archivo} ({$tamañoFormateado})");

                if (!$dryRun) {
                    if (Storage::disk('public')->delete($archivo)) {
                        $eliminados++;
                        $this->info("    ✅ Eliminado");
                    } else {
                        $this->error("    ❌ Error al eliminar");
                    }
                }
            }

            // ✅ RESUMEN
            $this->newLine();
            $totalTamañoFormateado = $this->formatearTamaño($totalTamaño);
            
            if ($dryRun) {
                $this->info("📊 RESUMEN (DRY-RUN):");
                $this->info("  • PDFs huérfanos: {$archivosHuerfanos->count()}");
                $this->info("  • Espacio a liberar: {$totalTamañoFormateado}");
                $this->warn("  ⚠️  Ejecuta sin --dry-run para eliminar realmente");
            } else {
                $this->info("📊 RESUMEN:");
                $this->info("  • PDFs eliminados: {$eliminados}/{$archivosHuerfanos->count()}");
                $this->info("  • Espacio liberado: {$totalTamañoFormateado}");
                
                Log::info('Limpieza de PDFs huérfanos completada', [
                    'pdfs_encontrados' => $archivosHuerfanos->count(),
                    'pdfs_eliminados' => $eliminados,
                    'espacio_liberado_bytes' => $totalTamaño,
                ]);
            }

            // ✅ LIMPIAR DIRECTORIOS VACÍOS
            if (!$dryRun && $eliminados > 0) {
                $this->limpiarDirectoriosVacios();
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());
            Log::error('Error en comando limpiar-pdfs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * ✅ FORMATEAR TAMAÑO DE ARCHIVO
     */
    private function formatearTamaño(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * ✅ LIMPIAR DIRECTORIOS VACÍOS
     */
    private function limpiarDirectoriosVacios(): void
    {
        $directoriosAños = Storage::disk('public')->directories('permisos_laborales');
        
        foreach ($directoriosAños as $dirAño) {
            $directoriosMeses = Storage::disk('public')->directories($dirAño);
            
            foreach ($directoriosMeses as $dirMes) {
                $archivos = Storage::disk('public')->files($dirMes);
                if (empty($archivos)) {
                    Storage::disk('public')->deleteDirectory($dirMes);
                    $this->line("  🗂️  Directorio vacío eliminado: {$dirMes}");
                }
            }
            
            // Verificar si el directorio del año quedó vacío
            $directoriosMeses = Storage::disk('public')->directories($dirAño);
            if (empty($directoriosMeses)) {
                Storage::disk('public')->deleteDirectory($dirAño);
                $this->line("  📁 Directorio de año eliminado: {$dirAño}");
            }
        }
    }
}