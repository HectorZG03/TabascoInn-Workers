<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PermisosLaborales extends Model
{
    use HasFactory;
    
    protected $table = 'permisos_laborales';
    protected $primaryKey = 'id_permiso';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'tipo_permiso',
        'motivo',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'es_por_horas',
        'observaciones',
        'estatus_permiso',
        'ruta_pdf',
        // ✅ NUEVOS CAMPOS PARA CANCELACIÓN
        'motivo_cancelacion',
        'fecha_cancelacion',
        'cancelado_por',
    ];
    
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'es_por_horas' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // ✅ CAST PARA FECHA DE CANCELACIÓN
        'fecha_cancelacion' => 'datetime',
    ];

    // ✅ CONSTANTES ACTUALIZADAS
    const TIPOS_PERMISO = [
        'Vacaciones' => 'Vacaciones',
        'Licencia Médica' => 'Licencia Médica',
        'Licencia por Maternidad' => 'Licencia por Maternidad',
        'Licencia por Paternidad' => 'Licencia por Paternidad',
        'Permiso Personal' => 'Permiso Personal',
        'Permiso por Estudios' => 'Permiso por Estudios',
        'Permiso por Capacitación' => 'Permiso por Capacitación',
        'Licencia sin Goce de Sueldo' => 'Licencia sin Goce de Sueldo',
        'Permiso Especial' => 'Permiso Especial',
        'Permiso por Duelo' => 'Permiso por Duelo',
        'Permiso por Matrimonio' => 'Permiso por Matrimonio',
        'Incapacidad Temporal' => 'Incapacidad Temporal',
    ];

    const ESTATUS_PERMISO = [
        'activo' => 'Activo',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ];

    // ✅ RELACIÓN UTILIZADA
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    // ✅ ACCESSORS UTILIZADOS EN LAS VISTAS
    public function getDiasDePermisoAttribute(): int
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) return 0;
        return Carbon::parse($this->fecha_inicio)->diffInDays(Carbon::parse($this->fecha_fin)) + 1;
    }

    public function getDiasRestantesAttribute(): int
    {
        if ($this->estatus_permiso !== 'activo') return 0;
        if ($this->fecha_fin < now()->startOfDay()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }
    
    public function getDiasVencidosAttribute(): int
    {
        if ($this->estatus_permiso !== 'activo') return 0;
        if ($this->fecha_fin >= now()->startOfDay()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }

    public function getTipoPermisoTextoAttribute(): string
    {
        return self::TIPOS_PERMISO[$this->tipo_permiso] ?? ucfirst($this->tipo_permiso);
    }

    public function getEstatusPermisoTextoAttribute(): string
    {
        return self::ESTATUS_PERMISO[$this->estatus_permiso] ?? 'Estatus Desconocido';
    }

    public function getMotivoTextoAttribute(): string
    {
        return $this->motivo;
    }

    // ✅ NUEVO ACCESSOR PARA CANCELACIÓN
    public function getEstaCanceladoAttribute(): bool
    {
        return $this->estatus_permiso === 'cancelado';
    }

    public function getFechaCancelacionFormateadaAttribute(): ?string
    {
        return $this->fecha_cancelacion ? $this->fecha_cancelacion->format('d/m/Y H:i') : null;
    }

    // ✅ ACCESSORS PARA PDF (UTILIZADOS)
    public function getTienePdfAttribute(): bool
    {
        return !empty($this->ruta_pdf) && Storage::disk('public')->exists($this->ruta_pdf);
    }

    public function getUrlPdfAttribute(): ?string
    {
        if (!$this->tiene_pdf) {
            return null;
        }
        
        return Storage::url($this->ruta_pdf);
    }

    public function getNombrePdfAttribute(): ?string
    {
        if (!$this->ruta_pdf) {
            return null;
        }
        
        return basename($this->ruta_pdf);
    }

    // ✅ MÉTODO ESTÁTICO UTILIZADO
    public static function getTiposDisponibles(): array
    {
        return self::TIPOS_PERMISO;
    }

    // ✅ NUEVO MÉTODO PARA CANCELAR PERMISO
    public function cancelarPermiso(string $motivo, string $canceladoPor): bool
    {
        if ($this->estatus_permiso !== 'activo') {
            return false;
        }

        return $this->update([
            'estatus_permiso' => 'cancelado',
            'motivo_cancelacion' => $motivo,
            'fecha_cancelacion' => now(),
            'cancelado_por' => $canceladoPor,
        ]);
    }
}