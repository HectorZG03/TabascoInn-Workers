<!-- Modal de detalles del despido -->
<div class="modal fade" id="modalDetalles{{ $despido->id_baja }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-{{ $despido->es_cancelado ? 'warning' : ($despido->tipo_baja === 'temporal' ? 'info' : 'danger') }} text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-x-fill me-1"></i> Baja #{{ $despido->id_baja }}
                    <span class="badge bg-light text-dark ms-2">{{ ucfirst($despido->tipo_baja) }}</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Datos del trabajador -->
                <div class="mb-3">
                    <strong>Trabajador:</strong> {{ $despido->trabajador->nombre_completo }} <br>
                    <strong>ID:</strong> {{ $despido->trabajador->id_trabajador }} <br>
                    <strong>Estatus:</strong> <span class="badge bg-{{ $despido->trabajador->estaActivo() ? 'success' : 'secondary' }}">{{ ucfirst($despido->trabajador->estatus) }}</span>
                </div>

                <!-- Datos de la baja -->
                <div class="mb-3">
                    <strong>Fecha de Baja:</strong> {{ $despido->fecha_baja->format('d/m/Y') }} <br>

                    @if($despido->tipo_baja === 'temporal' && $despido->fecha_reintegro)
                        <strong>Fecha de Reintegro:</strong> {{ \Carbon\Carbon::parse($despido->fecha_reintegro)->format('d/m/Y') }} <br>
                    @endif

                    <strong>Condición de Salida:</strong> {{ $despido->condicion_salida }}
                </div>

                <!-- Motivo -->
                <div class="mb-3">
                    <strong>Motivo:</strong>
                    <div class="border rounded p-2 bg-light">{{ $despido->motivo }}</div>
                </div>

                <!-- Observaciones -->
                @if($despido->observaciones)
                <div class="mb-3">
                    <strong>Observaciones:</strong>
                    <div class="border rounded p-2 bg-light">{{ $despido->observaciones }}</div>
                </div>
                @endif

                <!-- Información de cancelación -->
                @if($despido->es_cancelado)
                <div class="mb-3">
                    <strong>Cancelado por:</strong> {{ $despido->usuarioCancelacion->name ?? 'N/A' }} <br>
                    <strong>Fecha:</strong> {{ $despido->fecha_cancelacion->format('d/m/Y H:i') }} <br>
                    <strong>Motivo:</strong>
                    <div class="border rounded p-2 bg-light">{{ $despido->motivo_cancelacion ?? 'No especificado' }}</div>
                </div>
                @endif
            </div>

            <div class="modal-footer">
                <a href="{{ route('trabajadores.show', $despido->trabajador->id_trabajador) }}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="bi bi-person-lines-fill"></i> Ver Perfil
                </a>
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
