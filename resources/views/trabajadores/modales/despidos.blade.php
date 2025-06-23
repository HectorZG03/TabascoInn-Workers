{{-- ✅ MODAL DE DESPIDO --}}
<div class="modal fade" id="modalDespido" tabindex="-1" aria-labelledby="modalDespidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalDespidoLabel">
                    <i class="bi bi-exclamation-triangle"></i> Dar de Baja a Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formDespido" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Información del trabajador -->
                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Información Importante
                        </h6>
                        <p class="mb-0">
                            Está a punto de dar de baja al trabajador <strong id="nombreTrabajador"></strong>. 
                            Esta acción cambiará su estado a "Inactivo" y se creará un registro permanente de la baja.
                        </p>
                    </div>

                    <div class="row">
                        <!-- Fecha de Baja -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_baja" class="form-label">
                                <i class="bi bi-calendar-x"></i> Fecha de Baja *
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_baja" 
                                   name="fecha_baja" 
                                   value="{{ date('Y-m-d') }}"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            <div class="form-text">No puede ser posterior a hoy</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Condición de Salida -->
                        <div class="col-md-6 mb-3">
                            <label for="condicion_salida" class="form-label">
                                <i class="bi bi-list-check"></i> Condición de Salida *
                            </label>
                            <select class="form-select" id="condicion_salida" name="condicion_salida" required>
                                <option value="">Seleccionar condición...</option>
                                <option value="Voluntaria">Voluntaria (Renuncia)</option>
                                <option value="Despido con Causa">Baja con Causa</option>
                                <option value="Despido sin Causa">Baja sin Causa</option>
                                <option value="Mutuo Acuerdo">Mutuo Acuerdo</option>
                                <option value="Abandono de Trabajo">Abandono de Trabajo</option>
                                <option value="Fin de Contrato">Fin de Contrato</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div class="mb-3">
                        <label for="motivo" class="form-label">
                            <i class="bi bi-chat-text"></i> Motivo de la Baja *
                        </label>
                        <textarea class="form-control" 
                                  id="motivo" 
                                  name="motivo" 
                                  rows="3" 
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="Descripción detallada del motivo de la baja..."
                                  required></textarea>
                        <div class="form-text">
                            Mínimo 10 caracteres, máximo 500. <span id="contadorMotivo">0/500</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">
                            <i class="bi bi-clipboard-data"></i> Observaciones Adicionales
                        </label>
                        <textarea class="form-control" 
                                  id="observaciones" 
                                  name="observaciones" 
                                  rows="3" 
                                  maxlength="1000"
                                  placeholder="Observaciones adicionales, recomendaciones o notas relevantes..."></textarea>
                        <div class="form-text">
                            Opcional. Máximo 1000 caracteres. <span id="contadorObservaciones">0/1000</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarDespido">
                        <i class="bi bi-person-x"></i> Confirmar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT DEL MODAL DE DESPIDOS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables del modal de despido
    const modalDespido = document.getElementById('modalDespido');
    const formDespido = document.getElementById('formDespido');
    const nombreTrabajador = document.getElementById('nombreTrabajador');
    const fechaBaja = document.getElementById('fecha_baja');
    const motivo = document.getElementById('motivo');
    const observaciones = document.getElementById('observaciones');
    const btnConfirmarDespido = document.getElementById('btnConfirmarDespido');
    const contadorMotivo = document.getElementById('contadorMotivo');
    const contadorObservaciones = document.getElementById('contadorObservaciones');
    
    // Verificar que todos los elementos existen antes de continuar
    if (!modalDespido || !formDespido) {
        console.log('Modal de despido no encontrado, saltando inicialización');
        return;
    }
    
    // ✅ ABRIR MODAL DE DESPIDO
    document.querySelectorAll('.btn-despedir').forEach(btn => {
        btn.addEventListener('click', function() {
            const trabajadorId = this.dataset.id;
            const trabajadorNombre = this.dataset.nombre;
            const fechaIngreso = this.dataset.fechaIngreso;
            
            // Configurar modal
            nombreTrabajador.textContent = trabajadorNombre;
            formDespido.action = `/trabajadores/${trabajadorId}/despedir`;
            fechaBaja.min = fechaIngreso;
            
            // Limpiar formulario
            formDespido.reset();
            fechaBaja.value = new Date().toISOString().split('T')[0];
            
            // Resetear contadores
            if (contadorMotivo) contadorMotivo.textContent = '0/500';
            if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Mostrar modal
            const modal = new bootstrap.Modal(modalDespido);
            modal.show();
        });
    });
    
    // ✅ CONTADORES DE CARACTERES
    if (motivo && contadorMotivo) {
        motivo.addEventListener('input', function() {
            const length = this.value.length;
            contadorMotivo.textContent = `${length}/500`;
            contadorMotivo.className = length > 450 ? 'text-warning' : 'text-muted';
        });
    }
    
    if (observaciones && contadorObservaciones) {
        observaciones.addEventListener('input', function() {
            const length = this.value.length;
            contadorObservaciones.textContent = `${length}/1000`;
            contadorObservaciones.className = length > 900 ? 'text-warning' : 'text-muted';
        });
    }
    
    // ✅ VALIDACIÓN DEL FORMULARIO
    if (formDespido) {
        formDespido.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validar fecha de baja
            if (!fechaBaja.value) {
                showFieldError(fechaBaja, 'La fecha de baja es obligatoria');
                isValid = false;
            }
            
            // Validar condición de salida
            const condicionSalida = document.getElementById('condicion_salida');
            if (!condicionSalida.value) {
                showFieldError(condicionSalida, 'Debe seleccionar una condición de salida');
                isValid = false;
            }
            
            // Validar motivo
            if (!motivo.value.trim()) {
                showFieldError(motivo, 'El motivo es obligatorio');
                isValid = false;
            } else if (motivo.value.trim().length < 10) {
                showFieldError(motivo, 'El motivo debe tener al menos 10 caracteres');
                isValid = false;
            }
            
            if (isValid) {
                // Deshabilitar botón para evitar doble envío
                btnConfirmarDespido.disabled = true;
                btnConfirmarDespido.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                
                // Enviar formulario
                this.submit();
            }
        });
    }
    
    // ✅ FUNCIÓN PARA MOSTRAR ERRORES
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }
    }
    
    // ✅ RESETEAR MODAL AL CERRARSE
    if (modalDespido) {
        modalDespido.addEventListener('hidden.bs.modal', function() {
            // Limpiar formulario
            if (formDespido) {
                formDespido.reset();
            }
            
            // Limpiar validaciones
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Resetear botón
            if (btnConfirmarDespido) {
                btnConfirmarDespido.innerHTML = '<i class="bi bi-person-x"></i> Confirmar Baja';
            }
            
            // Resetear contadores
            if (contadorMotivo) contadorMotivo.textContent = '0/500';
            if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';
        });
    }
    
    console.log('✅ Modal de despidos inicializado correctamente');
});
</script>