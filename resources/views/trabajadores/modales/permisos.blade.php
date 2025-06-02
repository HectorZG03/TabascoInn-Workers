{{-- ✅ MODAL DE PERMISOS LABORALES --}}
<div class="modal fade" id="modalPermisos" tabindex="-1" aria-labelledby="modalPermisosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPermisosLabel">
                    <i class="bi bi-calendar-event"></i> Asignar Permiso Laboral
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formPermisos" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Información del trabajador -->
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Información del Permiso
                        </h6>
                        <p class="mb-0">
                            Está a punto de asignar un permiso laboral a <strong id="nombreTrabajadorPermiso"></strong>. 
                            Esta acción cambiará temporalmente su estado y se creará un registro del permiso.
                        </p>
                    </div>

                    <div class="row">
                        <!-- Tipo de Permiso -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_permiso" class="form-label">
                                <i class="bi bi-list-check"></i> Tipo de Permiso *
                            </label>
                            <select class="form-select" id="tipo_permiso" name="tipo_permiso" required>
                                <option value="">Seleccionar tipo de permiso...</option>
                                <option value="vacaciones">Vacaciones</option>
                                <option value="incapacidad_medica">Incapacidad Médica</option>
                                <option value="licencia_maternidad">Licencia por Maternidad</option>
                                <option value="licencia_paternidad">Licencia por Paternidad</option>
                                <option value="licencia_sin_goce">Licencia sin Goce de Sueldo</option>
                                <option value="permiso_especial">Permiso Especial</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Duración Calculada -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-range"></i> Duración del Permiso
                            </label>
                            <div class="form-control bg-light text-center" id="duracionPermiso">
                                <span class="fw-bold text-primary">0 días</span>
                            </div>
                            <div class="form-text text-center">Se calculará automáticamente</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fecha de Inicio -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="bi bi-calendar-plus"></i> Fecha de Inicio *
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            <div class="form-text">No puede ser anterior a hoy</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Fecha de Fin -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="bi bi-calendar-check"></i> Fecha de Fin *
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_fin" 
                                   name="fecha_fin" 
                                   required>
                            <div class="form-text">Debe ser igual o posterior al inicio</div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_permiso" class="form-label">
                            <i class="bi bi-chat-text"></i> Observaciones y Detalles
                        </label>
                        <textarea class="form-control" 
                                  id="observaciones_permiso" 
                                  name="observaciones" 
                                  rows="4" 
                                  maxlength="1000"
                                  placeholder="Detalles del permiso, motivo, recomendaciones médicas, etc..."></textarea>
                        <div class="form-text">
                            Opcional. Máximo 1000 caracteres. <span id="contadorObservacionesPermiso">0/1000</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2">
                                <i class="bi bi-lightbulb"></i> Información Importante
                            </h6>
                            <ul class="mb-0 small">
                                <li>El trabajador pasará automáticamente al estado correspondiente al tipo de permiso</li>
                                <li>El permiso se activará inmediatamente desde la fecha de inicio</li>
                                <li>Puede finalizar o cancelar el permiso desde la gestión de permisos</li>
                                <li>Los trabajadores se reactivarán automáticamente al vencer el permiso</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirmación -->
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmarPermiso" required>
                        <label class="form-check-label" for="confirmarPermiso">
                            <strong>Confirmo que he revisado las fechas y deseo asignar este permiso laboral</strong>
                        </label>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info" id="btnConfirmarPermiso" disabled>
                        <i class="bi bi-calendar-plus"></i> Asignar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT DEL MODAL DE PERMISOS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables del modal de permisos
    const modalPermisos = document.getElementById('modalPermisos');
    const formPermisos = document.getElementById('formPermisos');
    const nombreTrabajadorPermiso = document.getElementById('nombreTrabajadorPermiso');
    const tipoPermiso = document.getElementById('tipo_permiso');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const observacionesPermiso = document.getElementById('observaciones_permiso');
    const confirmarPermiso = document.getElementById('confirmarPermiso');
    const btnConfirmarPermiso = document.getElementById('btnConfirmarPermiso');
    const contadorObservacionesPermiso = document.getElementById('contadorObservacionesPermiso');
    const duracionPermiso = document.getElementById('duracionPermiso');
    
    // Verificar que todos los elementos existen antes de continuar
    if (!modalPermisos || !formPermisos) {
        console.log('Modal de permisos no encontrado, saltando inicialización');
        return;
    }
    
    // ✅ ABRIR MODAL DE PERMISOS
    document.querySelectorAll('.btn-permisos').forEach(btn => {
        btn.addEventListener('click', function() {
            const trabajadorId = this.dataset.id;
            const trabajadorNombre = this.dataset.nombre;
            
            // Configurar modal
            nombreTrabajadorPermiso.textContent = trabajadorNombre;
            formPermisos.action = `/trabajadores/${trabajadorId}/permisos`;
            
            // Limpiar formulario
            formPermisos.reset();
            fechaInicio.value = '';
            fechaFin.value = '';
            confirmarPermiso.checked = false;
            btnConfirmarPermiso.disabled = true;
            
            // Resetear contadores y duración
            if (contadorObservacionesPermiso) contadorObservacionesPermiso.textContent = '0/1000';
            if (duracionPermiso) duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Mostrar modal
            const modal = new bootstrap.Modal(modalPermisos);
            modal.show();
        });
    });
    
    // ✅ CALCULAR DURACIÓN DEL PERMISO
    function calcularDuracion() {
        if (fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            
            if (fin >= inicio) {
                const diffTime = fin - inicio;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                duracionPermiso.innerHTML = `<span class="fw-bold text-success">${diffDays} día${diffDays !== 1 ? 's' : ''}</span>`;
                
                // Actualizar validación de fecha fin
                fechaFin.setCustomValidity('');
            } else {
                duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fechas inválidas</span>';
                fechaFin.setCustomValidity('La fecha de fin debe ser igual o posterior a la de inicio');
            }
        } else {
            duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
        }
    }
    
    // ✅ EVENTOS PARA CALCULAR DURACIÓN
    if (fechaInicio) {
        fechaInicio.addEventListener('change', function() {
            // Actualizar fecha mínima de fin
            fechaFin.min = this.value;
            calcularDuracion();
        });
    }
    
    if (fechaFin) {
        fechaFin.addEventListener('change', calcularDuracion);
    }
    
    // ✅ CONTADOR DE CARACTERES PARA OBSERVACIONES
    if (observacionesPermiso && contadorObservacionesPermiso) {
        observacionesPermiso.addEventListener('input', function() {
            const length = this.value.length;
            contadorObservacionesPermiso.textContent = `${length}/1000`;
            contadorObservacionesPermiso.className = length > 900 ? 'text-warning' : 'text-muted';
        });
    }
    
    // ✅ HABILITAR/DESHABILITAR BOTÓN SEGÚN CHECKBOX
    if (confirmarPermiso && btnConfirmarPermiso) {
        confirmarPermiso.addEventListener('change', function() {
            btnConfirmarPermiso.disabled = !this.checked;
        });
    }
    
    // ✅ VALIDACIÓN DEL FORMULARIO
    if (formPermisos) {
        formPermisos.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validar tipo de permiso
            if (!tipoPermiso.value) {
                showFieldError(tipoPermiso, 'Debe seleccionar un tipo de permiso');
                isValid = false;
            }
            
            // Validar fecha de inicio
            if (!fechaInicio.value) {
                showFieldError(fechaInicio, 'La fecha de inicio es obligatoria');
                isValid = false;
            } else if (new Date(fechaInicio.value) < new Date()) {
                showFieldError(fechaInicio, 'La fecha de inicio no puede ser anterior a hoy');
                isValid = false;
            }
            
            // Validar fecha de fin
            if (!fechaFin.value) {
                showFieldError(fechaFin, 'La fecha de fin es obligatoria');
                isValid = false;
            } else if (new Date(fechaFin.value) < new Date(fechaInicio.value)) {
                showFieldError(fechaFin, 'La fecha de fin debe ser igual o posterior a la de inicio');
                isValid = false;
            }
            
            // Validar confirmación
            if (!confirmarPermiso.checked) {
                showFieldError(confirmarPermiso, 'Debe confirmar que desea asignar el permiso');
                isValid = false;
            }
            
            if (isValid) {
                // Deshabilitar botón para evitar doble envío
                btnConfirmarPermiso.disabled = true;
                btnConfirmarPermiso.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                
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
    if (modalPermisos) {
        modalPermisos.addEventListener('hidden.bs.modal', function() {
            // Limpiar formulario
            if (formPermisos) {
                formPermisos.reset();
            }
            
            // Limpiar validaciones
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Resetear botón
            if (btnConfirmarPermiso) {
                btnConfirmarPermiso.disabled = true;
                btnConfirmarPermiso.innerHTML = '<i class="bi bi-calendar-plus"></i> Asignar Permiso';
            }
            
            // Resetear contadores y duración
            if (contadorObservacionesPermiso) contadorObservacionesPermiso.textContent = '0/1000';
            if (duracionPermiso) duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
        });
    }
    
    console.log('✅ Modal de permisos laborales inicializado correctamente');
});
</script>