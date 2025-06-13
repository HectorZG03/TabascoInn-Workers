<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ContratoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminContratosController extends Controller
{
    /**
     * Mostrar contratos del trabajador (vista principal)
     */
    public function show(Trabajador $trabajador)
    {
        // ✅ Cargar relaciones necesarias
        $trabajador->load([
            'fichaTecnica.categoria.area'
        ]);

        // ✅ Obtener contratos del trabajador ordenados por fecha de inicio (más reciente primero)
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get();

        // ✅ Calcular estadísticas de contratos
        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        // ✅ Procesar información adicional para cada contrato
        $contratos = $contratos->map(function ($contrato) {
            // Calcular información adicional
            $contrato->dias_restantes_calculados = $contrato->diasRestantes();
            $contrato->esta_vigente_bool = $contrato->estaVigente();
            $contrato->estado_calculado = $contrato->estado;
            
            // Información de duración formateada
            $contrato->duracion_completa = $this->formatearDuracionCompleta($contrato);
            
            // Color del badge según el estado
            $contrato->color_estado = $this->obtenerColorEstado($contrato->estado_calculado);
            
            // Información de archivos
            $contrato->archivo_existe = $contrato->ruta_archivo && Storage::disk('public')->exists($contrato->ruta_archivo);
            
            return $contrato;
        });

        // ✅ LOG SIMPLE - Sin datos de usuario
        Log::info('Contratos del trabajador consultados', [
            'trabajador_id' => $trabajador->id_trabajador,
            'total_contratos' => $contratos->count(),
            'contratos_vigentes' => $estadisticas['vigentes']
        ]);

        return view('trabajadores.secciones_perfil.contrato_trabajador', compact(
            'trabajador',
            'contratos',
            'estadisticas'
        ));
    }

    /**
     * ✅ Descargar contrato específico
     */
    public function descargar(Trabajador $trabajador, ContratoTrabajador $contrato)
    {
        try {
            // Verificar que el contrato pertenece al trabajador
            if ($contrato->id_trabajador !== $trabajador->id_trabajador) {
                abort(403, 'No autorizado para descargar este contrato');
            }

            // Verificar que el archivo existe
            if (!$contrato->ruta_archivo || !Storage::disk('public')->exists($contrato->ruta_archivo)) {
                Log::warning('Intento de descarga de archivo inexistente', [
                    'contrato_id' => $contrato->id_contrato,
                    'trabajador_id' => $trabajador->id_trabajador,
                    'ruta_archivo' => $contrato->ruta_archivo
                ]);
                
                return back()->withErrors(['error' => 'El archivo del contrato no existe o ha sido eliminado']);
            }

            $rutaCompleta = Storage::disk('public')->path($contrato->ruta_archivo);
            
            if (!file_exists($rutaCompleta)) {
                abort(404, 'Archivo físico no encontrado');
            }

            // Generar nombre descriptivo para la descarga
            $nombreDescarga = $this->generarNombreDescarga($trabajador, $contrato);

            // ✅ LOG SIMPLE - Sin datos de usuario
            Log::info('Contrato descargado', [
                'contrato_id' => $contrato->id_contrato,
                'trabajador_id' => $trabajador->id_trabajador,
                'nombre_archivo' => $nombreDescarga
            ]);

            return Response::download($rutaCompleta, $nombreDescarga, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descargar contrato', [
                'error' => $e->getMessage(),
                'contrato_id' => $contrato->id_contrato ?? 'N/A',
                'trabajador_id' => $trabajador->id_trabajador
            ]);
            
            return back()->withErrors(['error' => 'Error al procesar la descarga del contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ Calcular estadísticas de contratos
     */
    private function calcularEstadisticasContratos($contratos): array
    {
        $hoy = Carbon::today();
        
        $vigentes = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estaVigente();
        })->count();
        
        $expirados = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estado === 'expirado';
        })->count();
        
        $pendientes = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estado === 'pendiente';
        })->count();
        
        // Contratos próximos a vencer (30 días)
        $proximosVencer = $contratos->filter(function ($contrato) use ($hoy) {
            return $contrato->estaVigente() && $contrato->diasRestantes() <= 30;
        })->count();
        
        // Contrato más reciente
        $contratoActual = $contratos->filter(function ($contrato) {
            return $contrato->estaVigente();
        })->first();
        
        // Duración total acumulada
        $duracionTotalDias = $contratos->sum(function ($contrato) {
            if ($contrato->tipo_duracion === 'dias') {
                return $contrato->duracion;
            } else {
                // Convertir meses a días aproximadamente
                return $contrato->duracion * 30;
            }
        });

        return [
            'total' => $contratos->count(),
            'vigentes' => $vigentes,
            'expirados' => $expirados,
            'pendientes' => $pendientes,
            'proximos_vencer' => $proximosVencer,
            'contrato_actual' => $contratoActual,
            'duracion_total_dias' => $duracionTotalDias,
            'duracion_total_texto' => $this->convertirDiasATexto($duracionTotalDias),
            'tiene_contrato_vigente' => $vigentes > 0,
        ];
    }

    /**
     * ✅ Formatear duración completa del contrato
     */
    private function formatearDuracionCompleta(ContratoTrabajador $contrato): string
    {
        $inicio = $contrato->fecha_inicio_contrato->format('d/m/Y');
        $fin = $contrato->fecha_fin_contrato->format('d/m/Y');
        $duracion = $contrato->duracion_texto;
        
        return "{$duracion} (del {$inicio} al {$fin})";
    }

    /**
     * ✅ Obtener color del badge según el estado
     */
    private function obtenerColorEstado(string $estado): string
    {
        return match($estado) {
            'vigente' => 'success',
            'expirado' => 'danger',
            'pendiente' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * ✅ Generar nombre descriptivo para descarga
     */
    private function generarNombreDescarga(Trabajador $trabajador, ContratoTrabajador $contrato): string
    {
        $nombreTrabajador = str_replace(' ', '_', $trabajador->nombre_completo);
        $fechaInicio = $contrato->fecha_inicio_contrato->format('Y-m-d');
        $estado = ucfirst($contrato->estado);
        
        return "Contrato_{$nombreTrabajador}_{$fechaInicio}_{$estado}.pdf";
    }

    /**
     * ✅ Convertir días a texto legible
     */
    private function convertirDiasATexto(int $dias): string
    {
        if ($dias < 30) {
            return "{$dias} días";
        } elseif ($dias < 365) {
            $meses = round($dias / 30, 1);
            return "{$meses} meses";
        } else {
            $años = round($dias / 365, 1);
            return "{$años} años";
        }
    }

    /**
     * ✅ API: Obtener información resumida de contratos (para AJAX si se necesita)
     */
    public function obtenerResumen(Trabajador $trabajador)
    {
        $contratos = ContratoTrabajador::where('id_trabajador', $trabajador->id_trabajador)
            ->orderBy('fecha_inicio_contrato', 'desc')
            ->get(['id_contrato', 'fecha_inicio_contrato', 'fecha_fin_contrato', 'tipo_duracion', 'duracion']);

        $estadisticas = $this->calcularEstadisticasContratos($contratos);

        return response()->json([
            'success' => true,
            'data' => [
                'total_contratos' => $estadisticas['total'],
                'contrato_vigente' => $estadisticas['tiene_contrato_vigente'],
                'proximos_vencer' => $estadisticas['proximos_vencer'],
                'duracion_total' => $estadisticas['duracion_total_texto']
            ]
        ]);
    }
}