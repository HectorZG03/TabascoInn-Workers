@extends('layouts.app')

@section('title', 'Gestión de Permisos Laborales - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-calendar-event-fill text-info"></i> Gestión de Permisos Laborales
                    </h2>
                    <p class="text-muted mb-0">Administra los permisos y ausencias del personal</p>
                </div>
                <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-people"></i> Ver Trabajadores
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['activos'] ?? 0 }}</div>
                            <div class="text-white-50">Permisos Activos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-range fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                            <div class="text-white-50">Total Permisos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-month fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['este_mes'] ?? 0 }}</div>
                            <div class="text-white-50">Este Mes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-x fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['vencidos'] ?? 0 }}</div>
                            <div class="text-dark">Vencidos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('permisos.index') }}">
                <div class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar Trabajador</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Nombre del trabajador...">
                    </div>

                    <!-- Tipo de Permiso -->
                    <div class="col-md-2">
                        <label for="tipo_permiso" class="form-label">Tipo de Permiso</label>
                        <select class="form-select" id="tipo_permiso" name="tipo_permiso">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposPermisos as $valor => $texto)
                                <option value="{{ $valor }}" 
                                        {{ request('tipo_permiso') == $valor ? 'selected' : '' }}>
                                    {{ $texto }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="activos" {{ request('estado') == 'activos' ? 'selected' : '' }}>Activos</option>
                            <option value="vencidos" {{ request('estado') == 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div class="col-md-2">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_desde" 
                               name="fecha_desde" 
                               value="{{ request('fecha_desde') }}">
                    </div>

                    <!-- Fecha Hasta -->
                    <div class="col-md-2">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_hasta" 
                               name="fecha_hasta" 
                               value="{{ request('fecha_hasta') }}">
                    </div>

                    <!-- Botones -->
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="{{ route('permisos.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Permisos -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list"></i> 
                Permisos Laborales ({{ $permisos->total() }} encontrados)
            </h6>
        </div>
        
        <div class="card-body p-0">
            @if($permisos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Trabajador</th>
                                <th>Tipo de Permiso</th>
                                <th>Periodo</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permisos as $permiso)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; border-radius: 50%; font-size: 12px;">
                                                {{ substr($permiso->trabajador->nombre_trabajador, 0, 1) }}{{ substr($permiso->trabajador->ape_pat, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $permiso->trabajador->nombre_completo }}</div>
                                                <div class="text-muted small">
                                                    {{ $permiso->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $colorTipo = [
                                                'vacaciones' => 'info',
                                                'incapacidad_medica' => 'warning',
                                                'licencia_maternidad' => 'primary',
                                                'licencia_paternidad' => 'primary',
                                                'licencia_sin_goce' => 'secondary',
                                                'permiso_especial' => 'success'
                                            ];
                                            $iconoTipo = [
                                                'vacaciones' => 'bi-calendar-heart',
                                                'incapacidad_medica' => 'bi-heart-pulse',
                                                'licencia_maternidad' => 'bi-person-hearts',
                                                'licencia_paternidad' => 'bi-person-hearts',
                                                'licencia_sin_goce' => 'bi-pause-circle',
                                                'permiso_especial' => 'bi-clock'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $colorTipo[$permiso->tipo_permiso] ?? 'secondary' }} fs-6">
                                            <i class="{{ $iconoTipo[$permiso->tipo_permiso] ?? 'bi-calendar' }}"></i>
                                            {{ $tiposPermisos[$permiso->tipo_permiso] ?? $permiso->tipo_permiso }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium">{{ $permiso->fecha_inicio->format('d/m/Y') }}</div>
                                            <div class="text-muted">{{ $permiso->fecha_fin->format('d/m/Y') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $permiso->dias_de_permiso }} día{{ $permiso->dias_de_permiso != 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($permiso->fecha_fin >= now())
                                            <span class="badge bg-success">Activo</span>
                                            @if($permiso->fecha_fin->diffInDays(now()) <= 3)
                                                <div class="text-warning small mt-1">
                                                    <i class="bi bi-exclamation-triangle"></i> Próximo a vencer
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Vencido</span>
                                            <div class="text-muted small mt-1">
                                                Hace {{ $permiso->fecha_fin->diffInDays(now()) }} días
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($permiso->observaciones)
                                            <div class="text-truncate" style="max-width: 150px;" title="{{ $permiso->observaciones }}">
                                                {{ $permiso->observaciones }}
                                            </div>
                                        @else
                                            <span class="text-muted">Sin observaciones</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Ver detalles -->
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalles{{ $permiso->id_permiso }}"
                                                    title="Ver Detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            @if($permiso->fecha_fin >= now())
                                                <button type="button" 
                                                        class="btn btn-outline-warning"
                                                        title="Finalizar Permiso"
                                                        onclick="finalizarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-outline-danger"
                                                        title="Cancelar Permiso"
                                                        onclick="cancelarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Modal de detalles del permiso -->
                                        <div class="modal fade" id="modalDetalles{{ $permiso->id_permiso }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background-color: #17a2b8; color: white;">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-calendar-event-fill"></i> Detalles del Permiso Laboral
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <!-- Información del trabajador -->
                                                            <div class="col-md-6 mb-4">
                                                                <div class="card border-primary">
                                                                    <div class="card-header bg-primary text-white">
                                                                        <h6 class="mb-0"><i class="bi bi-person"></i> Datos del Trabajador</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="text-center mb-3">
                                                                            <div class="avatar-circle mx-auto" style="background-color: #007bff; width: 60px; height: 60px; font-size: 18px;">
                                                                                {{ substr($permiso->trabajador->nombre_trabajador, 0, 1) }}{{ substr($permiso->trabajador->ape_pat, 0, 1) }}
                                                                            </div>
                                                                        </div>
                                                                        <h5 class="text-center text-primary mb-3">{{ $permiso->trabajador->nombre_completo }}</h5>
                                                                        
                                                                        <div class="row text-sm">
                                                                            <div class="col-12 mb-2">
                                                                                <strong>ID:</strong> {{ $permiso->trabajador->id_trabajador }}
                                                                            </div>
                                                                            @if($permiso->trabajador->fichaTecnica)
                                                                            <div class="col-12 mb-2">
                                                                                <strong>Área:</strong> {{ $permiso->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}
                                                                            </div>
                                                                            <div class="col-12 mb-2">
                                                                                <strong>Cargo:</strong> {{ $permiso->trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                                                                            </div>
                                                                            @endif
                                                                            <div class="col-12 mb-2">
                                                                                <strong>Fecha Ingreso:</strong> 
                                                                                <span class="badge bg-success">{{ $permiso->trabajador->fecha_ingreso->format('d/m/Y') }}</span>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <strong>Estado Actual:</strong> 
                                                                                <span class="badge bg-{{ $colorTipo[$permiso->trabajador->estatus] ?? 'secondary' }}">
                                                                                    {{ $tiposPermisos[$permiso->trabajador->estatus] ?? ucfirst($permiso->trabajador->estatus) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Información del permiso -->
                                                            <div class="col-md-6 mb-4">
                                                                <div class="card border-info">
                                                                    <div class="card-header bg-info text-white">
                                                                        <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Datos del Permiso</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Tipo de Permiso:</label>
                                                                            <div>
                                                                                <span class="badge bg-{{ $colorTipo[$permiso->tipo_permiso] ?? 'secondary' }} fs-6">
                                                                                    <i class="{{ $iconoTipo[$permiso->tipo_permiso] ?? 'bi-calendar' }}"></i>
                                                                                    {{ $tiposPermisos[$permiso->tipo_permiso] ?? $permiso->tipo_permiso }}
                                                                                </span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Periodo:</label>
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <small class="text-muted">Inicio:</small>
                                                                                    <div class="fw-medium">{{ $permiso->fecha_inicio->format('d/m/Y') }}</div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <small class="text-muted">Fin:</small>
                                                                                    <div class="fw-medium">{{ $permiso->fecha_fin->format('d/m/Y') }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Duración:</label>
                                                                            <div>
                                                                                <span class="badge bg-light text-dark border fs-6">
                                                                                    {{ $permiso->dias_de_permiso }} día{{ $permiso->dias_de_permiso != 1 ? 's' : '' }}
                                                                                </span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Estado:</label>
                                                                            <div>
                                                                                @if($permiso->fecha_fin >= now())
                                                                                    <span class="badge bg-success fs-6">Activo</span>
                                                                                    @if($permiso->fecha_fin->diffInDays(now()) <= 3)
                                                                                        <div class="text-warning small mt-1">
                                                                                            <i class="bi bi-exclamation-triangle"></i> Próximo a vencer en {{ $permiso->fecha_fin->diffInDays(now()) }} días
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="text-muted small mt-1">
                                                                                            Finaliza en {{ $permiso->fecha_fin->diffInDays(now()) }} días
                                                                                        </div>
                                                                                    @endif
                                                                                @else
                                                                                    <span class="badge bg-secondary fs-6">Vencido</span>
                                                                                    <div class="text-muted small mt-1">
                                                                                        Venció hace {{ $permiso->fecha_fin->diffInDays(now()) }} días
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Observaciones (si existen) -->
                                                            @if($permiso->observaciones)
                                                            <div class="col-12">
                                                                <div class="card border-warning">
                                                                    <div class="card-header bg-warning">
                                                                        <h6 class="mb-0"><i class="bi bi-sticky"></i> Observaciones</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="bg-light p-3 rounded">
                                                                            {{ $permiso->observaciones }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle"></i> Cerrar
                                                        </button>
                                                        
                                                        @if($permiso->fecha_fin >= now())
                                                            <button type="button" 
                                                                    class="btn btn-warning" 
                                                                    onclick="finalizarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')"
                                                                    data-bs-dismiss="modal">
                                                                <i class="bi bi-check-circle"></i> Finalizar Permiso
                                                            </button>
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-danger" 
                                                                    onclick="cancelarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')"
                                                                    data-bs-dismiss="modal">
                                                                <i class="bi bi-x-circle"></i> Cancelar Permiso
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Estado vacío -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-calendar-check text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">No se encontraron permisos</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'tipo_permiso', 'estado', 'fecha_desde', 'fecha_hasta']))
                            Intenta ajustar los filtros de búsqueda.
                        @else
                            Los permisos laborales aparecerán aquí cuando se asignen.
                        @endif
                    </p>
                </div>
            @endif
        </div>
        
        <!-- Paginación -->
        @if($permisos->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $permisos->firstItem() }} a {{ $permisos->lastItem() }} 
                        de {{ $permisos->total() }} permisos
                    </div>
                    {{ $permisos->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Estilos adicionales -->
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}
</style>

<script>
function finalizarPermiso(permisoId, nombreTrabajador) {
    if (confirm(`¿Está seguro de finalizar el permiso de ${nombreTrabajador}? El trabajador será reactivado.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permisos/${permisoId}/finalizar`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelarPermiso(permisoId, nombreTrabajador) {
    if (confirm(`¿Está seguro de cancelar el permiso de ${nombreTrabajador}? Esta acción eliminará el registro y reactivará al trabajador.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permisos/${permisoId}/cancelar`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection