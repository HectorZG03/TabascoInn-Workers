<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        // ✅ NUEVOS: Días laborables y descanso
        'dias_laborables',
        'dias_descanso', 
        'horas_semanales',
        // ✅ NUEVOS: Beneficiario principal (simplificado)
        'beneficiario_nombre',
        'beneficiario_parentesco',
    ];

    // ✅ OPTIMIZADO PARA LARAVEL 12: Mejor casting
    protected $casts = [
        'sueldo_diarios' => 'decimal:2',
        'horas_trabajo' => 'decimal:2',
        'horas_semanales' => 'decimal:2',
        // ✅ MEJOR: Cast de tiempo sin datetime para Laravel 12
        'hora_entrada' => 'datetime:H:i',
        'hora_salida' => 'datetime:H:i',
        // ✅ NUEVOS: JSON para días
        'dias_laborables' => 'array',
        'dias_descanso' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES ACTUALIZADAS Y CLARIFICADAS
    public const TURNOS_DISPONIBLES = [
        'diurno' => 'Diurno (06:00 - 18:00)',
        'nocturno' => 'Nocturno (18:00 - 06:00)', 
        'mixto' => 'Mixto/Rotativo',
    ];

    // ✅ NUEVOS: Días de la semana disponibles
    public const DIAS_SEMANA = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    // ✅ NUEVOS: Horarios de trabajo más comunes
    public const HORARIOS_COMUNES = [
        'tiempo_completo' => ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'],
        'seis_dias' => ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'],
        'rotativo' => ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'],
    ];

    // ✅ NUEVOS: Parentescos válidos para beneficiarios
    public const PARENTESCOS_BENEFICIARIO = [
        'esposo' => 'Esposo',
        'esposa' => 'Esposa',
        'hijo' => 'Hijo',
        'hija' => 'Hija',
        'padre' => 'Padre',
        'madre' => 'Madre',
        'hermano' => 'Hermano',
        'hermana' => 'Hermana',
        'abuelo' => 'Abuelo',
        'abuela' => 'Abuela',
        'otro' => 'Otro',
    ];

    // ✅ CONSTANTES PARA CLASIFICACIÓN DE TURNOS (usadas en controlador)
    public const HORARIO_DIURNO_INICIO = '06:00';
    public const HORARIO_DIURNO_FIN = '18:00';
    public const HORARIO_NOCTURNO_INICIO = '18:00';
    public const HORARIO_NOCTURNO_FIN = '06:00';

    // ✅ RANGOS DE HORAS VÁLIDAS
    public const HORAS_MINIMAS = 1;
    public const HORAS_MAXIMAS = 16;
    public const HORAS_SEMANALES_MAXIMAS = 112; // 16 horas x 7 días

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
            Log::warning('Error calculando horas trabajadas', [
                'ficha_id' => $this->id_ficha,
                'entrada' => $this->hora_entrada,
                'salida' => $this->hora_salida,
                'error' => $e->getMessage()
            ]);
            
            return $this->horas_trabajo ?? 0;
        }
    }

    /**
     * ✅ NUEVO: Calcular horas semanales automáticamente
     */
    public function getHorasSemanalesCalculadasAttribute()
    {
        $horasDiarias = $this->horas_trabajadas_calculadas;
        $diasLaborables = $this->dias_laborables ?? [];
        
        return round($horasDiarias * count($diasLaborables), 2);
    }

    /**
     * ✅ NUEVO: Obtener días laborables formateados
     */
    public function getDiasLaborablesTextoAttribute()
    {
        if (!$this->dias_laborables) {
            return 'No especificado';
        }

        $dias = collect($this->dias_laborables)->map(function($dia) {
            return self::DIAS_SEMANA[$dia] ?? $dia;
        });

        return $dias->join(', ');
    }

    /**
     * ✅ NUEVO: Obtener días de descanso formateados
     */
    public function getDiasDescansoTextoAttribute()
    {
        if (!$this->dias_descanso) {
            return 'No especificado';
        }

        $dias = collect($this->dias_descanso)->map(function($dia) {
            return self::DIAS_SEMANA[$dia] ?? $dia;
        });

        return $dias->join(', ');
    }

    /**
     * ✅ NUEVO: Obtener información completa del beneficiario (simplificado)
     */
    public function getBeneficiarioCompletoAttribute()
    {
        if (!$this->beneficiario_nombre) {
            return 'No especificado';
        }

        $info = $this->beneficiario_nombre;
        
        if ($this->beneficiario_parentesco) {
            $parentesco = self::PARENTESCOS_BENEFICIARIO[$this->beneficiario_parentesco] ?? $this->beneficiario_parentesco;
            $info .= " ({$parentesco})";
        }

        return $info;
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
            Log::warning('Error calculando turno', [
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
     * ✅ NUEVO: Obtener descripción completa del horario
     */
    public function getHorarioCompletoAttribute()
    {
        $horario = $this->horario_formateado;
        $diasLaborables = $this->dias_laborables_texto;
        $horasSemanales = $this->horas_semanales_calculadas;
        
        return "{$horario} | {$diasLaborables} | {$horasSemanales}h/sem";
    }

    /**
     * ✅ NUEVO: Validar si los días laborables son válidos
     */
    public function getEsDiasValidosAttribute()
    {
        $diasLaborables = $this->dias_laborables ?? [];
        $diasDescanso = $this->dias_descanso ?? [];
        
        // No debe haber días duplicados
        $todosDias = array_merge($diasLaborables, $diasDescanso);
        return count($todosDias) === count(array_unique($todosDias)) && count($diasLaborables) > 0;
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * ✅ NUEVO: Calcular días de descanso automáticamente
     */
    public static function calcularDiasDescanso(array $diasLaborables)
    {
        $todosDias = array_keys(self::DIAS_SEMANA);
        return array_values(array_diff($todosDias, $diasLaborables));
    }

    /**
     * ✅ NUEVO: Validar datos de beneficiario (simplificado)
     */
    public static function validarBeneficiario($nombre, $parentesco)
    {
        return [
            'valido' => !empty($nombre) && !empty($parentesco),
            'errores' => []
        ];
    }

    // ===== SCOPES PARA LARAVEL 12 =====

    /**
     * ✅ NUEVO: Scope para filtrar por días específicos
     */
    public function scopeTrabajaDia($query, $dia)
    {
        return $query->whereJsonContains('dias_laborables', $dia);
    }

    /**
     * ✅ NUEVO: Scope para trabajadores de tiempo completo
     */
    public function scopeTiempoCompleto($query)
    {
        return $query->where('horas_semanales', '>=', 40);
    }

    /**
     * ✅ NUEVO: Scope para trabajadores con beneficiario
     */
    public function scopeConBeneficiario($query)
    {
        return $query->whereNotNull('beneficiario_nombre');
    }
}