<?php

namespace App\Console\Commands;

use App\Models\ContratoTrabajador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarContratosVencidos extends Command
{
    /**
     * El nombre y firma del comando de consola.
     */
    protected $signature = 'contratos:verificar-vencidos 
                            {--dry-run : Solo mostrar resultados sin hacer cambios}
                            {--dias-notificacion=7 : Días de anticipación para notificar vencimientos}';

    /**
     * Descripción del comando de consola.
     */
    protected $description = 'Verifica contratos vencidos y próximos a vencer, actualizando su estado automáticamente';

    /**
     * Ejecutar el comando de consola.
     */
    public function handle(): int
    {
        $this->info('🔍 Iniciando verificación de contratos...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $diasNotificacion = (int) $this->option('dias-notificacion');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios en la base de datos');
            $this->newLine();
        }

        try {
            // 1. Verificar contratos vencidos que deben terminarse
            $this->procesarContratosVencidos($dryRun);
            
            // 2. Notificar contratos próximos a vencer
            $this->notificarContratosProximosAVencer($diasNotificacion);
            
            // 3. Mostrar estadísticas finales
            $this->mostrarEstadisticas();

            $this->newLine();
            $this->info('✅ Verificación completada exitosamente');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error durante la verificación: {$e->getMessage()}");
            Log::error('Error en VerificarContratosVencidos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Procesar contratos vencidos y marcarlos como terminados
     */
    private function procesarContratosVencidos(bool $dryRun): void
    {
        $this->info('📋 Procesando contratos vencidos...');

        // Obtener contratos que deben terminarse
        $contratosVencidos = ContratoTrabajador::paraTerminar()
            ->with(['trabajador:id_trabajador,nombre_trabajador,ape_pat,ape_mat'])
            ->get();

        if ($contratosVencidos->isEmpty()) {
            $this->line('   ✅ No hay contratos vencidos pendientes de terminar');
            return;
        }

        $this->warn("   📊 Encontrados: {$contratosVencidos->count()} contratos vencidos");
        $this->newLine();

        $terminados = 0;
        $errores = 0;

        foreach ($contratosVencidos as $contrato) {
            $nombreTrabajador = $contrato->trabajador->nombre_completo ?? 'N/A';
            $fechaVencimiento = $contrato->fecha_fin_contrato->format('d/m/Y');
            $diasVencido = Carbon::today()->diffInDays($contrato->fecha_fin_contrato);

            $this->line("   🔸 Contrato #{$contrato->id_contrato} - {$nombreTrabajador}");
            $this->line("      Vencido: {$fechaVencimiento} ({$diasVencido} días atrás)");

            if (!$dryRun) {
                try {
                    $resultado = $contrato->marcarComoTerminado(
                        "Terminado automáticamente por vencimiento ({$diasVencido} días vencido)"
                    );

                    if ($resultado) {
                        $this->line("      ✅ Marcado como terminado");
                        $terminados++;
                        
                        Log::info('Contrato terminado automáticamente', [
                            'contrato_id' => $contrato->id_contrato,
                            'trabajador_id' => $contrato->id_trabajador,
                            'trabajador_nombre' => $nombreTrabajador,
                            'fecha_vencimiento' => $fechaVencimiento,
                            'dias_vencido' => $diasVencido
                        ]);
                    } else {
                        $this->line("      ❌ Error al terminar contrato");
                        $errores++;
                    }
                } catch (\Exception $e) {
                    $this->line("      ❌ Error: {$e->getMessage()}");
                    $errores++;
                    
                    Log::error('Error al terminar contrato vencido', [
                        'contrato_id' => $contrato->id_contrato,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->line("      🔄 Se terminaría automáticamente");
            }

            $this->newLine();
        }

        if (!$dryRun) {
            $this->info("📊 Resultados:");
            $this->line("   ✅ Terminados: {$terminados}");
            if ($errores > 0) {
                $this->line("   ❌ Errores: {$errores}");
            }
        }
    }

    /**
     * Notificar contratos próximos a vencer
     */
    private function notificarContratosProximosAVencer(int $dias): void
    {
        $this->info("🔔 Verificando contratos próximos a vencer (próximos {$dias} días)...");

        $contratosProximos = ContratoTrabajador::proximosAVencer($dias)
            ->with(['trabajador:id_trabajador,nombre_trabajador,ape_pat,ape_mat'])
            ->orderBy('fecha_fin_contrato', 'asc')
            ->get();

        if ($contratosProximos->isEmpty()) {
            $this->line('   ✅ No hay contratos próximos a vencer');
            return;
        }

        $this->warn("   📊 Encontrados: {$contratosProximos->count()} contratos próximos a vencer");
        $this->newLine();

        foreach ($contratosProximos as $contrato) {
            $nombreTrabajador = $contrato->trabajador->nombre_completo ?? 'N/A';
            $fechaVencimiento = $contrato->fecha_fin_contrato->format('d/m/Y');
            $diasRestantes = $contrato->diasRestantes();

            $color = $diasRestantes <= 7 ? 'error' : ($diasRestantes <= 15 ? 'warn' : 'line');
            
            $this->$color("   🔸 Contrato #{$contrato->id_contrato} - {$nombreTrabajador}");
            $this->$color("      Vence: {$fechaVencimiento} ({$diasRestantes} días restantes)");
            
            if ($contrato->puedeRenovarse()) {
                $this->$color("      ⚡ Puede renovarse");
            }
        }

        $this->newLine();
        Log::info('Contratos próximos a vencer notificados', [
            'total' => $contratosProximos->count(),
            'dias_anticipacion' => $dias
        ]);
    }

    /**
     * Mostrar estadísticas generales de contratos
     */
    private function mostrarEstadisticas(): void
    {
        $this->info('📊 Estadísticas generales de contratos:');

        $estadisticas = [
            'Vigentes' => ContratoTrabajador::vigentes()->count(),
            'Próximos a vencer (30 días)' => ContratoTrabajador::proximosAVencer(30)->count(),
            'Próximos a vencer (15 días)' => ContratoTrabajador::proximosAVencer(15)->count(),
            'Próximos a vencer (7 días)' => ContratoTrabajador::proximosAVencer(7)->count(),
            'Vencidos pendientes' => ContratoTrabajador::paraTerminar()->count(),
            'Terminados' => ContratoTrabajador::porEstatus(ContratoTrabajador::ESTATUS_TERMINADO)->count(),
            'Renovados' => ContratoTrabajador::porEstatus(ContratoTrabajador::ESTATUS_RENOVADO)->count(),
        ];

        foreach ($estadisticas as $concepto => $cantidad) {
            $color = match($concepto) {
                'Vencidos pendientes' => $cantidad > 0 ? 'error' : 'info',
                'Próximos a vencer (7 días)' => $cantidad > 0 ? 'error' : 'info',
                'Próximos a vencer (15 días)' => $cantidad > 0 ? 'warn' : 'info',
                default => 'info'
            };
            
            $this->$color("   {$concepto}: {$cantidad}");
        }
    }
}