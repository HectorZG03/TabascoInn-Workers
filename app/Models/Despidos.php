<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Despidos extends Model
{
    use HasFactory;

    protected $table = 'despidos';

    protected $primaryKey = 'id_baja';

    public $incrementing = true;

    protected $keyType = 'int';

    // ✅ HABILITAMOS TIMESTAMPS para auditoria
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'fecha_baja',
        'motivo',
        'condicion_salida',
        'observaciones',
        'estado',
        'fecha_cancelacion',
        'motivo_cancelacion',
        'cancelado_por',
        'tipo_baja',
        'fecha_reintegro',
        'creado_por',
        'actualizado_por'
    ];

    protected $dates = [
        'fecha_baja',
        'fecha_cancelacion',
        'fecha_reintegro', // <--- AGREGAR AQUÍ
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'fecha_baja' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'fecha_reintegro' => 'datetime', // <--- AGREGAR AQUÍ
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA ESTADOS
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_CANCELADO = 'cancelado';
    const TIPO_TEMPORAL = 'temporal';
    const TIPO_DEFINITIVA = 'definitiva';

    const TIPOS_BAJA = [
        self::TIPO_TEMPORAL => 'Temporal',
        self::TIPO_DEFINITIVA => 'Definitiva'
    ];

    
    const ESTADOS = [
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_CANCELADO => 'Cancelado',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function usuarioCancelacion()
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelado_por', 'id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Solo despidos activos (no cancelados)
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Solo despidos cancelados
     */
    public function scopeCancelados($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADO);
    }

    /**
     * Despidos por fecha específica
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_baja', $fecha);
    }

    /**
     * Despidos entre fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_baja', [$fechaInicio, $fechaFin]);
    }

    /**
     * Buscar por motivo
     */
    public function scopePorMotivo($query, $motivo)
    {
        return $query->where('motivo', 'LIKE', "%{$motivo}%");
    }

    /**
     * Despidos del mes actual (solo activos)
     */
    public function scopeDelMesActual($query)
    {
        return $query->activos()
                     ->whereMonth('fecha_baja', Carbon::now()->month)
                     ->whereYear('fecha_baja', Carbon::now()->year);
    }

    /**
     * Despidos del año actual (solo activos)
     */
    public function scopeDelAnoActual($query)
    {
        return $query->activos()
                     ->whereYear('fecha_baja', Carbon::now()->year);
    }

    /**
     * Historial completo de un trabajador
     */
    public function scopeHistorialTrabajador($query, $trabajadorId)
    {
        return $query->where('id_trabajador', $trabajadorId)
                     ->orderBy('fecha_baja', 'desc');
    }

    // ========================================
    // ATRIBUTOS CALCULADOS
    // ========================================

    public function getDiasDesdeEjecutadoAttribute()
    {
        if (!$this->fecha_baja) {
            return null;
        }
        return Carbon::parse($this->fecha_baja)->diffInDays(Carbon::now());
    }

    public function getEsActivoAttribute()
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function getEsCanceladoAttribute()
    {
        return $this->estado === self::ESTADO_CANCELADO;
    }

    public function getEstadoTextoAttribute()
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }

    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_baja,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'fecha_baja' => $this->fecha_baja?->format('d/m/Y'),
            'motivo' => $this->motivo,
            'condicion_salida' => $this->condicion_salida,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado_texto,
            'dias_transcurridos' => $this->dias_desde_ejecutado,
            'fecha_cancelacion' => $this->fecha_cancelacion?->format('d/m/Y'),
        ];
    }

    // ========================================
    // MÉTODOS DE UTILIDAD
    // ========================================

    /**
     * Cancelar/revertir el despido
     */
    public function cancelar($motivo = null, $usuarioId = null)
    {
        $this->update([
            'estado' => self::ESTADO_CANCELADO,
            'fecha_cancelacion' => now(),
            'motivo_cancelacion' => $motivo ?? 'Trabajador reactivado',
            'cancelado_por' => $usuarioId,
        ]);
    }

    /**
     * Verificar si puede ser cancelado
     */
    public function puedeSerCancelado()
    {
        return $this->es_activo;
    }

    public function esBajaVoluntaria()
    {
        $motivosVoluntarios = [
            'renuncia', 'dimisión', 'voluntaria', 'personal',
            'mejor oferta', 'cambio de trabajo'
        ];

        foreach ($motivosVoluntarios as $motivo) {
            if (stripos($this->motivo, $motivo) !== false) {
                return true;
            }
        }

        return $this->condicion_salida === 'Voluntaria';
    }

    public function esDespidoJustificado()
    {
        $motivosJustificados = [
            'falta grave', 'indisciplina', 'robo', 'fraude',
            'abandono', 'incumplimiento', 'negligencia'
        ];

        foreach ($motivosJustificados as $motivo) {
            if (stripos($this->motivo, $motivo) !== false) {
                return true;
            }
        }

        return in_array($this->condicion_salida, ['Despido con Causa', 'Abandono de Trabajo']);
    }

    public function getTipoBajaInferidaAttribute()
    {
        if ($this->esBajaVoluntaria()) {
            return 'Voluntaria';
        } elseif ($this->esDespidoJustificado()) {
            return 'Despido Justificado';
        } else {
            return 'Otro';
        }
    }

    // ========================================
    // MÉTODOS ESTÁTICOS PARA ESTADÍSTICAS
    // ========================================

    /**
     * Estadísticas por mes (solo activos)
     */
    public static function estadisticasPorMes($año = null)
    {
        $año = $año ?? Carbon::now()->year;

        return static::activos()
                    ->selectRaw('MONTH(fecha_baja) as mes, COUNT(*) as total')
                    ->whereYear('fecha_baja', $año)
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
    }

    /**
     * Estadísticas por motivo (solo activos)
     */
    public static function estadisticasPorMotivo($año = null)
    {
        $año = $año ?? Carbon::now()->year;

        return static::activos()
                    ->selectRaw('motivo, COUNT(*) as total')
                    ->whereYear('fecha_baja', $año)
                    ->groupBy('motivo')
                    ->orderBy('total', 'desc')
                    ->get();
    }

    /**
     * Contar despidos por estado
     */
    public static function contarPorEstado()
    {
        return static::selectRaw('estado, COUNT(*) as total')
                    ->groupBy('estado')
                    ->pluck('total', 'estado')
                    ->toArray();
    }

    /**
     * Obtener trabajadores con múltiples bajas
     */
    public static function trabajadoresConMultiplesBajas()
    {
        return static::selectRaw('id_trabajador, COUNT(*) as total_bajas')
                    ->groupBy('id_trabajador')
                    ->having('total_bajas', '>', 1)
                    ->orderBy('total_bajas', 'desc')
                    ->get();
    }

      // ✅ RELACIÓN CON USUARIO QUE CREÓ LA BAJA


    // ✅ ACCESOR PARA TIPO DE BAJA FORMATEADO
    public function getTipoBajaTextoAttribute()
    {
        return self::TIPOS_BAJA[$this->tipo_baja] ?? 'Desconocido';
    }

    public function esTemporal()
    {
        return $this->tipo_baja === 'temporal';
    }

// ✅ CORREGIR LAS RELACIONES EN EL MODELO DESPIDOS (líneas 203-210)

    // ✅ RELACIÓN CON USUARIO QUE CREÓ LA BAJA
    public function creadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'creado_por');
    }

    // ✅ RELACIÓN CON USUARIO QUE ACTUALIZÓ
    public function actualizadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'actualizado_por');
    }
}
