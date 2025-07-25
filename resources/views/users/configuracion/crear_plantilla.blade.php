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
                                  class="form-control @error('contenido_html') is-invalid @enderror">{{ old('contenido_html', $plantillaBase->contenido_html ?? $contenidoDefault ?? '') }}</textarea>
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
                                    <option value="ambos" {{ old('tipo_contrato', $plantillaBase->tipo_contrato ?? 'ambos') == 'ambos' ? 'selected' : '' }}>
                                        Ambos Tipos de Contrato
                                    </option>
                                </select>
                                @error('tipo_contrato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de Cambios</label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="2" 
                                      placeholder="Describe qué cambios incluye esta versión...">{{ old('descripcion') }}</textarea>
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
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Guarda frecuentemente tu trabajo
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
    console.log('🚀 Inicializando editor de plantillas');

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
                console.log('✅ TinyMCE inicializado');
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
    const formPlantilla = document.getElementById('formPlantilla');
    if (formPlantilla) {
        formPlantilla.addEventListener('submit', function(e) {
            if (editorInstance) {
                // Asegurar que el contenido del editor se guarde en el textarea
                editorInstance.save();
            }
            
            const contenido = document.getElementById('editorContenido').value;
            if (!contenido.trim()) {
                e.preventDefault();
                alert('El contenido de la plantilla no puede estar vacío');
                return false;
            }
            
            console.log('💾 Guardando plantilla...');
        });
    }

    console.log('✅ Editor de plantillas inicializado correctamente');
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
</style>
@endpush
@endsection

{{-- Contenido por defecto si no hay plantilla base --}}
@php
$contenidoDefault = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Individual de Trabajo</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; margin: 40px; color: #000; }
        .clausula { text-align: justify; margin-bottom: 12px; }
        .clausula-numero { font-weight: bold; text-decoration: underline; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .uppercase { text-transform: uppercase; }
    </style>
</head>
<body>
    <h1 class="center">
        CONTRATO INDIVIDUAL DE TRABAJO<br>
        POR TIEMPO {{contrato_tipo}}
    </h1>
    
    <p class="clausula">
        CONTRATO INDIVIDUAL DE TRABAJO QUE CELEBRAN POR UNA PARTE LA EMPRESA 
        <span class="bold uppercase">{{empresa_nombre}}</span> REPRESENTADA POR 
        {{empresa_representante}}, A LA CUAL EN LO SUCESIVO SE LE DENOMINARÁ "PATRÓN", 
        Y POR LA OTRA EL/LA C. <span class="bold uppercase">{{trabajador_nombre_completo}}</span>, 
        EN SU CALIDAD DE "TRABAJADOR", AL TENOR DE LAS SIGUIENTES:
    </p>
    
    <p class="clausula">
        <span class="clausula-numero">PRIMERA:</span> El trabajador se obliga a prestar sus servicios 
        en la categoría de <span class="bold">{{categoria_puesto}}</span>, percibiendo un salario diario 
        de <span class="bold">${{salario_diario_numero}} ({{salario_diario_texto}}) PESOS MEXICANOS</span>.
    </p>
    
    <p class="clausula">
        <span class="clausula-numero">SEGUNDA:</span> La duración del presente contrato será 
        {{contrato_fecha_inicio}} {{contrato_fecha_fin}}, con horario de {{horario_entrada}} 
        a {{horario_salida}} horas.
    </p>
    
    <!-- Agregar más cláusulas según necesidades -->
</body>
</html>
';
@endphp