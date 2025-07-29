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
        'origen_codigo',          // ✅ USANDO origen_codigo en lugar de origen_modelo/campo
        'activa',
        'obligatoria'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'obligatoria' => 'boolean'
    ];

    // ===== CONSTANTES ACTUALIZADAS =====
    
    public const CATEGORIAS = [
        'trabajador' => 'Datos del Trabajador',
        'contrato' => 'Información del Contrato',
        'fechas' => 'Fechas',
        'horarios' => 'Horarios y Jornada',
        'salariales' => 'Información Salarial',
        'beneficiario' => 'Beneficiario'
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
     * ✅ ACTUALIZADO: Obtener valor de una variable ejecutando codigo PHP directamente
     */
    public function obtenerValor($trabajador, $datosContrato = []): string
    {
        try {
            // Si no tiene código, devolver ejemplo
            if (!$this->origen_codigo) {
                return $this->formato_ejemplo ?? '';
            }

            // ✅ PREPARAR VARIABLES DISPONIBLES EN EL SCOPE
            $fecha_inicio = $datosContrato['fecha_inicio'] ?? null;
            $fecha_fin = $datosContrato['fecha_fin'] ?? null;
            $duracion_texto = $datosContrato['duracion_texto'] ?? null;
            $salario_texto = $datosContrato['salario_texto'] ?? null;
            
            // ✅ ASEGURAR QUE EL TRABAJADOR TENGA LAS RELACIONES CARGADAS
            if (!$trabajador->relationLoaded('fichaTecnica')) {
                $trabajador->load('fichaTecnica.categoria');
            }
            
            // ✅ EJECUTAR EL CÓDIGO PHP ALMACENADO
            $codigo = $this->origen_codigo;
            
            // Añadir return si no lo tiene
            if (!str_starts_with(trim($codigo), 'return')) {
                $codigo = "return {$codigo};";
            }
            
            $resultado = eval($codigo);
            
            return (string) ($resultado ?? '');
            
        } catch (\ParseError $e) {
            Log::error("Error de sintaxis en variable {$this->nombre_variable}: " . $e->getMessage());
            return $this->formato_ejemplo ?? "Error: Sintaxis incorrecta";
            
        } catch (\Error $e) {
            Log::error("Error fatal en variable {$this->nombre_variable}: " . $e->getMessage());
            return $this->formato_ejemplo ?? "Error: Código inválido";
            
        } catch (\Exception $e) {
            Log::error("Error general en variable {$this->nombre_variable}: " . $e->getMessage());
            return $this->formato_ejemplo ?? "Error: No disponible";
        }
    }

    /**
     * ✅ NUEVO: Validar el código PHP de la variable
     */
    public function validarCodigo(): array
    {
        $errores = [];
        
        if (!$this->origen_codigo) {
            return $errores;
        }
        
        try {
            // Intentar parsear el código
            $codigo = $this->origen_codigo;
            if (!str_starts_with(trim($codigo), 'return')) {
                $codigo = "return {$codigo};";
            }
            
            // Validar sintaxis básica
            if (strpos($codigo, '<?php') !== false) {
                $errores[] = "No incluyas etiquetas PHP (<?php)";
            }
            
            // Verificar que tenga punto y coma al final si es necesario
            $codigoLimpio = trim($codigo);
            if (!str_ends_with($codigoLimpio, ';')) {
                $errores[] = "El código debe terminar con punto y coma (;)";
            }
            
        } catch (\ParseError $e) {
            $errores[] = "Error de sintaxis: " . $e->getMessage();
        }
        
        return $errores;
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
                if (!empty($valor)) {
                    try {
                        \Carbon\Carbon::parse($valor);
                    } catch (\Exception $e) {
                        $errores[] = "Debe ser una fecha válida";
                    }
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

    /**
     * ✅ NUEVO: Obtener resumen del código para debug
     */
    public function getCodigoResumenAttribute(): string
    {
        if (!$this->origen_codigo) {
            return 'Sin código';
        }
        
        $codigo = $this->origen_codigo;
        if (strlen($codigo) > 50) {
            return substr($codigo, 0, 50) . '...';
        }
        
        return $codigo;
    }
}