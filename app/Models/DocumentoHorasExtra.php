<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoHorasExtra extends Model
{
    use HasFactory;
    
    protected $table = 'documentos_horas_extra';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_horas_extra',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_mime',
        'tamaño_archivo',
        'subido_por',
    ];
    
    protected $casts = [
        'tamaño_archivo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function horasExtra(): BelongsTo
    {
        return $this->belongsTo(HorasExtra::class, 'id_horas_extra', 'id');
    }

    // Accessors
    public function getTamañoFormateadoAttribute(): string
    {
        $bytes = $this->tamaño_archivo;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nombre_original, PATHINFO_EXTENSION);
    }

    public function getIconoAttribute(): string
    {
        $extension = strtolower($this->extension);
        
        return match ($extension) {
            'pdf' => 'bi-file-pdf',
            'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image',
            'doc', 'docx' => 'bi-file-word',
            'xls', 'xlsx' => 'bi-file-excel',
            default => 'bi-file-earmark'
        };
    }
}