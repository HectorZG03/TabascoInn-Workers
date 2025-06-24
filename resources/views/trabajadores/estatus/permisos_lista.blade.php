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
                            <div class="fs-4 fw-bold">{{ $stats['finalizados'] ?? 0 }}</div>
                            <div class="text-dark">Finalizados</div>
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
                                        {{-- ✅ USAR ESTATUS_PERMISO EN LUGAR DE FECHA --}}
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
                                    {{-- ✅ REEMPLAZAR LA SECCIÓN DE ACCIONES EN LA TABLA DE permisos_lista.blade.php --}}

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
                                            
                                            {{-- ✅ BOTÓN PARA GENERAR/DESCARGAR PDF --}}
                                            <a href="{{ route('permisos.pdf', $permiso->id_permiso) }}" 
                                            class="btn btn-outline-success"
                                            title="Generar y Descargar PDF"
                                            target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            
                                            {{-- ✅ SOLO MOSTRAR ACCIONES DE GESTIÓN SI EL PERMISO ESTÁ ACTIVO --}}
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
                                            @else
                                                {{-- ✅ SI EL PERMISO NO ESTÁ ACTIVO, MOSTRAR OPCIONES LIMITADAS --}}
                                                @if($permiso->tiene_pdf && $permiso->pdf_necesita_regeneracion)
                                                    <button type="button" 
                                                            class="btn btn-outline-info"
                                                            title="Regenerar PDF (datos actualizados)"
                                                            onclick="regenerarPdf({{ $permiso->id_permiso }})">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>

                                        {{-- ✅ BADGE INDICADOR DE PDF --}}
                                        @if($permiso->tiene_pdf)
                                            <div class="mt-1">
                                                <span class="badge bg-success" title="PDF disponible">
                                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                                </span>
                                                @if($permiso->pdf_necesita_regeneracion)
                                                    <span class="badge bg-warning text-dark" title="PDF desactualizado">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="mt-1">
                                                <span class="badge bg-light text-muted" title="Sin PDF generado">
                                                    <i class="bi bi-file-earmark"></i> Sin PDF
                                                </span>
                                            </div>
                                        @endif

                                    <!-- Modal de detalles del permiso -->
                                    <div class="modal fade" id="modalDetalles{{ $permiso->id_permiso }}" tabindex="-1" aria-labelledby="modalDetallesLabel{{ $permiso->id_permiso }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">

                                                {{-- ✅ Header del modal --}}
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalDetallesLabel{{ $permiso->id_permiso }}">
                                                        Detalles del Permiso #{{ $permiso->id_permiso }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>

                                                {{-- ✅ Body del modal --}}
                                                <div class="modal-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Trabajador:</strong> {{ $permiso->trabajador->nombre_completo ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Tipo:</strong> {{ $permiso->tipo_permiso_texto }}</li>
                                                        <li class="list-group-item"><strong>Motivo:</strong> {{ $permiso->motivo_texto }}</li>
                                                        <li class="list-group-item"><strong>Fechas:</strong> {{ $permiso->fecha_inicio->format('d/m/Y') }} al {{ $permiso->fecha_fin->format('d/m/Y') }}</li>
                                                        <li class="list-group-item"><strong>Observaciones:</strong> {{ $permiso->observaciones ?? 'Ninguna' }}</li>
                                                        <li class="list-group-item"><strong>Estatus:</strong> {{ $permiso->estatus_permiso_texto }}</li>
                                                        {{-- Puedes agregar más campos aquí --}}
                                                    </ul>
                                                </div>

                                                {{-- ✅ Footer del modal --}}
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle"></i> Cerrar
                                                    </button>

                                                    <a href="{{ route('permisos.pdf', $permiso->id_permiso) }}" 
                                                    class="btn btn-success" title="Generar y Descargar PDF" target="_blank">
                                                        <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
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
    if (confirm(`⚠️ ¿Está seguro de ELIMINAR DEFINITIVAMENTE el permiso de ${nombreTrabajador}?\n\nEsta acción:\n• Eliminará el permiso de la base de datos\n• Eliminará el archivo PDF asociado\n• Reactivará al trabajador\n• NO SE PUEDE DESHACER\n\n¿Continuar?`)) {
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

/**
 * ✅ NUEVA FUNCIÓN PARA REGENERAR PDF
 */
function regenerarPdf(permisoId) {
    if (confirm('¿Desea regenerar el PDF con los datos actualizados? El archivo anterior será reemplazado.')) {
        // Crear formulario para regenerar PDF
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permisos/${permisoId}/pdf/regenerar`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

    /**
     * ✅ FUNCIÓN PARA MOSTRAR LOADING EN BOTONES PDF
     */
    function mostrarLoadingPdf(elemento) {
        const iconoOriginal = elemento.innerHTML;
        elemento.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        elemento.disabled = true;
        
        // Restaurar después de 3 segundos
        setTimeout(() => {
            elemento.innerHTML = iconoOriginal;
            elemento.disabled = false;
        }, 3000);
    }

    // ✅ AÑADIR LOADING A BOTONES PDF
    document.addEventListener('DOMContentLoaded', function() {
        const botonesPdf = document.querySelectorAll('a[href*="/pdf"]');
        botonesPdf.forEach(boton => {
            boton.addEventListener('click', function() {
                mostrarLoadingPdf(this);
            });
        });
    });
</script>

@endsection