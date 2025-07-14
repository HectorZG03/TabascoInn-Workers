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

    {{-- ✅ USAR EL NUEVO COMPONENTE DE ESTADÍSTICAS --}}
    @include('components.estadisticas', [
        'tipo' => 'permisos',
        'stats' => $stats
    ])

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('permisos.index') }}" enctype="multipart/form-data">
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
                            <option value="finalizados" {{ request('estado') == 'finalizados' ? 'selected' : '' }}>Finalizados</option>
                            <option value="cancelados" {{ request('estado') == 'cancelados' ? 'selected' : '' }}>Cancelados</option>
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
                                <th>Motivo</th>
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
                                        <span class="badge bg-{{ $coloresPermiso[$permiso->tipo_permiso] ?? 'secondary' }} fs-6">
                                            <i class="{{ $iconosPermiso[$permiso->tipo_permiso] ?? 'bi-calendar' }}"></i>
                                            {{ $tiposPermisos[$permiso->tipo_permiso] ?? $permiso->tipo_permiso }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="badge bg-light text-dark border">
                                                {{ $permiso->motivo_texto }}
                                            </span>
                                        </div>
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
                                        @if($permiso->estatus_permiso === 'activo')
                                            @if($permiso->fecha_fin >= now())
                                                <span class="badge bg-success">Activo</span>
                                                @if($permiso->dias_restantes <= 3 && $permiso->dias_restantes > 0)
                                                    <div class="text-warning small mt-1">
                                                        <i class="bi bi-exclamation-triangle"></i> Próximo a vencer
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge bg-warning text-dark">Vencido</span>
                                                <div class="text-muted small mt-1">
                                                    Hace {{ $permiso->dias_vencidos }} día{{ $permiso->dias_vencidos != 1 ? 's' : '' }}
                                                </div>
                                            @endif
                                        @elseif($permiso->estatus_permiso === 'finalizado')
                                            <span class="badge bg-primary">Finalizado</span>
                                            <div class="text-muted small mt-1">
                                                <i class="bi bi-check-circle"></i> Completado
                                            </div>
                                        @elseif($permiso->estatus_permiso === 'cancelado')
                                            <span class="badge bg-secondary">Cancelado</span>
                                            <div class="text-muted small mt-1">
                                                <i class="bi bi-x-circle"></i> Cancelado
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
                                            
                                            <!-- Solo mostrar acciones de gestión si el permiso está activo -->
                                            @if($permiso->estatus_permiso === 'activo')
                                                <button type="button" 
                                                        class="btn btn-outline-warning"
                                                        title="Finalizar Permiso"
                                                        onclick="finalizarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-outline-danger"
                                                        title="Eliminar Permiso Definitivamente"
                                                        onclick="cancelarPermiso({{ $permiso->id_permiso }}, '{{ $permiso->trabajador->nombre_completo }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                           @if($permiso->tiene_pdf)
                                                <a href="{{ route('permisos.descargar', $permiso) }}" 
                                                class="btn btn-outline-info"
                                                title="Descargar archivo adjunto">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @else
                                                <!-- Botón para abrir modal subir archivo -->
                                                <button type="button" 
                                                        class="btn btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalSubirArchivo{{ $permiso->id_permiso }}"
                                                        title="Subir archivo del permiso">
                                                    <i class="bi bi-upload"></i>
                                                </button>

                                                <!-- Incluir el componente modal para subir archivo -->
                                                <x-modal_subir_archivo :permiso-id="$permiso->id_permiso" />
                                            @endif

                                        </div>

                                    <!-- Modal de detalles del permiso -->
                                    <div class="modal fade" id="modalDetalles{{ $permiso->id_permiso }}" tabindex="-1" aria-labelledby="modalDetallesLabel{{ $permiso->id_permiso }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <!-- Header del modal -->
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalDetallesLabel{{ $permiso->id_permiso }}">
                                                        Detalles del Permiso #{{ $permiso->id_permiso }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>

                                                <!-- Body del modal -->
                                                <div class="modal-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Trabajador:</strong> {{ $permiso->trabajador->nombre_completo ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Tipo:</strong> {{ $permiso->tipo_permiso_texto }}</li>
                                                        <li class="list-group-item"><strong>Motivo:</strong> {{ $permiso->motivo_texto }}</li>
                                                        <li class="list-group-item"><strong>Fechas:</strong> {{ $permiso->fecha_inicio->format('d/m/Y') }} al {{ $permiso->fecha_fin->format('d/m/Y') }}</li>
                                                        <li class="list-group-item"><strong>Observaciones:</strong> {{ $permiso->observaciones ?? 'Ninguna' }}</li>
                                                        <li class="list-group-item"><strong>Estatus:</strong> {{ $permiso->estatus_permiso_texto }}</li>
                                                    </ul>
                                                </div>

                                                <!-- Footer del modal -->
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle"></i> Cerrar
                                                    </button>
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

{{-- ✅ ORDEN CORRECTO DE SCRIPTS PARA LISTA DE PERMISOS --}}

{{-- 1. PRIMERO: Script de rutas dinámicas globales --}}
<script src="{{ asset('js/app-routes.js') }}"></script>

{{-- 2. SEGUNDO: Variables globales de configuración --}}
<script>
// ✅ VARIABLES GLOBALES PARA LA APLICACIÓN
window.APP_DEBUG = @json(config('app.debug'));
window.currentUser = @json([
    'id' => Auth::id(),
    'nombre' => Auth::user()->nombre,
    'tipo' => Auth::user()->tipo
]);

// ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
if (typeof AppRoutes === 'undefined') {
    console.error('❌ CRÍTICO: app-routes.js no se cargó correctamente');
} else {
    console.log('✅ AppRoutes disponible para lista de permisos');
}
</script>

{{-- 3. TERCERO: Script específico de lista de permisos --}}
<script src="{{ asset('js/listas/permisos_lista.js') }}"></script>

{{-- 4. CUARTO: Script de inicialización y debug --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ EJECUTAR DEBUG EN DESARROLLO
    if (typeof window.APP_DEBUG !== 'undefined' && window.APP_DEBUG) {
        setTimeout(() => {
            if (typeof window.debugRutasPermisos === 'function') {
                console.group('🔍 Debug Lista Permisos');
                window.debugRutasPermisos();
                console.log('Funciones disponibles:', {
                    finalizarPermiso: typeof window.finalizarPermiso,
                    cancelarPermiso: typeof window.cancelarPermiso,
                    subirArchivoPermiso: typeof window.subirArchivoPermiso,
                    descargarArchivoPermiso: typeof window.descargarArchivoPermiso
                });
                console.groupEnd();
            }
        }, 1000);
    }
    
    console.log('✅ Lista de permisos con rutas dinámicas inicializada correctamente');
});
</script>

@endsection