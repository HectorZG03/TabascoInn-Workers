@extends('layouts.app')

@section('title', 'Editor de Plantillas de Contrato')

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
                                Editor de Plantillas de Contrato
                            </h4>
                            <p class="text-muted mb-0">
                                Gestiona las plantillas de contratos laborales con variables dinámicas
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('configuracion.plantillas.create') }}" 
                               class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Nueva Plantilla
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
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

    <div class="row">
        {{-- Panel izquierdo: Lista de plantillas --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul text-primary"></i>
                        Plantillas Existentes
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($plantillas->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($plantillas as $plantilla)
                                <div class="list-group-item {{ $plantilla->activa ? 'border-start border-success border-4' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                {{ $plantilla->nombre_plantilla }}
                                                <span class="badge bg-{{ $plantilla->estado_color }} ms-2">
                                                    {{ $plantilla->estado_text }}
                                                </span>
                                            </h6>
                                            <p class="mb-1 text-muted small">
                                                {{ $plantilla->tipo_contrato_text }} - {{ $plantilla->version_text }}
                                            </p>
                                            <small class="text-muted">
                                                Modificado {{ $plantilla->updated_at->diffForHumans() }}
                                                @if($plantilla->modificador)
                                                    por {{ $plantilla->modificador->name }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('configuracion.plantillas.show', $plantilla) }}">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('configuracion.plantillas.edit', $plantilla) }}">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('configuracion.plantillas.toggle', $plantilla) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="dropdown-item {{ $plantilla->activa ? 'text-warning' : 'text-success' }}">
                                                            <i class="bi bi-{{ $plantilla->activa ? 'pause' : 'play' }}"></i>
                                                            {{ $plantilla->activa ? 'Desactivar' : 'Activar' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-info" 
                                                       href="{{ route('configuracion.plantillas.exportar', $plantilla->tipo_contrato) }}">
                                                        <i class="bi bi-download"></i> Exportar
                                                    </a>
                                                </li>
                                                {{-- EN EL DROPDOWN DE ACCIONES --}}
                                                <li>
                                                    <form action="{{ route('configuracion.plantillas.destroy', $plantilla) }}" 
                                                        method="POST" 
                                                        class="d-inline"
                                                        onsubmit="return confirm('¿Eliminar permanentemente esta plantilla? Esta acción no se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-plus text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-muted">No hay plantillas</h6>
                            <p class="text-muted small">Crea tu primera plantilla de contrato</p>
                            <a href="{{ route('configuracion.plantillas.create') }}" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-plus"></i> Crear Plantilla
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Estado de plantilla activa --}}
            @if($plantillaActiva)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-check-circle"></i>
                            Plantilla Activa
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6>{{ $plantillaActiva->nombre_plantilla }}</h6>
                        <p class="mb-2">
                            <span class="badge bg-primary">{{ $plantillaActiva->tipo_contrato_text }}</span>
                            <span class="badge bg-info">{{ $plantillaActiva->version_text }}</span>
                        </p>
                        <small class="text-muted">
                            Variables utilizadas: {{ count($plantillaActiva->variables_utilizadas ?? []) }}
                        </small>
                        <div class="mt-2">
                            <a href="{{ route('configuracion.plantillas.show', $plantillaActiva) }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Panel derecho: Variables disponibles --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-braces text-primary"></i>
                                Variables Disponibles
                            </h5>
                        </div>
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="filtroCategoria">
                                <option value="">Todas las categorías</option>
                                @foreach(\App\Models\VariableContrato::CATEGORIAS as $key => $nombre)
                                    <option value="{{ $key }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="contenedorVariables">
                        @foreach($variablesPorCategoria as $categoria => $grupo)
                            <div class="col-md-6 mb-4 categoria-grupo" data-categoria="{{ $categoria }}">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="bi bi-folder2-open"></i>
                                        {{ $grupo['nombre'] }}
                                    </h6>
                                    @foreach($grupo['variables'] as $variable)
                                        <div class="mb-2 variable-item" 
                                             data-categoria="{{ $categoria }}"
                                             data-variable="{{ $variable->nombre_variable }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <small class="text-muted fw-bold">{{ $variable->etiqueta }}</small>
                                                    @if($variable->obligatoria)
                                                        <span class="badge bg-danger ms-1" title="Variable obligatoria">
                                                            <i class="bi bi-exclamation"></i>
                                                        </span>
                                                    @endif
                                                    <div class="mt-1">
                                                        <code class="text-primary variable-codigo" 
                                                              data-variable="{{ $variable->variable_formateada }}"
                                                              style="cursor: pointer; user-select: all;"
                                                              title="Click para copiar">
                                                            {{ $variable->variable_formateada }}
                                                        </code>
                                                    </div>
                                                    @if($variable->formato_ejemplo)
                                                        <small class="text-muted d-block">
                                                            Ej: {{ $variable->formato_ejemplo }}
                                                        </small>
                                                    @endif
                                                    @if($variable->descripcion)
                                                        <small class="text-muted d-block">
                                                            {{ $variable->descripcion }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <small class="badge bg-{{ $variable->estado_color }}">
                                                    {{ $variable->tipo_dato_text }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
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
                        <i class="bi bi-info-circle"></i>
                        Ayuda Rápida
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Usar Variables:</h6>
                            <ul class="small">
                                <li>Click en el código de una variable para copiarlo</li>
                                <li>Las variables se escriben como <code>@{{variable_nombre}}</code></li>
                                <li>Variables con <span class="badge bg-danger">!</span> son obligatorias</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Tipos de Plantilla:</h6>
                            <ul class="small">
                                <li><strong>Determinado:</strong> Solo para contratos con fecha fin</li>
                                <li><strong>Indeterminado:</strong> Solo para contratos sin fecha fin</li>
                                <li><strong>Ambos:</strong> Se usa para cualquier tipo de contrato</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando editor de plantillas de contrato');
    
    // ===== FILTRO DE CATEGORÍAS =====
    const filtroCategoria = document.getElementById('filtroCategoria');
    const categoriasGrupos = document.querySelectorAll('.categoria-grupo');
    
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function() {
            const categoriaSeleccionada = this.value;
            
            categoriasGrupos.forEach(grupo => {
                if (!categoriaSeleccionada || grupo.dataset.categoria === categoriaSeleccionada) {
                    grupo.style.display = 'block';
                } else {
                    grupo.style.display = 'none';
                }
            });
            
            console.log('🔍 Filtro aplicado:', categoriaSeleccionada || 'Todas');
        });
    }
    
    // ===== COPIAR VARIABLES AL HACER CLICK =====
    const variablesCodigo = document.querySelectorAll('.variable-codigo');
    
    variablesCodigo.forEach(variable => {
        variable.addEventListener('click', function() {
            const textoVariable = this.dataset.variable;
            
            // Copiar al clipboard
            navigator.clipboard.writeText(textoVariable).then(() => {
                // Feedback visual
                const originalColor = this.style.backgroundColor;
                this.style.backgroundColor = '#d4edda';
                this.style.transition = 'background-color 0.3s';
                
                setTimeout(() => {
                    this.style.backgroundColor = originalColor;
                }, 500);
                
                // Mostrar tooltip
                mostrarTooltip(this, 'Copiado!');
                
                console.log('📋 Variable copiada:', textoVariable);
            }).catch(err => {
                console.error('❌ Error copiando variable:', err);
                mostrarTooltip(this, 'Error al copiar');
            });
        });
    });
    
    // ===== FUNCIÓN PARA MOSTRAR TOOLTIPS =====
    function mostrarTooltip(elemento, mensaje) {
        // Crear tooltip temporal
        const tooltip = document.createElement('div');
        tooltip.className = 'position-absolute bg-dark text-white p-1 rounded small';
        tooltip.style.cssText = 'top: -30px; left: 50%; transform: translateX(-50%); z-index: 1000;';
        tooltip.textContent = mensaje;
        
        // Posicionar relativo al elemento
        elemento.style.position = 'relative';
        elemento.appendChild(tooltip);
        
        // Remover después de 1 segundo
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 1000);
    }
    
    // ===== CONFIRMACIONES PARA ACCIONES IMPORTANTES =====
    const botonesActivar = document.querySelectorAll('form[action*="toggle"] button');
    
    botonesActivar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            const accion = this.textContent.trim();
            const confirmMessage = accion.includes('Desactivar') ? 
                '¿Desactivar esta plantilla? Los nuevos contratos no la usarán.' :
                '¿Activar esta plantilla? Se desactivarán otras plantillas del mismo tipo.';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
    
    console.log('✅ Editor de plantillas inicializado correctamente');
});
</script>


<style>
.variable-codigo:hover {
    background-color: #f8f9fa !important;
    border-radius: 4px;
    padding: 2px 4px;
}

.categoria-grupo {
    transition: opacity 0.3s ease;
}

.list-group-item {
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
@endsection