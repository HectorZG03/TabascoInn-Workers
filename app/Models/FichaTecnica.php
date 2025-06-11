<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // ✅ Agregado


class FichaTecnica extends Model
{
    use HasFactory;

    protected $table = 'ficha_tecnica';
    protected $primaryKey = 'id_ficha';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_trabajador',
        'id_categoria',
        'sueldo_diarios',
        'formacion',
        'grado_estudios',
        // ✅ HORARIOS: Campos principales
        'hora_entrada',
        'hora_salida',
        // ✅ CALCULADOS: Se llenan automáticamente
        'horas_trabajo',
        'turno',
    ];

    // ✅ OPTIMIZADO PARA LARAVEL 12: Mejor casting
    protected $casts = [
        'sueldo_diarios' => 'decimal:2',
        'horas_trabajo' => 'decimal:2',
        // ✅ MEJOR: Cast de tiempo sin datetime para Laravel 12
        'hora_entrada' => 'datetime:H:i',
        'hora_salida' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES ACTUALIZADAS Y CLARIFICADAS
    public const TURNOS_DISPONIBLES = [
        'diurno' => 'Diurno (06:00 - 18:00)',
        'nocturno' => 'Nocturno (18:00 - 06:00)', 
        'mixto' => 'Mixto/Rotativo',
    ];

    // ✅ CONSTANTES PARA CLASIFICACIÓN DE TURNOS (usadas en controlador)
    public const HORARIO_DIURNO_INICIO = '06:00';
    public const HORARIO_DIURNO_FIN = '18:00';
    public const HORARIO_NOCTURNO_INICIO = '18:00';
    public const HORARIO_NOCTURNO_FIN = '06:00';

    // ✅ RANGOS DE HORAS VÁLIDAS
    public const HORAS_MINIMAS = 1;
    public const HORAS_MAXIMAS = 16;

    // ===== RELACIONES =====
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function area()
    {
        return $this->hasOneThrough(
            Area::class,
            Categoria::class,
            'id_categoria',
            'id_area',
            'id_categoria',
            'id_area'
        );
    }

    // ===== ACCESSORS MEJORADOS PARA LARAVEL 12 =====

    /**
     * ✅ OPTIMIZADO: Calcular horas trabajadas automáticamente
     */
    public function getHorasTrabajadasCalculadasAttribute()
    {
        if (!$this->hora_entrada || !$this->hora_salida) {
            return $this->horas_trabajo ?? 0;
        }

        try {
            $entrada = Carbon::parse($this->hora_entrada);
            $salida = Carbon::parse($this->hora_salida);
            
            // Si la salida es menor que la entrada, significa que cruza medianoche
            if ($salida->lte($entrada)) {
                $salida->addDay();
            }
            
            return round($entrada->diffInMinutes($salida) / 60, 2);
        } catch (\Exception $e) {
            \Log::warning('Error calculando horas trabajadas', [
                'ficha_id' => $this->id_ficha,
                'entrada' => $this->hora_entrada,
                'salida' => $this->hora_salida,
                'error' => $e->getMessage()
            ]);
            
            return $this->horas_trabajo ?? 0;
        }
    }

    /**
     * ✅ OPTIMIZADO: Calcular turno automáticamente basado en horarios
     */
    public function getTurnoCalculadoAttribute()
    {
        if (!$this->hora_entrada || !$this->hora_salida) {
            return $this->turno ?? 'mixto';
        }

        try {
            $entrada = Carbon::parse($this->hora_entrada);
            $salida = Carbon::parse($this->hora_salida);
            
            // Horarios de referencia
            $inicioMatutino = Carbon::parse(self::HORARIO_DIURNO_INICIO);
            $finMatutino = Carbon::parse(self::HORARIO_DIURNO_FIN);
            $inicioNocturno = Carbon::parse(self::HORARIO_NOCTURNO_INICIO);
            
            // Si cruza medianoche, es nocturno
            if ($salida->lte($entrada)) {
                return 'nocturno';
            }
            
            // Clasificar según horarios
            if ($entrada->gte($inicioMatutino) && $salida->lte($finMatutino)) {
                return 'diurno';
            } elseif ($entrada->gte($inicioNocturno) || $salida->lte($inicioMatutino)) {
                return 'nocturno';
            } else {
                return 'mixto';
            }
        } catch (\Exception $e) {
            \Log::warning('Error calculando turno', [
                'ficha_id' => $this->id_ficha,
                'entrada' => $this->hora_entrada,
                'salida' => $this->hora_salida,
                'error' => $e->getMessage()
            ]);
            
            return $this->turno ?? 'mixto';
        }
    }

    /**
     * ✅ MEJORADO: Obtener horario formateado con validación
     */
    public function getHorarioFormateadoAttribute()
    {
        if (!$this->hora_entrada || !$this->hora_salida) {
            return 'No especificado';
        }
        
        try {
            $entrada = Carbon::parse($this->hora_entrada)->format('H:i');
            $salida = Carbon::parse($this->hora_salida)->format('H:i');
            
            return "{$entrada} - {$salida}";
        } catch (\Exception $e) {
            return 'Formato inválido';
        }
    }

    /**
     * ✅ NUEVO: Obtener descripción del turno con horarios
     */
    public function getTurnoDescripcionAttribute()
    {
        $turno = $this->turno_calculado;
        $horario = $this->horario_formateado;
        
        return match($turno) {
            'diurno' => "Diurno ({$horario})",
            'nocturno' => "Nocturno ({$horario})",
            'mixto' => "Mixto ({$horario})",
            default => "Sin especificar ({$horario})"
        };
    }

    /**
     * ✅ NUEVO: Validar si el horario es válido
     */
    public function getEsHorarioValidoAttribute()
    {
        $horas = $this->horas_trabajadas_calculadas;
        return $horas >= self::HORAS_MINIMAS && $horas <= self::HORAS_MAXIMAS;
    }

    /**
     * ✅ NUEVO: Obtener sueldo formateado
     */
    public function getSueldoFormateadoAttribute()
    {
        return $this->sueldo_diarios ? '$' . number_format($this->sueldo_diarios, 2) : '$0.00';
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * ✅ NUEVO: Calcular horas entre dos horarios (método estático)
     */
    public static function calcularHorasEntre($horaEntrada, $horaSalida)
    {
        try {
            $entrada = Carbon::parse($horaEntrada);
            $salida = Carbon::parse($horaSalida);
            
            if ($salida->lte($entrada)) {
                $salida->addDay();
            }
            
            return round($entrada->diffInMinutes($salida) / 60, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ✅ NUEVO: Determinar turno según horarios (método estático)
     */
    public static function determinarTurno($horaEntrada, $horaSalida)
    {
        try {
            $entrada = Carbon::parse($horaEntrada);
            $salida = Carbon::parse($horaSalida);
            
            $inicioMatutino = Carbon::parse(self::HORARIO_DIURNO_INICIO);
            $finMatutino = Carbon::parse(self::HORARIO_DIURNO_FIN);
            $inicioNocturno = Carbon::parse(self::HORARIO_NOCTURNO_INICIO);
            
            if ($salida->lte($entrada)) {
                return 'nocturno';
            }
            
            if ($entrada->gte($inicioMatutino) && $salida->lte($finMatutino)) {
                return 'diurno';
            } elseif ($entrada->gte($inicioNocturno) || $salida->lte($inicioMatutino)) {
                return 'nocturno';
            } else {
                return 'mixto';
            }
        } catch (\Exception $e) {
            return 'mixto';
        }
    }

    // ===== SCOPES PARA LARAVEL 12 =====

    /**
     * ✅ NUEVO: Scope para filtrar por turno
     */
    public function scopeTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }

    /**
     * ✅ NUEVO: Scope para filtrar por rango de horas
     */
    public function scopeHorasEntre($query, $minHoras, $maxHoras)
    {
        return $query->whereBetween('horas_trabajo', [$minHoras, $maxHoras]);
    }

    /**
     * ✅ NUEVO: Scope para horarios válidos
     */
    public function scopeHorariosValidos($query)
    {
        return $query->whereBetween('horas_trabajo', [self::HORAS_MINIMAS, self::HORAS_MAXIMAS]);
    }
}