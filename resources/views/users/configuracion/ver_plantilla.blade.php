@extends('layouts.app')

@section('title', 'Ver Plantilla de Contrato')

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
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                {{ $plantilla->nombre_plantilla }}
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
                                Creada {{ $plantilla->created_at->diffForHumans() }}
                                @if($plantilla->creador)
                                    por {{ $plantilla->creador->name }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="{{ route('configuracion.plantillas.edit', $plantilla) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
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

    <div class="row">
        {{-- Panel izquierdo: Información de la plantilla --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-primary"></i>
                        Información de la Plantilla
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Nombre:</dt>
                        <dd class="col-sm-7">{{ $plantilla->nombre_plantilla }}</dd>
                        
                        <dt class="col-sm-5">Tipo:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-primary">{{ $plantilla->tipo_contrato_text }}</span>
                        </dd>
                        
                        <dt class="col-sm-5">Versión:</dt>
                        <dd class="col-sm-7">{{ $plantilla->version_text }}</dd>
                        
                        <dt class="col-sm-5">Estado:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ $plantilla->estado_color }}">
                                {{ $plantilla->estado_text }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-5">Creado:</dt>
                        <dd class="col-sm-7">
                            {{ $plantilla->created_at->format('d/m/Y H:i') }}
                            @if($plantilla->creador)
                                <br><small class="text-muted">por {{ $plantilla->creador->name }}</small>
                            @endif
                        </dd>
                        
                        @if($plantilla->updated_at->gt($plantilla->created_at))
                            <dt class="col-sm-5">Modificado:</dt>
                            <dd class="col-sm-7">
                                {{ $plantilla->updated_at->format('d/m/Y H:i') }}
                                @if($plantilla->modificador)
                                    <br><small class="text-muted">por {{ $plantilla->modificador->name }}</small>
                                @endif
                            </dd>
                        @endif
                        
                        <dt class="col-sm-5">Variables:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-info">{{ count($plantilla->variables_utilizadas ?? []) }}</span>
                        </dd>
                    </dl>
                    
                    @if($plantilla->descripcion)
                        <hr>
                        <h6>Descripción:</h6>
                        <p class="text-muted">{{ $plantilla->descripcion }}</p>
                    @endif
                </div>
            </div>

            {{-- Variables utilizadas --}}
            @if($variablesUtilizadas->count() > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-braces text-primary"></i>
                            Variables Utilizadas
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($variablesUtilizadas as $variable)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $variable->etiqueta }}</h6>
                                            <code class="text-primary small">{{ $variable->variable_formateada }}</code>
                                            @if($variable->formato_ejemplo)
                                                <div class="text-muted small mt-1">
                                                    Ej: {{ $variable->formato_ejemplo }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $variable->prioridad_color }}">
                                                {{ $variable->prioridad_text }}
                                            </span>
                                            <span class="badge bg-secondary">
                                                {{ $variable->tipo_dato_text }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-tools text-primary"></i>
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('configuracion.plantillas.edit', $plantilla) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Editar Plantilla
                        </a>
                        
                        <button type="button" 
                                class="btn btn-outline-info" 
                                id="btnVistaPrevia">
                            <i class="bi bi-eye"></i> Vista Previa
                        </button>
                        
                        <form action="{{ route('configuracion.plantillas.toggle', $plantilla) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn btn-outline-{{ $plantilla->activa ? 'warning' : 'success' }} w-100"
                                    onclick="return confirm('{{ $plantilla->activa ? '¿Desactivar esta plantilla?' : '¿Activar esta plantilla?' }}')">
                                <i class="bi bi-{{ $plantilla->activa ? 'pause' : 'play' }}"></i>
                                {{ $plantilla->activa ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        
                        <a href="{{ route('configuracion.plantillas.exportar', $plantilla->tipo_contrato) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-download"></i> Exportar HTML
                        </a>
                        
                        <a href="{{ route('configuracion.plantillas.create', ['base' => $plantilla->id_plantilla]) }}" 
                           class="btn btn-outline-info">
                            <i class="bi bi-files"></i> Crear Copia
                        </a>
                        {{-- EN LA SECCIÓN DE ACCIONES --}}
                        <form action="{{ route('configuracion.plantillas.destroy', $plantilla) }}" 
                            method="POST" 
                            class="d-inline"
                            onsubmit="return confirm('¿Eliminar permanentemente esta plantilla? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 mt-2">
                                <i class="bi bi-trash"></i> Eliminar Permanentemente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel derecho: Contenido de la plantilla --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-code-square text-primary"></i>
                                Contenido de la Plantilla
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="vistaOptions" id="vistaHTML" checked>
                                <label class="btn btn-outline-primary" for="vistaHTML">
                                    <i class="bi bi-code"></i> HTML
                                </label>
                                
                                <input type="radio" class="btn-check" name="vistaOptions" id="vistaPreview">
                                <label class="btn btn-outline-primary" for="vistaPreview">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Contenido HTML --}}
                    <div id="contenidoHTML" class="p-3">
                        <pre><code class="language-html" style="max-height: 600px; overflow-y: auto;">{{ $plantilla->contenido_html }}</code></pre>
                    </div>
                    
                    {{-- Vista previa renderizada --}}
                    <div id="contenidoPreview" 
                         class="p-3" 
                         style="display: none; max-height: 600px; overflow-y: auto; border-left: 1px solid #dee2e6;">
                        <div class="text-center text-muted">
                            <i class="bi bi-hourglass-split"></i>
                            <div>Generando vista previa...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Prism.js para syntax highlighting --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando vista de plantilla');
    
    const vistaHTML = document.getElementById('vistaHTML');
    const vistaPreview = document.getElementById('vistaPreview');
    const contenidoHTML = document.getElementById('contenidoHTML');
    const contenidoPreview = document.getElementById('contenidoPreview');
    const btnVistaPrevia = document.getElementById('btnVistaPrevia');
    
    // ===== CAMBIO ENTRE VISTAS =====
    [vistaHTML, vistaPreview].forEach(radio => {
        radio.addEventListener('change', function() {
            if (vistaHTML.checked) {
                contenidoHTML.style.display = 'block';
                contenidoPreview.style.display = 'none';
            } else {
                contenidoHTML.style.display = 'none';
                contenidoPreview.style.display = 'block';
                generarVistaPrevia();
            }
        });
    });
    
    // ===== GENERAR VISTA PREVIA =====
    function generarVistaPrevia() {
        contenidoPreview.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div class="mt-2">Generando vista previa...</div>
            </div>
        `;
        
        fetch('{{ route("configuracion.plantillas.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                contenido_html: @json($plantilla->contenido_html),
                tipo_contrato: '{{ $plantilla->tipo_contrato }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contenidoPreview.innerHTML = data.contenido_html;
                console.log('👀 Vista previa generada');
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
    
    // ===== BOTÓN VISTA PREVIA INDEPENDIENTE =====
    if (btnVistaPrevia) {
        btnVistaPrevia.addEventListener('click', function() {
            vistaPreview.checked = true;
            vistaPreview.dispatchEvent(new Event('change'));
        });
    }
    
    console.log('✅ Vista de plantilla inicializada');
});
</script>
{{-- Prism.js CSS --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">

<style>
pre[class*="language-"] {
    margin: 0;
    border-radius: 0;
    background: #f8f9fa !important;
}

code[class*="language-"] {
    font-size: 0.875rem;
    line-height: 1.4;
}

#contenidoPreview {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 12px;
    line-height: 1.4;
    background: white;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

dl.row dt {
    font-weight: 600;
    color: #495057;
}

dl.row dd {
    color: #6c757d;
}
</style>

@endsection