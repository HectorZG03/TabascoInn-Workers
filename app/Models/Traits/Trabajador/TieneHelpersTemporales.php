<?php

namespace App\Models\Traits\Trabajador;

use Carbon\Carbon;

trait TieneHelpersTemporales
{
    public function getEdadAttribute()  
    {
        return $this->fecha_nacimiento ? Carbon::parse($this->fecha_nacimiento)->age : null;
    }

    public function getAntiguedadTextoAttribute()
    {
        return match($this->antiguedad) {
            0 => 'Nuevo',
            1 => '1 año', 
            default => "{$this->antiguedad} años"
        };
    }

    public function getEsNuevoAttribute(): bool
    {
        return $this->antiguedad === 0;
    }

    public function getResumenBajasAttribute()
    {
        $total = $this->despidos()->count();
        if ($total === 0) return 'Sin historial de bajas';

        $activos = $this->despidos()->where('estado', 'activo')->count();
        $cancelados = $this->despidos()->where('estado', 'cancelado')->count();

        $resumen = "Total: {$total}";
        if ($activos > 0) $resumen .= " | Activas: {$activos}";
        if ($cancelados > 0) $resumen .= " | Canceladas: {$cancelados}";

        return $resumen;
    }

    public function totalDespidos()
    {
        return $this->despidos()->count();
    }

    public function despidosActivos()
    {
        return $this->despidos()->where('estado', 'activo')->count();
    }

    public function despidosCancelados()
    {
        return $this->despidos()->where('estado', 'cancelado')->count();
    }
}
