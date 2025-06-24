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
        'ruta_pdf', // ✅ NUEVO CAMPO
    ];
    
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA TIPOS DE PERMISO
    const TIPOS_PERMISO = [
        'permiso' => 'Con Permiso',
        'suspendido' => 'Suspendido',
    ];

    // ✅ CONSTANTES PARA ESTATUS DEL PERMISO
    const ESTATUS_PERMISO = [
        'activo' => 'Activo',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ];

    // ✅ MOTIVOS PREDEFINIDOS PARA PERMISOS
    const MOTIVOS_PERMISO = [
        'vacaciones' => 'Vacaciones',
        'incapacidad_medica' => 'Incapacidad Médica',
        'licencia_maternidad' => 'Licencia por Maternidad',
        'licencia_paternidad' => 'Licencia por Paternidad',
        'licencia_sin_goce' => 'Licencia sin Goce de Sueldo',
        'permiso_especial' => 'Permiso Especial',
        'asuntos_personales' => 'Asuntos Personales',
        'emergencia_familiar' => 'Emergencia Familiar',
        'estudios' => 'Permiso por Estudios',
        'cita_medica' => 'Cita Médica',
        'tramites_oficiales' => 'Trámites Oficiales',
    ];

    // ✅ MOTIVOS PREDEFINIDOS PARA SUSPENSIONES
    const MOTIVOS_SUSPENSION = [
        'falta_disciplinaria' => 'Falta Disciplinaria',
        'incumplimiento_normas' => 'Incumplimiento de Normas',
        'investigacion_interna' => 'Investigación Interna',
        'ausencia_injustificada' => 'Ausencia Injustificada',
        'bajo_rendimiento' => 'Bajo Rendimiento',
        'conducta_inapropiada' => 'Conducta Inapropiada',
        'violacion_politicas' => 'Violación de Políticas',
        'proceso_administrativo' => 'Proceso Administrativo',
        'falta_grave' => 'Falta Grave',
        'insubordinacion' => 'Insubordinación',
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
    
    public function getDiasDesdeFinAttribute(): int
    {
        return (int) $this->fecha_fin->startOfDay()->diffInDays(now()->startOfDay());
    }

    public function getTipoPermisoTextoAttribute()
    {
        return self::TIPOS_PERMISO[$this->tipo_permiso] ?? 'Tipo Desconocido';
    }

    public function getEstatusPermisoTextoAttribute()
    {
        return self::ESTATUS_PERMISO[$this->estatus_permiso] ?? 'Estatus Desconocido';
    }

    public function getMotivoTextoAttribute()
    {
        // Buscar en motivos de permisos primero
        if (array_key_exists($this->motivo, self::MOTIVOS_PERMISO)) {
            return self::MOTIVOS_PERMISO[$this->motivo];
        }
        
        // Luego en motivos de suspensiones
        if (array_key_exists($this->motivo, self::MOTIVOS_SUSPENSION)) {
            return self::MOTIVOS_SUSPENSION[$this->motivo];
        }
        
        // Si no está predefinido, devolver el valor tal como está (personalizado)
        return ucfirst(str_replace('_', ' ', $this->motivo));
    }

    public function getColorPermisoAttribute()
    {
        $colores = [
            'permiso' => 'info',
            'suspendido' => 'danger',
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

    public function getIconoPermisoAttribute()
    {
        $iconos = [
            'permiso' => 'bi-calendar-event',
            'suspendido' => 'bi-exclamation-triangle',
        ];
        
        return $iconos[$this->tipo_permiso] ?? 'bi-question-circle';
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

    // ✅ NUEVOS ACCESSORS PARA PDF - CORREGIDOS SIN BARRAS INVERSAS
    
    /**
     * ✅ VERIFICAR SI TIENE PDF GENERADO
     */
    public function getTienePdfAttribute(): bool
    {
        return !empty($this->ruta_pdf) && Storage::disk('public')->exists($this->ruta_pdf);
    }

    /**
     * ✅ OBTENER URL PÚBLICA DEL PDF
     */
    public function getUrlPdfAttribute(): ?string
    {
        if (!$this->tiene_pdf) {
            return null;
        }
        
        return Storage::url($this->ruta_pdf);
    }

    /**
     * ✅ OBTENER NOMBRE DEL ARCHIVO PDF
     */
    public function getNombrePdfAttribute(): ?string
    {
        if (!$this->ruta_pdf) {
            return null;
        }
        
        return basename($this->ruta_pdf);
    }

    /**
     * ✅ OBTENER TAMAÑO DEL ARCHIVO PDF EN BYTES
     */
    public function getTamanoPdfAttribute(): ?int
    {
        if (!$this->tiene_pdf) {
            return null;
        }
        
        return Storage::size('public/' . $this->ruta_pdf);
    }

    /**
     * ✅ OBTENER TAMAÑO DEL ARCHIVO PDF FORMATEADO
     */
    public function getTamanoPdfFormateadoAttribute(): ?string
    {
        $tamano = $this->tamano_pdf;
        
        if (!$tamano) {
            return null;
        }
        
        if ($tamano < 1024) {
            return $tamano . ' B';
        } elseif ($tamano < 1048576) {
            return round($tamano / 1024, 2) . ' KB';
        } else {
            return round($tamano / 1048576, 2) . ' MB';
        }
    }

    /**
     * ✅ VERIFICAR SI EL PDF NECESITA REGENERARSE
     */
    public function pdfNecesitaRegeneracion(): bool
    {
        if (!$this->tiene_pdf) {
            return true;
        }
        
        // ✅ VERIFICAR SI EL PERMISO HA SIDO MODIFICADO DESPUÉS DE GENERAR EL PDF
        $fechaModificacion = Storage::lastModified('public/' . $this->ruta_pdf);
        return $this->updated_at->timestamp > $fechaModificacion;
    }

    /**
     * ✅ ELIMINAR PDF DEL STORAGE
     */
    public function eliminarPdf(): bool
    {
        if (!$this->ruta_pdf) {
            return true;
        }
        
        try {
            if (Storage::exists('public/' . $this->ruta_pdf)) {
                Storage::delete('public/' . $this->ruta_pdf);
            }
            
            $this->update(['ruta_pdf' => null]);
            
            Log::info('PDF de permiso eliminado', [
                'permiso_id' => $this->id_permiso,
                'ruta_anterior' => $this->ruta_pdf,
            ]);
            
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

    // ✅ SCOPES ACTUALIZADOS CON ESTATUS_PERMISO
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_permiso', $tipo);
    }
    
    public function scopePermisos($query)
    {
        return $query->where('tipo_permiso', 'permiso');
    }
    
    public function scopeSuspensiones($query)
    {
        return $query->where('tipo_permiso', 'suspendido');
    }
    
    // ✅ SCOPES POR ESTATUS DEL PERMISO
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
                    ->where('estatus_permiso', 'activo'); // Solo activos vencidos
    }
    
    public function scopeVigentes($query)
    {
        return $query->where('fecha_fin', '>=', now())
                    ->where('estatus_permiso', 'activo'); // Solo activos vigentes
    }
    
    public function scopeDelMes($query, $año = null, $mes = null)
    {
        $año = $año ?? now()->year;
        $mes = $mes ?? now()->month;
        
        return $query->whereYear('fecha_inicio', $año)
                    ->whereMonth('fecha_inicio', $mes);
    }
    
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
    }

    public function scopePorMotivo($query, $motivo)
    {
        return $query->where('motivo', $motivo);
    }

    public function scopePorEstatus($query, $estatus)
    {
        return $query->where('estatus_permiso', $estatus);
    }

    // ✅ MÉTODOS DE VERIFICACIÓN ACTUALIZADOS
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

    public function esPermiso()
    {
        return $this->tipo_permiso === 'permiso';
    }

    public function esSuspension()
    {
        return $this->tipo_permiso === 'suspendido';
    }

    public function puedeFinalizarse()
    {
        return $this->estaActivo();
    }

    public function puedeCancelarse()
    {
        return $this->estaActivo();
    }

    public function diasRestantes()
    {
        return $this->dias_restantes;
    }

    // ✅ MÉTODOS ESTÁTICOS
    public static function getMotivosPorTipo($tipoPermiso)
    {
        switch ($tipoPermiso) {
            case 'permiso':
                return self::MOTIVOS_PERMISO;
            case 'suspendido':
                return self::MOTIVOS_SUSPENSION;
            default:
                return [];
        }
    }

    public static function getTodosLosMotivos()
    {
        return array_merge(self::MOTIVOS_PERMISO, self::MOTIVOS_SUSPENSION);
    }

    public static function getEstatusValidos()
    {
        return array_keys(self::ESTATUS_PERMISO);
    }

    public static function getTiposValidos()
    {
        return array_keys(self::TIPOS_PERMISO);
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

    // ✅ MÉTODO RESUMEN ACTUALIZADO CON INFO DEL PDF
    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_permiso,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'tipo_permiso' => $this->tipo_permiso,
            'tipo_permiso_texto' => $this->tipo_permiso_texto,
            'estatus_permiso' => $this->estatus_permiso,
            'estatus_permiso_texto' => $this->estatus_permiso_texto,
            'motivo' => $this->motivo,
            'motivo_texto' => $this->motivo_texto,
            'fecha_inicio' => $this->fecha_inicio->format('d/m/Y'),
            'fecha_fin' => $this->fecha_fin->format('d/m/Y'),
            'dias_permiso' => $this->dias_de_permiso,
            'esta_activo' => $this->estaActivo(),
            'esta_vigente' => $this->estaVigente(),
            'esta_vencido' => $this->estaVencido(),
            'dias_restantes' => $this->dias_restantes,
            'puede_finalizarse' => $this->puedeFinalizarse(),
            'puede_cancelarse' => $this->puedeCancelarse(),
            'color_tipo' => $this->color_permiso,
            'color_estatus' => $this->color_estatus,
            'icono_tipo' => $this->icono_permiso,
            'icono_estatus' => $this->icono_estatus,
            'observaciones' => $this->observaciones,
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            // ✅ NUEVOS CAMPOS PARA PDF
            'tiene_pdf' => $this->tiene_pdf,
            'url_pdf' => $this->url_pdf,
            'nombre_pdf' => $this->nombre_pdf,
            'tamano_pdf' => $this->tamano_pdf_formateado,
            'pdf_necesita_regeneracion' => $this->pdfNecesitaRegeneracion(),
        ];
    }

    // ✅ MÉTODO PARA VALIDAR MOTIVO SEGÚN TIPO
    public function validarMotivoParaTipo()
    {
        $motivosValidos = self::getMotivosPorTipo($this->tipo_permiso);
        return array_key_exists($this->motivo, $motivosValidos);
    }

    // ✅ MÉTODO PARA OBTENER CONFLICTOS
    public static function buscarConflictos($trabajadorId, $fechaInicio, $fechaFin, $excluirPermisoId = null)
    {
        $query = self::where('id_trabajador', $trabajadorId)
            ->where('estatus_permiso', 'activo') // Solo activos
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