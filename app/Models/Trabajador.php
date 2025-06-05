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

    // ✅ CONSTANTES ACTUALIZADAS PARA LOS 5 ESTADOS
    const ESTADOS_PRINCIPALES = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
    ];

    const ESTADOS_ESPECIALES = [
        'permiso' => 'Con Permiso',
        'suspendido' => 'Suspendido',
        'prueba' => 'En Período de Prueba', // ✅ CORREGIDO: era 'a_prueba'
    ];

    const TODOS_ESTADOS = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        'permiso' => 'Con Permiso',
        'suspendido' => 'Suspendido',
        'prueba' => 'En Período de Prueba', // ✅ CORREGIDO
    ];

    // Estados que permiten que el trabajador regrese automáticamente
    const ESTADOS_TEMPORALES = ['permiso'];

    // Estados que requieren acción administrativa
    const ESTADOS_CRITICOS = ['suspendido', 'inactivo'];

    // Estados de prueba
    const ESTADOS_PRUEBA = ['prueba']; // ✅ CORREGIDO

    // Estados que permiten asignar permisos
    const ESTADOS_PARA_PERMISOS = ['activo'];

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

    public function historialPromociones()
    {
        return $this->hasMany(HistorialPromocion::class, 'id_trabajador', 'id_trabajador')
                    ->orderBy('fecha_cambio', 'desc');
    }

    public function ultimaPromocion()
    {
        return $this->hasOne(HistorialPromocion::class, 'id_trabajador', 'id_trabajador')
                    ->latest('fecha_cambio');
    }

    public function promociones()
    {
        return $this->historialPromociones()
                    ->where('tipo_cambio', 'promocion');
    }

    // ✅ RELACIONES CON PERMISOS ACTUALIZADAS
    public function permisos()
    {
        return $this->hasMany(PermisosLaborales::class, 'id_trabajador', 'id_trabajador')
                    ->orderBy('created_at', 'desc');
    }

    public function permisosActivos()
    {
        return $this->hasMany(PermisosLaborales::class, 'id_trabajador', 'id_trabajador')
                    ->where('estatus_permiso', 'activo'); // ✅ USAR ESTATUS_PERMISO
    }

    public function permisoActual()
    {
        return $this->hasOne(PermisosLaborales::class, 'id_trabajador', 'id_trabajador')
                    ->where('estatus_permiso', 'activo') // ✅ SOLO ACTIVOS
                    ->latest('fecha_inicio');
    }

    public function historialPermisos()
    {
        return $this->hasMany(PermisosLaborales::class, 'id_trabajador', 'id_trabajador')
                    ->orderBy('created_at', 'desc');
    }

    // ✅ SCOPES ACTUALIZADOS
    public function scopeActivos($query)  
    {
        return $query->where('estatus', 'activo');
    }

    public function scopeInactivos($query) 
    {
        return $query->where('estatus', 'inactivo');
    }

    public function scopeConPermiso($query)
    {
        return $query->where('estatus', 'permiso');
    }

    public function scopeSuspendidos($query)
    {
        return $query->where('estatus', 'suspendido');
    }

    public function scopeEnPrueba($query)
    {
        return $query->where('estatus', 'prueba'); // ✅ CORREGIDO
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estatus', 'activo');
    }

    public function scopeCriticos($query)
    {
        return $query->whereIn('estatus', self::ESTADOS_CRITICOS);
    }

    public function scopeTemporales($query)
    {
        return $query->whereIn('estatus', self::ESTADOS_TEMPORALES);
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

    // ✅ ACCESSORS ACTUALIZADOS
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

    public function getEstatusTextoAttribute()
    {
        return self::TODOS_ESTADOS[$this->estatus] ?? 'Estado Desconocido';
    }

    public function getEstatusColorAttribute()
    {
        $colores = [
            'activo' => 'success',
            'inactivo' => 'secondary',
            'permiso' => 'info',
            'suspendido' => 'danger',
            'prueba' => 'warning', // ✅ CORREGIDO
        ];

        return $colores[$this->estatus] ?? 'secondary';
    }

    public function getEstatusIconoAttribute()
    {
        $iconos = [
            'activo' => 'bi-person-check',
            'inactivo' => 'bi-person-x',
            'permiso' => 'bi-calendar-event',
            'suspendido' => 'bi-exclamation-triangle',
            'prueba' => 'bi-clock-history', // ✅ CORREGIDO
        ];

        return $iconos[$this->estatus] ?? 'bi-question-circle';
    }

    // ✅ MÉTODOS DE VERIFICACIÓN DE ESTADO ACTUALIZADOS
    public function estaActivo()
    {
        return $this->estatus === 'activo';
    }

    public function estaInactivo()
    {
        return $this->estatus === 'inactivo';
    }

    public function tienePermiso()
    {
        return $this->estatus === 'permiso';
    }

    public function estaSuspendido()
    {
        return $this->estatus === 'suspendido';
    }

    public function estaEnPrueba()
    {
        return $this->estatus === 'prueba'; // ✅ CORREGIDO
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

    // ✅ MÉTODO PRINCIPAL CORREGIDO - CONSIDERA ESTATUS_PERMISO
    public function puedeAsignarPermiso()
    {
        // 1. El trabajador debe estar activo
        if (!$this->estaActivo()) {
            return false;
        }
        
        // 2. No debe tener permisos ACTIVOS
        $tienePermisoActivo = $this->permisosActivos()->exists();
        
        return !$tienePermisoActivo;
    }

    // ✅ MÉTODO ADICIONAL PARA VERIFICAR PERMISOS ESPECÍFICOS
    public function tienePermisoActivoEnFechas($fechaInicio, $fechaFin)
    {
        return $this->permisosActivos()
            ->where(function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                      ->orWhere(function($q) use ($fechaInicio, $fechaFin) {
                          $q->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                      });
            })->exists();
    }

    // ✅ ACCESSORS ADICIONALES PARA PERMISOS
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

    // ✅ MUTATORS EXISTENTES
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

    // ✅ MÉTODOS PARA CAMBIAR ESTADO ACTUALIZADOS
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

    public function darPermiso()
    {
        $this->estatus = 'permiso';
        $this->id_baja = null;
        return $this->save();
    }

    public function ponerEnPrueba()
    {
        $this->estatus = 'prueba'; // ✅ CORREGIDO
        $this->id_baja = null;
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

    // ✅ MÉTODOS DE UTILIDAD
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
            'puede_asignar_permiso' => $this->puedeAsignarPermiso(),
            'tiene_permiso_activo' => $this->permisosActivos()->exists(),
            'fecha_creacion' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
        ];
    }
}