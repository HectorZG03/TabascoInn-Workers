<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
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
        'observaciones',
        'estatus_permiso',
        'ruta_pdf',
    ];
    
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ TIPOS DE PERMISO (Categorías que van al select)
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

    // ✅ ESTATUS DEL PERMISO
    const ESTATUS_PERMISO = [
        'activo' => 'Activo',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ];

    // ✅ RELACIONES
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    // ✅ ACCESSORS PRINCIPALES
    public function getDiasDePermisoAttribute()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) return null;
        return Carbon::parse($this->fecha_inicio)->diffInDays(Carbon::parse($this->fecha_fin)) + 1;
    }

    public function getDiasRestantesAttribute(): int
    {
        if ($this->estaVencido() || !$this->estaActivo()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }
    
    public function getDiasVencidosAttribute(): int
    {
        if (!$this->estaVencido()) return 0;
        return (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay());
    }

    public function getTipoPermisoTextoAttribute()
    {
        return self::TIPOS_PERMISO[$this->tipo_permiso] ?? ucfirst($this->tipo_permiso);
    }

    public function getEstatusPermisoTextoAttribute()
    {
        return self::ESTATUS_PERMISO[$this->estatus_permiso] ?? 'Estatus Desconocido';
    }

    public function getMotivoTextoAttribute()
    {
        // Como el motivo ahora es texto libre, devolver tal como está
        return $this->motivo;
    }

    public function getColorTipoAttribute()
    {
        $colores = [
            'Vacaciones' => 'success',
            'Licencia Médica' => 'danger',
            'Licencia por Maternidad' => 'info',
            'Licencia por Paternidad' => 'info', 
            'Permiso Personal' => 'warning',
            'Permiso por Estudios' => 'primary',
            'Permiso por Capacitación' => 'primary',
            'Licencia sin Goce de Sueldo' => 'secondary',
            'Permiso Especial' => 'dark',
            'Permiso por Duelo' => 'dark',
            'Permiso por Matrimonio' => 'success',
            'Incapacidad Temporal' => 'danger',
        ];
        
        return $colores[$this->tipo_permiso] ?? 'secondary';
    }

    public function getColorEstatusAttribute()
    {
        $colores = [
            'activo' => 'success',
            'finalizado' => 'primary',
            'cancelado' => 'secondary',
        ];
        
        return $colores[$this->estatus_permiso] ?? 'secondary';
    }

    public function getIconoTipoAttribute()
    {
        $iconos = [
            'Vacaciones' => 'bi-sun',
            'Licencia Médica' => 'bi-heart-pulse',
            'Licencia por Maternidad' => 'bi-person-hearts',
            'Licencia por Paternidad' => 'bi-person-hearts', 
            'Permiso Personal' => 'bi-person',
            'Permiso por Estudios' => 'bi-mortarboard',
            'Permiso por Capacitación' => 'bi-book',
            'Licencia sin Goce de Sueldo' => 'bi-dash-circle',
            'Permiso Especial' => 'bi-star',
            'Permiso por Duelo' => 'bi-heart',
            'Permiso por Matrimonio' => 'bi-suit-heart',
            'Incapacidad Temporal' => 'bi-bandaid',
        ];
        
        return $iconos[$this->tipo_permiso] ?? 'bi-calendar-event';
    }

    public function getIconoEstatusAttribute()
    {
        $iconos = [
            'activo' => 'bi-check-circle',
            'finalizado' => 'bi-check-square',
            'cancelado' => 'bi-x-circle',
        ];
        
        return $iconos[$this->estatus_permiso] ?? 'bi-question-circle';
    }

    // ✅ ACCESSORS PARA PDF
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

    public function eliminarPdf(): bool
    {
        if (!$this->ruta_pdf) {
            return true;
        }
        
        try {
            if (Storage::disk('public')->exists($this->ruta_pdf)) {
                $eliminado = Storage::disk('public')->delete($this->ruta_pdf);
                
                if ($eliminado) {
                    Log::info('PDF de permiso eliminado exitosamente', [
                        'permiso_id' => $this->id_permiso,
                        'ruta_eliminada' => $this->ruta_pdf,
                    ]);
                } else {
                    Log::warning('No se pudo eliminar el PDF del storage', [
                        'permiso_id' => $this->id_permiso,
                        'ruta_intentada' => $this->ruta_pdf,
                    ]);
                    return false;
                }
            }
            
            // Limpiar ruta en BD
            $this->update(['ruta_pdf' => null]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar PDF de permiso', [
                'permiso_id' => $this->id_permiso,
                'ruta_pdf' => $this->ruta_pdf,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    // ✅ SCOPES
    public function scopeActivos($query)
    {
        return $query->where('estatus_permiso', 'activo');
    }
    
    public function scopeFinalizados($query)
    {
        return $query->where('estatus_permiso', 'finalizado');
    }
    
    public function scopeCancelados($query)
    {
        return $query->where('estatus_permiso', 'cancelado');
    }
    
    public function scopeVencidos($query)
    {
        return $query->where('fecha_fin', '<', now())
                    ->where('estatus_permiso', 'activo');
    }
    
    public function scopeVigentes($query)
    {
        return $query->where('fecha_fin', '>=', now())
                    ->where('estatus_permiso', 'activo');
    }
    
    public function scopeDelMes($query, $año = null, $mes = null)
    {
        $año = $año ?? now()->year;
        $mes = $mes ?? now()->month;
        
        return $query->whereYear('fecha_inicio', $año)
                    ->whereMonth('fecha_inicio', $mes);
    }
    
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_permiso', $tipo);
    }

    public function scopePorMotivo($query, $motivo)
    {
        return $query->where('motivo', 'like', "%{$motivo}%");
    }

    public function scopePorEstatus($query, $estatus)
    {
        return $query->where('estatus_permiso', $estatus);
    }

    // ✅ MÉTODOS DE VERIFICACIÓN
    public function estaActivo()
    {
        return $this->estatus_permiso === 'activo';
    }
    
    public function estaFinalizado()
    {
        return $this->estatus_permiso === 'finalizado';
    }
    
    public function estaCancelado()
    {
        return $this->estatus_permiso === 'cancelado';
    }
    
    public function estaVencido()
    {
        return $this->estaActivo() && $this->fecha_fin < now()->startOfDay();
    }

    public function estaVigente()
    {
        return $this->estaActivo() && $this->fecha_fin >= now()->startOfDay();
    }

    public function puedeFinalizarse()
    {
        return $this->estaActivo();
    }

    public function puedeCancelarse()
    {
        return $this->estaActivo();
    }

    // ✅ MÉTODOS ESTÁTICOS
    public static function getTiposDisponibles()
    {
        return self::TIPOS_PERMISO;
    }

    public static function getEstatusValidos()
    {
        return array_keys(self::ESTATUS_PERMISO);
    }

    // ✅ MÉTODOS DE ACCIÓN
    public function finalizar($observacionAdicional = null)
    {
        $observaciones = $this->observaciones;
        
        if ($observacionAdicional) {
            $observaciones .= "\n" . $observacionAdicional;
        }
        
        $observaciones .= "\n[FINALIZADO EL " . now()->format('d/m/Y H:i') . "]";
        
        return $this->update([
            'estatus_permiso' => 'finalizado',
            'observaciones' => $observaciones,
        ]);
    }

    public function cancelar($observacionAdicional = null)
    {
        $observaciones = $this->observaciones;
        
        if ($observacionAdicional) {
            $observaciones .= "\n" . $observacionAdicional;
        }
        
        $observaciones .= "\n[CANCELADO EL " . now()->format('d/m/Y H:i') . "]";
        
        return $this->update([
            'estatus_permiso' => 'cancelado',
            'observaciones' => $observaciones,
        ]);
    }

    // ✅ RESUMEN COMPLETO
    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_permiso,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'tipo_permiso' => $this->tipo_permiso,
            'tipo_permiso_texto' => $this->tipo_permiso_texto,
            'motivo' => $this->motivo,
            'motivo_texto' => $this->motivo_texto,
            'estatus_permiso' => $this->estatus_permiso,
            'estatus_permiso_texto' => $this->estatus_permiso_texto,
            'fecha_inicio' => $this->fecha_inicio->format('d/m/Y'),
            'fecha_fin' => $this->fecha_fin->format('d/m/Y'),
            'dias_permiso' => $this->dias_de_permiso,
            'esta_activo' => $this->estaActivo(),
            'esta_vigente' => $this->estaVigente(),
            'esta_vencido' => $this->estaVencido(),
            'dias_restantes' => $this->dias_restantes,
            'puede_finalizarse' => $this->puedeFinalizarse(),
            'puede_cancelarse' => $this->puedeCancelarse(),
            'color_tipo' => $this->color_tipo,
            'color_estatus' => $this->color_estatus,
            'icono_tipo' => $this->icono_tipo,
            'icono_estatus' => $this->icono_estatus,
            'observaciones' => $this->observaciones,
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'tiene_pdf' => $this->tiene_pdf,
            'url_pdf' => $this->url_pdf,
            'nombre_pdf' => $this->nombre_pdf,
        ];
    }

    // ✅ BUSCAR CONFLICTOS
    public static function buscarConflictos($trabajadorId, $fechaInicio, $fechaFin, $excluirPermisoId = null)
    {
        $query = self::where('id_trabajador', $trabajadorId)
            ->where('estatus_permiso', 'activo')
            ->where(function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                  ->orWhere(function($subQ) use ($fechaInicio, $fechaFin) {
                      $subQ->where('fecha_inicio', '<=', $fechaInicio)
                           ->where('fecha_fin', '>=', $fechaFin);
                  });
            });
            
        if ($excluirPermisoId) {
            $query->where('id_permiso', '!=', $excluirPermisoId);
        }
        
        return $query->get();
    }
}