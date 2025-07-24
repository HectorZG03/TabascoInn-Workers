@extends('layouts.app')

@section('title', 'Gestión de Gerentes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-users-cog text-primary me-2"></i>
                        Gestión de Gerentes
                    </h2>
                    <p class="text-muted mb-0">Administra la lista de gerentes del sistema</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGerente">
                    <i class="fas fa-plus me-2"></i>Nuevo Gerente
                </button>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('gerentes.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Buscar gerente</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="buscar" 
                               value="{{ request('buscar') }}" 
                               placeholder="Nombre o apellidos...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="activos" {{ request('estatus') == 'activos' ? 'selected' : '' }}>Activos</option>
                        <option value="inactivos" {{ request('estatus') == 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('gerentes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de gerentes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Lista de Gerentes
                <span class="badge bg-primary ms-2">{{ $gerentes->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($gerentes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Descripción</th>
                                <th>Estatus</th>
                                <th>Fecha Registro</th>
                                <th width="140">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gerentes as $gerente)
                            <tr>
                                <td>
                                    <strong>{{ $gerente->nombre_completo }}</strong>
                                </td>
                                <td>
                                    @if($gerente->telefono)
                                        <span class="text-muted">
                                            <i class="fas fa-phone me-1"></i>{{ $gerente->telefono_formateado }}
                                        </span>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($gerente->descripcion)
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $gerente->descripcion }}">
                                            {{ $gerente->descripcion }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin descripción</span>
                                    @endif
                                </td>
                                <td>
                                    @if($gerente->activo)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $gerente->created_at->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Botón Editar -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                title="Editar gerente"
                                                onclick="editarGerente({{ $gerente->id }}, '{{ $gerente->nombre }}', '{{ $gerente->apellido_paterno }}', '{{ $gerente->apellido_materno }}', '{{ $gerente->telefono }}', '{{ addslashes($gerente->descripcion) }}')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <!-- Botón Eliminar -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                title="Eliminar gerente"
                                                onclick="confirmarEliminacion({{ $gerente->id }}, '{{ $gerente->nombre_completo }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                                                                <!-- Botón Activar/Desactivar -->
                                        <form action="{{ route('gerentes.toggle-estatus', $gerente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $gerente->activo ? 'warning' : 'success' }}" 
                                                    title="{{ $gerente->activo ? 'Desactivar' : 'Activar' }} gerente">
                                                @if($gerente->activo)
                                                    <i class="bi bi-toggle-off"></i>
                                                @else
                                                    <i class="bi bi-toggle-on"></i>
                                                @endif
                                            </button>
                                        </form>
                                        
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No hay gerentes registrados</h5>
                    <p class="text-muted">Comienza agregando el primer gerente del sistema</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGerente">
                        <i class="bi bi-plus me-2"></i>Agregar Gerente
                    </button>
                </div>
            @endif
        </div>
        
        @if($gerentes->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Mostrando {{ $gerentes->firstItem() }} - {{ $gerentes->lastItem() }} de {{ $gerentes->total() }} gerentes
                </div>
                {{ $gerentes->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal para crear/editar gerente -->
<div class="modal fade" id="modalGerente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formGerente" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-tie me-2"></i>
                        <span id="tituloModal">Nuevo Gerente</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" id="telefono" class="form-control" 
                                   placeholder="Ej: (999) 123-4567">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" 
                                      placeholder="Información adicional sobre el gerente..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form para eliminación -->
<form id="formEliminar" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection


<script>
function editarGerente(id, nombre, apellidoPaterno, apellidoMaterno, telefono, descripcion) {
    // Cambiar título del modal
    document.getElementById('tituloModal').textContent = 'Editar Gerente';
    
    // Cambiar acción del formulario
    document.getElementById('formGerente').action = `/configuracion/gerentes/${id}`;
    
    // Agregar método PUT
    let methodInput = document.getElementById('formGerente').querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        document.getElementById('formGerente').appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Llenar campos
    document.getElementById('nombre').value = nombre;
    document.getElementById('apellido_paterno').value = apellidoPaterno;
    document.getElementById('apellido_materno').value = apellidoMaterno || '';
    document.getElementById('telefono').value = telefono || '';
    document.getElementById('descripcion').value = descripcion || '';
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalGerente')).show();
}

function confirmarEliminacion(id, nombreCompleto) {
    if (confirm(`¿Estás seguro de que deseas eliminar al gerente "${nombreCompleto}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.getElementById('formEliminar');
        form.action = `/configuracion/gerentes/${id}`;
        form.submit();
    }
}

// Limpiar modal al cerrarse
document.getElementById('modalGerente').addEventListener('hidden.bs.modal', function () {
    // Resetear formulario
    document.getElementById('formGerente').reset();
    
    // Resetear título
    document.getElementById('tituloModal').textContent = 'Nuevo Gerente';
    
    // Resetear acción
    document.getElementById('formGerente').action = '{{ route("gerentes.store") }}';
    
    // Remover método PUT si existe
    const methodInput = document.getElementById('formGerente').querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }
});
</script>