<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    // ✅ ACCESSORS
    public function getUrlAttribute(): string
    {
        return Storage::url($this->ruta);
    }

    public function getTamañoAttribute(): string
    {
        $tamaño = Storage::size($this->ruta);
        return $this->formatearTamaño($tamaño);
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nombre_original, PATHINFO_EXTENSION);
    }

    // ✅ MÉTODOS DE UTILIDAD
    public function existe(): bool
    {
        return Storage::exists($this->ruta);
    }

    public function eliminarArchivo(): bool
    {
        if ($this->existe()) {
            return Storage::delete($this->ruta);
        }
        return true;
    }

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

    // ✅ MÉTODOS ESTÁTICOS
    public static function generarRutaArchivo(int $trabajadorId, string $nombreOriginal): string
    {
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $timestamp = now()->format('Y-m-d_His');
        $nombreLimpio = str_replace(' ', '_', pathinfo($nombreOriginal, PATHINFO_FILENAME));
        
        return "vacaciones/documentos/trabajador_{$trabajadorId}/{$timestamp}_{$nombreLimpio}.{$extension}";
    }

    public static function validarArchivo($archivo): array
    {
        $errores = [];

        // Validar que sea un archivo
        if (!$archivo || !$archivo->isValid()) {
            $errores[] = 'El archivo no es válido';
            return $errores;
        }

        // Validar extensión
        if (strtolower($archivo->getClientOriginalExtension()) !== 'pdf') {
            $errores[] = 'Solo se permiten archivos PDF';
        }

        // Validar tamaño (2MB máximo)
        if ($archivo->getSize() > 2048 * 1024) {
            $errores[] = 'El archivo no puede ser mayor a 2MB';
        }

        return $errores;
    }
}