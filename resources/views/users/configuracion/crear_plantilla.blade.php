@extends('layouts.app')

@section('title', isset($plantillaBase) ? 'Editar Plantilla de Contrato' : 'Nueva Plantilla de Contrato')

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
                                <i class="bi bi-{{ isset($plantillaBase) ? 'pencil' : 'plus-lg' }} text-primary"></i>
                                {{ isset($plantillaBase) ? 'Editar' : 'Nueva' }} Plantilla de Contrato
                            </h4>
                            <p class="text-muted mb-0">
                                {{ isset($plantillaBase) ? 'Modifica la plantilla existente' : 'Crea una nueva plantilla con variables dinámicas' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
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

    {{-- Alertas de éxito/error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ isset($plantillaBase) ? route('configuracion.plantillas.update', $plantillaBase) : route('configuracion.plantillas.store') }}" 
          method="POST" 
          id="formPlantilla">
        @csrf
        @if(isset($plantillaBase))
            @method('PUT')
        @endif

        <div class="row">
            {{-- Panel izquierdo: Editor --}}
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    Editor de Contenido HTML
                                </h5>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" id="btnPreview">
                                        <i class="bi bi-eye"></i> Vista Previa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFormatear">
                                        <i class="bi bi-code"></i> Formatear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- Editor HTML básico más grande --}}
                        <div class="position-relative">
                            <textarea name="contenido_html" 
                                      id="editorContenido" 
                                      class="form-control editor-html @error('contenido_html') is-invalid @enderror"
                                      rows="25"
                                      placeholder="Escribe el contenido HTML de la plantilla aquí. Usa variables como @{{trabajador_nombre_completo}}, @{{empresa_nombre}}, etc.">{{ old('contenido_html', $plantillaBase->contenido_html ?? $contenidoDefault ?? '') }}</textarea>
                            <div class="editor-toolbar">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Escribe HTML. Usa las variables del panel derecho insertándolas como @{{variable_nombre}}
                                </small>
                            </div>
                        </div>
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
                            Configuración de la Plantilla
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
                                       value="{{ old('nombre_plantilla', $plantillaBase->nombre_plantilla ?? 'Contrato Individual de Trabajo') }}" 
                                       required>
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
                                    <option value="determinado" {{ old('tipo_contrato', $plantillaBase->tipo_contrato ?? '') == 'determinado' ? 'selected' : '' }}>
                                        Solo Contratos Determinados
                                    </option>
                                    <option value="indeterminado" {{ old('tipo_contrato', $plantillaBase->tipo_contrato ?? '') == 'indeterminado' ? 'selected' : '' }}>
                                        Solo Contratos Indeterminados
                                    </option>
                                </select>
                                @error('tipo_contrato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de Cambios</label>
                            <textarea class="form-control textarea-large @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4" 
                                      placeholder="Describe qué cambios incluye esta versión...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if(!isset($plantillaBase))
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="activar_inmediatamente" 
                                       name="activar_inmediatamente" 
                                       value="1" 
                                       {{ old('activar_inmediatamente', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activar_inmediatamente">
                                    Activar inmediatamente (desactivará otras plantillas del mismo tipo)
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('configuracion.plantillas.index') }}" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-check-lg"></i> 
                                {{ isset($plantillaBase) ? 'Actualizar Plantilla' : 'Crear Plantilla' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel derecho: Variables --}}
            <div class="col-lg-4">
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
                                                                  style="cursor: pointer;"
                                                                  title="Click para copiar">
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

                {{-- Panel de ayuda --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-lightbulb"></i>
                            Consejos
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Click en <span class="badge bg-primary">+</span> para insertar variables
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Usa <strong>Vista Previa</strong> para probar con datos reales
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Las variables en <span class="badge bg-danger">rojo</span> son obligatorias
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Click en el código para copiarlo al portapapeles
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Usa HTML básico: &lt;p&gt;, &lt;strong&gt;, &lt;br&gt;, etc.
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
                <div class="alert alert-info d-none" id="alertaPreview">
                    <i class="bi bi-info-circle"></i>
                    <span id="mensajePreview"></span>
                </div>
                <hr>
                <div id="contenidoPreview" 
                     style="height: 600px; overflow-y: auto; border: 1px solid #dee2e6; padding: 20px; background: white;">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-eye-slash" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Vista Previa</h5>
                        <p>Haz clic en "Actualizar Vista Previa" para generar la vista previa</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnActualizarPreview">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar Vista Previa
                </button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando editor básico de plantillas');

    const editorContenido = document.getElementById('editorContenido');
    
    // ===== INSERTAR VARIABLES EN EL EDITOR =====
    document.addEventListener('click', function(e) {
        let btn = null;
        if (e.target.classList.contains('btn-insertar-variable')) {
            btn = e.target;
        } else if (e.target.closest('.btn-insertar-variable')) {
            btn = e.target.closest('.btn-insertar-variable');
        }
        
        if (btn) {
            e.preventDefault();
            const variable = btn.dataset.variable;
            
            if (!variable || !editorContenido) {
                mostrarMensaje('Error al insertar variable', 'error');
                return;
            }
            
            // Insertar variable en la posición del cursor
            const cursorPos = editorContenido.selectionStart;
            const textBefore = editorContenido.value.substring(0, cursorPos);
            const textAfter = editorContenido.value.substring(cursorPos);
            
            editorContenido.value = textBefore + variable + ' ' + textAfter;
            editorContenido.focus();
            editorContenido.setSelectionRange(cursorPos + variable.length + 1, cursorPos + variable.length + 1);
            
            // Feedback visual
            const originalHTML = btn.innerHTML;
            const originalClasses = btn.className;
            
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.className = btn.className.replace('btn-outline-primary', 'btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.className = originalClasses;
            }, 1000);
            
            mostrarMensaje(`Variable ${variable} insertada`, 'success');
            console.log('📝 Variable insertada:', variable);
        }
    });

    // ===== COPIAR VARIABLES AL HACER CLICK EN EL CÓDIGO =====
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('variable-codigo')) {
            const variable = e.target.dataset.variable;
            
            if (!variable) return;
            
            navigator.clipboard.writeText(variable).then(() => {
                const originalBg = e.target.style.backgroundColor;
                e.target.style.backgroundColor = '#d4edda';
                
                setTimeout(() => {
                    e.target.style.backgroundColor = originalBg;
                }, 1000);
                
                mostrarMensaje(`Variable ${variable} copiada al portapapeles`, 'success');
                console.log('📋 Variable copiada:', variable);
            }).catch(err => {
                console.error('❌ Error copiando variable:', err);
                mostrarMensaje('Error al copiar la variable', 'error');
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
                const etiqueta = variable.dataset.etiqueta?.toLowerCase() || '';
                const nombre = variable.dataset.variable?.toLowerCase() || '';
                const ejemplo = variable.dataset.ejemplo?.toLowerCase() || '';
                
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
    const modalPreview = document.getElementById('modalPreview');
    let bsModalPreview = null;
    
    if (modalPreview) {
        bsModalPreview = new bootstrap.Modal(modalPreview);
    }
    
    if (btnPreview) {
        btnPreview.addEventListener('click', function() {
            if (bsModalPreview) {
                bsModalPreview.show();
            }
        });
    }

    const btnActualizarPreview = document.getElementById('btnActualizarPreview');
    if (btnActualizarPreview) {
        btnActualizarPreview.addEventListener('click', generarVistaPrevia);
    }

    function generarVistaPrevia() {
        const contenidoHtml = editorContenido.value;
        const trabajadorId = document.getElementById('trabajadorPreview')?.value || '';
        const tipoContrato = document.getElementById('tipoContratoPreview')?.value || 'determinado';
        const contenidoPreview = document.getElementById('contenidoPreview');
        
        if (!contenidoHtml.trim()) {
            contenidoPreview.innerHTML = `
                <div class="alert alert-warning text-center py-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h5 class="mt-3">Contenido Vacío</h5>
                    <p>Añade contenido en el editor antes de generar la vista previa.</p>
                </div>
            `;
            return;
        }
        
        contenidoPreview.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <div class="mt-3">Generando vista previa...</div>
            </div>
        `;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('{{ route("configuracion.plantillas.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                mostrarMensaje('Vista previa generada correctamente', 'success');
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            contenidoPreview.innerHTML = `
                <div class="alert alert-danger text-center py-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h5 class="mt-3">Error</h5>
                    <p>${error.message}</p>
                </div>
            `;
            mostrarMensaje('Error al generar vista previa', 'error');
        });
    }

    // ===== BOTÓN FORMATEAR HTML =====
    const btnFormatear = document.getElementById('btnFormatear');
    if (btnFormatear) {
        btnFormatear.addEventListener('click', function() {
            const contenido = editorContenido.value;
            if (contenido.trim()) {
                // Formato básico del HTML
                const formateado = contenido
                    .replace(/></g, '>\n<')
                    .replace(/^\s+|\s+$/gm, '')
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0)
                    .join('\n');
                
                editorContenido.value = formateado;
                mostrarMensaje('HTML formateado', 'success');
            }
        });
    }

    // ===== FUNCIÓN PARA MOSTRAR MENSAJES =====
    function mostrarMensaje(mensaje, tipo = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo === 'error' ? 'danger' : tipo} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const iconos = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'error': 'x-circle',
            'info': 'info-circle'
        };
        
        alertDiv.innerHTML = `
            <i class="bi bi-${iconos[tipo] || 'info-circle'}"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // ===== VALIDACIÓN DEL FORMULARIO =====
    const formPlantilla = document.getElementById('formPlantilla');
    if (formPlantilla) {
        formPlantilla.addEventListener('submit', function(e) {
            const contenido = editorContenido.value;
            const nombre = document.getElementById('nombre_plantilla').value;
            const tipo = document.getElementById('tipo_contrato').value;
            
            if (!nombre.trim()) {
                e.preventDefault();
                mostrarMensaje('El nombre de la plantilla es obligatorio', 'error');
                document.getElementById('nombre_plantilla').focus();
                return false;
            }
            
            if (!tipo) {
                e.preventDefault();
                mostrarMensaje('Debes seleccionar un tipo de contrato', 'error');
                document.getElementById('tipo_contrato').focus();
                return false;
            }
            
            if (!contenido.trim()) {
                e.preventDefault();
                mostrarMensaje('El contenido de la plantilla no puede estar vacío', 'error');
                editorContenido.focus();
                return false;
            }
            
            mostrarMensaje('Guardando plantilla...', 'info');
        });
    }

    console.log('✅ Editor básico inicializado correctamente');
});
</script>

<style>
/* Editor HTML básico más grande */
.editor-html {
    min-height: 500px !important;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
}

.textarea-large {
    min-height: 120px !important;
    resize: vertical;
}

.editor-toolbar {
    position: absolute;
    bottom: 10px;
    right: 15px;
    background: rgba(255, 255, 255, 0.9);
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

/* Variables mejoradas */
.variable-codigo:hover {
    background-color: #e3f2fd !important;
    border-radius: 3px;
    padding: 2px 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.variable-item:hover {
    background-color: #f8f9fa;
}

/* Modal más grande */
.modal-xl .modal-dialog {
    max-width: 95%;
}

#contenidoPreview {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 12px;
    line-height: 1.4;
}

/* Alertas flotantes */
.alert.position-fixed {
    animation: slideInRight 0.3s ease-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0d6efd;
}

.btn-insertar-variable {
    transition: all 0.2s ease;
}

.btn-insertar-variable:hover {
    transform: scale(1.05);
}
</style>

@endsection

{{-- Contenido por defecto básico --}}
@php
$contenidoDefault = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Individual de Trabajo</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4; 
            margin: 40px; 
            color: #000; 
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .clausula { text-align: justify; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="center">
        <h1>CONTRATO INDIVIDUAL DE TRABAJO</h1>
        <h2>POR TIEMPO {{contrato_tipo}}</h2>
    </div>
    
    <p class="clausula">
        Contrato que celebran <strong>{{empresa_nombre}}</strong> como "PATRÓN" 
        y <strong>{{trabajador_nombre_completo}}</strong> como "TRABAJADOR".
    </p>
    
    <p class="clausula">
        <strong>PRIMERA:</strong> El trabajador prestará servicios como 
        {{categoria_puesto}} con salario diario de ${{salario_diario_numero}} 
        ({{salario_diario_texto}}).
    </p>
    
    <p class="clausula">
        <strong>SEGUNDA:</strong> Horario de {{horario_entrada}} a {{horario_salida}} horas.
    </p>
    
    <br><br>
    <div style="display: flex; justify-content: space-between;">
        <div style="text-align: center;">
            <hr style="width: 200px;">
            <p>{{empresa_representante}}<br>PATRÓN</p>
        </div>
        <div style="text-align: center;">
            <hr style="width: 200px;">
            <p>{{trabajador_nombre_completo}}<br>TRABAJADOR</p>
        </div>
    </div>
</body>
</html>';
@endphp