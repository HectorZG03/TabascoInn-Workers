<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DiaFestivo extends Model
{
    use HasFactory;

    protected $table = 'dias_festivos';

    protected $fillable = [
        'año',
        'fecha',
        'nombre',
        'es_oficial',
        'observaciones',
        'creado_por'
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_oficial' => 'boolean',
        'año' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES - Días festivos oficiales en México
    public const DIAS_FESTIVOS_OFICIALES = [
        '01-01' => 'Año Nuevo',
        '02-05' => 'Aniversario de la Constitución (Primer lunes de febrero)',
        '03-21' => 'Natalicio de Benito Juárez (Tercer lunes de marzo)',
        '05-01' => 'Día del Trabajo',
        '09-16' => 'Día de la Independencia',
        '11-20' => 'Revolución Mexicana (Tercer lunes de noviembre)',
        '10-01' => 'Transmisión del Poder Ejecutivo Federal (cada 6 años)', // ✅ CORREGIDO: 10-01 no 12-01
        '12-25' => 'Navidad',
    ];

    // ✅ RELACIONES
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ✅ SCOPES
    public function scopeDelAño($query, $año)
    {
        return $query->where('año', $año);
    }

    public function scopeOficiales($query)
    {
        return $query->where('es_oficial', true);
    }

    public function scopeEnRango($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('fecha', 'asc');
    }

    // ✅ ACCESSORS
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha->format('d/m/Y');
    }

    public function getDiaSemanaAttribute()
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $dias[$this->fecha->dayOfWeek];
    }

    public function getMesTextoAttribute()
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$this->fecha->month];
    }

    // ✅ MÉTODOS ESTÁTICOS

    /**
     * Obtener días festivos entre dos fechas
     */
    public static function obtenerDiasFestivosEnRango($fechaInicio, $fechaFin)
    {
        $fechaInicioCarbon = Carbon::parse($fechaInicio);
        $fechaFinCarbon = Carbon::parse($fechaFin);
        
        return self::whereBetween('fecha', [$fechaInicioCarbon, $fechaFinCarbon])
            ->orderBy('fecha')
            ->get();
    }

    /**
     * Verificar si una fecha es día festivo
     */
    // En App\Models\DiaFestivo.php
    public static function esDiaFestivo($fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        
        return self::whereYear('fecha', $fechaCarbon->year) // ✅ AÑADIDO: filtrar por año
            ->whereDate('fecha', $fechaCarbon->toDateString())
            ->where('es_oficial', true)
            ->exists();
    }

    /**
     * Obtener siguiente día hábil considerando festivos y días de descanso
     */
    public static function siguienteDiaHabil($fecha, array $diasDescanso = [])
    {
        $fechaCarbon = Carbon::parse($fecha);
        
        // Mapeo de días de descanso a números (0=domingo, 6=sábado)
        $diasSemanaMap = [
            'domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3,
            'jueves' => 4, 'viernes' => 5, 'sabado' => 6
        ];
        
        $diasDescansoNumeros = array_map(function($dia) use ($diasSemanaMap) {
            return $diasSemanaMap[strtolower($dia)] ?? null;
        }, $diasDescanso);
        
        $diasDescansoNumeros = array_filter($diasDescansoNumeros, function($dia) {
            return $dia !== null;
        });
        
        $intentos = 0;
        $maxIntentos = 30; // Prevenir loops infinitos
        
        while ($intentos < $maxIntentos) {
            $intentos++;
            
            // Verificar si es día de descanso
            $esDiaDescanso = in_array($fechaCarbon->dayOfWeek, $diasDescansoNumeros);
            
            // Verificar si es día festivo
            $esDiaFestivo = self::esDiaFestivo($fechaCarbon);
            
            if (!$esDiaDescanso && !$esDiaFestivo) {
                return $fechaCarbon;
            }
            
            $fechaCarbon->addDay();
        }
        
        return $fechaCarbon;
    }

    /**
     * Copiar días festivos de un año a otro
     */
    public static function copiarDiasFestivosDeAño($añoOrigen, $añoDestino, $usuarioId = null)
    {
        $diasOrigen = self::where('año', $añoOrigen)->get();
        $diasCopiados = 0;
        
        foreach ($diasOrigen as $diaOrigen) {
            // Ajustar la fecha al nuevo año
            $nuevaFecha = Carbon::parse($diaOrigen->fecha)->setYear($añoDestino);
            
            // Verificar que no exista ya
            $existe = self::where('año', $añoDestino)
                ->whereDate('fecha', $nuevaFecha)
                ->exists();
            
            if (!$existe) {
                self::create([
                    'año' => $añoDestino,
                    'fecha' => $nuevaFecha,
                    'nombre' => $diaOrigen->nombre,
                    'es_oficial' => $diaOrigen->es_oficial,
                    'observaciones' => $diaOrigen->observaciones,
                    'creado_por' => $usuarioId
                ]);
                $diasCopiados++;
            }
        }
        
        return $diasCopiados;
    }

    /**
     * Generar días festivos oficiales para un año
     */
    public static function generarDiasFestivosOficiales($año, $usuarioId = null)
    {
        $festivos = [];
        
        // Días fijos
        $festivos[] = ['fecha' => "$año-01-01", 'nombre' => 'Año Nuevo'];
        $festivos[] = ['fecha' => "$año-05-01", 'nombre' => 'Día del Trabajo'];
        $festivos[] = ['fecha' => "$año-09-16", 'nombre' => 'Día de la Independencia'];
        $festivos[] = ['fecha' => "$año-12-25", 'nombre' => 'Navidad'];
        
        // Días móviles (calcular según la ley)
        // Primer lunes de febrero
        $febrero = Carbon::create($año, 2, 1);
        while ($febrero->dayOfWeek !== Carbon::MONDAY) {
            $febrero->addDay();
        }
        $festivos[] = ['fecha' => $febrero->toDateString(), 'nombre' => 'Aniversario de la Constitución'];
        
        // Tercer lunes de marzo
        $marzo = Carbon::create($año, 3, 1);
        $lunesMarzo = 0;
        while ($lunesMarzo < 3) {
            if ($marzo->dayOfWeek === Carbon::MONDAY) {
                $lunesMarzo++;
                if ($lunesMarzo === 3) break;
            }
            $marzo->addDay();
        }
        $festivos[] = ['fecha' => $marzo->toDateString(), 'nombre' => 'Natalicio de Benito Juárez'];
        
        // Tercer lunes de noviembre
        $noviembre = Carbon::create($año, 11, 1);
        $lunesNoviembre = 0;
        while ($lunesNoviembre < 3) {
            if ($noviembre->dayOfWeek === Carbon::MONDAY) {
                $lunesNoviembre++;
                if ($lunesNoviembre === 3) break;
            }
            $noviembre->addDay();
        }
        $festivos[] = ['fecha' => $noviembre->toDateString(), 'nombre' => 'Revolución Mexicana'];
        
        // ✅ CORREGIDO: Transmisión del Poder Ejecutivo (cada 6 años: 2024, 2030, 2036...) - 1 DE OCTUBRE
        if (($año - 2024) % 6 === 0 && $año >= 2024) {
            $festivos[] = ['fecha' => "$año-10-01", 'nombre' => 'Transmisión del Poder Ejecutivo Federal'];
        }
        
        // Crear registros
        $creados = 0;
        foreach ($festivos as $festivo) {
            $existe = self::where('año', $año)
                ->whereDate('fecha', $festivo['fecha'])
                ->exists();
            
            if (!$existe) {
                self::create([
                    'año' => $año,
                    'fecha' => $festivo['fecha'],
                    'nombre' => $festivo['nombre'],
                    'es_oficial' => true,
                    'creado_por' => $usuarioId
                ]);
                $creados++;
            }
        }
        
        return $creados;
    }
}