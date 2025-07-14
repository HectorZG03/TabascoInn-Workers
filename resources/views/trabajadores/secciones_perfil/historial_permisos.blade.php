@extends('layouts.app')

@section('title', 'Historial de Permisos - ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock-history fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Historial de Permisos</h4>
                                <p class="mb-0 opacity-75">{{ $trabajador->nombre_completo }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-person"></i> Ver Perfil
                            </a>
                            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Lista de Trabajadores
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Trabajador -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                                style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem;">
                                {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-1">{{ $trabajador->nombre_completo }}</h5>
                            <p class="text-muted mb-1">
                                <i class="bi bi-briefcase"></i>
                                {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                            </p>
                            <span class="badge bg-{{ $trabajador->estatus_color }}">
                                <i class="{{ $trabajador->estatus_icono }}"></i> {{ $trabajador->estatus_texto }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h5 text-primary mb-0">{{ $permisos->total() }}</div>
                                    <small class="text-muted">Total Permisos</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 text-success mb-0">{{ $trabajador->permisosActivos()->count() }}</div>
                                    <small class="text-muted">Activos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Registro de Permisos
            </h5>
        </div>

    <div class="card-body">
        <!-- ✅ FORMULARIO DE FILTROS SIMPLE -->
        <form method="GET" action="{{ route('trabajadores.perfil.permisos.historial', $trabajador) }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        @foreach($tiposPermisos as $key => $tipo)
                            <option value="{{ $key }}" {{ ($filtros['tipo'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" {{ ($filtros['estado'] ?? '') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="finalizado" {{ ($filtros['estado'] ?? '') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="cancelado" {{ ($filtros['estado'] ?? '') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="{{ $filtros['desde'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $filtros['hasta'] ?? '' }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('trabajadores.perfil.permisos.historial', $trabajador) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- ✅ TABLA DE RESULTADOS -->
        @if($permisos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Período</th>
                            <th>Días</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permisos as $permiso)
                        <tr>
                            <td>
                                <span class="badge bg-info">{{ $permiso->tipo_permiso_texto }}</span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $permiso->motivo }}">
                                    {{ $permiso->motivo }}
                                </div>
                            </td>
                            <td>
                                <strong>{{ $permiso->fecha_inicio->format('d/m/Y') }}</strong> - 
                                <strong>{{ $permiso->fecha_fin->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">
                                    Solicitado: {{ $permiso->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $permiso->dias_de_permiso }} días</span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($permiso->estatus_permiso == 'activo') bg-success
                                    @elseif($permiso->estatus_permiso == 'finalizado') bg-info
                                    @else bg-secondary
                                    @endif">
                                    {{ $permiso->estatus_permiso_texto }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('permisos.detalle', $permiso) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ✅ PAGINACIÓN -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Mostrando {{ $permisos->firstItem() }} a {{ $permisos->lastItem() }} 
                        de {{ $permisos->total() }} registros
                    </small>
                </div>
                <div>
                    {{ $permisos->links() }}
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1 mb-3"></i>
                <h5>No se encontraron permisos</h5>
                <p class="mb-0">
                    @if(count($filtros) > 0)
                        No hay permisos que coincidan con los filtros aplicados.
                    @else
                        Este trabajador no tiene historial de permisos registrados.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
</div>

@include('components.alertas')
@endsection