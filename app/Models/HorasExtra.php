<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HorasExtra extends Model
{
    use HasFactory;
    
    protected $table = 'horas_extra';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'tipo',
        'horas',
        'fecha',
        'descripcion',
        'autorizado_por',
    ];
    
    protected $casts = [
        'horas' => 'decimal:2', // ✅ CAMBIO: Decimal con 2 decimales
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA TIPOS
    const TIPO_ACUMULADAS = 'acumuladas';
    const TIPO_DEVUELTAS = 'devueltas';
    
    const TIPOS_DISPONIBLES = [
        self::TIPO_ACUMULADAS => 'Horas Acumuladas',
        self::TIPO_DEVUELTAS => 'Horas Devueltas',
    ];

    // ✅ RELACIONES
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    // ✅ SCOPES BÁSICOS
    public function scopeAcumuladas($query)
    {
        return $query->where('tipo', self::TIPO_ACUMULADAS);
    }
    
    public function scopeDevueltas($query)
    {
        return $query->where('tipo', self::TIPO_DEVUELTAS);
    }
    
    public function scopeDelTrabajador($query, $trabajadorId)
    {
        return $query->where('id_trabajador', $trabajadorId);
    }

    // ✅ ACCESSORS ACTUALIZADOS PARA DECIMALES
    public function getTipoTextoAttribute(): string
    {
        return self::TIPOS_DISPONIBLES[$this->tipo] ?? 'Tipo Desconocido';
    }
    
    public function getHorasFormateadasAttribute(): string
    {
        // ✅ ACTUALIZADO: Formatear decimales correctamente
        $horas = (float) $this->horas;
        
        if ($horas == 1) {
            return '1 hora';
        } elseif ($horas < 1) {
            // Para fracciones menores a 1 hora
            $minutos = $horas * 60;
            return number_format($horas, 1) . ' horas (' . round($minutos) . ' min)';
        } else {
            // Mostrar decimales solo si es necesario
            return ($horas == floor($horas)) ? 
                number_format($horas, 0) . ' horas' : 
                number_format($horas, 1) . ' horas';
        }
    }
    
    public function getColorTipoAttribute(): string
    {
        return match($this->tipo) {
            self::TIPO_ACUMULADAS => 'success',
            self::TIPO_DEVUELTAS => 'warning',
            default => 'secondary'
        };
    }
    
    public function getIconoTipoAttribute(): string
    {
        return match($this->tipo) {
            self::TIPO_ACUMULADAS => 'bi-plus-circle',
            self::TIPO_DEVUELTAS => 'bi-dash-circle',
            default => 'bi-clock'
        };
    }
    
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha->format('d/m/Y');
    }
    
    public function getResumenAttribute(): array
    {
        return [
            'id' => $this->id,
            'trabajador' => $this->trabajador->nombre_completo ?? 'Sin trabajador',
            'tipo' => $this->tipo,
            'tipo_texto' => $this->tipo_texto,
            'horas' => $this->horas,
            'horas_formateadas' => $this->horas_formateadas,
            'fecha' => $this->fecha_formateada,
            'descripcion' => $this->descripcion,
            'autorizado_por' => $this->autorizado_por,
            'color_tipo' => $this->color_tipo,
            'icono_tipo' => $this->icono_tipo,
            'fecha_registro' => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    // ✅ MÉTODOS ESTÁTICOS ACTUALIZADOS PARA DECIMALES
    
    /**
     * Calcular saldo actual de horas extra de un trabajador
     */
    public static function calcularSaldo(int $trabajadorId): float
    {
        $acumuladas = self::delTrabajador($trabajadorId)
                         ->acumuladas()
                         ->sum('horas');
                         
        $devueltas = self::delTrabajador($trabajadorId)
                        ->devueltas()
                        ->sum('horas');
                        
        return round((float) ($acumuladas - $devueltas), 2); // ✅ Resultado decimal con 2 decimales
    }
    
    /**
     * Verificar si un trabajador puede devolver cierta cantidad de horas
     */
    public static function puedeDevolver(int $trabajadorId, float $horas): bool
    {
        $saldoActual = self::calcularSaldo($trabajadorId);
        return $saldoActual >= $horas;
    }
    
    /**
     * Registrar horas acumuladas
     */
    public static function registrarAcumuladas(
        int $trabajadorId, 
        float $horas, // ✅ CAMBIO: Float en lugar de int
        string $fecha, 
        ?string $descripcion = null
    ): self {
        return self::create([
            'id_trabajador' => $trabajadorId,
            'tipo' => self::TIPO_ACUMULADAS,
            'horas' => round($horas, 2), // ✅ Redondear a 2 decimales
            'fecha' => $fecha,
            'descripcion' => $descripcion,
            'autorizado_por' => Auth::user()->email ?? 'Sistema',
        ]);
    }
    
    /**
     * Registrar horas devueltas (con validación de saldo)
     */
    public static function registrarDevueltas(
        int $trabajadorId, 
        float $horas, // ✅ CAMBIO: Float en lugar de int
        string $fecha, 
        ?string $descripcion = null
    ): self {
        if (!self::puedeDevolver($trabajadorId, $horas)) {
            throw new \Exception(
                'No hay suficientes horas acumuladas. Saldo disponible: ' . 
                self::calcularSaldo($trabajadorId) . ' horas'
            );
        }
        
        return self::create([
            'id_trabajador' => $trabajadorId,
            'tipo' => self::TIPO_DEVUELTAS,
            'horas' => round($horas, 2), // ✅ Redondear a 2 decimales
            'fecha' => $fecha,
            'descripcion' => $descripcion,
            'autorizado_por' => Auth::user()->email ?? 'Sistema',
        ]);
    }

    // ✅ MÉTODOS DE UTILIDAD BÁSICOS
    
    public function esAcumulada(): bool
    {
        return $this->tipo === self::TIPO_ACUMULADAS;
    }
    
    public function esDevuelta(): bool
    {
        return $this->tipo === self::TIPO_DEVUELTAS;
    }

    // ✅ VALIDACIÓN ACTUALIZADA PARA DECIMALES
    protected static function booted()
    {
        static::saving(function ($registro) {
            if ($registro->horas <= 0) {
                throw new \InvalidArgumentException('La cantidad de horas debe ser mayor a 0');
            }
            
            // ✅ CAMBIO: Redondear a 2 decimales en lugar de convertir a entero
            $registro->horas = round((float) $registro->horas, 2);
            
            // ✅ VALIDACIÓN ADICIONAL: Máximo 24 horas por registro
            if ($registro->horas > 24) {
                throw new \InvalidArgumentException('No se pueden registrar más de 24 horas por día');
            }
        });
    }
}