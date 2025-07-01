{{-- ✅ MODAL DE PERMISOS LABORALES CON TIPO PERSONALIZADO --}}
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
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Información del Permiso</h6>
                        <p class="mb-0">
                            Está a punto de asignar un permiso laboral a <strong id="nombreTrabajadorPermiso"></strong>.
                            El trabajador cambiará al estado "Con Permiso" durante el periodo seleccionado.
                        </p>
                    </div>

                    <div class="row">
                        <!-- ✅ TIPO DE PERMISO MEJORADO -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_permiso" class="form-label">
                                <i class="bi bi-tags"></i> Tipo de Permiso <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_permiso" name="tipo_permiso" required onchange="toggleTipoPersonalizado()">
                                <option value="">Seleccione el tipo...</option>
                                <option value="Vacaciones">🏖️ Vacaciones</option>
                                <option value="Licencia Médica">🏥 Licencia Médica</option>
                                <option value="Licencia por Maternidad">👶 Licencia por Maternidad</option>
                                <option value="Licencia por Paternidad">👨‍👶 Licencia por Paternidad</option>
                                <option value="Permiso Personal">👤 Permiso Personal</option>
                                <option value="Permiso por Estudios">🎓 Permiso por Estudios</option>
                                <option value="Permiso por Capacitación">📚 Permiso por Capacitación</option>
                                <option value="Licencia sin Goce de Sueldo">💼 Licencia sin Goce de Sueldo</option>
                                <option value="Permiso Especial">⭐ Permiso Especial</option>
                                <option value="Permiso por Duelo">🖤 Permiso por Duelo</option>
                                <option value="Permiso por Matrimonio">💒 Permiso por Matrimonio</option>
                                <option value="Incapacidad Temporal">🩺 Incapacidad Temporal</option>
                                <option value="Licencia por Familiar Enfermo">👨‍⚕️ Licencia por Familiar Enfermo</option>
                                <option value="Permiso por Emergencia">🚨 Permiso por Emergencia</option>
                                <option value="Licencia Sindical">🤝 Licencia Sindical</option>
                                <!-- ✅ OPCIÓN PARA TIPO PERSONALIZADO -->
                                <option value="OTRO">✏️ Otro (especificar)</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar-range"></i> Duración</label>
                            <div class="form-control bg-light text-center" id="duracionPermiso">
                                <span class="fw-bold text-primary">0 días</span>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ CAMPO PERSONALIZADO PARA TIPO DE PERMISO -->
                    <div class="mb-3" id="tipoPersonalizadoContainer" style="display: none;">
                        <label for="tipo_personalizado" class="form-label">
                            <i class="bi bi-pencil-square me-1"></i> Especificar Tipo de Permiso <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="tipo_personalizado" 
                               name="tipo_personalizado" 
                               placeholder="Escriba el tipo de permiso específico..."
                               maxlength="80">
                        <small class="form-text text-muted">
                            <i class="bi bi-lightbulb-fill text-warning"></i> 
                            Escriba el tipo exacto cuando ninguna de las opciones anteriores sea apropiada.
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">
                            <i class="bi bi-clipboard-check"></i> Motivo del Permiso <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="motivo" 
                               name="motivo" 
                               placeholder="Escriba el motivo específico del permiso..." 
                               maxlength="100"
                               required>
                        <small class="form-text text-muted">Mínimo 3 caracteres, máximo 100.</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="bi bi-calendar-plus"></i> Fecha de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" min="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="bi bi-calendar-check"></i> Fecha de Fin <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- ✅ Toggle para activar permisos por horas --}}
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="es_por_horas" value="0">
                        <input class="form-check-input" type="checkbox" id="es_por_horas" name="es_por_horas" value="1">
                        <label class="form-check-label fw-semibold text-muted" for="es_por_horas">
                            ¿Este permiso será por horas específicas dentro del día?
                        </label>
                    </div>

                    {{-- ✅ Campos de horas --}}
                    <div id="camposHoras" class="row d-none">
                        <div class="col-md-6 mb-3">
                            <label for="hora_inicio" class="form-label">
                                <i class="bi bi-clock"></i> Hora de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hora_fin" class="form-label">
                                <i class="bi bi-clock-history"></i> Hora de Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" id="hora_fin" name="hora_fin">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">
                            <i class="bi bi-chat-text"></i> Observaciones Adicionales
                        </label>
                        <textarea class="form-control" 
                                  id="observaciones" 
                                  name="observaciones" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Información adicional, contacto de emergencia, referencias médicas, etc..."></textarea>
                        <small class="form-text text-muted">Opcional. Máximo 500 caracteres.</small>
                    </div>

                    <div class="card bg-light border-info">
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2 text-info"><i class="bi bi-lightbulb"></i> Información importante</h6>
                            <div class="small text-muted">
                                • El trabajador pasará al estado "Con Permiso" automáticamente<br>
                                • Se puede finalizar antes del vencimiento si regresa antes<br>
                                • Se reactivará automáticamente al finalizar el periodo<br>
                                • Se generará un registro detallado del permiso
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info" id="btnConfirmarPermiso">
                        <i class="bi bi-check-circle"></i> Asignar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleTipoPersonalizado() {
        const select = document.getElementById('tipo_permiso');
        const container = document.getElementById('tipoPersonalizadoContainer');
        const input = document.getElementById('tipo_personalizado');
        
        if (select.value === 'OTRO') {
            container.style.display = 'block';
            input.required = true;
            input.focus();
        } else {
            container.style.display = 'none';
            input.required = false;
            input.value = '';
            input.classList.remove('is-invalid');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modalPermisos = document.getElementById('modalPermisos');
        const formPermisos = document.getElementById('formPermisos');
        const nombreTrabajadorPermiso = document.getElementById('nombreTrabajadorPermiso');
        const tipoPermiso = document.getElementById('tipo_permiso');
        const tipoPersonalizado = document.getElementById('tipo_personalizado');
        const motivo = document.getElementById('motivo');
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const btnConfirmarPermiso = document.getElementById('btnConfirmarPermiso');
        const duracionPermiso = document.getElementById('duracionPermiso');

        const esPorHoras = document.getElementById('es_por_horas');
        const camposHoras = document.getElementById('camposHoras');
        const horaInicio = document.getElementById('hora_inicio');
        const horaFin = document.getElementById('hora_fin');

        if (!modalPermisos || !formPermisos) return;

        document.querySelectorAll('.btn-permisos').forEach(btn => {
            btn.addEventListener('click', function () {
                const trabajadorId = this.dataset.id;
                const trabajadorNombre = this.dataset.nombre;

                nombreTrabajadorPermiso.textContent = trabajadorNombre;
                formPermisos.action = `/trabajadores/${trabajadorId}/permisos`;

                resetForm();
                new bootstrap.Modal(modalPermisos).show();
            });
        });

        fechaInicio?.addEventListener('change', () => {
            if (fechaFin) fechaFin.min = fechaInicio.value;
            calcularDuracion();
        });

        fechaFin?.addEventListener('change', calcularDuracion);

        esPorHoras?.addEventListener('change', () => {
            if (esPorHoras.checked) {
                camposHoras.classList.remove('d-none');
                horaInicio.required = true;
                horaFin.required = true;
            } else {
                camposHoras.classList.add('d-none');
                horaInicio.required = false;
                horaFin.required = false;
                horaInicio.value = '';
                horaFin.value = '';
            }
        });

        function calcularDuracion() {
            if (!fechaInicio.value || !fechaFin.value) {
                duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
                return;
            }

            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);

            if (fin >= inicio) {
                const diff = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
                duracionPermiso.innerHTML = `<span class="fw-bold text-success">${diff} día${diff > 1 ? 's' : ''}</span>`;
                fechaFin.setCustomValidity('');
            } else {
                duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fechas inválidas</span>';
                fechaFin.setCustomValidity('La fecha de fin debe ser igual o posterior a la de inicio');
            }
        }

        formPermisos.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(formPermisos);
            const valores = {
                tipoPermiso: formData.get('tipo_permiso'),
                tipoPersonalizado: formData.get('tipo_personalizado'),
                motivo: formData.get('motivo'),
                fechaInicio: formData.get('fecha_inicio'),
                fechaFin: formData.get('fecha_fin'),
                esPorHoras: formData.get('es_por_horas') === '1',
                horaInicio: formData.get('hora_inicio'),
                horaFin: formData.get('hora_fin'),
            };

            let isValid = true;

            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            if (!valores.tipoPermiso) {
                showFieldError(tipoPermiso, 'Debe seleccionar un tipo de permiso');
                isValid = false;
            } else if (valores.tipoPermiso === 'OTRO') {
                if (!valores.tipoPersonalizado || valores.tipoPersonalizado.trim().length < 3) {
                    showFieldError(tipoPersonalizado, 'Debe especificar el tipo de permiso (mín. 3 caracteres)');
                    isValid = false;
                }
            }

            if (!valores.motivo || valores.motivo.trim().length < 3) {
                showFieldError(motivo, 'El motivo es obligatorio (mín. 3 caracteres)');
                isValid = false;
            }

            if (!valores.fechaInicio) {
                showFieldError(fechaInicio, 'La fecha de inicio es obligatoria');
                isValid = false;
            }

            if (!valores.fechaFin) {
                showFieldError(fechaFin, 'La fecha de fin es obligatoria');
                isValid = false;
            }

            if (valores.esPorHoras) {
                if (!valores.horaInicio) {
                    showFieldError(horaInicio, 'La hora de inicio es obligatoria');
                    isValid = false;
                }
                if (!valores.horaFin) {
                    showFieldError(horaFin, 'La hora de fin es obligatoria');
                    isValid = false;
                }
                if (valores.horaInicio && valores.horaFin && valores.horaFin <= valores.horaInicio) {
                    showFieldError(horaFin, 'La hora de fin debe ser posterior a la de inicio');
                    isValid = false;
                }
            }

            if (isValid) {
                btnConfirmarPermiso.disabled = true;
                btnConfirmarPermiso.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                formPermisos.submit();
            }
        });

        function showFieldError(field, message) {
            field.classList.add('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = message;
        }

        function resetForm() {
            formPermisos.reset();
            duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
            btnConfirmarPermiso.disabled = false;
            btnConfirmarPermiso.innerHTML = '<i class="bi bi-check-circle"></i> Asignar Permiso';
            camposHoras.classList.add('d-none');
            horaInicio.required = false;
            horaFin.required = false;
            toggleTipoPersonalizado();
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }

        modalPermisos.addEventListener('hidden.bs.modal', resetForm);
    });
</script>
