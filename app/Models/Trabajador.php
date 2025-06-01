<?php

namespace App\Models;  

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Trabajador extends Model  
{
    use HasFactory;

    protected $table = 'trabajadores';
    protected $primaryKey = 'id_trabajador';  
    public $incrementing = true;
    protected $keyType = 'int'; 
    public $timestamps = true;

    protected $fillable = [
        'id_baja',
        'nombre_trabajador',
        'ape_pat',
        'ape_mat',
        'fecha_nacimiento',
        'curp',
        'rfc',
        'no_nss',
        'telefono',
        'correo',
        'direccion',
        'fecha_ingreso',
        'antiguedad',
        'estatus',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'antiguedad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA ESTADOS
    const ESTADOS_PRINCIPALES = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
    ];

    const ESTADOS_AUSENCIAS = [
        'vacaciones' => 'Vacaciones',
        'incapacidad_medica' => 'Incapacidad Médica',
        'licencia_maternidad' => 'Licencia por Maternidad',
        'licencia_paternidad' => 'Licencia por Paternidad',
        'licencia_sin_goce' => 'Licencia sin Goce de Sueldo',
        'permiso_especial' => 'Permiso Especial',
    ];

    const ESTADOS_ADMINISTRATIVOS = [
        'suspendido' => 'Suspendido',
    ];

    const TODOS_ESTADOS = [
        // Estados principales
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        // Ausencias temporales
        'vacaciones' => 'Vacaciones',
        'incapacidad_medica' => 'Incapacidad Médica',
        'licencia_maternidad' => 'Licencia por Maternidad',
        'licencia_paternidad' => 'Licencia por Paternidad',
        'licencia_sin_goce' => 'Licencia sin Goce de Sueldo',
        'permiso_especial' => 'Permiso Especial',
        // Administrativos
        'suspendido' => 'Suspendido',
    ];

    // Estados que permiten que el trabajador regrese
    const ESTADOS_TEMPORALES = [
        'vacaciones', 'incapacidad_medica', 'licencia_maternidad', 
        'licencia_paternidad', 'licencia_sin_goce', 'permiso_especial'
    ];

    // Estados que requieren acción administrativa
    const ESTADOS_CRITICOS = ['suspendido', 'inactivo'];

    // ✅ RELACIONES
    public function fichaTecnica()  
    {
        return $this->hasOne(FichaTecnica::class, 'id_trabajador', 'id_trabajador');
    }

    public function despido()
    {
        return $this->hasOne(Despidos::class, 'id_trabajador', 'id_trabajador');
    }

    public function documentos()
    {
        return $this->hasOne(DocumentoTrabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function categoria()
    {
        return $this->hasOneThrough(
            Categoria::class,  
            FichaTecnica::class,  
            'id_trabajador',
            'id_categoria',
            'id_trabajador',
            'id_categoria'
        );
    }

    // ✅ SCOPES ACTUALIZADOS PARA ENUM
    public function scopeActivos($query)  
    {
        return $query->where('estatus', 'activo');
    }

    public function scopeInactivos($query) 
    {
        return $query->where('estatus', 'inactivo');
    }

    public function scopeEnAusencia($query)
    {
        return $query->whereIn('estatus', self::ESTADOS_TEMPORALES);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estatus', 'activo');
    }

    public function scopeCriticos($query)
    {
        return $query->whereIn('estatus', self::ESTADOS_CRITICOS);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estatus', $estado);
    }

    public function scopeBuscarPorNombre($query, $nombre) 
    {
        return $query->where(function($q) use ($nombre) {
            $q->where('nombre_trabajador', 'like', "%{$nombre}%")
              ->orWhere('ape_pat', 'like', "%{$nombre}%")
              ->orWhere('ape_mat', 'like', "%{$nombre}%");
        });
    }

    public function scopePorArea($query, $areaId)
    {
        return $query->whereHas('fichaTecnica.categoria.area', function($q) use ($areaId) {
            $q->where('id_area', $areaId);
        });
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->whereHas('fichaTecnica.categoria', function($q) use ($categoriaId) {
            $q->where('id_categoria', $categoriaId);
        });
    }

    // ✅ ACCESSORS
    public function getNombreCompletoAttribute()  
    {
        return trim($this->nombre_trabajador . ' ' . $this->ape_pat . ' ' . $this->ape_mat);
    }

    public function getEdadAttribute()  
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        return Carbon::parse($this->fecha_nacimiento)->age;
    }

    public function getAntiguedadCalculadaAttribute()
    {
        if (!$this->fecha_ingreso) {
            return 0;
        }
        return (int) Carbon::parse($this->fecha_ingreso)->diffInYears(now());
    }

    // ✅ ACCESSORS PARA ESTADOS
    public function getEstatusTextoAttribute()
    {
        return self::TODOS_ESTADOS[$this->estatus] ?? 'Estado Desconocido';
    }

    public function getEstatusColorAttribute()
    {
        $colores = [
            'activo' => 'success',
            'inactivo' => 'secondary',
            'vacaciones' => 'info',
            'incapacidad_medica' => 'warning',
            'licencia_maternidad' => 'primary',
            'licencia_paternidad' => 'primary',
            'licencia_sin_goce' => 'warning',
            'permiso_especial' => 'info',
            'suspendido' => 'danger',
        ];

        return $colores[$this->estatus] ?? 'secondary';
    }

    public function getEstatusIconoAttribute()
    {
        $iconos = [
            'activo' => 'bi-person-check',
            'inactivo' => 'bi-person-x',
            'vacaciones' => 'bi-calendar-heart',
            'incapacidad_medica' => 'bi-heart-pulse',
            'licencia_maternidad' => 'bi-person-hearts',
            'licencia_paternidad' => 'bi-person-hearts',
            'licencia_sin_goce' => 'bi-pause-circle',
            'permiso_especial' => 'bi-clock',
            'suspendido' => 'bi-exclamation-triangle',
        ];

        return $iconos[$this->estatus] ?? 'bi-question-circle';
    }

    // ✅ MÉTODOS DE VERIFICACIÓN DE ESTADO
    public function estaActivo()
    {
        return $this->estatus === 'activo';
    }

    public function estaInactivo()
    {
        return $this->estatus === 'inactivo';
    }

    public function estaEnAusencia()
    {
        return in_array($this->estatus, self::ESTADOS_TEMPORALES);
    }

    public function estaDisponible()
    {
        return $this->estatus === 'activo';
    }

    public function requiereAtencion()
    {
        return in_array($this->estatus, self::ESTADOS_CRITICOS);
    }

    public function puedeRegresar()
    {
        return in_array($this->estatus, self::ESTADOS_TEMPORALES);
    }

    // ✅ OTROS ACCESSORS EXISTENTES
    public function getPorcentajeDocumentosAttribute()
    {
        return $this->documentos ? $this->documentos->porcentaje_completado : 0;
    }

    public function getEsNuevoAttribute()
    {
        return $this->created_at && $this->created_at->diffInDays(now()) <= 30;
    }

    public function getSueldoDiarioAttribute()
    {
        return $this->fichaTecnica ? $this->fichaTecnica->sueldo_diarios : 0;
    }

    public function getNombreAreaAttribute()
    {
        return $this->fichaTecnica && $this->fichaTecnica->categoria && $this->fichaTecnica->categoria->area 
            ? $this->fichaTecnica->categoria->area->nombre_area 
            : 'Sin área';
    }

    public function getNombreCategoriaAttribute()
    {
        return $this->fichaTecnica && $this->fichaTecnica->categoria 
            ? $this->fichaTecnica->categoria->nombre_categoria 
            : 'Sin categoría';
    }

    // ✅ MUTATORS
    public function setCurpAttribute($value)
    {
        $this->attributes['curp'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setCorreoAttribute($value)
    {
        $this->attributes['correo'] = $value ? strtolower(trim($value)) : null;
    }

    public function setNombreTrabajadorAttribute($value)
    {
        $this->attributes['nombre_trabajador'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    public function setApePatAttribute($value)
    {
        $this->attributes['ape_pat'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    public function setApeMatAttribute($value)
    {
        $this->attributes['ape_mat'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    public function setAntiguedadAttribute($value)
    {
        $this->attributes['antiguedad'] = (int) $value;
    }

    // ✅ MÉTODOS PARA CAMBIAR ESTADO
    public function activar()
    {
        $this->estatus = 'activo';
        $this->id_baja = null;
        return $this->save();
    }

    public function darDeBaja($motivoId = null)
    {
        $this->estatus = 'inactivo';
        $this->id_baja = $motivoId;
        return $this->save();
    }

    public function suspender($motivoId = null)
    {
        $this->estatus = 'suspendido';
        $this->id_baja = $motivoId;
        return $this->save();
    }

    public function enviarVacaciones()
    {
        $this->estatus = 'vacaciones';
        return $this->save();
    }

    public function ponerEnIncapacidad()
    {
        $this->estatus = 'incapacidad_medica';
        return $this->save();
    }

    public function cambiarEstado($nuevoEstado, $motivoId = null)
    {
        if (!array_key_exists($nuevoEstado, self::TODOS_ESTADOS)) {
            throw new \InvalidArgumentException("Estado '{$nuevoEstado}' no es válido");
        }

        $this->estatus = $nuevoEstado;
        
        // Si es un estado crítico, asociar motivo de baja
        if (in_array($nuevoEstado, self::ESTADOS_CRITICOS)) {
            $this->id_baja = $motivoId;
        } else {
            $this->id_baja = null;
        }

        return $this->save();
    }

    // ✅ MÉTODO DE UTILIDAD
    public function actualizarAntiguedad()
    {
        if ($this->fecha_ingreso) {
            $this->antiguedad = $this->antiguedad_calculada;
            $this->save();
        }
    }

    public function getResumenAttribute()
    {
        return [
            'id' => $this->id_trabajador,
            'nombre_completo' => $this->nombre_completo,
            'edad' => $this->edad,
            'antiguedad' => $this->antiguedad,
            'estatus' => $this->estatus,
            'estatus_texto' => $this->estatus_texto,
            'estatus_color' => $this->estatus_color,
            'categoria' => $this->nombre_categoria,
            'area' => $this->nombre_area,
            'sueldo_diario' => $this->sueldo_diario,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'porcentaje_documentos' => $this->porcentaje_documentos,
            'es_nuevo' => $this->es_nuevo,
            'puede_regresar' => $this->puedeRegresar(),
            'requiere_atencion' => $this->requiereAtencion(),
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
        ];
    }
}