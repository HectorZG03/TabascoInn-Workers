<?php

namespace App\Models\Traits\Trabajador;

trait TieneMutators
{
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
}
