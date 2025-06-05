{{-- ✅ MODAL DE PERMISOS LABORALES SIMPLIFICADO --}}
<div class="modal fade" id="modalPermisos" tabindex="-1" aria-labelledby="modalPermisosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPermisosLabel">
                    <i class="bi bi-calendar-event"></i> Asignar Permiso o Suspensión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formPermisos" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Información del trabajador -->
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Información del Proceso
                        </h6>
                        <p class="mb-0">
                            Está a punto de cambiar el estado de <strong id="nombreTrabajadorPermiso"></strong>. 
                            Esta acción cambiará su estado y se creará un registro del proceso.
                        </p>
                    </div>

                    <div class="row">
                        <!-- Tipo de Acción -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_permiso" class="form-label">
                                <i class="bi bi-list-check"></i> Tipo de Acción *
                            </label>
                            <select class="form-select" id="tipo_permiso" name="tipo_permiso" required>
                                <option value="">Seleccionar acción...</option>
                                <option value="permiso">
                                    🟢 Dar Permiso
                                </option>
                                <option value="suspendido">
                                    🔴 Suspender
                                </option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Duración Calculada -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-range"></i> Duración
                            </label>
                            <div class="form-control bg-light text-center" id="duracionPermiso">
                                <span class="fw-bold text-primary">0 días</span>
                            </div>
                            <div class="form-text text-center">Se calculará automáticamente</div>
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div class="mb-3" id="motivoContainer" style="display: none;">
                        <label for="motivo" class="form-label">
                            <i class="bi bi-clipboard-check"></i> Motivo
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="motivo" 
                               name="motivo" 
                               maxlength="100"
                               placeholder="Ingrese el motivo específico...">
                        <div class="form-text">
                            Escriba el motivo específico
                            <span class="float-end">
                                <span id="contadorMotivo">0/100</span>
                            </span>
                        </div>
                        <div class="invalid-feedback"></div>
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
                                  placeholder="Detalles adicionales, referencias, recomendaciones, etc..."></textarea>
                        <div class="form-text">
                            Opcional. Máximo 1000 caracteres. 
                            <span class="float-end">
                                <span id="contadorObservacionesPermiso">0/1000</span>
                            </span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Información según tipo -->
                    <div class="card bg-light border-0" id="informacionTipo" style="display: none;">
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2">
                                <i class="bi bi-lightbulb"></i> <span id="tituloInformacion">Información</span>
                            </h6>
                            <div id="contenidoInformacion"></div>
                        </div>
                    </div>

                    <!-- Confirmación -->
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmarPermiso" required>
                        <label class="form-check-label" for="confirmarPermiso">
                            <strong id="textoConfirmacion">Confirmo que he revisado la información y deseo proceder</strong>
                        </label>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info" id="btnConfirmarPermiso">
                        <i class="bi bi-check-circle"></i> <span id="textoBoton">Confirmar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT COMPLETAMENTE FUNCIONAL --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Iniciando modal de permisos...');
    
    // Variables del modal
    const modalPermisos = document.getElementById('modalPermisos');
    const formPermisos = document.getElementById('formPermisos');
    const nombreTrabajadorPermiso = document.getElementById('nombreTrabajadorPermiso');
    const tipoPermiso = document.getElementById('tipo_permiso');
    const motivoContainer = document.getElementById('motivoContainer');
    const motivoInput = document.getElementById('motivo');
    const contadorMotivo = document.getElementById('contadorMotivo');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const observacionesPermiso = document.getElementById('observaciones_permiso');
    const contadorObservacionesPermiso = document.getElementById('contadorObservacionesPermiso');
    const confirmarPermiso = document.getElementById('confirmarPermiso');
    const btnConfirmarPermiso = document.getElementById('btnConfirmarPermiso');
    const duracionPermiso = document.getElementById('duracionPermiso');
    const informacionTipo = document.getElementById('informacionTipo');
    const tituloInformacion = document.getElementById('tituloInformacion');
    const contenidoInformacion = document.getElementById('contenidoInformacion');
    const textoConfirmacion = document.getElementById('textoConfirmacion');
    const textoBoton = document.getElementById('textoBoton');
    
    // ✅ VERIFICAR ELEMENTOS CRÍTICOS
    if (!modalPermisos || !formPermisos) {
        console.log('❌ Modal de permisos no encontrado');
        return;
    }
    
    console.log('✅ Elementos del modal encontrados:', {
        modalPermisos: !!modalPermisos,
        motivoInput: !!motivoInput,
        contadorMotivo: !!contadorMotivo,
        btnConfirmarPermiso: !!btnConfirmarPermiso
    });

    // ✅ ABRIR MODAL
    document.querySelectorAll('.btn-permisos').forEach(btn => {
        btn.addEventListener('click', function() {
            const trabajadorId = this.dataset.id;
            const trabajadorNombre = this.dataset.nombre;
            
            console.log('📂 Abriendo modal para:', trabajadorNombre);
            
            nombreTrabajadorPermiso.textContent = trabajadorNombre;
            formPermisos.action = `/trabajadores/${trabajadorId}/permisos`;
            
            resetForm();
            
            const modal = new bootstrap.Modal(modalPermisos);
            modal.show();
        });
    });
    
    // ✅ CAMBIO DE TIPO - SIMPLIFICADO
    if (tipoPermiso) {
        tipoPermiso.addEventListener('change', function() {
            const tipo = this.value;
            console.log('🔄 Tipo seleccionado:', tipo);
            
            if (tipo) {
                motivoContainer.style.display = 'block';
                informacionTipo.style.display = 'block';
                
                // Actualizar información según tipo
                if (tipo === 'permiso') {
                    tituloInformacion.textContent = 'Información sobre Permisos';
                    contenidoInformacion.innerHTML = `
                        <small class="text-muted">
                            • El trabajador pasará al estado "Con Permiso"<br>
                            • Se reactivará automáticamente al finalizar el periodo<br>
                            • Puede finalizar o cancelar antes del vencimiento
                        </small>
                    `;
                    textoConfirmacion.textContent = 'Confirmo que deseo asignar este permiso laboral';
                    textoBoton.textContent = 'Asignar Permiso';
                } else {
                    tituloInformacion.textContent = 'Información sobre Suspensiones';
                    contenidoInformacion.innerHTML = `
                        <small class="text-warning">
                            • El trabajador pasará al estado "Suspendido"<br>
                            • NO se reactivará automáticamente<br>
                            • Requiere acción administrativa para reactivar
                        </small>
                    `;
                    textoConfirmacion.textContent = 'Confirmo que deseo suspender a este trabajador';
                    textoBoton.textContent = 'Suspender';
                }
            } else {
                motivoContainer.style.display = 'none';
                informacionTipo.style.display = 'none';
            }
            
            validarFormulario();
        });
    }
    
    // ✅ CONTADOR DE MOTIVO - SIN VALIDACIÓN
    if (motivoInput && contadorMotivo) {
        motivoInput.addEventListener('input', function() {
            const length = this.value.length;
            contadorMotivo.textContent = `${length}/100`;
            
            if (length > 90) {
                contadorMotivo.className = 'text-warning';
            } else {
                contadorMotivo.className = 'text-muted';
            }
            
            console.log('✏️ Motivo actualizado:', length, 'caracteres');
            // NO llamamos validarFormulario() aquí
        });
        
        // También agregar evento keyup para mayor responsividad
        motivoInput.addEventListener('keyup', function() {
            const length = this.value.length;
            contadorMotivo.textContent = `${length}/100`;
            
            if (length > 90) {
                contadorMotivo.className = 'text-warning';
            } else {
                contadorMotivo.className = 'text-muted';
            }
            // NO llamamos validarFormulario() aquí
        });
    }
    
    // ✅ CONTADOR DE OBSERVACIONES
    if (observacionesPermiso && contadorObservacionesPermiso) {
        observacionesPermiso.addEventListener('input', function() {
            const length = this.value.length;
            contadorObservacionesPermiso.textContent = `${length}/1000`;
            
            if (length > 900) {
                contadorObservacionesPermiso.className = 'text-warning';
            } else {
                contadorObservacionesPermiso.className = 'text-muted';
            }
        });
    }
    
    // ✅ CALCULAR DURACIÓN
    function calcularDuracion() {
        if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            
            if (fin >= inicio) {
                const diffTime = fin - inicio;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                duracionPermiso.innerHTML = `<span class="fw-bold text-success">${diffDays} día${diffDays !== 1 ? 's' : ''}</span>`;
                if (fechaFin.setCustomValidity) {
                    fechaFin.setCustomValidity('');
                }
            } else {
                duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fechas inválidas</span>';
                if (fechaFin.setCustomValidity) {
                    fechaFin.setCustomValidity('La fecha de fin debe ser igual o posterior a la de inicio');
                }
            }
        } else {
            duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
        }
        validarFormulario();
    }
    
    // ✅ EVENTOS PARA FECHAS
    if (fechaInicio) {
        fechaInicio.addEventListener('change', function() {
            if (fechaFin) {
                fechaFin.min = this.value;
            }
            calcularDuracion();
        });
    }
    
    if (fechaFin) {
        fechaFin.addEventListener('change', calcularDuracion);
    }
    
    // ✅ VALIDACIÓN SIN MOTIVO
    function validarFormulario() {
        // Verificar que todos los elementos existan
        if (!tipoPermiso || !fechaInicio || !fechaFin || !confirmarPermiso || !btnConfirmarPermiso) {
            console.log('❌ Faltan elementos para validación');
            return;
        }
        
        const tipo = tipoPermiso.value;
        const inicio = fechaInicio.value;
        const fin = fechaFin.value;
        const confirmado = confirmarPermiso.checked;
        
        // Validación simple SIN MOTIVO
        const tipoOk = tipo !== '';
        const fechaInicioOk = inicio !== '';
        const fechaFinOk = fin !== '';
        const confirmadoOk = confirmado;
        
        // Validar fechas si ambas están llenas
        let fechasOk = true;
        if (fechaInicioOk && fechaFinOk) {
            const inicioDate = new Date(inicio);
            const finDate = new Date(fin);
            fechasOk = finDate >= inicioDate;
        }
        
        const esValido = tipoOk && fechaInicioOk && fechaFinOk && fechasOk && confirmadoOk;
        
        // Habilitar/deshabilitar botón
        btnConfirmarPermiso.disabled = !esValido;
        
        console.log('🔍 Validación SIN MOTIVO:', {
            tipo: tipoOk ? '✅' : '❌',
            fechaInicio: fechaInicioOk ? '✅' : '❌',
            fechaFin: fechaFinOk ? '✅' : '❌',
            fechasValidas: fechasOk ? '✅' : '❌',
            confirmado: confirmadoOk ? '✅' : '❌',
            resultado: esValido ? '✅ VÁLIDO' : '❌ INVÁLIDO'
        });
    }
    
    // ✅ EVENTOS PARA VALIDACIÓN - SIN MOTIVO
    if (tipoPermiso) tipoPermiso.addEventListener('change', validarFormulario);
    if (fechaInicio) fechaInicio.addEventListener('change', validarFormulario);
    if (fechaFin) fechaFin.addEventListener('change', validarFormulario);
    if (confirmarPermiso) confirmarPermiso.addEventListener('change', validarFormulario);
    
    // ✅ ENVÍO DEL FORMULARIO
    if (formPermisos) {
        formPermisos.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('📤 Enviando formulario...');
            
            if (!btnConfirmarPermiso.disabled) {
                btnConfirmarPermiso.disabled = true;
                btnConfirmarPermiso.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                this.submit();
            } else {
                console.log('❌ Formulario no válido, no se puede enviar');
            }
        });
    }
    
    // ✅ RESETEAR FORMULARIO
    function resetForm() {
        console.log('🔄 Reseteando formulario...');
        
        if (formPermisos) formPermisos.reset();
        
        if (motivoContainer) motivoContainer.style.display = 'none';
        if (informacionTipo) informacionTipo.style.display = 'none';
        if (btnConfirmarPermiso) {
            btnConfirmarPermiso.disabled = true;
            btnConfirmarPermiso.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar';
        }
        
        if (contadorObservacionesPermiso) contadorObservacionesPermiso.textContent = '0/1000';
        if (contadorMotivo) contadorMotivo.textContent = '0/100';
        if (duracionPermiso) duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
        
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
    
    // ✅ RESETEAR AL CERRAR MODAL
    if (modalPermisos) {
        modalPermisos.addEventListener('hidden.bs.modal', resetForm);
    }
    
    console.log('✅ Modal de permisos inicializado correctamente');
    
    // ✅ VALIDACIÓN INICIAL
    setTimeout(validarFormulario, 100);
});
</script>