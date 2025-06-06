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

    // ✅ CONSTANTE UTILIZADA EN CONTROLADORES
    public const TODOS_ESTADOS = [
        'activo' => 'Activo',
        'permiso' => 'Con Permiso Temporal',
        'suspendido' => 'Suspendido',
        'prueba' => 'Período de Prueba',
        'inactivo' => 'Inactivo'
    ];

    // ✅ RELACIONES UTILIZADAS EN CONTROLADORES
    
    /**
     * Relación con FichaTecnica (muy utilizada)
     */
    public function fichaTecnica()  
    {
        return $this->hasOne(FichaTecnica::class, 'id_trabajador', 'id_trabajador');
    }

    /**
     * Relación con Despidos (utilizada en DespidosController)
     */
    public function despido()
    {
        return $this->hasOne(Despidos::class, 'id_trabajador', 'id_trabajador');
    }

    /**
     * Relación con DocumentoTrabajador (utilizada en ActPerfilTrabajadorController)
     */
    public function documentos()
    {
        return $this->hasOne(DocumentoTrabajador::class, 'id_trabajador', 'id_trabajador');
    }

    /**
     * Relación con HistorialPromocion (utilizada en ActPerfilTrabajadorController)
     */
    public function historialPromociones()
    {
        return $this->hasMany(HistorialPromocion::class, 'id_trabajador', 'id_trabajador')
                    ->orderBy('fecha_cambio', 'desc');
    }

    /**
     * Relación con TODOS los despidos - historial completo
     */
    public function despidos()
    {
        return $this->hasMany(Despidos::class, 'id_trabajador', 'id_trabajador');
    }

    /**
     * Relación con ContactoEmergencia (creada en TrabajadorController)
     */
    public function contactosEmergencia()
    {
        return $this->hasMany(ContactoEmergencia::class, 'id_trabajador', 'id_trabajador');
    }

    /**
     * Relación con permisos activos (referenciada en puedeAsignarPermiso)
     */
    public function permisosActivos()
    {
        return $this->hasMany(PermisosLaborales::class, 'id_trabajador', 'id_trabajador')
                    ->where('estatus_permiso', 'activo');
    }

    // ✅ ACCESSORS UTILIZADOS

    /**
     * Nombre completo del trabajador (muy utilizado)
     */
    public function getNombreCompletoAttribute()  
    {
        return trim($this->nombre_trabajador . ' ' . $this->ape_pat . ' ' . $this->ape_mat);
    }

    /**
     * Edad calculada (utilizada en estadísticas)
     */
    public function getEdadAttribute()  
    {
        return $this->fecha_nacimiento ? Carbon::parse($this->fecha_nacimiento)->age : null;
    }

    /**
     * Texto del estatus para mostrar en vistas
     */
    public function getEstatusTextoAttribute() 
    { 
        return self::TODOS_ESTADOS[$this->estatus] ?? 'Estado Desconocido';
    }

    /**
     * Color del badge para el estatus (usado en vistas)
     */
    public function getEstatusColorAttribute() 
    { 
        $colores = [
            'activo' => 'success',
            'permiso' => 'info', 
            'suspendido' => 'danger',
            'prueba' => 'warning',
            'inactivo' => 'secondary'
        ];
        
        return $colores[$this->estatus] ?? 'secondary';
    }

    /**
     * Ícono para el estatus (usado en vistas)
     */
    public function getEstatusIconoAttribute() 
    { 
        $iconos = [
            'activo' => 'bi-person-check',
            'permiso' => 'bi-calendar-event',
            'suspendido' => 'bi-exclamation-triangle', 
            'prueba' => 'bi-clock-history',
            'inactivo' => 'bi-person-x'
        ];
        
        return $iconos[$this->estatus] ?? 'bi-person';
    }

    // ✅ MÉTODOS DE VERIFICACIÓN UTILIZADOS EN CONTROLADORES Y VISTAS

    /**
     * Verificar si el trabajador está activo (usado en DespidosController)
     */
    public function estaActivo(): bool 
    { 
        return $this->estatus === 'activo'; 
    }

    /**
     * Verificar si el trabajador está inactivo (usado en DespidosController)
     */
    public function estaInactivo(): bool 
    { 
        return $this->estatus === 'inactivo'; 
    }

    /**
     * Verificar si tiene despido activo (usado en DespidosController)
     * Nota: Había un error tipográfico "tieneSpidoActivo" en DespidosController
     */
    public function tieneDespidoActivo(): bool
    {
        return $this->despidos()->where('estado', 'activo')->exists();
    }

    /**
     * Verificar si puede recibir permisos (usado en PermisosLaboralesController)
     */
    public function puedeAsignarPermiso(): bool
    {
        return $this->estaActivo() && !$this->permisosActivos()->exists();
    }

    /**
     * Verificar si puede regresar de un estado temporal (usado en vistas)
     */
    public function puedeRegresar(): bool
    {
        return in_array($this->estatus, ['permiso', 'suspendido']);
    }

    /**
     * Verificar si el estado requiere atención (usado en vistas)
     */
    public function requiereAtencion(): bool
    {
        return in_array($this->estatus, ['suspendido', 'inactivo']);
    }

    // ✅ MUTATORS PARA DATOS LIMPIOS

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

    // ✅ MÉTODOS AUXILIARES PARA DESPIDOS (utilizados en el propio modelo)

    /**
     * Contar total de despidos del trabajador
     */
    public function totalDespidos()
    {
        return $this->despidos()->count();
    }

    /**
     * Verificar si tiene múltiples bajas históricas (usado en vistas/controladores)
     */
    public function tieneMultiplesBajas(): bool
    {
        return $this->totalDespidos() > 1;
    }

    /**
     * Obtener resumen de historial de bajas
     */
    public function getResumenBajasAttribute()
    {
        if ($this->totalDespidos() === 0) {
            return 'Sin historial de bajas';
        }

        $activos = $this->despidos()->where('estado', 'activo')->count();
        $cancelados = $this->despidos()->where('estado', 'cancelado')->count();
        $total = $this->totalDespidos();

        $resumen = "Total: {$total}";
        
        if ($activos > 0) {
            $resumen .= " | Activas: {$activos}";
        }
        
        if ($cancelados > 0) {
            $resumen .= " | Canceladas: {$cancelados}";
        }

        return $resumen;
    }
}