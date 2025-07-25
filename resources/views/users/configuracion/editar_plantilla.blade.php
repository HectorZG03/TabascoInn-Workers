@extends('layouts.app')

@section('title', 'Editar Plantilla de Contrato')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="bi bi-pencil-square text-primary"></i>
                                Editar: {{ $plantilla->nombre_plantilla }}
                            </h4>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ $plantilla->estado_color }}">
                                    {{ $plantilla->estado_text }}
                                </span>
                                <span class="badge bg-primary">
                                    {{ $plantilla->tipo_contrato_text }}
                                </span>
                                <span class="badge bg-info">
                                    {{ $plantilla->version_text }}
                                </span>
                            </div>
                            <p class="text-muted mb-0">
                                Modifica esta plantilla - se creará una nueva versión automáticamente
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="{{ route('configuracion.plantillas.show', $plantilla) }}" 
                                   class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i> Ver Original
                                </a>
                                <a href="{{ route('configuracion.plantillas.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerta informativa sobre versionado --}}
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <div>
            <strong>Versionado automático:</strong> Al guardar se creará la versión {{ $plantilla->version + 1 }} y se activará automáticamente. 
            La versión {{ $plantilla->version }} se conservará en el historial.
        </div>
    </div>

    <form action="{{ route('configuracion.plantillas.update', $plantilla) }}" 
          method="POST" 
          id="formEditarPlantilla">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Panel izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    Editor de Contenido
                                </h5>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" id="btnPreview">
                                        <i class="bi bi-eye"></i> Vista Previa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnInsertarVariable">
                                        <i class="bi bi-braces"></i> Variables
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- Editor TinyMCE --}}
                        <textarea name="contenido_html" 
                                  id="editorContenido" 
                                  class="form-control @error('contenido_html') is-invalid @enderror">{{ old('contenido_html', $plantilla->contenido_html) }}</textarea>
                        @error('contenido_html')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Configuración básica --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-gear text-primary"></i>
                            Configuración de la Nueva Versión
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_plantilla" class="form-label">Nombre de la Plantilla *</label>
                                <input type="text" 
                                       class="form-control @error('nombre_plantilla') is-invalid @enderror" 
                                       id="nombre_plantilla" 
                                       name="nombre_plantilla" 
                                       value="{{ old('nombre_plantilla', $plantilla->nombre_plantilla) }}" 
                                       required>
                                <div class="form-text">Mantén el mismo nombre para conservar el historial de versiones</div>
                                @error('nombre_plantilla')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_contrato" class="form-label">Tipo de Contrato *</label>
                                <select class="form-select @error('tipo_contrato') is-invalid @enderror" 
                                        id="tipo_contrato" 
                                        name="tipo_contrato" 
                                        required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="determinado" {{ old('tipo_contrato', $plantilla->tipo_contrato) == 'determinado' ? 'selected' : '' }}>
                                        Solo Contratos Determinados
                                    </option>
                                    <option value="indeterminado" {{ old('tipo_contrato', $plantilla->tipo_contrato) == 'indeterminado' ? 'selected' : '' }}>
                                        Solo Contratos Indeterminados
                                    </option>
                                    <option value="ambos" {{ old('tipo_contrato', $plantilla->tipo_contrato) == 'ambos' ? 'selected' : '' }}>
                                        Ambos Tipos de Contrato
                                    </option>
                                </select>
                                @error('tipo_contrato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de Cambios *</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Describe qué cambios incluye esta nueva versión..."
                                      required>{{ old('descripcion') }}</textarea>
                            <div class="form-text">Explica qué modificaste en esta versión para el historial</div>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Información de la nueva versión --}}
                        <div class="alert alert-light border">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Versión actual:</strong><br>
                                    <span class="badge bg-info">{{ $plantilla->version_text }}</span>
                                    <small class="text-muted d-block">{{ $plantilla->updated_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>Nueva versión:</strong><br>
                                    <span class="badge bg-success">v{{ $plantilla->version + 1 }}</span>
                                    <small class="text-muted d-block">{{ now()->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>Estado:</strong><br>
                                    <span class="badge bg-warning">Se activará automáticamente</span>
                                    <small class="text-muted d-block">Reemplazará la versión actual</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('configuracion.plantillas.show', $plantilla) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                                <a href="{{ route('configuracion.plantillas.index') }}" 
                                   class="btn btn-outline-info ms-2">
                                    <i class="bi bi-list"></i> Ver Todas
                                </a>
                            </div>
                            <button type="submit" class="btn btn-success" id="btnActualizar">
                                <i class="bi bi-check-lg"></i> 
                                Crear Versión {{ $plantilla->version + 1 }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel derecho: Variables --}}
            <div class="col-lg-4">
                {{-- Información de la plantilla actual --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle text-primary"></i>
                            Plantilla Actual
                        </h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Versión:</dt>
                            <dd class="col-7">{{ $plantilla->version_text }}</dd>
                            
                            <dt class="col-5">Estado:</dt>
                            <dd class="col-7">
                                <span class="badge bg-{{ $plantilla->estado_color }}">
                                    {{ $plantilla->estado_text }}
                                </span>
                            </dd>
                            
                            <dt class="col-5">Tipo:</dt>
                            <dd class="col-7">{{ $plantilla->tipo_contrato_text }}</dd>
                            
                            <dt class="col-5">Variables:</dt>
                            <dd class="col-7">
                                <span class="badge bg-info">{{ count($plantilla->variables_utilizadas ?? []) }}</span>
                            </dd>
                            
                            <dt class="col-5">Modificada:</dt>
                            <dd class="col-7">
                                {{ $plantilla->updated_at->diffForHumans() }}
                                @if($plantilla->modificador)
                                    <br><small class="text-muted">por {{ $plantilla->modificador->name }}</small>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Selector de variables --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-braces"></i>
                            Variables Disponibles
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="buscarVariable" 
                                   placeholder="Buscar variable...">
                        </div>
                        <div class="accordion accordion-flush" id="accordionVariables">
                            @foreach($variablesPorCategoria as $categoria => $grupo)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ $loop->index }}">
                                            <i class="bi bi-folder2-open me-2"></i>
                                            {{ $grupo['nombre'] }}
                                            <span class="badge bg-secondary ms-2">{{ count($grupo['variables']) }}</span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $loop->index }}" 
                                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                         data-bs-parent="#accordionVariables">
                                        <div class="accordion-body p-0">
                                            @foreach($grupo['variables'] as $variable)
                                                <div class="p-2 border-bottom variable-item" 
                                                     data-variable="{{ $variable->nombre_variable }}"
                                                     data-etiqueta="{{ $variable->etiqueta }}"
                                                     data-ejemplo="{{ $variable->formato_ejemplo }}">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <div class="fw-bold text-dark small">{{ $variable->etiqueta }}</div>
                                                            <code class="text-primary small variable-codigo" 
                                                                  data-variable="{{ $variable->variable_formateada }}" 
                                                                  style="cursor: pointer;">
                                                                {{ $variable->variable_formateada }}
                                                            </code>
                                                            @if($variable->formato_ejemplo)
                                                                <div class="text-muted small">{{ $variable->formato_ejemplo }}</div>
                                                            @endif
                                                            @if($variable->obligatoria)
                                                                <span class="badge bg-danger small">Obligatoria</span>
                                                            @endif
                                                        </div>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary btn-insertar-variable" 
                                                                data-variable="{{ $variable->variable_formateada }}"
                                                                title="Insertar en el editor">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Panel de consejos --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-lightbulb"></i>
                            Consejos para Editar
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Usa <strong>Vista Previa</strong> frecuentemente
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Describe bien tus cambios en "Descripción"
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                La nueva versión se activa automáticamente
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Puedes volver a la versión anterior si es necesario
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modal de Vista Previa --}}
<div class="modal fade" id="modalPreview" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Vista Previa del Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Trabajador de Prueba:</label>
                        <select class="form-select" id="trabajadorPreview">
                            <option value="">Datos de ejemplo</option>
                            {{-- Se llenará con AJAX --}}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Contrato:</label>
                        <select class="form-select" id="tipoContratoPreview">
                            <option value="determinado">Tiempo Determinado</option>
                            <option value="indeterminado">Tiempo Indeterminado</option>
                        </select>
                    </div>
                </div>
                <hr>
                <div id="contenidoPreview" style="height: 600px; overflow-y: auto; border: 1px solid #dee2e6; padding: 20px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-hourglass-split"></i>
                        Generando vista previa...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarPreview">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- TinyMCE --}}
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando editor de plantillas (EDICIÓN)');

    let editorInstance = null;

    // ===== CONFIGURAR TINYMCE =====
    tinymce.init({
        selector: '#editorContenido',
        height: 600,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
            'pagebreak', 'nonbreaking'
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | code | fullscreen',
        content_style: `
            body { 
                font-family: DejaVu Sans, sans-serif; 
                font-size: 12px; 
                line-height: 1.4; 
                margin: 40px; 
                color: #000; 
            }
            .clausula-numero { font-weight: bold; text-decoration: underline; }
            .bold { font-weight: bold; }
            .center { text-align: center; }
            .uppercase { text-transform: uppercase; }
        `,
        setup: function(editor) {
            editor.on('init', function() {
                editorInstance = editor;
                console.log('✅ TinyMCE inicializado para edición');
            });
        },
        language: 'es'
    });

    // ===== INSERTAR VARIABLES EN EL EDITOR =====
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-insertar-variable') || 
            e.target.closest('.btn-insertar-variable')) {
            
            const btn = e.target.classList.contains('btn-insertar-variable') ? 
                        e.target : e.target.closest('.btn-insertar-variable');
            
            const variable = btn.dataset.variable;
            
            if (editorInstance && variable) {
                editorInstance.insertContent(variable + ' ');
                console.log('📝 Variable insertada:', variable);
                
                // Feedback visual
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-plus"></i>';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 1000);
            }
        }
    });

    // ===== COPIAR VARIABLES AL HACER CLICK EN EL CÓDIGO =====
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('variable-codigo')) {
            const variable = e.target.dataset.variable;
            
            navigator.clipboard.writeText(variable).then(() => {
                e.target.style.backgroundColor = '#d4edda';
                setTimeout(() => {
                    e.target.style.backgroundColor = '';
                }, 500);
                console.log('📋 Variable copiada:', variable);
            });
        }
    });

    // ===== BUSCADOR DE VARIABLES =====
    const buscarVariable = document.getElementById('buscarVariable');
    if (buscarVariable) {
        buscarVariable.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase();
            const variables = document.querySelectorAll('.variable-item');
            
            variables.forEach(variable => {
                const etiqueta = variable.dataset.etiqueta.toLowerCase();
                const nombre = variable.dataset.variable.toLowerCase();
                const ejemplo = variable.dataset.ejemplo ? variable.dataset.ejemplo.toLowerCase() : '';
                
                if (etiqueta.includes(busqueda) || nombre.includes(busqueda) || ejemplo.includes(busqueda)) {
                    variable.style.display = 'block';
                } else {
                    variable.style.display = 'none';
                }
            });
        });
    }

    // ===== VISTA PREVIA =====
    const btnPreview = document.getElementById('btnPreview');
    const modalPreview = new bootstrap.Modal(document.getElementById('modalPreview'));
    
    if (btnPreview) {
        btnPreview.addEventListener('click', function() {
            generarVistaPrevia();
            modalPreview.show();
        });
    }

    const btnActualizarPreview = document.getElementById('btnActualizarPreview');
    if (btnActualizarPreview) {
        btnActualizarPreview.addEventListener('click', generarVistaPrevia);
    }

    function generarVistaPrevia() {
        if (!editorInstance) {
            console.warn('⚠️ Editor no disponible para vista previa');
            return;
        }

        const contenidoHtml = editorInstance.getContent();
        const trabajadorId = document.getElementById('trabajadorPreview').value;
        const tipoContrato = document.getElementById('tipoContratoPreview').value;
        const contenidoPreview = document.getElementById('contenidoPreview');
        
        // Mostrar loading
        contenidoPreview.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div class="mt-2">Generando vista previa...</div>
            </div>
        `;

        // Hacer petición AJAX
        fetch('{{ route("configuracion.plantillas.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                contenido_html: contenidoHtml,
                trabajador_id: trabajadorId || null,
                tipo_contrato: tipoContrato
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contenidoPreview.innerHTML = data.contenido_html;
                console.log('👀 Vista previa generada exitosamente');
            } else {
                contenidoPreview.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error: ${data.error || 'No se pudo generar la vista previa'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('❌ Error en vista previa:', error);
            contenidoPreview.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error de conexión al generar la vista previa
                </div>
            `;
        });
    }

    // ===== VALIDACIÓN DEL FORMULARIO =====
    const formEditarPlantilla = document.getElementById('formEditarPlantilla');
    if (formEditarPlantilla) {
        formEditarPlantilla.addEventListener('submit', function(e) {
            if (editorInstance) {
                // Asegurar que el contenido del editor se guarde en el textarea
                editorInstance.save();
            }
            
            const contenido = document.getElementById('editorContenido').value;
            const descripcion = document.getElementById('descripcion').value;
            
            if (!contenido.trim()) {
                e.preventDefault();
                alert('El contenido de la plantilla no puede estar vacío');
                return false;
            }
            
            if (!descripcion.trim()) {
                e.preventDefault();
                alert('Debes describir qué cambios hiciste en esta versión');
                document.getElementById('descripcion').focus();
                return false;
            }
            
            if (!confirm('¿Crear nueva versión {{ $plantilla->version + 1 }} y activarla automáticamente?')) {
                e.preventDefault();
                return false;
            }
            
            console.log('💾 Actualizando plantilla...');
        });
    }

    console.log('✅ Editor de plantillas (EDICIÓN) inicializado correctamente');
});
</script>
@endpush

@push('styles')
<style>
.variable-codigo:hover {
    background-color: #e3f2fd !important;
    border-radius: 3px;
    padding: 1px 3px;
}

.variable-item:hover {
    background-color: #f8f9fa;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0d6efd;
}

#contenidoPreview {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 12px;
    line-height: 1.4;
}

.tox-tinymce {
    border-radius: 0 0 0.375rem 0.375rem;
}

dl.row dt {
    font-weight: 600;
    color: #495057;
}

dl.row dd {
    color: #6c757d;
}
</style>
@endpush
@endsection