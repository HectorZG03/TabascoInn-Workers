<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DocumentoTrabajador extends Model
{
    use HasFactory;

    protected $table = 'documentos_trabajador';
    protected $primaryKey = 'id_documento';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'id_trabajador',
        'ine',
        'acta_nacimiento',
        'comprobante_domicilio',
        'acta_residencia',
        'nss',
        'curp_documento',
        'rfc_documento',
        'contrato_trabajo',
        'carta_recomendacion',
        'certificados_estudios',
        'examenes_medicos',
        'fotos',
        'porcentaje_completado',
        'fecha_ultima_actualizacion',
        'observaciones',
        'documentos_basicos_completos',
        'estado',
    ];

    protected $casts = [
        'porcentaje_completado' => 'decimal:2',
        'fecha_ultima_actualizacion' => 'datetime',
        'documentos_basicos_completos' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA DOCUMENTOS
    const DOCUMENTOS_BASICOS = [
        'ine' => 'INE/IFE',
        'acta_nacimiento' => 'Acta de Nacimiento',
        'nss' => 'Número de Seguro Social',
        'curp_documento' => 'CURP (Documento)',
    ];

    const TODOS_DOCUMENTOS = [
        'ine' => 'INE/IFE',
        'acta_nacimiento' => 'Acta de Nacimiento',
        'comprobante_domicilio' => 'Comprobante de Domicilio',
        'acta_residencia' => 'Acta de Residencia',
        'nss' => 'Número de Seguro Social',
        'curp_documento' => 'CURP (Documento)',
        'rfc_documento' => 'RFC (Documento)',
    ];

    /**
     * Relación: Los documentos pertenecen a un trabajador
     */
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    // ✅ SCOPES
    public function scopeCompletos($query)
    {
        return $query->where('porcentaje_completado', 100);
    }

    public function scopeIncompletos($query)
    {
        return $query->where('porcentaje_completado', '<', 100);
    }

    public function scopeBasicosCompletos($query)
    {
        return $query->where('documentos_basicos_completos', true);
    }

    // ✅ MÉTODO PRINCIPAL SIMPLIFICADO - SIN EVENTOS AUTOMÁTICOS
    public function calcularPorcentaje($actualizar = false)
    {
        $documentosParaCalculo = self::TODOS_DOCUMENTOS;
        
        $completados = 0;
        $total = count($documentosParaCalculo);
        
        foreach (array_keys($documentosParaCalculo) as $campo) {
            if (!empty($this->$campo)) {
                $completados++;
            }
        }
        
        $porcentaje = $total > 0 ? round(($completados / $total) * 100, 2) : 0;
        $basicosCompletos = $this->verificarDocumentosBasicos();
        $estado = $this->determinarEstado($porcentaje, $basicosCompletos);
        
        // ✅ ASIGNAR VALORES SIN DISPARAR EVENTOS
        $this->porcentaje_completado = $porcentaje;
        $this->documentos_basicos_completos = $basicosCompletos;
        $this->estado = $estado;
        $this->fecha_ultima_actualizacion = now();
        
        // ✅ GUARDAR SOLO SI SE SOLICITA Y SIN EVENTOS
        if ($actualizar) {
            // Usar updateQuietly para evitar eventos
            return $this->updateQuietly([
                'porcentaje_completado' => $porcentaje,
                'documentos_basicos_completos' => $basicosCompletos,
                'estado' => $estado,
                'fecha_ultima_actualizacion' => now()
            ]);
        }
        
        return $porcentaje;
    }

    /**
     * Verificar si los documentos básicos están completos
     */
    public function verificarDocumentosBasicos()
    {
        foreach (array_keys(self::DOCUMENTOS_BASICOS) as $campo) {
            if (empty($this->$campo)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determinar estado basado en porcentaje y documentos básicos
     */
    private function determinarEstado($porcentaje, $basicosCompletos)
    {
        if ($porcentaje >= 100) {
            return 'completo';
        } elseif ($basicosCompletos) {
            return 'revision';
        } elseif ($porcentaje >= 50) {
            return 'parcial';
        } else {
            return 'incompleto';
        }
    }

    // ✅ ACCESSORS Y ATRIBUTOS
    public function getDocumentosFaltantesAttribute()
    {
        $faltantes = [];
        foreach (self::TODOS_DOCUMENTOS as $campo => $nombre) {
            if (empty($this->$campo)) {
                $faltantes[] = $nombre;
            }
        }
        return $faltantes;
    }

    public function getDocumentosBasicosFaltantesAttribute()
    {
        $faltantes = [];
        foreach (self::DOCUMENTOS_BASICOS as $campo => $nombre) {
            if (empty($this->$campo)) {
                $faltantes[] = $nombre;
            }
        }
        return $faltantes;
    }

    public function getDocumentosPresentesAttribute()
    {
        $presentes = [];
        foreach (self::TODOS_DOCUMENTOS as $campo => $nombre) {
            if (!empty($this->$campo)) {
                $presentes[$campo] = $nombre;
            }
        }
        return $presentes;
    }

    public function getEstadoTextoAttribute()
    {
        $estados = [
            'incompleto' => 'Incompleto',
            'parcial' => 'Parcial',
            'revision' => 'En Revisión',
            'completo' => 'Completo',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
        ];
        
        return $estados[$this->estado] ?? 'Desconocido';
    }

    public function getColorProgresoAttribute()
    {
        $porcentaje = $this->porcentaje_completado;
        
        if ($porcentaje >= 100) return 'success';
        if ($porcentaje >= 75) return 'info';
        if ($porcentaje >= 50) return 'warning';
        return 'danger';
    }

    // ✅ MÉTODOS UTILITARIOS
    public function aprobar($observaciones = null)
    {
        return $this->updateQuietly([
            'estado' => 'aprobado',
            'observaciones' => $observaciones,
            'fecha_ultima_actualizacion' => now(),
        ]);
    }

    public function rechazar($observaciones = null)
    {
        return $this->updateQuietly([
            'estado' => 'rechazado',
            'observaciones' => $observaciones,
            'fecha_ultima_actualizacion' => now(),
        ]);
    }

    // ✅ EVENTOS SIMPLIFICADOS - SIN BUCLES
    protected static function boot()
    {
        parent::boot();

        // ✅ SOLO al crear: calcular una vez y listo
        static::created(function ($documento) {
            // Calcular porcentaje inicial sin disparar más eventos
            $documento->calcularPorcentaje(true);
        });
    }
}