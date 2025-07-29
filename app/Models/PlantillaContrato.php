<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PlantillaContrato extends Model
{
    protected $table = 'plantillas_contratos';
    protected $primaryKey = 'id_plantilla';
    
    protected $fillable = [
        'nombre_plantilla',
        'tipo_contrato',
        'contenido_html',
        'variables_utilizadas',
        'version',
        'activa',
        'descripcion',
        'creado_por',
        'modificado_por'
    ];

    protected $casts = [
        'variables_utilizadas' => 'array',
        'activa' => 'boolean',
        'version' => 'integer'
    ];

    // ===== RELACIONES =====
    
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function modificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    // ===== SCOPES =====
    
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_contrato', $tipo);
    }

    public function scopeUltimaVersion($query)
    {
        return $query->orderBy('version', 'desc');
    }

    // ===== MÉTODOS ESTÁTICOS =====
    
    /**
     * Obtener la plantilla activa para un tipo de contrato
     */
    public static function obtenerActiva(string $tipoContrato = 'ambos'): ?self
    {
        return self::activa()
                  ->porTipo($tipoContrato)
                  ->ultimaVersion()
                  ->first();
    }

    /**
     * Crear nueva versión de plantilla
     */
    public static function crearNuevaVersion(array $datos, int $usuarioId): self
    {
        // Desactivar versión anterior si existe
        if (isset($datos['tipo_contrato'])) {
            self::where('tipo_contrato', $datos['tipo_contrato'])
                ->where('activa', true)
                ->update(['activa' => false]);
        }

        // Obtener siguiente versión
        $ultimaVersion = self::where('nombre_plantilla', $datos['nombre_plantilla'])
                            ->max('version') ?? 0;

        return self::create([
            'nombre_plantilla' => $datos['nombre_plantilla'],
            'tipo_contrato' => $datos['tipo_contrato'],
            'contenido_html' => $datos['contenido_html'],
            'variables_utilizadas' => self::extraerVariables($datos['contenido_html']),
            'version' => $ultimaVersion + 1,
            'activa' => true,
            'descripcion' => $datos['descripcion'] ?? null,
            'creado_por' => $usuarioId,
            'modificado_por' => $usuarioId
        ]);
    }

    /**
     * Extraer variables del contenido HTML
     */
    public static function extraerVariables(string $contenidoHtml): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $contenidoHtml, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Reemplazar variables en el contenido
     */
    public function reemplazarVariables(array $valoresVariables): string
    {
        $contenido = $this->contenido_html;
        
        foreach ($valoresVariables as $variable => $valor) {
            $contenido = str_replace("{{" . $variable . "}}", $valor, $contenido);
        }
        
        return $contenido;
    }

    /**
     * Validar que todas las variables necesarias estén disponibles
     */
    public function validarVariables(array $valoresVariables): array
    {
        $variablesNecesarias = $this->variables_utilizadas ?? [];
        $variablesFaltantes = [];
        
        foreach ($variablesNecesarias as $variable) {
            if (!isset($valoresVariables[$variable])) {
                $variablesFaltantes[] = $variable;
            }
        }
        
        return $variablesFaltantes;
    }

    // ===== ACCESSORS =====
    
    public function getVersionTextAttribute(): string
    {
        return "v{$this->version}";
    }

    public function getTipoContratoTextAttribute(): string
    {
        return match($this->tipo_contrato) {
            'determinado' => 'Tiempo Determinado',
            'indeterminado' => 'Tiempo Indeterminado',
            'ambos' => 'Ambos Tipos',
            default => 'Tipo Desconocido'
        };
    }

    public function getEstadoTextAttribute(): string
    {
        return $this->activa ? 'Activa' : 'Inactiva';
    }

    public function getEstadoColorAttribute(): string
    {
        return $this->activa ? 'success' : 'secondary';
    }
}