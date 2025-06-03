@extends('layouts.app')

@section('title', 'Búsqueda de Empleados - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0" style="color: #007A4D;">
                        <i class="bi bi-search"></i> Búsqueda de Empleados
                    </h2>
                    <p class="text-muted mb-0">Encuentra y consulta información de los trabajadores</p>
                </div>
                <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-people"></i> Ver Todos los Trabajadores
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda Avanzada -->
    <div class="card shadow mb-4" style="border-top: 3px solid #007A4D;">
        <div class="card-header" style="background-color: #F8F9FA;">
            <h6 class="mb-0" style="color: #007A4D;">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('trabajadores.buscar') }}">
                <div class="row g-3">
                    <!-- Búsqueda general -->
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-medium">Búsqueda General</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, apellido, ID...">
                        </div>
                    </div>

                    <!-- Área -->
                    <div class="col-md-2">
                        <label for="area" class="form-label fw-medium">Área</label>
                        <select class="form-select" id="area" name="area">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}" 
                                        {{ request('area') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre_area }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label for="estatus" class="form-label fw-medium">Estado</label>
                        <select class="form-select" id="estatus" name="estatus">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estatus') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="vacaciones" {{ request('estatus') == 'vacaciones' ? 'selected' : '' }}>Vacaciones</option>
                            <option value="incapacidad_medica" {{ request('estatus') == 'incapacidad_medica' ? 'selected' : '' }}>Incapacidad</option>
                            <option value="despedido" {{ request('estatus') == 'despedido' ? 'selected' : '' }}>Despedido</option>
                            <option value="retirado" {{ request('estatus') == 'retirado' ? 'selected' : '' }}>Retirado</option>
                        </select>
                    </div>

                    <!-- Fecha de ingreso -->
                    <div class="col-md-2">
                        <label for="fecha_ingreso_desde" class="form-label fw-medium">Ingreso desde</label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_ingreso_desde" 
                               name="fecha_ingreso_desde" 
                               value="{{ request('fecha_ingreso_desde') }}">
                    </div>

                    <!-- Fecha hasta -->
                    <div class="col-md-2">
                        <label for="fecha_ingreso_hasta" class="form-label fw-medium">Hasta</label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_ingreso_hasta" 
                               name="fecha_ingreso_hasta" 
                               value="{{ request('fecha_ingreso_hasta') }}">
                    </div>
                </div>

                <!-- Filtros adicionales (segunda fila) -->
                <div class="row g-3 mt-2">
                    <!-- Categoría -->
                    <div class="col-md-3">
                        <label for="categoria" class="form-label fw-medium">Cargo/Categoría</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Todos los cargos</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria }}" 
                                        {{ request('categoria') == $categoria->id_categoria ? 'selected' : '' }}>
                                    {{ $categoria->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn flex-fill" style="background-color: #007A4D; color: white;">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="{{ route('trabajadores.buscar') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas de búsqueda -->
    @if(isset($trabajadores))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background-color: #007A4D; color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $trabajadores->total() }}</div>
                            <div style="color: rgba(255,255,255,0.8);">Resultados encontrados</div>
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
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['activos'] ?? 0 }}</div>
                            <div class="text-white-50">Activos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-heart fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['en_ausencia'] ?? 0 }}</div>
                            <div class="text-white-50">En Ausencia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-person-x fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fs-4 fw-bold">{{ $stats['inactivos'] ?? 0 }}</div>
                            <div class="text-white-50">Inactivos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resultados de búsqueda -->
    @if(isset($trabajadores))
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #F8F9FA;">
            <h6 class="mb-0" style="color: #007A4D;">
                <i class="bi bi-list-ul"></i> 
                Resultados ({{ $trabajadores->total() }} encontrados)
            </h6>
            
            <!-- Exportar resultados -->
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-success" onclick="exportarResultados('excel')">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button class="btn btn-outline-success" onclick="exportarResultados('pdf')">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($trabajadores->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #F8F9FA;">
                            <tr>
                                <th>Empleado</th>
                                <th>Área / Cargo</th>
                                <th>Estado</th>
                                <th>Fecha Ingreso</th>
                                <th>Antigüedad</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trabajadores as $trabajador)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3" style="background-color: #007A4D; width: 35px; height: 35px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $trabajador->nombre_completo }}</div>
                                                <div class="text-muted small">ID: {{ $trabajador->id_trabajador }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium">{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}</div>
                                            <div class="text-muted">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin cargo' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $estadoColores = [
                                                'activo' => 'success',
                                                'vacaciones' => 'info',
                                                'incapacidad_medica' => 'warning',
                                                'licencia_maternidad' => 'primary',
                                                'licencia_paternidad' => 'primary',
                                                'licencia_sin_goce' => 'secondary',
                                                'permiso_especial' => 'info',
                                                'despedido' => 'danger',
                                                'retirado' => 'dark'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $estadoColores[$trabajador->estatus] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $trabajador->estatus)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium">{{ $trabajador->fecha_ingreso->format('d/m/Y') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $trabajador->antiguedad_texto }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($trabajador->telefono)
                                                <div><i class="bi bi-phone"></i> {{ $trabajador->telefono }}</div>
                                            @endif
                                            @if($trabajador->email)
                                                <div><i class="bi bi-envelope"></i> {{ $trabajador->email }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalles{{ $trabajador->id_trabajador }}"
                                                    title="Ver Detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <a href="{{ route('trabajadores.show', $trabajador) }}" 
                                               class="btn btn-outline-success" 
                                               title="Ir al Perfil">
                                                <i class="bi bi-person"></i>
                                            </a>
                                        </div>

                                        <!-- Modal de detalles rápidos -->
                                        <div class="modal fade" id="modalDetalles{{ $trabajador->id_trabajador }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background-color: #007A4D; color: white;">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-person-badge"></i> {{ $trabajador->nombre_completo }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-success">Información Personal</h6>
                                                                <table class="table table-sm">
                                                                    <tr><td><strong>ID:</strong></td><td>{{ $trabajador->id_trabajador }}</td></tr>
                                                                    <tr><td><strong>Género:</strong></td><td>{{ $trabajador->sexo == 'M' ? 'Masculino' : 'Femenino' }}</td></tr>
                                                                    <tr><td><strong>Edad:</strong></td><td>{{ $trabajador->edad }} años</td></tr>
                                                                    @if($trabajador->telefono)
                                                                    <tr><td><strong>Teléfono:</strong></td><td>{{ $trabajador->telefono }}</td></tr>
                                                                    @endif
                                                                    @if($trabajador->email)
                                                                    <tr><td><strong>Email:</strong></td><td>{{ $trabajador->email }}</td></tr>
                                                                    @endif
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-success">Información Laboral</h6>
                                                                <table class="table table-sm">
                                                                    <tr><td><strong>Área:</strong></td><td>{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A' }}</td></tr>
                                                                    <tr><td><strong>Cargo:</strong></td><td>{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'N/A' }}</td></tr>
                                                                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-{{ $estadoColores[$trabajador->estatus] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $trabajador->estatus)) }}</span></td></tr>
                                                                    <tr><td><strong>Ingreso:</strong></td><td>{{ $trabajador->fecha_ingreso->format('d/m/Y') }}</td></tr>
                                                                    <tr><td><strong>Antigüedad:</strong></td><td>{{ $trabajador->antiguedad_texto }}</td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn" style="background-color: #007A4D; color: white;">
                                                            <i class="bi bi-person"></i> Ver Perfil Completo
                                                        </a>
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
                        <i class="bi bi-search" style="font-size: 4rem; color: #007A4D;"></i>
                    </div>
                    <h5 class="text-muted">No se encontraron empleados</h5>
                    <p class="text-muted">
                        Intenta ajustar los filtros de búsqueda o usar términos diferentes.
                    </p>
                </div>
            @endif
        </div>
        

        <!-- Paginación Simple -->
        @if($trabajadores->hasPages())
            <div class="card-footer" style="background-color: #F8F9FA;">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Información básica -->
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1" style="color: #007A4D;"></i>
                        Mostrando {{ $trabajadores->firstItem() }} - {{ $trabajadores->lastItem() }} 
                        de {{ $trabajadores->total() }} empleados
                    </div>
                    
                    <!-- Paginación básica -->
                    <nav aria-label="Paginación">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Anterior --}}
                            @if ($trabajadores->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $trabajadores->previousPageUrl() }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Páginas (máximo 5) --}}
                            @foreach ($trabajadores->getUrlRange(
                                max(1, $trabajadores->currentPage() - 2),
                                min($trabajadores->lastPage(), $trabajadores->currentPage() + 2)
                            ) as $page => $url)
                                @if ($page == $trabajadores->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link" style="background-color: #007A4D; border-color: #007A4D;">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}" style="color: #007A4D;">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Siguiente --}}
                            @if ($trabajadores->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $trabajadores->nextPageUrl() }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        @endif
    </div>
    @else
    <!-- Estado inicial (sin búsqueda) -->
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-search" style="font-size: 5rem; color: #007A4D; opacity: 0.5;"></i>
            </div>
            <h4 style="color: #007A4D;">Búsqueda de Empleados</h4>
            <p class="text-muted mb-4">
                Utiliza los filtros de arriba para encontrar empleados específicos.<br>
                Puedes buscar por nombre, área, cargo, estado y más criterios.
            </p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-start">
                        <div class="col-md-6">
                            <h6 style="color: #007A4D;">💡 Consejos de búsqueda:</h6>
                            <ul class="text-muted">
                                <li>Usa nombres completos o parciales</li>
                                <li>Filtra por área para resultados específicos</li>
                                <li>Combina múltiples filtros</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 style="color: #007A4D;">🔍 Puedes buscar por:</h6>
                            <ul class="text-muted">
                                <li>Nombre y apellidos</li>
                                <li>ID de empleado</li>
                                <li>Área y cargo</li>
                                <li>Estado laboral</li>
                                <li>Rango de fechas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function exportarResultados(formato) {
    // Obtener parámetros de búsqueda actuales
    const params = new URLSearchParams(window.location.search);
    params.append('export', formato);
    
    // Crear enlace de descarga
    const url = `{{ route('trabajadores.buscar') }}?${params.toString()}`;
    window.open(url, '_blank');
}
</script>

@endsection