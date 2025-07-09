@extends('layouts.app')

@section('title', 'Lista de Trabajadores - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-people-fill text-primary"></i> Lista de Trabajadores
                    </h2>
                    <p class="text-muted mb-0">Gestiona todos los empleados del hotel</p>
                </div>
                <div class="d-flex gap-2">
                    {{-- Botón de Importación Masiva --}}
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImportacion">
                        <i class="bi bi-cloud-upload"></i> Importar Excel
                    </button>
                    {{-- Botón existente --}}
                    <a href="{{ route('trabajadores.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Nuevo Trabajador
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('components.alertas')

    {{-- ✅ USAR EL NUEVO COMPONENTE DE ESTADÍSTICAS --}}
    @include('components.estadisticas', [
        'tipo' => 'trabajadores',
        'stats' => $stats
    ])

    <!-- Filtros y Búsqueda -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros y Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('trabajadores.index') }}" id="filtrosForm">
                <div class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, CURP, RFC...">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label for="estatus" class="form-label">Estado</label>
                        <select class="form-select" id="estatus" name="estatus">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $valor => $texto)
                                <option value="{{ $valor }}" 
                                        {{ request('estatus') == $valor ? 'selected' : '' }}>
                                    {{ $texto }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Área -->
                    <div class="col-md-2">
                        <label for="area" class="form-label">Área</label>
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

                    <!-- Categoría -->
                    <div class="col-md-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria }}" 
                                        {{ request('categoria') == $categoria->id_categoria ? 'selected' : '' }}>
                                    {{ $categoria->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Trabajadores -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list"></i> 
                Trabajadores ({{ $trabajadores->total() }} encontrados)
            </h6>
        </div>
        
        <div class="card-body p-0">
            @if($trabajadores->count() > 0)
                <!-- Vista de Tarjetas (móvil) -->
                <div class="d-md-none">
                    @foreach($trabajadores as $trabajador)
                        <div class="border-bottom p-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%;">
                                        {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $trabajador->nombre_completo }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-briefcase"></i> 
                                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                                    </p>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-building"></i> 
                                        {{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-success">
                                                ${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}
                                            </span>
                                            <span class="badge bg-{{ $trabajador->estatus_color }}">
                                                <i class="{{ $trabajador->estatus_icono }}"></i>
                                                {{ $trabajador->estatus_texto }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

               <!-- Vista de Tabla (escritorio) -->
                <div class="d-none d-md-block table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Perfil</th>
                                <th>Trabajador</th>
                                <th>Área / Categoría</th>
                                <th>Estado</th>
                                <th>Contacto</th>
                                <th>Sueldo</th>
                                <th>Antigüedad</th>
                                <th>Documentos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trabajadores as $trabajador)
                                <tr>
                                    <td>
                                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                            style="width: 40px; height: 40px; border-radius: 50%; font-size: 14px;">
                                            {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                @php
                                                    $saldoHorasExtra = \App\Models\HorasExtra::calcularSaldo($trabajador->id_trabajador);
                                                @endphp

                                                <div class="fw-medium">
                                                    {{ $trabajador->nombre_completo }}

                                                    @if($saldoHorasExtra > 0)
                                                        <span class="text-info ms-2 d-inline-flex align-items-center" title="Tiene {{ $saldoHorasExtra }}h de horas extra acumuladas">
                                                            <i class="bi bi-clock-history me-1"></i>
                                                            <small><strong>con horas extra</strong></small>
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="text-muted small">{{ $trabajador->curp }}</div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        <div class="text-primary fw-medium">{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A' }}</div>
                                        <div class="text-muted small">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $trabajador->estatus_color }} fs-6">
                                            <i class="{{ $trabajador->estatus_icono }}"></i>
                                            {{ $trabajador->estatus_texto }}
                                        </span>
                                        @if($trabajador->puedeRegresar())
                                            <div class="text-muted small mt-1">
                                                <i class="bi bi-clock"></i> Temporal
                                            </div>
                                        @elseif($trabajador->requiereAtencion())
                                            <div class="text-warning small mt-1">
                                                <i class="bi bi-exclamation-triangle"></i> Atención
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <i class="bi bi-telephone text-muted"></i> {{ $trabajador->telefono }}
                                        </div>
                                        @if($trabajador->correo)
                                            <div class="small text-muted">
                                                <i class="bi bi-envelope"></i> {{ $trabajador->correo }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            ${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            {{-- ✅ USAR DATOS CALCULADOS EN EL CONTROLADOR --}}
                                            <div class="fw-medium">
                                                {{ $trabajador->antiguedad_texto ?? 'N/A' }}
                                            </div>
                                            <div class="text-muted">
                                                {{ $trabajador->fecha_ingreso ? $trabajador->fecha_ingreso->format('d/m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($trabajador->documentos)
                                            @php
                                                $porcentaje = $trabajador->documentos->porcentaje_completado ?? 0;
                                                $color = $porcentaje >= 75 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ $porcentaje }}%"></div>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                {{ $porcentaje }}%
                                                @if($trabajador->documentos->documentos_basicos_completos)
                                                    <span class="badge badge-sm bg-success ms-1">Básicos ✓</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">Sin documentos</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // ✅ NUEVA LÓGICA: Permitir acciones a trabajadores activos y en prueba
                                            // Solo excluir a los suspendidos
                                            $puedeRealizarAcciones = !$trabajador->estaSuspendido();
                                        @endphp
                                        
                                        <div class="d-flex flex-wrap gap-1">
                                            <!-- Ver Perfil Completo - SIEMPRE DISPONIBLE -->
                                            <a href="{{ route('trabajadores.perfil.show', $trabajador) }}" 
                                            class="btn btn-outline-primary btn-sm" 
                                            title="Ver Perfil Completo">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </a>
                                            
                                            @if($puedeRealizarAcciones)
                                                <!-- Asignar Permisos -->
                                                <button type="button" 
                                                        class="btn btn-outline-info btn-sm btn-permisos" 
                                                        title="Asignar Permisos"
                                                        data-id="{{ $trabajador->id_trabajador }}"
                                                        data-nombre="{{ $trabajador->nombre_completo }}">
                                                    <i class="bi bi-file-earmark-plus"></i>
                                                </button>
                                                
                                                <!-- Despedir -->
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm btn-despedir" 
                                                        title="Dar de baja al trabajador"
                                                        data-id="{{ $trabajador->id_trabajador }}"
                                                        data-nombre="{{ $trabajador->nombre_completo }}"
                                                        data-fecha-ingreso="{{ $trabajador->fecha_ingreso->format('Y-m-d') }}">
                                                    <i class="bi bi-person-dash"></i>
                                                </button>

                                                {{-- Asignar Horas Extra --}}
                                                <a href="#" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalAsignarHoras{{ $trabajador->id_trabajador }}"
                                                    title="Asignar Horas Extra">
                                                    <i class="bi bi-clock">+</i>
                                                </a>

                                                @php
                                                    $saldoActual = \App\Models\HorasExtra::calcularSaldo($trabajador->id_trabajador);
                                                    $botonRestarDeshabilitado = $saldoActual <= 0;
                                                @endphp

                                                <!-- Compensar Horas Extra -->
                                                <a href="#" 
                                                    class="btn btn-outline-warning btn-sm {{ $botonRestarDeshabilitado ? 'disabled' : '' }}" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalRestarHoras{{ $trabajador->id_trabajador }}"
                                                    title="Compensar Horas Extra (Saldo actual: {{ $saldoActual }}h)"
                                                    {{ $botonRestarDeshabilitado ? 'aria-disabled=true' : '' }}>
                                                    <i class="bi bi-clock">-</i>
                                                </a>
                                            @else
                                                <!-- Mensaje para trabajadores suspendidos -->
                                                <span class="text-muted small fst-italic">
                                                    <i class="bi bi-exclamation-circle"></i> Sin acciones disponibles
                                                </span>
                                            @endif
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
                        <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">No se encontraron trabajadores</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'area', 'categoria']))
                            Intenta ajustar los filtros de búsqueda.
                        @else
                            Comienza agregando tu primer trabajador.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'area', 'categoria']))
                        <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Limpiar filtros
                        </a>
                    @else
                        <a href="{{ route('trabajadores.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Agregar primer trabajador
                        </a>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Paginación Mejorada -->
        @if($trabajadores->hasPages())
            <div class="card-footer bg-light">
                <div class="row align-items-center">
                    <!-- Información de registros -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="bi bi-info-circle me-2"></i>
                            <span>
                                Mostrando 
                                <strong class="text-primary">{{ $trabajadores->firstItem() }}</strong> 
                                a 
                                <strong class="text-primary">{{ $trabajadores->lastItem() }}</strong> 
                                de 
                                <strong class="text-primary">{{ $trabajadores->total() }}</strong> 
                                trabajadores
                            </span>
                        </div>
                        
                        <!-- Información adicional en pantallas grandes -->
                        <div class="d-none d-lg-block mt-1">
                            <small class="text-muted">
                                Página {{ $trabajadores->currentPage() }} de {{ $trabajadores->lastPage() }}
                                @if(request()->hasAny(['search', 'estatus', 'area', 'categoria']))
                                    <span class="badge bg-info ms-2">
                                        <i class="bi bi-funnel"></i> Filtros aplicados
                                    </span>
                                @endif
                            </small>
                        </div>
                    </div>
                    
                    <!-- Controles de paginación -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end justify-content-center mt-2 mt-md-0">
                            <!-- Paginación personalizada -->
                            <nav aria-label="Navegación de trabajadores">
                                <ul class="pagination pagination-sm mb-0">
                                    {{-- Ir a primera página --}}
                                    @if ($trabajadores->currentPage() > 3)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->url(1) }}" title="Primera página">
                                                <i class="bi bi-chevron-double-left"></i>
                                            </a>
                                        </li>
                                    @endif
                                    
                                    {{-- Página anterior --}}
                                    @if ($trabajadores->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="bi bi-chevron-left"></i>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->previousPageUrl() }}" rel="prev" title="Página anterior">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Números de página --}}
                                    @php
                                        $currentPage = $trabajadores->currentPage();
                                        $lastPage = $trabajadores->lastPage();
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($lastPage, $currentPage + 2);
                                    @endphp

                                    @if($startPage > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->url(1) }}">1</a>
                                        </li>
                                        @if($startPage > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($page = $startPage; $page <= $endPage; $page++)
                                        @if ($page == $currentPage)
                                            <li class="page-item active">
                                                <span class="page-link">
                                                    {{ $page }}
                                                    <span class="visually-hidden">(actual)</span>
                                                </span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $trabajadores->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if($endPage < $lastPage)
                                        @if($endPage < $lastPage - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->url($lastPage) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    {{-- Página siguiente --}}
                                    @if ($trabajadores->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->nextPageUrl() }}" rel="next" title="Página siguiente">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="bi bi-chevron-right"></i>
                                            </span>
                                        </li>
                                    @endif
                                    
                                    {{-- Ir a última página --}}
                                    @if ($trabajadores->currentPage() < $lastPage - 2)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $trabajadores->url($lastPage) }}" title="Última página">
                                                <i class="bi bi-chevron-double-right"></i>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ✅ INCLUIR MODALES PRINCIPALES --}}
@include('trabajadores.modales.despidos')
@include('trabajadores.modales.permisos')
@include('trabajadores.modales.importacion')

{{-- ✅ MODALES DE HORAS EXTRAS - Generados solo para trabajadores con acciones disponibles --}}
@if($trabajadores->count() > 0)
    @foreach($trabajadores as $trabajador)
        @if(!$trabajador->estaSuspendido())
            @include('trabajadores.modales.asignar_horas_extras', ['trabajador' => $trabajador])
            @include('trabajadores.modales.restar_horas_extras', [
                'trabajador' => $trabajador,
                'saldoActual' => \App\Models\HorasExtra::calcularSaldo($trabajador->id_trabajador)
            ])
        @endif
    @endforeach
@endif

{{-- ✅ CARGAR FORMATO GLOBAL ANTES DE LOS MODALES --}}
<script src="{{ asset('js/formato-global.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ FUNCIONALIDAD DE FILTROS Y BÚSQUEDA
    const areaSelect = document.getElementById('area');
    const categoriaSelect = document.getElementById('categoria');
    const estatusSelect = document.getElementById('estatus');
    const searchInput = document.getElementById('search');
    
    // Cargar categorías cuando cambia el área
    if (areaSelect && categoriaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            
            // Limpiar categorías
            categoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
            
            if (areaId) {
                // Cargar categorías de esta área
                fetch(`/api/categorias/${areaId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(categorias => {
                        categorias.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.id_categoria;
                            option.textContent = categoria.nombre_categoria;
                            
                            // Mantener selección si existe
                            if (categoria.id_categoria == '{{ request("categoria") }}') {
                                option.selected = true;
                            }
                            
                            categoriaSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error cargando categorías:', error);
                    });
            }
        });
    }
    
    // Auto-submit con debounce para búsqueda
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filtrosForm').submit();
            }, 500);
        });
    }
    
    // Submit inmediato para selects
    if (areaSelect) {
        areaSelect.addEventListener('change', () => {
            setTimeout(() => {
                document.getElementById('filtrosForm').submit();
            }, 100);
        });
    }
    
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', () => {
            document.getElementById('filtrosForm').submit();
        });
    }
    
    // Submit inmediato para estado
    if (estatusSelect) {
        estatusSelect.addEventListener('change', () => {
            document.getElementById('filtrosForm').submit();
        });
    }
    
    // Auto-hide alerts después de 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
    
    console.log('✅ Vista lista trabajadores inicializada correctamente');
});
</script>

@endsection