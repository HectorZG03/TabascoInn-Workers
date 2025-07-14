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
                        @if($estadoFiltro === 'cancelado')
                            <span class="badge bg-warning text-dark ms-2">Mostrando bajas canceladas</span>
                        @elseif($estadoFiltro === 'todos')
                            <span class="badge bg-info ms-2">Mostrando todo el historial</span>
                        @else
                            <span class="badge bg-danger ms-2">Mostrando bajas activas</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ USAR EL NUEVO COMPONENTE DE ESTADÍSTICAS --}}
    @include('components.estadisticas', [
        'tipo' => 'despidos',
        'stats' => $stats
    ])

    <!-- ✅ FILTROS ACTUALIZADOS -->
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
                        <!-- ✅ NUEVO: Filtro por estado -->
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado de las Bajas</label>
                            <select class="form-select" id="estado" name="estado">
                                @foreach($estados as $valor => $texto)
                                    <option value="{{ $valor }}" 
                                            {{ $estadoFiltro == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Búsqueda general -->
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nombre o motivo...">
                            </div>
                        </div>

                        <!-- Condición de salida -->
                        <div class="col-md-2">
                            <label for="condicion_salida" class="form-label">Condición</label>
                            <select class="form-select" id="condicion_salida" name="condicion_salida">
                                <option value="">Todas</option>
                                @foreach($condiciones as $condicion)
                                    <option value="{{ $condicion }}" 
                                            {{ request('condicion_salida') == $condicion ? 'selected' : '' }}>
                                        {{ Str::limit($condicion, 15) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha desde -->
                        <div class="col-md-2">
                            <label for="fecha_desde" class="form-label">Desde</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_desde" 
                                   name="fecha_desde" 
                                   value="{{ request('fecha_desde') }}">
                        </div>

                        <!-- Fecha hasta -->
                        <div class="col-md-1">
                            <label for="fecha_hasta" class="form-label">Hasta</label>
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
                                        <th>Tipo de Baja</th>
                                        <th>Trabajador</th>
                                        <th>Fecha de Reintegro</th>
                                        <th>Área/Cargo</th>
                                        <th>Fecha de Baja</th>
                                        <th>Condición de Salida</th>
                                        <th>Estado</th>
                                        <th>Motivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($despidos as $despido)
                                        <tr class="{{ $despido->es_cancelado ? 'table-warning' : '' }}">
                                            <!-- Tipo de Baja -->
                                            <td>
                                                <span class="badge {{ $despido->tipo_baja === 'temporal' ? 'bg-info' : 'bg-dark' }}">
                                                    {{ $despido->tipo_baja_texto }}
                                                </span>
                                            </td>

                                            <!-- Información del trabajador -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" 
                                                        style="background-color: {{ $despido->es_cancelado ? '#f0ad4e' : '#be0b0b' }};">
                                                        {{ substr($despido->trabajador->nombre_trabajador, 0, 1) }}{{ substr($despido->trabajador->ape_pat, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $despido->trabajador->nombre_completo }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID: {{ $despido->trabajador->id_trabajador }}</small>
                                                        @if($despido->trabajador->tieneMultiplesBajas())
                                                            <br>
                                                            <span class="badge bg-warning text-dark" 
                                                                title="Este trabajador tiene múltiples bajas en su historial">
                                                                <i class="bi bi-exclamation-triangle"></i> Múltiples bajas
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Fecha de Reintegro -->
                                            <td>
                                                @if($despido->tipo_baja === 'temporal')
                                                    @if($despido->fecha_reintegro->isPast())
                                                        <span class="badge bg-danger">
                                                            {{ $despido->fecha_reintegro->format('d/m/Y') }}
                                                        </span>
                                                        <div class="text-danger small">Vencido</div>
                                                    @else
                                                        <span class="badge bg-success">
                                                            {{ $despido->fecha_reintegro->format('d/m/Y') }}
                                                        </span>
                                                        <div class="text-muted small">
                                                            {{ $despido->fecha_reintegro->diffForHumans() }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
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
                                                <span class="badge {{ $despido->es_cancelado ? 'bg-warning text-dark' : 'bg-danger' }}">
                                                    {{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($despido->fecha_baja)->diffForHumans() }}
                                                </small>
                                                @if($despido->es_cancelado && $despido->fecha_cancelacion)
                                                    <br>
                                                    <small class="text-success">
                                                        <i class="bi bi-arrow-clockwise"></i> 
                                                        Cancelado: {{ $despido->fecha_cancelacion->format('d/m/Y') }}
                                                    </small>
                                                @endif
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

                                            <!-- Estado -->
                                            <td>
                                                @if($despido->es_activo)
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-circle"></i> Activo
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Cancelado
                                                    </span>
                                                    @if($despido->usuarioCancelacion)
                                                        <br>
                                                        <small class="text-muted">
                                                            por {{ $despido->usuarioCancelacion->name }}
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Motivo -->
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

                                                    <!-- Reactivar -->
                                                    @if($despido->es_activo)
                                                        <button type="button" 
                                                                class="btn btn-outline-success btn-sm" 
                                                                onclick="reactivarTrabajador({{ $despido->id_baja }}, '{{ $despido->trabajador->nombre_completo }}')"
                                                                title="Reactivar trabajador">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    @endif

                                                    <!-- Ver historial -->
                                                    <button type="button" 
                                                            class="btn btn-outline-info btn-sm" 
                                                            onclick="verHistorialTrabajador({{ $despido->trabajador->id_trabajador }})"
                                                            title="Ver historial completo">
                                                        <i class="bi bi-clock-history"></i>
                                                    </button>
                                                </div>

                                                <!-- Modales -->
                                                @include('trabajadores.modales.modal_detalles_despidos', ['despido' => $despido])
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
                                @if(request()->hasAny(['search', 'condicion_salida', 'fecha_desde', 'fecha_hasta', 'estado']))
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

<!-- ✅ MODAL PARA HISTORIAL COMPLETO -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i> Historial Completo de Bajas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historialContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando historial...</p>
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

.table-warning {
    --bs-table-accent-bg: rgba(255, 243, 205, 0.5);
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: relative;
}
</style>

{{-- ✅ ORDEN CORRECTO DE SCRIPTS PARA LISTA DE DESPIDOS --}}

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
    console.log('✅ AppRoutes disponible para lista de despidos');
}
</script>

{{-- 3. TERCERO: Script específico de lista de despidos --}}
<script src="{{ asset('js/listas/despidos_lista.js') }}"></script>

{{-- 4. CUARTO: Script de inicialización y debug --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ EJECUTAR DEBUG EN DESARROLLO
    if (typeof window.APP_DEBUG !== 'undefined' && window.APP_DEBUG) {
        setTimeout(() => {
            if (typeof window.debugRutasDespidos === 'function') {
                console.group('🔍 Debug Lista Despidos');
                window.debugRutasDespidos();
                console.log('Funciones disponibles:', {
                    verHistorialTrabajador: typeof window.verHistorialTrabajador,
                    reactivarTrabajador: typeof window.reactivarTrabajador
                });
                console.groupEnd();
            }
        }, 1000);
    }
    
    console.log('✅ Lista de despidos con rutas dinámicas inicializada correctamente');
});
</script>

@endsection