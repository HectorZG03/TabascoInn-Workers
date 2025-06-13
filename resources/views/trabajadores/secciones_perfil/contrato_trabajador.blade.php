{{-- resources/views/trabajadores/secciones_perfil/contrato_trabajador.blade.php --}}

<div class="container-fluid">
    {{-- ✅ Header con estadísticas de contratos --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-3">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                Administración de Contratos
                            </h4>
                            <p class="text-muted mb-0">
                                Gestión y visualización de contratos laborales de {{ $trabajador->nombre_completo }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 text-primary mb-0">{{ $estadisticas['total'] }}</div>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 text-success mb-0">{{ $estadisticas['vigentes'] }}</div>
                                    <small class="text-muted">Vigentes</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 text-info mb-0">{{ $estadisticas['duracion_total_texto'] }}</div>
                                    <small class="text-muted">Duración Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Alertas de estado importantes --}}
    @if($estadisticas['proximos_vencer'] > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Atención:</strong> Hay {{ $estadisticas['proximos_vencer'] }} contrato(s) próximo(s) a vencer en los próximos 30 días.
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$estadisticas['tiene_contrato_vigente'] && $estadisticas['total'] > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <div>
                        <strong>Sin contrato vigente:</strong> El trabajador no tiene ningún contrato activo actualmente.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ Contenido principal --}}
    <div class="row">
        <div class="col-12">
            @if($contratos->count() > 0)
                {{-- Lista de contratos --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary"></i>
                                Historial de Contratos
                            </h5>
                            <span class="badge bg-light text-dark">{{ $contratos->count() }} contrato(s)</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Estado</th>
                                        <th class="border-0">Período</th>
                                        <th class="border-0">Duración</th>
                                        <th class="border-0">Tipo</th>
                                        <th class="border-0">Días Restantes</th>
                                        <th class="border-0">Archivo</th>
                                        <th class="border-0">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contratos as $contrato)
                                        <tr class="{{ $contrato->esta_vigente_bool ? 'table-success' : '' }}">
                                            {{-- Estado --}}
                                            <td>
                                                <span class="badge bg-{{ $contrato->color_estado }} d-flex align-items-center gap-1" style="width: fit-content;">
                                                    @if($contrato->estado_calculado === 'vigente')
                                                        <i class="bi bi-check-circle"></i>
                                                    @elseif($contrato->estado_calculado === 'expirado')
                                                        <i class="bi bi-x-circle"></i>
                                                    @else
                                                        <i class="bi bi-clock"></i>
                                                    @endif
                                                    {{ ucfirst($contrato->estado_calculado) }}
                                                </span>
                                                @if($contrato->esta_vigente_bool)
                                                    <small class="d-block text-success mt-1">
                                                        <i class="bi bi-star-fill"></i> Activo
                                                    </small>
                                                @endif
                                            </td>

                                            {{-- Período --}}
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong>{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}</strong>
                                                    <small class="text-muted">hasta</small>
                                                    <strong>{{ $contrato->fecha_fin_contrato->format('d/m/Y') }}</strong>
                                                </div>
                                            </td>

                                            {{-- Duración --}}
                                            <td>
                                                <span class="fw-bold">{{ $contrato->duracion_texto }}</span>
                                                <small class="d-block text-muted">
                                                    {{ $contrato->esPorDias() ? 'Por días' : 'Por meses' }}
                                                </small>
                                            </td>

                                            {{-- Tipo de contrato --}}
                                            <td>
                                                <span class="badge bg-info">
                                                    Tiempo Determinado
                                                </span>
                                            </td>

                                            {{-- Días restantes --}}
                                            <td>
                                                @if($contrato->esta_vigente_bool)
                                                    @if($contrato->dias_restantes_calculados <= 30)
                                                        <span class="text-warning fw-bold">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            {{ $contrato->dias_restantes_calculados }} días
                                                        </span>
                                                    @else
                                                        <span class="text-success">
                                                            {{ $contrato->dias_restantes_calculados }} días
                                                        </span>
                                                    @endif
                                                @elseif($contrato->estado_calculado === 'expirado')
                                                    <span class="text-danger">
                                                        <i class="bi bi-x-circle"></i> Expirado
                                                    </span>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="bi bi-clock"></i> Pendiente
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Estado del archivo --}}
                                            <td>
                                                @if($contrato->archivo_existe)
                                                    <span class="text-success">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                        Disponible
                                                    </span>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="bi bi-file-earmark-x"></i>
                                                        No disponible
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Acciones --}}
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- Ver detalles --}}
                                                    <button type="button" 
                                                            class="btn btn-outline-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detalleContratoModal"
                                                            data-contrato-id="{{ $contrato->id_contrato }}"
                                                            data-contrato-inicio="{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}"
                                                            data-contrato-fin="{{ $contrato->fecha_fin_contrato->format('d/m/Y') }}"
                                                            data-contrato-duracion="{{ $contrato->duracion_completa }}"
                                                            data-contrato-estado="{{ $contrato->estado_calculado }}"
                                                            data-contrato-dias-restantes="{{ $contrato->dias_restantes_calculados }}"
                                                            title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    {{-- Descargar PDF --}}
                                                    @if($contrato->archivo_existe)
                                                        <a href="{{ route('trabajadores.contratos.descargar', [$trabajador, $contrato]) }}" 
                                                           class="btn btn-outline-primary btn-sm"
                                                           title="Descargar PDF"
                                                           target="_blank">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-outline-secondary btn-sm" 
                                                                disabled
                                                                title="Archivo no disponible">
                                                            <i class="bi bi-download"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ✅ Información adicional del contrato vigente --}}
                @if($estadisticas['contrato_actual'])
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-check"></i>
                                        Contrato Vigente Actual
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Período:</strong><br>
                                            {{ $estadisticas['contrato_actual']->fecha_inicio_contrato->format('d/m/Y') }} -
                                            {{ $estadisticas['contrato_actual']->fecha_fin_contrato->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Duración:</strong><br>
                                            {{ $estadisticas['contrato_actual']->duracion_texto }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Días Restantes:</strong><br>
                                            <span class="fw-bold {{ $estadisticas['contrato_actual']->diasRestantes() <= 30 ? 'text-warning' : 'text-success' }}">
                                                {{ $estadisticas['contrato_actual']->diasRestantes() }} días
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Vencimiento:</strong><br>
                                            {{ $estadisticas['contrato_actual']->fecha_fin_contrato->format('d \d\e F \d\e Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- Estado vacío --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">Sin Contratos Registrados</h5>
                        <p class="text-muted mb-4">
                            Este trabajador no tiene contratos registrados en el sistema.
                        </p>
                        <div class="alert alert-info d-inline-block">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> Los contratos se generan automáticamente al crear un trabajador o pueden agregarse manualmente.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ Modal para ver detalles del contrato --}}
<div class="modal fade" id="detalleContratoModal" tabindex="-1" aria-labelledby="detalleContratoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleContratoModalLabel">
                    <i class="bi bi-file-earmark-text"></i>
                    Detalles del Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trabajador:</label>
                            <p class="mb-0">{{ $trabajador->nombre_completo }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Categoría:</label>
                            <p class="mb-0">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'No especificada' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Área:</label>
                            <p class="mb-0">{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'No especificada' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha de Inicio:</label>
                            <p class="mb-0" id="modal-fecha-inicio">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha de Fin:</label>
                            <p class="mb-0" id="modal-fecha-fin">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Duración:</label>
                            <p class="mb-0" id="modal-duracion">-</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Estado Actual:</label>
                            <p class="mb-0">
                                <span class="badge" id="modal-estado-badge">-</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Días Restantes:</label>
                            <p class="mb-0" id="modal-dias-restantes">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ JavaScript específico para contratos --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ Manejar modal de detalles del contrato
    const detalleModal = document.getElementById('detalleContratoModal');
    if (detalleModal) {
        detalleModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Obtener datos del botón
            const contratoId = button.getAttribute('data-contrato-id');
            const inicio = button.getAttribute('data-contrato-inicio');
            const fin = button.getAttribute('data-contrato-fin');
            const duracion = button.getAttribute('data-contrato-duracion');
            const estado = button.getAttribute('data-contrato-estado');
            const diasRestantes = button.getAttribute('data-contrato-dias-restantes');
            
            // Actualizar contenido del modal
            document.getElementById('modal-fecha-inicio').textContent = inicio;
            document.getElementById('modal-fecha-fin').textContent = fin;
            document.getElementById('modal-duracion').textContent = duracion;
            document.getElementById('modal-dias-restantes').textContent = diasRestantes + ' días';
            
            // Actualizar badge de estado
            const estadoBadge = document.getElementById('modal-estado-badge');
            estadoBadge.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
            estadoBadge.className = 'badge';
            
            // Aplicar color según el estado
            if (estado === 'vigente') {
                estadoBadge.classList.add('bg-success');
            } else if (estado === 'expirado') {
                estadoBadge.classList.add('bg-danger');
            } else {
                estadoBadge.classList.add('bg-warning');
            }
        });
    }

    // ✅ Auto-hide de alertas después de 10 segundos
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        }, 10000);
    });

    console.log('✅ Administración de Contratos - Scripts inicializados');
});
</script>