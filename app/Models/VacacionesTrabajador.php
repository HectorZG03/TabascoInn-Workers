<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class VacacionesTrabajador extends Model
{
    use HasFactory;

    protected $table = 'vacaciones_trabajadores';
    protected $primaryKey = 'id_vacacion';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'creado_por',
        'periodo_vacacional',
        'año_correspondiente',
        'dias_correspondientes',
        'dias_solicitados',
        'dias_disfrutados',
        'dias_restantes',
        'fecha_inicio',
        'fecha_fin',
        'fecha_reintegro',
        'estado',
        'observaciones',
        'motivo_finalizacion',
        'justificada_por_documento',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_reintegro' => 'date',
        'año_correspondiente' => 'integer',
        'dias_correspondientes' => 'integer',
        'dias_solicitados' => 'integer',
        'dias_disfrutados' => 'integer',
        'dias_restantes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'justificada_por_documento' => 'boolean',
    ];

    // ✅ CONSTANTES
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'activa' => 'Activa',
        'finalizada' => 'Finalizada'
    ];

    public const ESTADOS_COLORES = [
        'pendiente' => 'warning',
        'activa' => 'success',
        'finalizada' => 'secondary'
    ];

    public const ESTADOS_ICONOS = [
        'pendiente' => 'bi-clock-history',
        'activa' => 'bi-calendar-check',
        'finalizada' => 'bi-check-circle'
    ];

    // ✅ DÍAS DE VACACIONES SEGÚN LFT MÉXICO
    // ✅ DÍAS DE VACACIONES SEGÚN LFT MÉXICO (2023+)
    public const DIAS_POR_ANTIGUEDAD = [
        0  => 6,   // Menos de 1 año (se asignan manualmente si aplica)
        1  => 12,
        2  => 14,
        3  => 16,
        4  => 18,
        5  => 20,
        6  => 22,
        11 => 24,
        16 => 26,
        21 => 28,
        26 => 30,
        31 => 32,
    ];


    // ✅ RELACIONES
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ✅ ACCESSORS
    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }

    public function getEstadoColorAttribute(): string
    {
        return self::ESTADOS_COLORES[$this->estado] ?? 'secondary';
    }

    public function getEstadoIconoAttribute(): string
    {
        return self::ESTADOS_ICONOS[$this->estado] ?? 'bi-question';
    }

    public function getDuracionDiasAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }

    public function getDiasTranscurridosAttribute(): int
    {
        if ($this->estado !== 'activa') {
            return 0;
        }
        
        $hoy = Carbon::today();
        if ($hoy->lt($this->fecha_inicio)) {
            return 0;
        }
        
        $fechaFin = $hoy->gt($this->fecha_fin) ? $this->fecha_fin : $hoy;
        return $this->fecha_inicio->diffInDays($fechaFin) + 1;
    }

    public function getPorcentajeCompletadoAttribute(): float
    {
        if ($this->dias_solicitados === 0) {
            return 0;
        }
        
        return ($this->dias_disfrutados / $this->dias_solicitados) * 100;
    }

    // ✅ MÉTODOS DE ESTADO
    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function esActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function esFinalizada(): bool
    {
        return $this->estado === 'finalizada';
    }

    public function puedeIniciar(): bool
    {
        return $this->esPendiente() && 
               //arbon::today()->gte($this->fecha_inicio) &&
               !$this->trabajador->tieneVacacionesActivas();
    }

    public function puedeFinalizarse(): bool
    {
        return $this->esActiva();
    }

    // ✅ MÉTODOS ESTÁTICOS
    public static function calcularDiasCorrespondientes(int $antiguedadAños): int
    {
        foreach (array_reverse(self::DIAS_POR_ANTIGUEDAD, true) as $años => $dias) {
            if ($antiguedadAños >= $años) {
                return $dias;
            }
        }
        
        return 6; // Por defecto, 6 días
    }

    public static function generarPeriodoVacacional(int $año): string
    {
        return $año . '-' . ($año + 1);
    }

    // ✅ SCOPES
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'finalizada');
    }

    public function scopePorTrabajador($query, int $idTrabajador)
    {
        return $query->where('id_trabajador', $idTrabajador);
    }

    public function scopePorPeriodo($query, string $periodo)
    {
        return $query->where('periodo_vacacional', $periodo);
    }

    // ✅ MÉTODOS DE ACCIÓN
    public function iniciar(int $usuarioId = null): bool
    {
        if (!$this->puedeIniciar()) {
            return false;
        }

        // Actualizar estado del trabajador
        $this->trabajador->update(['estatus' => 'vacaciones']);

        // Actualizar vacación
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => Carbon::today() // Ajustar si es necesario
        ]);

        return true;
    }

    public function finalizar(?string $motivo = null, int $usuarioId = null): bool
    {
        if (!$this->puedeFinalizarse()) {
            return false;
        }

        // Calcular días efectivamente disfrutados
        $diasDisfrutados = $this->dias_transcurridos;

        // Actualizar estado del trabajador a activo
        $this->trabajador->update(['estatus' => 'activo']);

        // Actualizar vacación
        $this->update([
            'estado' => 'finalizada',
            'dias_disfrutados' => $diasDisfrutados,
            'dias_restantes' => $this->dias_solicitados - $diasDisfrutados,
            'fecha_reintegro' => Carbon::today(),
            'motivo_finalizacion' => $motivo
        ]);

        return true;
    }

    public function documentos(): BelongsToMany
    {
        return $this->belongsToMany(
            DocumentoVacaciones::class,
            'documento_vacacion_vacaciones',
            'vacacion_id',
            'documento_vacacion_id',
            'id_vacacion',
            'id'
        )->withTimestamps();
    }

    // ✅ NUEVOS ACCESSORS - AGREGAR AL FINAL DEL MODELO
    public function getTieneDocumentoAttribute(): bool
    {
        return $this->documentos()->exists();
    }

    public function getDocumentoActivoAttribute(): ?DocumentoVacaciones
    {
        return $this->documentos()->latest()->first();
    }

    // ✅ NUEVOS MÉTODOS - AGREGAR AL FINAL DEL MODELO
    public function asociarDocumento(DocumentoVacaciones $documento): void
    {
        $this->documentos()->attach($documento->id);
        $this->update(['justificada_por_documento' => true]);
    }

    public function desasociarDocumento(DocumentoVacaciones $documento): void
    {
        $this->documentos()->detach($documento->id);
        
        // Si no tiene más documentos, actualizar el campo
        if (!$this->documentos()->exists()) {
            $this->update(['justificada_por_documento' => false]);
        }
    }
}