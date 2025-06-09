<?php

namespace App\Models\Traits\Trabajador;

trait TieneLogicaEstados
{
    public function estaActivo(): bool 
    { 
        return $this->estatus === 'activo'; 
    }

    public function estaInactivo(): bool 
    { 
        return $this->estatus === 'inactivo'; 
    }

    public function tienePermiso(): bool 
    { 
        return $this->estatus === 'permiso'; 
    }

    public function estaSuspendido(): bool 
    { 
        return $this->estatus === 'suspendido'; 
    }

    public function estaEnPrueba(): bool 
    { 
        return $this->estatus === 'prueba'; 
    }

    public function tieneDespidoActivo(): bool
    {
        return $this->despidos()->where('estado', 'activo')->exists();
    }

    public function puedeAsignarPermiso(): bool
    {
        return $this->estaActivo() && !$this->permisosActivos()->exists();
    }

    public function puedeRegresar(): bool
    {
        return in_array($this->estatus, ['permiso', 'suspendido']);
    }

    public function requiereAtencion(): bool
    {
        return in_array($this->estatus, ['suspendido', 'inactivo']);
    }

    public function tieneMultiplesBajas(): bool
    {
        return $this->despidos()->count() > 1;
    }
}
