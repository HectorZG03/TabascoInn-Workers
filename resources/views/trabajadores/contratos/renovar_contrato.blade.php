@extends('layouts.app')

@section('title', 'Renovar Contrato')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                <div class="card-body text-center py-4">
                    <h1 class="display-6 text-white mb-2">
                        <i class="bi bi-arrow-repeat me-3"></i>
                        Renovar Contrato Laboral
                    </h1>
                    <p class="text-white-50 mb-0 fs-5">
                        Crear nueva renovación para: {{ $trabajador->nombre_completo }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del contrato actual -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #f8f9ff;">
                    <h5 class="mb-0" style="color: #6f42c1;">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Información del Contrato Actual
                    </h5>
                </div>
                <div class="card-body" style="background-color: #fefeff;">
                    <div class="row">
                        <!-- Trabajador -->
                        <div class="col-lg-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; background-color: #e7e3ff; color: #6f42c1;">
                                    <i class="bi bi-person-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1" style="color: #6f42c1;">{{ $trabajador->nombre_completo }}</h6>
                                    <small class="text-muted">
                                        {{ $trabajador->fichaTecnica?->categoria?->area?->nombre_area ?? 'Sin área' }} - 
                                        {{ $trabajador->fichaTecnica?->categoria?->nombre_categoria ?? 'Sin categoría' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas actuales -->
                        <div class="col-lg-4 mb-3">
                            <h6 style="color: #6f42c1;">Período Actual</h6>
                            <p class="mb-1">
                                <strong>Inicio:</strong> {{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}
                            </p>
                            <p class="mb-1">
                                <strong>Fin:</strong> {{ $contrato->fecha_fin_contrato->format('d/m/Y') }}
                            </p>
                            <p class="mb-0">
                                <strong>Duración:</strong> {{ $contrato->duracion_texto }}
                            </p>
                        </div>

                        <!-- Estado actual -->
                        <div class="col-lg-4 mb-3">
                            <h6 style="color: #6f42c1;">Estado Actual</h6>
                            <p class="mb-1">
                                <span class="badge bg-{{ $contrato->color_estatus }} fw-normal">
                                    {{ $contrato->texto_estatus }}
                                </span>
                            </p>
                            @if($contrato->estaVigente())
                                <p class="mb-0">
                                    <small class="text-muted">
                                        Días restantes: <strong>{{ $contrato->diasRestantes() }}</strong>
                                    </small>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Alerta si no puede renovarse -->
                    @if(!$contrato->puedeRenovarse() && $contrato->estatus === 'activo')
                    <div class="alert alert-warning border-0 mt-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Renovación anticipada
                        </h6>
                        <p class="mb-0">
                            Este contrato tiene más de 30 días restantes. Las renovaciones normalmente se realizan 
                            cuando quedan 30 días o menos, pero puede proceder si es necesario.
                        </p>
                    </div>
                    @endif

                    @if($contrato->estatus !== 'activo')
                    <div class="alert alert-danger border-0 mt-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-x-circle me-2"></i>
                            Contrato no renovable
                        </h6>
                        <p class="mb-0">
                            Este contrato no puede renovarse porque su estado actual es: <strong>{{ $contrato->texto_estatus }}</strong>.
                            Solo los contratos activos pueden ser renovados.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de renovación -->
    @if($contrato->estatus === 'activo')
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header" style="background-color: #e7e3ff;">
                    <h5 class="mb-0" style="color: #6f42c1;">
                        <i class="bi bi-plus-circle me-2"></i>
                        Datos de la Nueva Renovación
                    </h5>
                </div>
                <div class="card-body" style="background-color: #fefeff;">
                    <form action="{{ route('trabajadores.contratos.renovar', [$trabajador, $contrato]) }}" 
                          method="POST" id="formRenovacion">
                        @csrf

                        <div class="row">
                            <!-- Fecha de inicio -->
                            <div class="col-lg-6 mb-3">
                                <label for="fecha_inicio" class="form-label fw-bold" style="color: #6f42c1;">
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    Fecha de Inicio *
                                </label>
                                <input type="date" 
                                       name="fecha_inicio" 
                                       id="fecha_inicio"
                                       class="form-control @error('fecha_inicio') is-invalid @enderror"
                                       value="{{ old('fecha_inicio', $contrato->fecha_fin_contrato->addDay()->format('Y-m-d')) }}"
                                       min="{{ $contrato->fecha_fin_contrato->format('Y-m-d') }}"
                                       required>
                                <div class="form-text">
                                    La renovación debe iniciar después del {{ $contrato->fecha_fin_contrato->format('d/m/Y') }}
                                </div>
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de fin -->
                            <div class="col-lg-6 mb-3">
                                <label for="fecha_fin" class="form-label fw-bold" style="color: #6f42c1;">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    Fecha de Fin *
                                </label>
                                <input type="date" 
                                       name="fecha_fin" 
                                       id="fecha_fin"
                                       class="form-control @error('fecha_fin') is-invalid @enderror"
                                       value="{{ old('fecha_fin') }}"
                                       required>
                                <div class="form-text">
                                    Fecha cuando terminará la renovación del contrato
                                </div>
                                @error('fecha_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo de duración -->
                            <div class="col-lg-6 mb-3">
                                <label for="tipo_duracion" class="form-label fw-bold" style="color: #6f42c1;">
                                    <i class="bi bi-clock me-1"></i>
                                    Tipo de Duración *
                                </label>
                                <select name="tipo_duracion" 
                                        id="tipo_duracion" 
                                        class="form-select @error('tipo_duracion') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="dias" {{ old('tipo_duracion', $contrato->tipo_duracion) === 'dias' ? 'selected' : '' }}>
                                        Por días
                                    </option>
                                    <option value="meses" {{ old('tipo_duracion', $contrato->tipo_duracion) === 'meses' ? 'selected' : '' }}>
                                        Por meses
                                    </option>
                                </select>
                                @error('tipo_duracion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duración calculada (readonly) -->
                            <div class="col-lg-6 mb-3">
                                <label class="form-label fw-bold" style="color: #6f42c1;">
                                    <i class="bi bi-calculator me-1"></i>
                                    Duración Calculada
                                </label>
                                <input type="text" 
                                       id="duracion_calculada" 
                                       class="form-control" 
                                       value="Seleccione las fechas..."
                                       readonly
                                       style="background-color: #f8f9fa;">
                                <div class="form-text">
                                    Se calcula automáticamente según las fechas seleccionadas
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label for="observaciones_renovacion" class="form-label fw-bold" style="color: #6f42c1;">
                                    <i class="bi bi-chat-text me-1"></i>
                                    Observaciones de la Renovación
                                </label>
                                <textarea name="observaciones_renovacion" 
                                          id="observaciones_renovacion"
                                          class="form-control @error('observaciones_renovacion') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Motivos, cambios o comentarios especiales sobre esta renovación...">{{ old('observaciones_renovacion') }}</textarea>
                                <div class="form-text">
                                    Opcional. Máximo 500 caracteres. Se agregará automáticamente la referencia al contrato anterior.
                                </div>
                                @error('observaciones_renovacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-3 justify-content-between flex-wrap">
                                    <div>
                                        <a href="{{ route('trabajadores.contratos.show', $trabajador) }}" 
                                           class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Cancelar
                                        </a>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                onclick="generarPreview()"
                                                id="btnPreview">
                                            <i class="bi bi-eye me-1"></i>
                                            Vista Previa
                                        </button>
                                        
                                        <button type="submit" 
                                                class="btn text-white" 
                                                style="background-color: #6f42c1;"
                                                id="btnRenovar">
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            Crear Renovación
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Historial de renovaciones -->
    @if($renovaciones->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #f0f0f0;">
                    <h5 class="mb-0" style="color: #6c757d;">
                        <i class="bi bi-clock-history me-2"></i>
                        Historial de Renovaciones
                        <span class="badge bg-secondary ms-2">{{ $renovaciones->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Período</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th>Creado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($renovaciones as $renovacion)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $renovacion->fecha_inicio_contrato->format('d/m/Y') }}</strong>
                                            <small class="text-muted">al</small>
                                            <strong>{{ $renovacion->fecha_fin_contrato->format('d/m/Y') }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $renovacion->duracion_texto }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $renovacion->color_estatus }}">
                                            {{ $renovacion->texto_estatus }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $renovacion->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($renovacion->ruta_archivo && \Storage::disk('public')->exists($renovacion->ruta_archivo))
                                        <a href="{{ route('trabajadores.contratos.descargar', [$trabajador, $renovacion]) }}" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Descargar contrato"
                                           target="_blank">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e1e5e9;
        padding: 0.75rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.15);
    }
    
    .btn {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .alert {
        border-radius: 10px;
        border-left: 4px solid;
    }
    
    .alert-warning {
        border-left-color: #ffc107;
    }
    
    .alert-danger {
        border-left-color: #dc3545;
    }
    
    .badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const tipoDuracion = document.getElementById('tipo_duracion');
    const duracionCalculada = document.getElementById('duracion_calculada');
    
    // Calcular duración automáticamente
    function calcularDuracion() {
        if (!fechaInicio.value || !fechaFin.value || !tipoDuracion.value) {
            duracionCalculada.value = 'Seleccione las fechas...';
            return;
        }
        
        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);
        
        if (fin <= inicio) {
            duracionCalculada.value = 'Fecha fin debe ser posterior al inicio';
            return;
        }
        
        if (tipoDuracion.value === 'dias') {
            const diffTime = fin - inicio;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            duracionCalculada.value = `${diffDays} ${diffDays === 1 ? 'día' : 'días'}`;
        } else if (tipoDuracion.value === 'meses') {
            const diffMonths = (fin.getFullYear() - inicio.getFullYear()) * 12 + 
                              (fin.getMonth() - inicio.getMonth());
            const ajusteDias = fin.getDate() >= inicio.getDate() ? 0 : -1;
            const totalMeses = Math.max(1, diffMonths + ajusteDias);
            duracionCalculada.value = `${totalMeses} ${totalMeses === 1 ? 'mes' : 'meses'}`;
        }
    }
    
    // Event listeners
    fechaInicio.addEventListener('change', calcularDuracion);
    fechaFin.addEventListener('change', calcularDuracion);
    tipoDuracion.addEventListener('change', calcularDuracion);
    
    // Actualizar fecha mínima de fin cuando cambia inicio
    fechaInicio.addEventListener('change', function() {
        if (this.value) {
            const minFin = new Date(this.value);
            minFin.setDate(minFin.getDate() + 1);
            fechaFin.min = minFin.toISOString().split('T')[0];
        }
    });
    
    // Calcular al cargar la página
    calcularDuracion();
});

// Función para generar preview (placeholder)
function generarPreview() {
    alert('Funcionalidad de vista previa estará disponible próximamente');
}

// Validación del formulario
document.getElementById('formRenovacion').addEventListener('submit', function(e) {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
    if (!fechaInicio || !fechaFin) {
        e.preventDefault();
        alert('Por favor complete todas las fechas requeridas');
        return;
    }
    
    if (new Date(fechaFin) <= new Date(fechaInicio)) {
        e.preventDefault();
        alert('La fecha de fin debe ser posterior a la fecha de inicio');
        return;
    }
    
    // Confirmar renovación
    if (!confirm('¿Está seguro de crear esta renovación? El contrato actual se marcará como renovado.')) {
        e.preventDefault();
        return;
    }
});
</script>
@endpush