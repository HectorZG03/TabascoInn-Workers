{{-- resources/views/trabajadores/estatus/vacaciones_lista.blade.php --}}
@extends('layouts.app')

@section('title', 'Lista de Trabajadores con Vacaciones')

@section('content')
<div class="container-fluid px-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="bi bi-calendar-check text-primary"></i> Trabajadores con Vacaciones
            </h1>
            <p class="text-muted mb-0">Gestión y seguimiento de vacaciones del personal</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['activas'] }}</div>
                            <div class="small text-white-75">Activas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['pendientes'] }}</div>
                            <div class="small text-dark">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['finalizadas'] }}</div>
                            <div class="small text-white-75">Finalizadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['canceladas'] }}</div>
                            <div class="small text-white-75">Canceladas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar3 fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['total'] }}</div>
                            <div class="small text-white-75">Total Vacaciones</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fs-5 fw-bold">{{ $stats['trabajadores_con_vacaciones'] }}</div>
                            <div class="small text-white-75">Trabajadores</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('trabajadores.vacaciones.lista') }}" id="filtrosForm">
                <div class="row g-3">
                    {{-- Búsqueda por nombre --}}
                    <div class="col-md-3">
                        <label class="form-label small">Buscar trabajador</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Nombre, apellidos o CURP..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Estado de vacaciones --}}
                    <div class="col-md-2">
                        <label class="form-label small">Estado</label>
                        <select class="form-select" name="estado_vacacion">
                            <option value="todas" {{ request('estado_vacacion') == 'todas' ? 'selected' : '' }}>Todas</option>
                            <option value="activa" {{ request('estado_vacacion') == 'activa' ? 'selected' : '' }}>Activas</option>
                            <option value="pendiente" {{ request('estado_vacacion') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                            <option value="finalizada" {{ request('estado_vacacion') == 'finalizada' ? 'selected' : '' }}>Finalizadas</option>
                            <option value="cancelada" {{ request('estado_vacacion') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                        </select>
                    </div>

                    {{-- Área --}}
                    <div class="col-md-2">
                        <label class="form-label small">Área</label>
                        <select class="form-select" name="area">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre_area }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Año correspondiente --}}
                    <div class="col-md-2">
                        <label class="form-label small">Año</label>
                        <select class="form-select" name="año_correspondiente">
                            <option value="">Todos los años</option>
                            @foreach($añosDisponibles as $año)
                                <option value="{{ $año }}" {{ request('año_correspondiente') == $año ? 'selected' : '' }}>
                                    {{ $año }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periodo vacacional --}}
                    <div class="col-md-3">
                        <label class="form-label small">Periodo Vacacional</label>
                        <select class="form-select" name="periodo_vacacional">
                            <option value="">Todos los periodos</option>
                            @foreach($periodosDisponibles as $periodo)
                                <option value="{{ $periodo }}" {{ request('periodo_vacacional') == $periodo ? 'selected' : '' }}>
                                    {{ $periodo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rango de fechas de inicio --}}
                    <div class="col-md-3">
                        <label class="form-label small">Fecha inicio (desde)</label>
                        <input type="date" class="form-control" name="fecha_inicio_desde" 
                               value="{{ request('fecha_inicio_desde') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Fecha inicio (hasta)</label>
                        <input type="date" class="form-control" name="fecha_inicio_hasta" 
                               value="{{ request('fecha_inicio_hasta') }}">
                    </div>

                    {{-- Rango de fechas de fin --}}
                    <div class="col-md-3">
                        <label class="form-label small">Fecha fin (desde)</label>
                        <input type="date" class="form-control" name="fecha_fin_desde" 
                               value="{{ request('fecha_fin_desde') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Fecha fin (hasta)</label>
                        <input type="date" class="form-control" name="fecha_fin_hasta" 
                               value="{{ request('fecha_fin_hasta') }}">
                    </div>

                    {{-- Botones --}}
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="{{ route('trabajadores.vacaciones.lista') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Limpiar filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de trabajadores --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people"></i> Lista de Trabajadores
                </h5>
                <span class="badge bg-white text-primary">
                    {{ $trabajadores->total() }} trabajadores encontrados
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">Trabajador</th>
                            <th width="15%">Área</th>
                            <th width="10%">Estado Vacaciones</th>
                            <th width="10%">Periodo</th>
                            <th width="10%">Fecha Inicio</th>
                            <th width="10%">Fecha Fin</th>
                            <th width="5%">Días</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trabajadores as $trabajador)
                            @foreach($trabajador->vacaciones as $vacacion)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $trabajador->nombre_completo }}</div>
                                                <small class="text-muted">
                                                    <i class="bi bi-card-text"></i> {{ $trabajador->curp }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->categoria)
                                            <small>{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A' }}</small><br>
                                            <span class="text-muted small">{{ $trabajador->fichaTecnica->categoria->nombre_categoria }}</span>
                                        @else
                                            <span class="text-muted">Sin área</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $estadoConfig = \App\Models\VacacionesTrabajador::ESTADOS[$vacacion->estado] ?? null;
                                        @endphp
                                        @if($estadoConfig)
                                            <span class="badge bg-{{ $estadoConfig['color'] }}">
                                                <i class="{{ $estadoConfig['icono'] }}"></i> {{ $estadoConfig['texto'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($vacacion->estado) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $vacacion->periodo_vacacional }}</small><br>
                                        <span class="text-muted small">Año: {{ $vacacion->año_correspondiente }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $vacacion->fecha_inicio->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $vacacion->fecha_fin->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $vacacion->dias_solicitados }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('trabajadores.perfil.show', $trabajador) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver perfil completo">
                                                <i class="bi bi-person-badge"></i>
                                            </a>
                                            <a href="{{ route('trabajadores.vacaciones.show', $trabajador) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver vacaciones">
                                                <i class="bi bi-calendar-check"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">No se encontraron trabajadores con los filtros aplicados</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
       {{-- Paginación --}}
        @if($trabajadores->hasPages())
            <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="mb-2 mb-md-0">
                    Mostrando {{ $trabajadores->firstItem() }} a {{ $trabajadores->lastItem() }} 
                    de {{ $trabajadores->total() }} registros
                </div>
                <div class="pagination-wrapper">
                    {{ $trabajadores->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif

    </div>
</div>


<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-weight: bold;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    
    .text-white-75 {
        color: rgba(255, 255, 255, 0.75) !important;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    table-responsive {
    overflow-x: auto;
    }
    .table td, .table th {
        white-space: nowrap; /* Evita que se rompan textos largos */
    }
</style>



<script>
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

@endsection