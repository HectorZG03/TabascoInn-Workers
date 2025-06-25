<?php

namespace App\Models\Traits\Trabajador;
use Illuminate\Database\Eloquent\Relations\HasMany;


use App\Models\{FichaTecnica, Despidos, DocumentoTrabajador, HistorialPromocion, ContactoEmergencia, PermisosLaborales, ContratoTrabajador, HorasExtra};

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

    // ✅ AGREGAR ESTA RELACIÓN Y MÉTODOS AL MODELO TRABAJADOR EXISTENTE



    // ✅ RELACIÓN CON HORAS EXTRA (agregar en la sección de relaciones)
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

    // ✅ MÉTODOS PARA GESTIÓN DE HORAS EXTRA (agregar en el modelo Trabajador)

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




}
