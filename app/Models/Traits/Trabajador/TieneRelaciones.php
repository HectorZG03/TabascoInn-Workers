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
    VacacionesTrabajador,
    DocumentoVacaciones
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

    // ✅ MÉTODOS PARA GESTIÓN DE HORAS EXTRA ACTUALIZADOS PARA DECIMALES
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
        return (float) $this->horasExtraAcumuladas()->sum('horas');
    }

    /**
     * ✅ NUEVO: Obtener total de horas devueltas/compensadas
     */
    public function getTotalHorasDevueltasAttribute(): float
    {
        return (float) $this->horasExtraDevueltas()->sum('horas');
    }

    /**
     * ✅ NUEVO: Verificar si puede asignar horas extra
     */
    public function puedeAsignarHorasExtra(): bool
    {
        return $this->estaActivo() || $this->estaEnPrueba();
    }

    /**
     * ✅ NUEVO: Verificar si puede compensar horas extra
     */
    public function puedeCompensarHorasExtra(): bool
    {
        return ($this->estaActivo() || $this->estaEnPrueba()) && $this->saldo_horas_extra > 0;
    }

    /**
     * ✅ NUEVO: Obtener horas extra formateadas para mostrar
     */
    public function getSaldoHorasExtraFormateadoAttribute(): string
    {
        $saldo = $this->saldo_horas_extra;
        
        if ($saldo == 1) {
            return '1 hora';
        } elseif ($saldo < 1 && $saldo > 0) {
            $minutos = $saldo * 60;
            return number_format($saldo, 1) . ' horas (' . round($minutos) . ' min)';
        } else {
            return ($saldo == floor($saldo)) ? 
                number_format($saldo, 0) . ' horas' : 
                number_format($saldo, 1) . ' horas';
        }
    }

    /**
     * ✅ NUEVO: Obtener estadísticas de horas extra
     */
    public function getEstadisticasHorasExtraAttribute(): array
    {
        return [
            'total_acumuladas' => $this->total_horas_acumuladas,
            'total_devueltas' => $this->total_horas_devueltas,
            'saldo_actual' => $this->saldo_horas_extra,
            'total_registros' => $this->horasExtra()->count(),
            'ultimo_movimiento' => $this->horasExtra()
                ->latest('fecha')
                ->latest('created_at')
                ->first()?->fecha,
            'puede_asignar' => $this->puedeAsignarHorasExtra(),
            'puede_compensar' => $this->puedeCompensarHorasExtra()
        ];
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

    // ✅ NUEVAS RELACIONES - AGREGAR AL FINAL DEL TRAIT
    /**
     * Relación con documentos de amortización de vacaciones
     */
    public function documentosVacaciones(): HasMany
    {
        return $this->hasMany(DocumentoVacaciones::class, 'trabajador_id', 'id_trabajador')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Documentos de vacaciones recientes
     */
    public function documentosVacacionesRecientes(): HasMany
    {
        return $this->documentosVacaciones()
                    ->where('created_at', '>=', now()->subMonths(6));
    }

    // ✅ NUEVOS MÉTODOS DE UTILIDAD - AGREGAR AL FINAL DEL TRAIT
    /**
     * Verificar si tiene documentos de vacaciones
     */
    public function tieneDocumentosVacaciones(): bool
    {
        return $this->documentosVacaciones()->exists();
    }

    /**
     * Obtener el último documento de vacaciones
     */
    public function getUltimoDocumentoVacacionesAttribute(): ?DocumentoVacaciones
    {
        return $this->documentosVacaciones()->first();
    }

    /**
     * Contar documentos de vacaciones
     */
    public function getTotalDocumentosVacacionesAttribute(): int
    {
        return $this->documentosVacaciones()->count();
    }

    // ✅ NUEVOS MÉTODOS AUXILIARES PARA HORAS EXTRA

    /**
     * Registrar horas extra acumuladas desde el modelo Trabajador
     */
    public function asignarHorasExtra(float $horas, string $fecha, string $descripcion = null): HorasExtra
    {
        if (!$this->puedeAsignarHorasExtra()) {
            throw new \Exception('Este trabajador no puede tener horas extra asignadas en su estado actual: ' . $this->estatus_texto);
        }

        return HorasExtra::registrarAcumuladas($this->id_trabajador, $horas, $fecha, $descripcion);
    }

    /**
     * Compensar horas extra desde el modelo Trabajador
     */
    public function compensarHorasExtra(float $horas, string $fecha, string $descripcion = null): HorasExtra
    {
        if (!$this->puedeCompensarHorasExtra()) {
            throw new \Exception('Este trabajador no puede compensar horas extra en su estado actual o no tiene saldo disponible');
        }

        return HorasExtra::registrarDevueltas($this->id_trabajador, $horas, $fecha, $descripcion);
    }

    /**
     * Obtener historial de horas extra con filtros
     */
    public function obtenerHistorialHorasExtra(string $tipo = null, int $limite = null)
    {
        $query = $this->horasExtra()
                     ->orderBy('fecha', 'desc')
                     ->orderBy('created_at', 'desc');

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($limite) {
            $query->limit($limite);
        }

        return $query->get();
    }
}