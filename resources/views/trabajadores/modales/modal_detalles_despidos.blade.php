{{-- resources/views/despidos/partials/modal-detalles.blade.php --}}

<!-- Modal de detalles de la baja -->
<div class="modal fade" id="modalDetalles{{ $despido->id_baja }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header {{ $despido->es_cancelado ? 'bg-warning' : 'bg-danger' }} text-white">
                <h5 class="modal-title">
                    <i class="bi bi-{{ $despido->es_cancelado ? 'clock-history' : 'person-x-fill' }}"></i> 
                    Detalles de la Baja #{{ $despido->id_baja }}
                    <span class="badge {{ $despido->es_cancelado ? 'bg-dark' : 'bg-light text-dark' }} ms-2">
                        {{ $despido->estado_texto }}
                    </span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- ✅ INFORMACIÓN DEL TRABAJADOR -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-person-circle"></i> Datos del Trabajador</h6>
                            </div>
                            <div class="card-body">
                                <!-- Avatar y nombre -->
                                <div class="text-center mb-3">
                                    <div class="avatar-circle mx-auto" 
                                         style="background-color: #007bff; width: 70px; height: 70px; font-size: 20px;">
                                        {{ substr($despido->trabajador->nombre_trabajador, 0, 1) }}{{ substr($despido->trabajador->ape_pat, 0, 1) }}
                                    </div>
                                    <h5 class="text-primary mt-2 mb-1">{{ $despido->trabajador->nombre_completo }}</h5>
                                    <small class="text-muted">ID: {{ $despido->trabajador->id_trabajador }}</small>
                                </div>
                                
                                <!-- Información básica -->
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><strong>Estado Actual:</strong></span>
                                            <span class="badge {{ $despido->trabajador->estaActivo() ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($despido->trabajador->estatus) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($despido->trabajador->fichaTecnica)
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Área:</strong></span>
                                            <span class="text-end">{{ $despido->trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Cargo:</strong></span>
                                            <span class="text-end">{{ $despido->trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Fecha Ingreso:</strong></span>
                                            <span class="badge bg-info">{{ $despido->trabajador->fecha_ingreso->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- ✅ INFORMACIÓN DE MÚLTIPLES BAJAS -->
                                    @if($despido->trabajador->tieneMultiplesBajas())
                                    <div class="col-12 mb-2">
                                        <div class="alert alert-warning py-2 mb-2">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Múltiples Bajas:</strong> {{ $despido->trabajador->resumen_bajas }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Tiempo en la empresa hasta la baja -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Tiempo Laborado:</strong></span>
                                            <span class="text-end">
                                                {{ $despido->trabajador->fecha_ingreso->diffForHumans(\Carbon\Carbon::parse($despido->fecha_baja), true) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ INFORMACIÓN DE LA BAJA -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-{{ $despido->es_cancelado ? 'warning' : 'danger' }} h-100">
                            <div class="card-header bg-{{ $despido->es_cancelado ? 'warning' : 'danger' }} text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-{{ $despido->es_cancelado ? 'clock-history' : 'exclamation-triangle' }}"></i> 
                                    Datos de la Baja
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Estado de la baja -->
                                <div class="alert alert-{{ $despido->es_cancelado ? 'warning' : 'danger' }} mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong>Estado de la Baja:</strong></span>
                                        <span class="badge bg-{{ $despido->es_cancelado ? 'dark' : 'light' }} text-{{ $despido->es_cancelado ? 'white' : 'dark' }} fs-6">
                                            {{ $despido->estado_texto }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Información principal -->
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

                                <!-- ✅ INFORMACIÓN DE CANCELACIÓN SI APLICA -->
                                @if($despido->es_cancelado)
                                <div class="mb-3">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white py-2">
                                            <h6 class="mb-0 fs-6"><i class="bi bi-check-circle"></i> Información de Cancelación</h6>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <strong>Fecha de Cancelación:</strong><br>
                                                    <span class="badge bg-success">{{ $despido->fecha_cancelacion->format('d/m/Y H:i') }}</span>
                                                </div>
                                                @if($despido->usuarioCancelacion)
                                                <div class="col-12">
                                                    <strong>Cancelado por:</strong><br>
                                                    <span class="text-success">{{ $despido->usuarioCancelacion->name }}</span>
                                                </div>
                                                @endif
                                                @if($despido->motivo_cancelacion)
                                                <div class="col-12">
                                                    <strong>Motivo de Cancelación:</strong><br>
                                                    <div class="bg-light p-2 rounded">
                                                        {{ $despido->motivo_cancelacion }}
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Auditoría -->
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <strong>Registro creado:</strong> {{ $despido->created_at->format('d/m/Y H:i') }}<br>
                                        <strong>Última actualización:</strong> {{ $despido->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ MOTIVO DE LA BAJA -->
                    <div class="col-12 mb-3">
                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0"><i class="bi bi-chat-text"></i> Motivo de la Baja</h6>
                            </div>
                            <div class="card-body">
                                <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                    {{ $despido->motivo }}
                                </div>
                                
                                <!-- Análisis automático del motivo -->
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Tipo detectado:</strong> 
                                        <span class="badge 
                                            @if($despido->esBajaVoluntaria()) bg-info
                                            @elseif($despido->esDespidoJustificado()) bg-danger
                                            @else bg-secondary
                                            @endif
                                        ">
                                            {{ $despido->tipo_baja }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ OBSERVACIONES ADICIONALES -->
                    @if($despido->observaciones)
                    <div class="col-12 mb-3">
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

                    <!-- ✅ RESUMEN DE HISTORIAL SI TIENE MÚLTIPLES BAJAS -->
                    @if($despido->trabajador->tieneMultiplesBajas())
                    <div class="col-12">
                        <div class="card border-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history"></i> 
                                    Resumen del Historial de Bajas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-primary mb-1">{{ $despido->trabajador->totalDespidos() }}</h4>
                                            <small class="text-muted">Total de Bajas</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-danger mb-1">{{ $despido->trabajador->despidosActivos() }}</h4>
                                            <small class="text-muted">Bajas Activas</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-success mb-1">{{ $despido->trabajador->despidosCancelados() }}</h4>
                                            <small class="text-muted">Bajas Canceladas</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm"
                                            onclick="verHistorialTrabajador({{ $despido->trabajador->id_trabajador }})"
                                            data-bs-dismiss="modal">
                                        <i class="bi bi-clock-history"></i> Ver Historial Completo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- ✅ FOOTER CON ACCIONES -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
                
                <!-- Ver historial completo del trabajador -->
                <button type="button" 
                        class="btn btn-outline-info"
                        onclick="verHistorialTrabajador({{ $despido->trabajador->id_trabajador }})"
                        data-bs-dismiss="modal">
                    <i class="bi bi-clock-history"></i> Ver Historial
                </button>
                
                <!-- Reactivar solo si es activo -->
                @if($despido->es_activo)
                    <button type="button" 
                            class="btn btn-success" 
                            data-bs-dismiss="modal"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalReactivar{{ $despido->id_baja }}">
                        <i class="bi bi-arrow-clockwise"></i> Reactivar Trabajador
                    </button>
                @endif
                
                <!-- Ir al perfil del trabajador -->
                <a href="{{ route('trabajadores.show', $despido->trabajador->id_trabajador) }}" 
                   class="btn btn-primary"
                   target="_blank">
                    <i class="bi bi-person-circle"></i> Ver Perfil Completo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ✅ ESTILOS ESPECÍFICOS PARA EL MODAL -->
<style>
/* Estilos para el modal de detalles */
.modal-xl .card {
    transition: all 0.3s ease;
}

.modal-xl .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

.alert {
    border-left: 4px solid;
}

.alert-danger {
    border-left-color: #dc3545 !important;
}

.alert-warning {
    border-left-color: #ffc107 !important;
}

.alert-success {
    border-left-color: #198754 !important;
}

.alert-info {
    border-left-color: #0dcaf0 !important;
}

/* Timeline para múltiples bajas en modal */
.timeline-mini {
    border-left: 2px solid #dee2e6;
    padding-left: 1rem;
    margin-left: 0.5rem;
}

.timeline-mini .timeline-item {
    position: relative;
    margin-bottom: 1rem;
}

.timeline-mini .timeline-item::before {
    content: '';
    position: absolute;
    left: -1.25rem;
    top: 0.25rem;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #6c757d;
}

.timeline-mini .timeline-item.active::before {
    background-color: #dc3545;
}

.timeline-mini .timeline-item.cancelled::before {
    background-color: #198754;
}
</style>