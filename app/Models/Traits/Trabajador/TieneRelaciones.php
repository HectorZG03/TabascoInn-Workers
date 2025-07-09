<?php

namespace App\Models\Traits\Trabajador;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\{
    FichaTecnica, 
    Despidos, 
    DocumentoTrabajador, 
    HistorialPromocion, 
    ContactoEmergencia, 
    PermisosLaborales, 
    ContratoTrabajador, 
    HorasExtra,
    VacacionesTrabajador  // ✅ NUEVA IMPORTACIÓN
};

trait TieneRelaciones
{
    public function fichaTecnica()  
    {
        return $this->hasOne(FichaTecnica::class, 'id_trabajador');
    }

    public function despido()
    {
        return $this->hasOne(Despidos::class, 'id_trabajador');
    }

    public function documentos()
    {
        return $this->hasOne(DocumentoTrabajador::class, 'id_trabajador');
    }

    public function historialPromociones()
    {
        return $this->hasMany(HistorialPromocion::class, 'id_trabajador')->orderBy('fecha_cambio', 'desc');
    }

    public function despidos()
    {
        return $this->hasMany(Despidos::class, 'id_trabajador');
    }

    public function contactosEmergencia()
    {
        return $this->hasMany(ContactoEmergencia::class, 'id_trabajador');
    }

    public function permisosActivos()
    {
        return $this->hasMany(PermisosLaborales::class, 'id_trabajador')->where('estatus_permiso', 'activo');
    }

    public function contratos()
    {
        return $this->hasMany(ContratoTrabajador::class, 'id_trabajador');
    }

    // ✅ RELACIÓN CON HORAS EXTRA
    public function horasExtra(): HasMany
    {
        return $this->hasMany(HorasExtra::class, 'id_trabajador', 'id_trabajador');
    }

    public function horasExtraAcumuladas(): HasMany
    {
        return $this->horasExtra()->acumuladas();
    }

    public function horasExtraDevueltas(): HasMany
    {
        return $this->horasExtra()->devueltas();
    }

    // ✅ NUEVAS RELACIONES DE VACACIONES
    public function vacaciones(): HasMany
    {
        return $this->hasMany(VacacionesTrabajador::class, 'id_trabajador', 'id_trabajador')
                   ->orderBy('created_at', 'desc');
    }

    public function vacacionesActivas(): HasMany
    {
        return $this->vacaciones()->activas();
    }

    public function vacacionesPendientes(): HasMany
    {
        return $this->vacaciones()->pendientes();
    }

    public function vacacionesFinalizadas(): HasMany
    {
        return $this->vacaciones()->finalizadas();
    }

    // ✅ MÉTODOS PARA GESTIÓN DE HORAS EXTRA (existentes)
    /**
     * Obtener saldo actual de horas extra
     */
    public function getSaldoHorasExtraAttribute(): float
    {
        return HorasExtra::calcularSaldo($this->id_trabajador);
    }

    /**
     * Obtener total de horas acumuladas
     */
    public function getTotalHorasAcumuladasAttribute(): float
    {
        return $this->horasExtraAcumuladas()->sum('horas');
    }

    // ✅ NUEVOS MÉTODOS PARA VACACIONES
    /**
     * Verificar si tiene vacaciones activas
     */
    public function tieneVacacionesActivas(): bool
    {
        return $this->vacacionesActivas()->exists();
    }

    /**
     * Verificar si tiene vacaciones pendientes
     */
    public function tieneVacacionesPendientes(): bool
    {
        return $this->vacacionesPendientes()->exists();
    }

    /**
     * Obtener la vacación actual (activa)
     */
    public function getVacacionActualAttribute(): ?VacacionesTrabajador
    {
        return $this->vacacionesActivas()->first();
    }

    /**
     * Contar despidos activos
     */
    public function despidosActivos(): int
    {
        return $this->despidos()->where('estado', 'activo')->count();
    }
}