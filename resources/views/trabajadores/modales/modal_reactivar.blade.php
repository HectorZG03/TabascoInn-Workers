{{-- resources/views/despidos/partials/modal-reactivar.blade.php --}}

<!-- Modal de confirmación para reactivar -->
<div class="modal fade" id="modalReactivar{{ $despido->id_baja }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-clockwise"></i> Reactivar Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('despidos.cancelar', $despido->id_baja) }}" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="modal-body">
                    <!-- Información del trabajador -->
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar-circle" style="background-color: #0dcaf0; width: 50px; height: 50px; font-size: 16px;">
                                    {{ substr($despido->trabajador->nombre_trabajador, 0, 1) }}{{ substr($despido->trabajador->ape_pat, 0, 1) }}
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="alert-heading mb-1">{{ $despido->trabajador->nombre_completo }}</h6>
                                <small class="text-muted">
                                    ID: {{ $despido->trabajador->id_trabajador }} | 
                                    Dado de baja el: {{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la baja actual -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Baja Actual</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Fecha:</strong> 
                                        <span class="badge bg-danger">{{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Condición:</strong> 
                                        <span class="badge bg-secondary">{{ $despido->condicion_salida }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Motivo:</strong>
                                        <div class="bg-light p-2 rounded mt-1" style="max-height: 100px; overflow-y: auto;">
                                            {{ $despido->motivo }}
                                        </div>
                                    </div>
                                    @if($despido->observaciones)
                                    <div>
                                        <strong>Observaciones:</strong>
                                        <div class="bg-light p-2 rounded mt-1" style="max-height: 80px; overflow-y: auto;">
                                            {{ $despido->observaciones }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-check-circle"></i> Acción a Realizar</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success">
                                        <h6 class="alert-heading">Efectos de la reactivación:</h6>
                                        <ul class="mb-0">
                                            <li>El trabajador volverá al estado <strong>"Activo"</strong></li>
                                            <li>La baja se marcará como <strong>"Cancelada"</strong></li>
                                            <li>Se mantendrá el registro histórico</li>
                                            <li>Podrá volver a trabajar normalmente</li>
                                        </ul>
                                    </div>
                                    
                                    @if($despido->trabajador->tieneMultiplesBajas())
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Atención:</strong> Este trabajador tiene múltiples bajas en su historial.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ MOTIVO DE LA REACTIVACIÓN -->
                    <div class="mb-4">
                        <label for="motivo_cancelacion{{ $despido->id_baja }}" class="form-label">
                            <i class="bi bi-chat-text"></i> Motivo de la Reactivación
                        </label>
                        <textarea class="form-control" 
                                  id="motivo_cancelacion{{ $despido->id_baja }}" 
                                  name="motivo_cancelacion" 
                                  rows="3" 
                                  maxlength="255"
                                  placeholder="Explique brevemente el motivo por el cual se reactiva al trabajador..."></textarea>
                        <div class="form-text">
                            Opcional. Máximo 255 caracteres. 
                            <span id="contador{{ $despido->id_baja }}">0/255</span>
                        </div>
                    </div>

                    <!-- Confirmación -->
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="confirmarReactivacion{{ $despido->id_baja }}" 
                               required>
                        <label class="form-check-label" for="confirmarReactivacion{{ $despido->id_baja }}">
                            <strong>Confirmo que deseo reactivar a este trabajador</strong>
                        </label>
                        <div class="invalid-feedback">
                            Debe confirmar que desea proceder con la reactivación
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                    <button type="submit" 
                            class="btn btn-success" 
                            id="btnReactivar{{ $despido->id_baja }}"
                            disabled>
                        <i class="bi bi-arrow-clockwise"></i> Confirmar Reactivación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables específicas para este modal
    const modalId = '{{ $despido->id_baja }}';
    const textarea = document.getElementById(`motivo_cancelacion${modalId}`);
    const contador = document.getElementById(`contador${modalId}`);
    const checkbox = document.getElementById(`confirmarReactivacion${modalId}`);
    const btnReactivar = document.getElementById(`btnReactivar${modalId}`);
    
    // Verificar que los elementos existen
    if (!textarea || !contador || !checkbox || !btnReactivar) {
        return;
    }
    
    // Contador de caracteres
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        contador.textContent = `${length}/255`;
        contador.className = length > 230 ? 'text-warning' : 'text-muted';
    });
    
    // Habilitar/deshabilitar botón según checkbox
    checkbox.addEventListener('change', function() {
        btnReactivar.disabled = !this.checked;
    });
    
    // Limpiar modal al cerrarse
    const modal = document.getElementById(`modalReactivar${modalId}`);
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Limpiar formulario
            if (textarea) textarea.value = '';
            if (contador) contador.textContent = '0/255';
            if (checkbox) checkbox.checked = false;
            if (btnReactivar) btnReactivar.disabled = true;
            
            // Limpiar validaciones
            document.querySelectorAll(`#modalReactivar${modalId} .is-invalid`)
                    .forEach(el => el.classList.remove('is-invalid'));
        });
    }
});
</script>