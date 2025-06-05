<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HistorialPromocion extends Model
{
    use HasFactory;

    protected $table = 'historial_promociones';
    protected $primaryKey = 'id_promocion';

    protected $fillable = [
        'id_trabajador',
        'id_categoria_anterior',
        'id_categoria_nueva',
        'sueldo_anterior',
        'sueldo_nuevo',
        'fecha_cambio',
        'tipo_cambio',
        'motivo',
        'observaciones',
        'usuario_cambio',
        'datos_adicionales'
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'sueldo_anterior' => 'decimal:2',
        'sueldo_nuevo' => 'decimal:2',
        'datos_adicionales' => 'array'
    ];

    protected $dates = [
        'fecha_cambio',
        'created_at',
        'updated_at'
    ];

    /**
     * RELACIONES
     */
    
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function categoriaAnterior(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria_anterior', 'id_categoria');
    }

    public function categoriaNueva(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria_nueva', 'id_categoria');
    }

    /**
     * SCOPES
     */
    
    public function scopeDelTrabajador($query, $trabajadorId)
    {
        return $query->where('id_trabajador', $trabajadorId);
    }

    public function scopePromociones($query)
    {
        return $query->where('tipo_cambio', 'promocion');
    }

    public function scopeTransferencias($query)
    {
        return $query->where('tipo_cambio', 'transferencia');
    }

    public function scopeAumentosSueldo($query)
    {
        return $query->where('tipo_cambio', 'aumento_sueldo');
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_cambio', [$fechaInicio, $fechaFin]);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_cambio', '>=', now()->subDays($dias));
    }

    public function scopeOrdenadoPorFecha($query, $direction = 'desc')
    {
        return $query->orderBy('fecha_cambio', $direction);
    }

    /**
     * ACCESSORS
     */
    
    public function getTipoCambioTextoAttribute(): string
    {
        $tipos = [
            'promocion' => 'Promoción',
            'transferencia' => 'Transferencia',
            'aumento_sueldo' => 'Aumento de Sueldo',
            'reclasificacion' => 'Reclasificación',
            'ajuste_salarial' => 'Ajuste Salarial',
            'inicial' => 'Registro Inicial'
        ];

        return $tipos[$this->tipo_cambio] ?? 'Desconocido';
    }

    public function getDiferenciaSueldoAttribute(): float
    {
        if ($this->sueldo_anterior === null) {
            return 0;
        }
        
        return $this->sueldo_nuevo - $this->sueldo_anterior;
    }

    public function getPorcentajeAumentoAttribute(): float
    {
        if ($this->sueldo_anterior === null || $this->sueldo_anterior <= 0) {
            return 0;
        }
        
        return (($this->sueldo_nuevo - $this->sueldo_anterior) / $this->sueldo_anterior) * 100;
    }

    public function getEsPromocionAttribute(): bool
    {
        return $this->tipo_cambio === 'promocion';
    }

    public function getEsAumentoAttribute(): bool
    {
        return $this->diferencia_sueldo > 0;
    }

    public function getFechaCambioHumanaAttribute(): string
    {
        return $this->fecha_cambio->diffForHumans();
    }

    public function getColorTipoCambioAttribute(): string
    {
        $colores = [
            'promocion' => 'success',
            'transferencia' => 'info',
            'aumento_sueldo' => 'primary',
            'reclasificacion' => 'warning',
            'ajuste_salarial' => 'secondary',
            'inicial' => 'dark'
        ];

        return $colores[$this->tipo_cambio] ?? 'light';
    }

    /**
     * MÉTODOS ESTÁTICOS PARA CREAR REGISTROS
     */
    
    public static function registrarCambio(array $datos): self
    {
        // Determinar automáticamente el tipo de cambio si no se especifica
        if (!isset($datos['tipo_cambio'])) {
            $datos['tipo_cambio'] = self::determinarTipoCambio($datos);
        }

        // Establecer fecha actual si no se especifica
        if (!isset($datos['fecha_cambio'])) {
            $datos['fecha_cambio'] = now();
        }

        return self::create($datos);
    }

    public static function registrarInicial(Trabajador $trabajador, FichaTecnica $fichaTecnica, string $usuario = null): self
    {
        return self::create([
            'id_trabajador' => $trabajador->id_trabajador,
            'id_categoria_anterior' => null,
            'id_categoria_nueva' => $fichaTecnica->id_categoria,
            'sueldo_anterior' => null,
            'sueldo_nuevo' => $fichaTecnica->sueldo_diarios,
            'fecha_cambio' => now(),
            'tipo_cambio' => 'inicial',
            'motivo' => 'Creación de ficha técnica inicial',
            'usuario_cambio' => $usuario,
            'datos_adicionales' => [
                'formacion' => $fichaTecnica->formacion,
                'grado_estudios' => $fichaTecnica->grado_estudios
            ]
        ]);
    }

    /**
     * MÉTODOS PRIVADOS
     */
    
    private static function determinarTipoCambio(array $datos): string
    {
        $categoriaAnterior = $datos['id_categoria_anterior'] ?? null;
        $categoriaNueva = $datos['id_categoria_nueva'];
        $sueldoAnterior = $datos['sueldo_anterior'] ?? null;
        $sueldoNuevo = $datos['sueldo_nuevo'];

        // Si es el primer registro
        if ($categoriaAnterior === null) {
            return 'inicial';
        }

        // Si cambió la categoría
        if ($categoriaAnterior !== $categoriaNueva) {
            // Verificar si es promoción o transferencia
            $catAnterior = Categoria::find($categoriaAnterior);
            $catNueva = Categoria::find($categoriaNueva);
            
            if ($catAnterior && $catNueva) {
                // Si cambió de área, es transferencia
                if ($catAnterior->id_area !== $catNueva->id_area) {
                    return 'transferencia';
                }
                // Si es dentro de la misma área, es reclasificación/promoción
                return 'reclasificacion';
            }
            
            return 'promocion';
        }

        // Si solo cambió el sueldo
        if ($sueldoAnterior !== null && $sueldoAnterior != $sueldoNuevo) {
            return 'aumento_sueldo';
        }

        // Caso general
        return 'ajuste_salarial';
    }

    /**
     * MÉTODOS DE CONSULTA
     */
    
    public static function obtenerHistorialTrabajador(int $trabajadorId, int $limite = null)
    {
        $query = self::with(['categoriaAnterior.area', 'categoriaNueva.area'])
                    ->delTrabajador($trabajadorId)
                    ->ordenadoPorFecha();
                    
        if ($limite) {
            $query->limit($limite);
        }
        
        return $query->get();
    }

    public static function obtenerUltimoCambio(int $trabajadorId): ?self
    {
        return self::delTrabajador($trabajadorId)
                  ->ordenadoPorFecha()
                  ->first();
    }

    public static function contarPromociones(int $trabajadorId): int
    {
        return self::delTrabajador($trabajadorId)
                  ->promociones()
                  ->count();
    }

    public static function obtenerEstadisticas(int $trabajadorId): array
    {
        $historial = self::delTrabajador($trabajadorId)->get();
        
        return [
            'total_cambios' => $historial->count(),
            'promociones' => $historial->where('tipo_cambio', 'promocion')->count(),
            'transferencias' => $historial->where('tipo_cambio', 'transferencia')->count(),
            'aumentos_sueldo' => $historial->where('tipo_cambio', 'aumento_sueldo')->count(),
            'reclasificaciones' => $historial->where('tipo_cambio', 'reclasificacion')->count(),
            'ajustes_salariales' => $historial->where('tipo_cambio', 'ajuste_salarial')->count(),
            'ultimo_cambio' => $historial->sortByDesc('fecha_cambio')->first(),
            'primer_sueldo' => $historial->sortBy('fecha_cambio')->first()?->sueldo_nuevo,
            'sueldo_actual' => $historial->sortByDesc('fecha_cambio')->first()?->sueldo_nuevo,
            // ✅ CRECIMIENTO SALARIAL REMOVIDO
        ];
    }

    // ✅ MÉTODO REMOVIDO - calcularCrecimientoSalarial ya no es necesario
}