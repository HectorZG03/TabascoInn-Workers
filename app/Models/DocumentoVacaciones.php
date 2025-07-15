<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

class DocumentoVacaciones extends Model
{
    use HasFactory;

    protected $table = 'documento_vacaciones';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'trabajador_id',
        'nombre_original',
        'ruta'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ RELACIONES
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id', 'id_trabajador');
    }

    public function vacaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            VacacionesTrabajador::class,
            'documento_vacacion_vacaciones',
            'documento_vacacion_id',
            'vacacion_id',
            'id',
            'id_vacacion'
        )->withTimestamps();
    }

    // ✅ ACCESSORS SEGUROS - CORREGIDOS
    public function getUrlAttribute(): string
    {
        // ✅ Verificar que el archivo existe antes de generar URL
        if ($this->existe()) {
            return Storage::disk('public')->url($this->ruta);
        }
        
        // Retornar URL de placeholder o mensaje de error
        return '#'; // O podrías usar asset('images/file-not-found.png')
    }

    public function getTamañoAttribute(): string
    {
        // ✅ Verificar que el archivo existe antes de obtener tamaño
        if ($this->existe()) {
            try {
                $tamaño = Storage::disk('public')->size($this->ruta);
                return $this->formatearTamaño($tamaño);
            } catch (\Exception $e) {
                \Log::warning("Error obteniendo tamaño de archivo", [
                    'documento_id' => $this->id,
                    'ruta' => $this->ruta,
                    'error' => $e->getMessage()
                ]);
                return 'Tamaño desconocido';
            }
        }
        
        return 'Archivo no encontrado';
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nombre_original, PATHINFO_EXTENSION);
    }

    // ✅ MÉTODO EXISTE - MEJORADO
    public function existe(): bool
    {
        try {
            return !empty($this->ruta) && Storage::disk('public')->exists($this->ruta);
        } catch (\Exception $e) {
            \Log::warning("Error verificando existencia de archivo", [
                'documento_id' => $this->id,
                'ruta' => $this->ruta,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ✅ MÉTODO ELIMINAR ARCHIVO - MEJORADO
    public function eliminarArchivo(): bool
    {
        try {
            if ($this->existe()) {
                return Storage::disk('public')->delete($this->ruta);
            }
            return true; // Si no existe, consideramos que ya está "eliminado"
        } catch (\Exception $e) {
            \Log::error("Error eliminando archivo", [
                'documento_id' => $this->id,
                'ruta' => $this->ruta,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ✅ MÉTODO PRIVADO MEJORADO
    private function formatearTamaño(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // ✅ SCOPES
    public function scopePorTrabajador($query, int $trabajadorId)
    {
        return $query->where('trabajador_id', $trabajadorId);
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ✅ MÉTODO ESTÁTICO CORREGIDO
    public static function generarRutaArchivo(int $trabajadorId, string $nombreOriginal): string
    {
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $timestamp = now()->format('Y-m-d_His');
        $nombreLimpio = str_replace(' ', '_', pathinfo($nombreOriginal, PATHINFO_FILENAME));
        
        // ✅ Solo generar el nombre del archivo, no la ruta completa
        // El directorio se maneja en el controlador
        return "{$timestamp}_{$nombreLimpio}.{$extension}";
    }

    // ✅ VALIDACIÓN DE ARCHIVO - MEJORADA
    public static function validarArchivo($archivo): array
    {
        $errores = [];

        // Validar que sea un archivo
        if (!$archivo || !$archivo->isValid()) {
            $errores[] = 'El archivo no es válido';
            return $errores;
        }

        // Validar extensión
        $extension = strtolower($archivo->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            $errores[] = 'Solo se permiten archivos PDF';
        }

        // Validar tamaño (2MB máximo)
        if ($archivo->getSize() > 2048 * 1024) {
            $errores[] = 'El archivo no puede ser mayor a 2MB';
        }

        // ✅ NUEVO: Validar que el archivo no esté corrupto
        try {
            $archivo->getRealPath();
        } catch (\Exception $e) {
            $errores[] = 'El archivo está corrupto o no se puede procesar';
        }

        return $errores;
    }

    // ✅ NUEVO: Método para verificar integridad
    public function verificarIntegridad(): array
    {
        $problemas = [];
        
        if (!$this->existe()) {
            $problemas[] = 'El archivo físico no existe en storage';
        }
        
        if (empty($this->ruta)) {
            $problemas[] = 'La ruta del archivo está vacía';
        }
        
        if (empty($this->nombre_original)) {
            $problemas[] = 'El nombre original está vacío';
        }
        
        return $problemas;
    }

    // ✅ NUEVO: Método para reparar archivos faltantes
    public static function diagnosticarStorage(): array
    {
        $documentos = self::all();
        $reporte = [
            'total' => $documentos->count(),
            'existentes' => 0,
            'faltantes' => 0,
            'problemas' => []
        ];

        foreach ($documentos as $documento) {
            if ($documento->existe()) {
                $reporte['existentes']++;
            } else {
                $reporte['faltantes']++;
                $reporte['problemas'][] = [
                    'id' => $documento->id,
                    'trabajador_id' => $documento->trabajador_id,
                    'nombre' => $documento->nombre_original,
                    'ruta' => $documento->ruta,
                    'fecha' => $documento->created_at->format('Y-m-d H:i:s')
                ];
            }
        }

        return $reporte;
    }
}