@extends('layouts.app')

@section('title', 'Historial de Bajas - ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-x fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Historial de Bajas</h4>
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
                                <div class="col-4">
                                    <div class="h5 text-secondary mb-0">{{ $bajas->total() }}</div>
                                    <small class="text-muted">Total Bajas</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 text-danger mb-0">{{ $trabajador->despidosActivos() }}</div>
                                    <small class="text-muted">Activas</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 text-success mb-0">{{ $trabajador->despidosCancelados() }}</div>
                                    <small class="text-muted">Canceladas</small>
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
                <i class="bi bi-list-ul me-2"></i>Registro de Bajas
            </h5>
        </div>

    <div class="card-body">
        <!-- ✅ FORMULARIO DE FILTROS SIMPLE -->
        <form method="GET" action="{{ route('trabajadores.perfil.bajas.historial', $trabajador) }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" {{ ($filtros['estado'] ?? '') == 'activo' ? 'selected' : '' }}>Activas</option>
                        <option value="cancelado" {{ ($filtros['estado'] ?? '') == 'cancelado' ? 'selected' : '' }}>Canceladas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="condicion" class="form-label">Condición</label>
                    <select name="condicion" id="condicion" class="form-select">
                        <option value="">Todas</option>
                        @foreach($condiciones as $condicion)
                            <option value="{{ $condicion }}" {{ ($filtros['condicion'] ?? '') == $condicion ? 'selected' : '' }}>
                                {{ $condicion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo_baja" class="form-label">Tipo</label>
                    <select name="tipo_baja" id="tipo_baja" class="form-select">
                        <option value="">Todos</option>
                        @foreach($tiposBaja as $key => $tipo)
                            <option value="{{ $key }}" {{ ($filtros['tipo_baja'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="{{ $filtros['desde'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $filtros['hasta'] ?? '' }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('trabajadores.perfil.bajas.historial', $trabajador) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- ✅ TABLA DE RESULTADOS -->
        @if($bajas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha Baja</th>
                            <th>Condición</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Fecha Reintegro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bajas as $baja)
                        <tr>
                            <td>
                                <strong>{{ $baja->fecha_baja->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $baja->fecha_baja->diffForHumans() }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $baja->condicion_salida }}
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($baja->tipo_baja == 'temporal') bg-warning text-dark
                                    @else bg-info
                                    @endif">
                                    {{ $baja->tipo_baja_texto }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" 
                                     title="{{ $baja->motivo }}">
                                    {{ $baja->motivo }}
                                </div>
                            </td>
                            <td>
                                @if($baja->es_activo)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $baja->estado_texto }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $baja->estado_texto }}
                                    </span>
                                    @if($baja->fecha_cancelacion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $baja->fecha_cancelacion->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($baja->fecha_reintegro)
                                    <strong>{{ $baja->fecha_reintegro->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($baja->fecha_reintegro->isPast())
                                            Venció {{ $baja->fecha_reintegro->diffForHumans() }}
                                        @else
                                            {{ $baja->fecha_reintegro->diffForHumans() }}
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('despidos.detalle', $baja) }}" 
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
                        Mostrando {{ $bajas->firstItem() }} a {{ $bajas->lastItem() }} 
                        de {{ $bajas->total() }} registros
                    </small>
                </div>
                <div>
                    {{ $bajas->links() }}
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1 mb-3"></i>
                <h5>No se encontraron bajas</h5>
                <p class="mb-0">
                    @if(count($filtros) > 0)
                        No hay bajas que coincidan con los filtros aplicados.
                    @else
                        Este trabajador no tiene historial de bajas registradas.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
</div>

@include('components.alertas')
@endsection