@extends('layouts.app')

@section('title', 'Administración de Contratos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0" style="background: linear-gradient(135deg, #007A4D 0%, #005A3A 100%);">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 text-white mb-2">
                        <i class="bi bi-file-earmark-text-fill me-3"></i>
                        Administración de Contratos
                    </h1>
                    <p class="text-white-50 mb-0 fs-5">
                        Sistema completo de gestión de contratos laborales
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #007A4D !important;">
                <div class="card-body text-center">
                    <i class="bi bi-file-check fs-1 text-success mb-2"></i>
                    <h3 class="text-success">{{ $estadisticas['vigentes'] }}</h3>
                    <h6 class="text-muted">Vigentes</h6>
                    <small class="text-success">En período activo</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #6f42c1 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-check fs-1 text-primary mb-2"></i>
                    <h3 class="text-primary">{{ $estadisticas['activos'] }}</h3>
                    <h6 class="text-muted">Activos</h6>
                    <small class="text-primary">{{ $estadisticas['porcentaje_activos'] }}% del total</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #dc3545 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-file-x fs-1 text-danger mb-2"></i>
                    <h3 class="text-danger">{{ $estadisticas['expirados'] }}</h3>
                    <h6 class="text-muted">Expirados</h6>
                    <small class="text-muted">Activos vencidos</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #ffc107 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-1 text-warning mb-2"></i>
                    <h3 class="text-warning">{{ $estadisticas['proximos_vencer'] }}</h3>
                    <h6 class="text-muted">Próximos a Vencer</h6>
                    <small class="text-warning">En 30 días</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #6c757d !important;">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-repeat fs-1 text-info mb-2"></i>
                    <h3 class="text-info">{{ $estadisticas['renovados'] }}</h3>
                    <h6 class="text-muted">Renovados</h6>
                    <small class="text-muted">Reemplazados</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #28a745 !important;">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-success mb-2"></i>
                    <h3 class="text-success">{{ $estadisticas['trabajadores_con_contrato'] }}</h3>
                    <h6 class="text-muted">Trabajadores</h6>
                    <small class="text-success">Con contratos activos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #E6F2ED;">
                    <h5 class="mb-0" style="color: #007A4D;">
                        <i class="bi bi-funnel me-2"></i>
                        Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body" style="background-color: #F8FBF9;">
                    <form method="GET" id="filtrosForm">
                        <div class="row g-3">
                            <!-- Estado -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fw-bold" style="color: #007A4D;">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados_filtro as $value => $label)
                                        <option value="{{ $value }}" {{ request('estado') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Área -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fw-bold" style="color: #007A4D;">Área</label>
                                <select name="area" class="form-select">
                                    <option value="">Todas las áreas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre_area }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Duración -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fw-bold" style="color: #007A4D;">Tipo</label>
                                <select name="tipo_duracion" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    @foreach($tipos_duracion as $value => $label)
                                        <option value="{{ $value }}" {{ request('tipo_duracion') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Trabajador -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold" style="color: #007A4D;">Trabajador</label>
                                <input type="text" name="trabajador" class="form-control" 
                                       placeholder="Nombre, CURP..." 
                                       value="{{ request('trabajador') }}">
                            </div>

                            <!-- Fecha Desde -->
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label fw-bold" style="color: #007A4D;">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" 
                                       value="{{ request('fecha_desde') }}">
                            </div>

                            <!-- Fecha Hasta -->
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label fw-bold" style="color: #007A4D;">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" 
                                       value="{{ request('fecha_hasta') }}">
                            </div>

                            <!-- Botones -->
                            <div class="col-12">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn text-white" style="background-color: #007A4D;">
                                        <i class="bi bi-search me-1"></i> Buscar
                                    </button>
                                    <a href="{{ route('contratos.admin.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de próximos vencimientos -->
    @if($estadisticas['vencen_semana'] > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <h6 class="alert-heading">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Atención: Contratos próximos a vencer
                </h6>
                <p class="mb-0">
                    Hay <strong>{{ $estadisticas['vencen_semana'] }}</strong> contratos que vencen en los próximos 7 días.
                    <a href="?estado=proximo_vencer" class="alert-link">Ver contratos</a>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabla de Contratos -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #E6F2ED;">
                    <h5 class="mb-0" style="color: #007A4D;">
                        <i class="bi bi-table me-2"></i>
                        Lista de Contratos
                        <span class="badge text-white ms-2" style="background-color: #007A4D;">
                            {{ $contratos->total() }} total
                        </span>
                    </h5>
                    
                    <!-- Ordenamiento -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-sort-down me-1"></i> Ordenar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'fecha_inicio_contrato', 'direction' => 'desc']) }}">
                                Fecha inicio (recientes)</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'fecha_fin_contrato', 'direction' => 'asc']) }}">
                                Fecha fin (próximos)</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'estado_calculado', 'direction' => 'asc']) }}">
                                Estado</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($contratos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #F8FBF9;">
                                    <tr>
                                        <th style="color: #007A4D;">Trabajador</th>
                                        <th style="color: #007A4D;">Área/Categoría</th>
                                        <th style="color: #007A4D;">Fecha Inicio</th>
                                        <th style="color: #007A4D;">Fecha Fin</th>
                                        <th style="color: #007A4D;">Duración</th>
                                        <th style="color: #007A4D;">Estado</th>
                                        <th style="color: #007A4D;">Días Restantes</th>
                                        <th style="color: #007A4D;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contratos as $contrato)
                                    <tr>
                                        <!-- Trabajador -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px; background-color: #E6F2ED; color: #007A4D;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $contrato->trabajador_nombre_completo ?? 'Sin nombre' }}</h6>
                                                    <small class="text-muted">
                                                        Status: 
                                                        <span class="badge badge-sm {{ $contrato->trabajador_estatus === 'activo' ? 'bg-success' : 'bg-warning' }}">
                                                            {{ ucfirst($contrato->trabajador_estatus ?? 'N/A') }}
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Área/Categoría -->
                                        <td>
                                            <div>
                                                <strong style="color: #007A4D;">{{ $contrato->trabajador_area }}</strong><br>
                                                <small class="text-muted">{{ $contrato->trabajador_categoria }}</small>
                                            </div>
                                        </td>

                                        <!-- Fecha Inicio -->
                                        <td>
                                            <div>
                                                <strong>{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">{{ $contrato->fecha_inicio_contrato->format('l') }}</small>
                                            </div>
                                        </td>

                                        <!-- Fecha Fin -->
                                        <td>
                                            <div>
                                                <strong>{{ $contrato->fecha_fin_contrato->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">{{ $contrato->fecha_fin_contrato->format('l') }}</small>
                                            </div>
                                        </td>

                                        <!-- Duración -->
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ $contrato->duracion_texto }}
                                            </span>
                                        </td>

                                        <!-- Estado -->
                                        <td>
                                            <span class="badge bg-{{ $contrato->color_estado }} fw-normal">
                                                {{ ucfirst($contrato->estado_calculado) }}
                                            </span>
                                        </td>

                                        <!-- Días Restantes -->
                                        <td>
                                            @if($contrato->estado_calculado === 'vigente')
                                                <div class="text-center">
                                                    @if($contrato->dias_restantes_calculados <= 7)
                                                        <span class="badge bg-danger">{{ $contrato->dias_restantes_calculados }} días</span>
                                                    @elseif($contrato->dias_restantes_calculados <= 30)
                                                        <span class="badge bg-warning text-dark">{{ $contrato->dias_restantes_calculados }} días</span>
                                                    @else
                                                        <span class="text-success">{{ $contrato->dias_restantes_calculados }} días</span>
                                                    @endif
                                                </div>
                                            @elseif($contrato->estado_calculado === 'expirado')
                                                <span class="text-danger small">Expirado</span>
                                            @else
                                                <span class="text-muted small">Pendiente</span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <!-- Ver perfil del trabajador -->
                                                @if($contrato->trabajador)
                                                <a href="{{ route('trabajadores.perfil.show', $contrato->trabajador) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Ver perfil del trabajador">
                                                    <i class="bi bi-person-lines-fill"></i>
                                                </a>
                                                @endif

                                                <!-- Ver contratos del trabajador -->
                                                @if($contrato->trabajador)
                                                <a href="{{ route('trabajadores.contratos.show', $contrato->trabajador) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Ver todos los contratos">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </a>
                                                @endif

                                                <!-- Descargar contrato -->
                                                @if($contrato->archivo_existe && $contrato->trabajador)
                                                <a href="{{ route('trabajadores.contratos.descargar', [$contrato->trabajador, $contrato]) }}" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="Descargar contrato"
                                                   target="_blank">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @endif

                                                <!-- Renovar contrato (solo si está próximo a vencer) -->
                                                @if($contrato->estado_calculado === 'vigente' && $contrato->dias_restantes_calculados <= 30 && $contrato->trabajador)
                                                <a type="button" 
                                                        class="btn btn-sm btn-outline-warning"
                                                        href="{{ route('trabajadores.contratos.renovar', [$contrato->trabajador, $contrato]) }}"
                                                        title="Renovar contrato"
                                                        >
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Mostrando {{ $contratos->firstItem() }} a {{ $contratos->lastItem() }} 
                                    de {{ $contratos->total() }} contratos
                                </div>
                                {{ $contratos->links() }}
                            </div>
                        </div>

                    @else
                        <!-- Sin resultados -->
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No se encontraron contratos</h4>
                            <p class="text-muted">
                                No hay contratos que coincidan con los filtros aplicados.
                            </p>
                            <a href="{{ route('contratos.admin.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Limpiar filtros
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Función para exportar a Excel (placeholder)
    function exportarExcel() {
        alert('Funcionalidad de exportación próximamente disponible');
    }
    
    // Auto-submit del formulario cuando cambian los filtros
    document.addEventListener('DOMContentLoaded', function() {
        const selectores = ['select[name="estado"]', 'select[name="area"]', 'select[name="tipo_duracion"]'];
        
        selectores.forEach(selector => {
            const elemento = document.querySelector(selector);
            if (elemento) {
                elemento.addEventListener('change', function() {
                    document.getElementById('filtrosForm').submit();
                });
            }
        });
    });
</script>
@endpush