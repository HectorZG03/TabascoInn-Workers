<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class VariableContrato extends Model
{
    protected $table = 'variables_contrato';
    protected $primaryKey = 'id_variable';
    
    protected $fillable = [
        'nombre_variable',
        'etiqueta',
        'descripcion',
        'categoria',
        'tipo_dato',
        'formato_ejemplo',
        'origen_modelo',
        'origen_campo',
        'origen_codigo',
        'activa',
        'obligatoria'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'obligatoria' => 'boolean'
    ];

    // ===== CONSTANTES =====
    
    public const CATEGORIAS = [
        'trabajador' => 'Datos del Trabajador',
        'empresa' => 'Datos de la Empresa',
        'contrato' => 'Información del Contrato',
        'fechas' => 'Fechas',
        'horarios' => 'Horarios y Jornada',
        'salariales' => 'Información Salarial',
        'beneficiario' => 'Beneficiario',
        'legal' => 'Aspectos Legales'
    ];

    public const TIPOS_DATO = [
        'texto' => 'Texto',
        'numero' => 'Número',
        'fecha' => 'Fecha',
        'hora' => 'Hora',
        'booleano' => 'Sí/No',
        'calculado' => 'Calculado'
    ];

    // ===== SCOPES =====
    
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeObligatorias($query)
    {
        return $query->where('obligatoria', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('categoria')
                    ->orderBy('obligatoria', 'desc')
                    ->orderBy('etiqueta');
    }

    // ===== MÉTODOS ESTÁTICOS =====
    
    /**
     * Obtener variables agrupadas por categoría
     */
    public static function obtenerPorCategorias(): array
    {
        $variables = self::activas()->ordenadas()->get();
        
        return $variables->groupBy('categoria')->map(function ($items, $categoria) {
            return [
                'nombre' => self::CATEGORIAS[$categoria] ?? ucfirst($categoria),
                'variables' => $items
            ];
        })->toArray();
    }

    /**
     * Obtener valor de una variable para un trabajador y contrato específico
     */
    public function obtenerValor($trabajador, $datosContrato = []): string
    {
        try {
            // Si tiene código personalizado, ejecutarlo
            if ($this->origen_codigo) {
                $codigo = $this->origen_codigo;
                
                // Variables disponibles en el scope
                $fecha_inicio = $datosContrato['fecha_inicio'] ?? null;
                $fecha_fin = $datosContrato['fecha_fin'] ?? null;
                $duracion_texto = $datosContrato['duracion_texto'] ?? null;
                $salario_texto = $datosContrato['salario_texto'] ?? null;
                
                // Ejecutar el código
                return eval("return $codigo;") ?: '';
            }
            
            // Si es de un modelo específico
            if ($this->origen_modelo && $this->origen_campo) {
                switch ($this->origen_modelo) {
                    case 'Trabajador':
                        return $trabajador->{$this->origen_campo} ?? '';
                    
                    case 'FichaTecnica':
                        return $trabajador->fichaTecnica->{$this->origen_campo} ?? '';
                    
                    default:
                        return '';
                }
            }
            
            return '';
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo valor de variable {$this->nombre_variable}: " . $e->getMessage());
            return $this->formato_ejemplo ?? '';
        }
    }

    /**
     * Validar una variable
     */
    public function validar(string $valor): array
    {
        $errores = [];
        
        // Validar según tipo de dato
        switch ($this->tipo_dato) {
            case 'numero':
                if (!is_numeric($valor) && !empty($valor)) {
                    $errores[] = "Debe ser un número válido";
                }
                break;
                
            case 'fecha':
                if (!empty($valor) && !\Carbon\Carbon::parse($valor)) {
                    $errores[] = "Debe ser una fecha válida";
                }
                break;
                
            case 'hora':
                if (!empty($valor) && !preg_match('/^([01]?\d|2[0-3]):[0-5]\d$/', $valor)) {
                    $errores[] = "Debe ser una hora válida (HH:MM)";
                }
                break;
        }
        
        // Validar si es obligatoria
        if ($this->obligatoria && empty($valor)) {
            $errores[] = "Esta variable es obligatoria";
        }
        
        return $errores;
    }

    // ===== ACCESSORS =====
    
    public function getCategoriaTextAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst($this->categoria);
    }

    public function getTipoDatoTextAttribute(): string
    {
        return self::TIPOS_DATO[$this->tipo_dato] ?? ucfirst($this->tipo_dato);
    }

    public function getVariableFormateadaAttribute(): string
    {
        return "{{" . $this->nombre_variable . "}}";
    }

    public function getEstadoTextAttribute(): string
    {
        return $this->activa ? 'Activa' : 'Inactiva';
    }

    public function getEstadoColorAttribute(): string
    {
        return $this->activa ? 'success' : 'secondary';
    }

    public function getPrioridadTextAttribute(): string
    {
        return $this->obligatoria ? 'Obligatoria' : 'Opcional';
    }

    public function getPrioridadColorAttribute(): string
    {
        return $this->obligatoria ? 'danger' : 'info';
    }
}