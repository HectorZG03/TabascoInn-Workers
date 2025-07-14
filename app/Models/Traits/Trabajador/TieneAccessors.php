<?php


namespace App\Models\Traits\Trabajador;

trait TieneAccessors
{
    public function getNombreCompletoAttribute()  
    {
        return trim($this->nombre_trabajador . ' ' . $this->ape_pat . ' ' . $this->ape_mat);
    }

    public function getEstatusTextoAttribute() 
    { 
        return match ($this->estatus) {
            'activo' => 'Activo',
            'permiso' => 'Permiso',
            'suspendido' => 'Suspendido',
            'prueba' => 'En Prueba',
            'inactivo' => 'Inactivo',
            'vacaciones' => 'Vacaciones',
            default => 'Estado Desconocido',
        };

    }

    public function getEstatusColorAttribute() 
    { 
        $colores = [
            'activo' => 'success',
            'permiso' => 'info', 
            'suspendido' => 'danger',
            'prueba' => 'warning',
            'inactivo' => 'secondary',
            'vacaciones' => 'primary',
        ];
        
        return $colores[$this->estatus] ?? 'secondary';
    }

    public function getEstatusIconoAttribute() 
    { 
        $iconos = [
            'activo' => 'bi-person-check',
            'permiso' => 'bi-calendar-event',
            'suspendido' => 'bi-exclamation-triangle', 
            'prueba' => 'bi-clock-history',
            'inactivo' => 'bi-person-x',
            'vacaciones' => 'bi-calendar-heart',
        ];
        
        return $iconos[$this->estatus] ?? 'bi-person';
    }
}
