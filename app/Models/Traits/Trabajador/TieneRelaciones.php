<?php

namespace App\Models\Traits\Trabajador;

use App\Models\{FichaTecnica, Despidos, DocumentoTrabajador, HistorialPromocion, ContactoEmergencia, PermisosLaborales};

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

}
