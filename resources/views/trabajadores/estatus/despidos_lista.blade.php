@extends('layouts.app')

@section('title', 'Lista de Bajas - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0" style="background: linear-gradient(to right, #ffebee, #ffffff);">
                <div class="card-header py-3" style="background-color: #be0b0b;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-person-x-fill"></i> Lista de Trabajadores Dados de Baja
                        </h3>
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body py-3" style="background-color: #ffebee;">
                    <p class="mb-0 text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Gestión y seguimiento de trabajadores que han causado baja en el hotel
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #be0b0b !important;">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-2 text-danger"></i>
                    <h4 class="mt-2 mb-1 text-danger">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total Bajas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #f44336 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-month fs-2" style="color: #f44336;"></i>
                    <h4 class="mt-2 mb-1" style="color: #f44336;">{{ $stats['este_mes'] }}</h4>
                    <small class="text-muted">Este Mes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #e91e63 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-year fs-2" style="color: #e91e63;"></i>
                    <h4 class="mt-2 mb-1" style="color: #e91e63;">{{ $stats['este_año'] }}</h4>
                    <small class="text-muted">Este Año</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #9c27b0 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-hand-thumbs-up fs-2" style="color: #9c27b0;"></i>
                    <h4 class="mt-2 mb-1" style="color: #9c27b0;">{{ $stats['voluntarias'] }}</h4>
                    <small class="text-muted">Renuncias Voluntarias</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #f8f9fa;">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel"></i> Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('despidos.index') }}" class="row g-3">
                        <!-- Búsqueda general -->
                        <div class="col-md-4">
                            <label for="search" class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nombre del trabajador o motivo...">
                            </div>
                        </div>

                        <!-- Condición de salida -->
                        <div class="col-md-3">
                            <label for="condicion_salida" class="form-label">Condición de Salida</label>
                            <select class="form-select" id="condicion_salida" name="condicion_salida">
                                <option value="">Todas las condiciones</option>
                                @foreach($condiciones as $condicion)
                                    <option value="{{ $condicion }}" 
                                            {{ request('condicion_salida') == $condicion ? 'selected' : '' }}>
                                        {{ $condicion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha desde -->
                        <div class="col-md-2">
                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_desde" 
                                   name="fecha_desde" 
                                   value="{{ request('fecha_desde') }}">
                        </div>

                        <!-- Fecha hasta -->
                        <div class="col-md-2">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_hasta" 
                                   name="fecha_hasta" 
                                   value="{{ request('fecha_hasta') }}">
                        </div>

                        <!-- Botones -->
                        <div class="col-md-1 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="{{ route('despidos.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de bajas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f8f9fa;">
                    <h6 class="mb-0">
                        <i class="bi bi-table"></i> 
                        Listado de Bajas ({{ $despidos->total() }} registros)
                    </h6>
                    <small class="text-muted">
                        Mostrando {{ $despidos->firstItem() ?? 0 }} - {{ $despidos->lastItem() ?? 0 }} 
                        de {{ $despidos->total() }} resultados
                    </small>
                </div>
                <div class="card-body p-0">
                    @if($despidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th>Trabajador</th>
                                        <th>Área/Cargo</th>
                                        <th>Fecha de Baja</th>
                                        <th>Condición de Salida</th>
                                        <th>Motivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($despidos as $despido)
                                        <tr>
                                            <!-- Información del trabajador -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="background-color: #be0b0b;">
                                                        {{ substr($despido->trabajador->nombre_trabajador, 0, 1) }}{{ substr($despido->trabajador->ape_pat, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $despido->trabajador->nombre_completo }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID: {{ $despido->trabajador->id_trabajador }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Área y cargo -->
                                            <td>
                                                @if($despido->trabajador->fichaTecnica)
                                                    <div>
                                                        <strong>{{ $despido->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $despido->trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Sin información</span>
                                                @endif
                                            </td>

                                            <!-- Fecha de baja -->
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($despido->fecha_baja)->diffForHumans() }}
                                                </small>
                                            </td>

                                            <!-- Condición de salida -->
                                            <td>
                                                <span class="badge 
                                                    @switch($despido->condicion_salida)
                                                        @case('Voluntaria') bg-info @break
                                                        @case('Despido con Causa') bg-danger @break
                                                        @case('Despido sin Causa') bg-warning text-dark @break
                                                        @case('Mutuo Acuerdo') bg-primary @break
                                                        @case('Abandono de Trabajo') bg-dark @break
                                                        @case('Fin de Contrato') bg-secondary @break
                                                        @default bg-light text-dark
                                                    @endswitch
                                                ">
                                                    {{ $despido->condicion_salida }}
                                                </span>
                                            </td>

                                            <!-- Motivo (truncado) -->
                                            <td>
                                                <div class="motivo-truncado" style="max-width: 200px;">
                                                    {{ Str::limit($despido->motivo, 50) }}
                                                    @if(strlen($despido->motivo) > 50)
                                                        <i class="bi bi-three-dots text-muted" 
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ $despido->motivo }}"></i>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Acciones -->
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Ver detalles -->
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalDetalles{{ $despido->id_baja }}"
                                                            title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <!-- Reactivar trabajador -->
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalReactivar{{ $despido->id_baja }}"
                                                            title="Reactivar trabajador">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                </div>

                                                <!-- Modal de detalles de la baja -->
                                                <div class="modal fade" id="modalDetalles{{ $despido->id_baja }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #be0b0b; color: white;">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-person-x-fill"></i> Detalles de la Baja
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
                                                                                        {{ substr($despido->trabajador->nombre_trabajador, 0, 1) }}{{ substr($despido->trabajador->ape_pat, 0, 1) }}
                                                                                    </div>
                                                                                </div>
                                                                                <h5 class="text-center text-primary mb-3">{{ $despido->trabajador->nombre_completo }}</h5>
                                                                                
                                                                                <div class="row text-sm">
                                                                                    <div class="col-12 mb-2">
                                                                                        <strong>ID:</strong> {{ $despido->trabajador->id_trabajador }}
                                                                                    </div>
                                                                                    @if($despido->trabajador->fichaTecnica)
                                                                                    <div class="col-12 mb-2">
                                                                                        <strong>Área:</strong> {{ $despido->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}
                                                                                    </div>
                                                                                    <div class="col-12 mb-2">
                                                                                        <strong>Cargo:</strong> {{ $despido->trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                                                                                    </div>
                                                                                    @endif
                                                                                    <div class="col-12 mb-2">
                                                                                        <strong>Fecha Ingreso:</strong> 
                                                                                        <span class="badge bg-success">{{ $despido->trabajador->fecha_ingreso->format('d/m/Y') }}</span>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <strong>Estado Actual:</strong> 
                                                                                        <span class="badge bg-danger">{{ ucfirst($despido->trabajador->estatus) }}</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Información de la baja -->
                                                                    <div class="col-md-6 mb-4">
                                                                        <div class="card border-danger">
                                                                            <div class="card-header bg-danger text-white">
                                                                                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Datos de la Baja</h6>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-bold">Fecha de Baja:</label>
                                                                                    <div>
                                                                                        <span class="badge bg-danger fs-6">
                                                                                            {{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}
                                                                                        </span>
                                                                                        <br>
                                                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($despido->fecha_baja)->diffForHumans() }}</small>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-bold">Condición de Salida:</label>
                                                                                    <div>
                                                                                        <span class="badge fs-6
                                                                                            @switch($despido->condicion_salida)
                                                                                                @case('Voluntaria') bg-info @break
                                                                                                @case('Despido con Causa') bg-danger @break
                                                                                                @case('Despido sin Causa') bg-warning text-dark @break
                                                                                                @case('Mutuo Acuerdo') bg-primary @break
                                                                                                @case('Abandono de Trabajo') bg-dark @break
                                                                                                @case('Fin de Contrato') bg-secondary @break
                                                                                                @default bg-light text-dark
                                                                                            @endswitch
                                                                                        ">
                                                                                            {{ $despido->condicion_salida }}
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-bold">Tiempo en la Empresa:</label>
                                                                                    <div class="text-muted">
                                                                                        {{ $despido->trabajador->fecha_ingreso->diffForHumans(\Carbon\Carbon::parse($despido->fecha_baja), true) }}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Motivo de la baja -->
                                                                    <div class="col-12 mb-3">
                                                                        <div class="card border-warning">
                                                                            <div class="card-header bg-warning">
                                                                                <h6 class="mb-0"><i class="bi bi-chat-text"></i> Motivo de la Baja</h6>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="bg-light p-3 rounded">
                                                                                    {{ $despido->motivo }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Observaciones (si existen) -->
                                                                    @if($despido->observaciones)
                                                                    <div class="col-12">
                                                                        <div class="card border-info">
                                                                            <div class="card-header bg-info text-white">
                                                                                <h6 class="mb-0"><i class="bi bi-sticky"></i> Observaciones Adicionales</h6>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="bg-light p-3 rounded">
                                                                                    {{ $despido->observaciones }}
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
                                                                <button type="button" 
                                                                        class="btn btn-success" 
                                                                        data-bs-dismiss="modal"
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalReactivar{{ $despido->id_baja }}">
                                                                    <i class="bi bi-arrow-clockwise"></i> Reactivar Trabajador
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal de confirmación para reactivar -->
                                                <div class="modal fade" id="modalReactivar{{ $despido->id_baja }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reactivar Trabajador</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>¿Estás seguro de que deseas reactivar a:</p>
                                                                <div class="alert alert-info">
                                                                    <strong>{{ $despido->trabajador->nombre_completo }}</strong><br>
                                                                    <small>Esta acción eliminará el registro de baja y cambiará su estado a "Activo"</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <form action="{{ route('despidos.cancelar', $despido->id_baja) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="bi bi-arrow-clockwise"></i> Reactivar
                                                                    </button>
                                                                </form>
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

                        <!-- Paginación -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        Mostrando {{ $despidos->firstItem() ?? 0 }} - {{ $despidos->lastItem() ?? 0 }} 
                                        de {{ $despidos->total() }} registros
                                    </small>
                                </div>
                                <div>
                                    {{ $despidos->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Estado vacío -->
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No se encontraron bajas</h5>
                            <p class="text-muted mb-0">
                                @if(request()->hasAny(['search', 'condicion_salida', 'fecha_desde', 'fecha_hasta']))
                                    No hay resultados que coincidan con los filtros aplicados.
                                    <br>
                                    <a href="{{ route('despidos.index') }}" class="btn btn-link">Limpiar filtros</a>
                                @else
                                    No hay registros de bajas en el sistema.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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

.card-hover:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.motivo-truncado {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<!-- Scripts para tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection