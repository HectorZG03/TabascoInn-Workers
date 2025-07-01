<!-- ✅ MODAL COMPLETAMENTE LIMPIO - SIN botón de preview -->
<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalContratoLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Finalizar Registro del Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Mensaje introductorio -->
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-3 fs-5"></i>
                        <div>
                            <h6 class="mb-1">¡Ya casi terminamos!</h6>
                            <p class="mb-0">Solo falta configurar el estado inicial y las fechas del contrato.</p>
                        </div>
                    </div>
                </div>

                <!-- Estado del Trabajador -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>
                            Estado Inicial del Trabajador
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="estatus" class="form-label fw-semibold">
                                    <i class="bi bi-gear me-1"></i>
                                    Seleccionar Estado Inicial *
                                </label>
                                <select class="form-select form-select-lg" 
                                        id="estatus" 
                                        name="estatus" 
                                        required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="activo">
                                        ✅ Activo - Trabajador operativo completo
                                    </option>
                                    <option value="prueba">
                                        🟡 En Prueba - Período de evaluación inicial
                                    </option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <strong>Activo:</strong> Opera normalmente desde el primer día.<br>
                                        <strong>En Prueba:</strong> Período de evaluación (30-90 días típicamente).
                                    </small>
                                </div>
                                <div id="errorEstatus" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Vista previa del estado -->
                        <div id="estadoPreview" class="mt-3" style="display: none;">
                            <div class="alert mb-0" id="estadoPreviewAlert">
                                <div class="d-flex align-items-center">
                                    <i id="estadoPreviewIcon" class="me-2 fs-5"></i>
                                    <div>
                                        <div class="fw-bold" id="estadoPreviewTexto">Estado</div>
                                        <small id="estadoPreviewDescripcion" class="text-muted">Descripción</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fechas del contrato -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-dark">
                            <i class="bi bi-calendar-range me-2"></i>
                            Período del Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fecha de inicio -->
                            <div class="col-md-6">
                                <label for="fecha_inicio_contrato" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-plus text-success me-1"></i>
                                    Fecha de Inicio *
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_inicio_contrato" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ date('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Cuándo inicia a trabajar</small>
                            </div>

                            <!-- Fecha de fin -->
                            <div class="col-md-6">
                                <label for="fecha_fin_contrato" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x text-warning me-1"></i>
                                    Fecha de Finalización *
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_fin_contrato"
                                       required>
                                <small class="text-muted">Cuándo termina el contrato</small>
                            </div>
                        </div>

                        <!-- Vista previa de duración -->
                        <div id="duracionPreview" class="mt-4" style="display: none;">
                            <div class="bg-light rounded p-3">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="text-primary">
                                            <i class="bi bi-hourglass-split fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="duracionTexto">-</div>
                                                <small class="text-muted">Duración Total</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-success">
                                            <i class="bi bi-calendar-check fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="fechaInicioTexto">-</div>
                                                <small class="text-muted">Inicia</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-warning">
                                            <i class="bi bi-calendar-x fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="fechaFinTexto">-</div>
                                                <small class="text-muted">Termina</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Errores de fechas -->
                        <div id="errorFechas" class="alert alert-warning mt-3" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="errorFechasTexto">Por favor verifica las fechas</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen de lo que se creará -->
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            Se creará automáticamente:
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-plus text-primary me-2 fs-5"></i>
                                    <span>Perfil completo del trabajador</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-briefcase text-success me-2 fs-5"></i>
                                    <span>Ficha técnica laboral</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf text-danger me-2 fs-5"></i>
                                    <span>Contrato en PDF</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-check text-info me-2 fs-5"></i>
                                    <span>Estado inicial configurado</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nota importante -->
                        <div class="mt-3">
                            <div class="alert alert-success mb-0">
                                <small>
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>¿Necesitas el contrato?</strong> 
                                    Después de crear el trabajador, podrás descargar el contrato desde su perfil.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer - SOLO DOS BOTONES -->
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                
                <button type="button" class="btn btn-success btn-lg px-4" id="btnCrearTrabajador">
                    <span id="btnTextoNormal">
                        <i class="bi bi-plus-circle me-2"></i>
                        Crear Trabajador Completo
                    </span>
                    <span id="btnTextoCargando" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Creando trabajador...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.card {
    border-radius: 10px;
}

.form-control-lg:focus, .form-select-lg:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#duracionPreview, #estadoPreview {
    animation: slideIn 0.3s ease-in-out;
}

@keyframes slideIn {
    from { 
        opacity: 0; 
        transform: translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

.btn-lg {
    border-radius: 10px;
    font-weight: 600;
}
</style>

<script>
/**
 * ✅ SCRIPT COMPLETAMENTE LIMPIO - SIN función de preview
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principales
    const form = document.getElementById('formTrabajador');
    const modal = new bootstrap.Modal(document.getElementById('modalContrato'));
    const estatusSelect = document.getElementById('estatus');
    const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
    const fechaFinInput = document.getElementById('fecha_fin_contrato');
    const btnCrear = document.getElementById('btnCrearTrabajador');
    
    let tipoCalculado = null;
    
    console.log('✅ Modal de contrato inicializado (SIN PREVIEW)');

    // ========================================
    // 🔵 INTERCEPTAR ENVÍO DEL FORMULARIO
    // ========================================
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                mostrarToast('Por favor completa todos los campos obligatorios', 'warning');
                return;
            }

            // Abrir modal directamente
            modal.show();
        });
    }

    // ========================================
    // 🔵 MANEJAR CAMBIO DE ESTADO
    // ========================================
    if (estatusSelect) {
        estatusSelect.addEventListener('change', function() {
            mostrarVistaEstado();
        });
    }

    function mostrarVistaEstado() {
        const estadoSeleccionado = estatusSelect.value;
        const estadoPreview = document.getElementById('estadoPreview');
        const previewAlert = document.getElementById('estadoPreviewAlert');
        const previewIcon = document.getElementById('estadoPreviewIcon');
        const previewTexto = document.getElementById('estadoPreviewTexto');
        const previewDescripcion = document.getElementById('estadoPreviewDescripcion');
        const errorEstatus = document.getElementById('errorEstatus');
        
        // Limpiar errores
        if (errorEstatus) errorEstatus.style.display = 'none';
        estatusSelect.classList.remove('is-invalid');
        
        if (!estadoSeleccionado) {
            if (estadoPreview) estadoPreview.style.display = 'none';
            return;
        }
        
        let alertClass, iconClass, textoEstado, descripcionEstado;
        
        switch (estadoSeleccionado) {
            case 'activo':
                alertClass = 'alert-success';
                iconClass = 'bi-check-circle-fill text-success';
                textoEstado = 'Trabajador Activo';
                descripcionEstado = 'Operará normalmente desde el primer día con todos los derechos.';
                break;
            case 'prueba':
                alertClass = 'alert-warning';
                iconClass = 'bi-hourglass-split text-warning';
                textoEstado = 'En Período de Prueba';
                descripcionEstado = 'Estará en evaluación durante el período establecido.';
                break;
            default:
                if (estadoPreview) estadoPreview.style.display = 'none';
                return;
        }
        
        // Actualizar vista previa
        if (previewAlert) previewAlert.className = `alert ${alertClass} mb-0`;
        if (previewIcon) previewIcon.className = `${iconClass} me-2 fs-5`;
        if (previewTexto) previewTexto.textContent = textoEstado;
        if (previewDescripcion) previewDescripcion.textContent = descripcionEstado;
        if (estadoPreview) estadoPreview.style.display = 'block';
        
        console.log('✅ Estado seleccionado:', estadoSeleccionado);
    }

    // ========================================
    // 🔵 MANEJAR CAMBIOS DE FECHAS
    // ========================================
    if (fechaInicioInput) {
        fechaInicioInput.addEventListener('change', calcularDuracion);
    }
    
    if (fechaFinInput) {
        fechaFinInput.addEventListener('change', calcularDuracion);
    }

    function calcularDuracion() {
        const fechaInicio = fechaInicioInput?.value;
        const fechaFin = fechaFinInput?.value;
        
        ocultarError();
        
        if (!fechaInicio) {
            ocultarDuracion();
            return;
        }

        // Configurar fecha mínima para fecha fin
        if (fechaFinInput && fechaInicio) {
            const minDate = new Date(fechaInicio);
            minDate.setDate(minDate.getDate() + 1);
            fechaFinInput.min = minDate.toISOString().split('T')[0];
        }

        if (!fechaFin) {
            ocultarDuracion();
            return;
        }

        // Validar fechas
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            mostrarError('La fecha de finalización debe ser posterior a la fecha de inicio');
            ocultarDuracion();
            return;
        }

        // Calcular duración
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        
        let tipoDuracion, duracionTexto;
        
        if (diasTotales > 30) {
            tipoDuracion = 'meses';
            let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
            
            if (fin.getDate() < inicio.getDate()) {
                meses--;
            }
            
            if (meses <= 0 && fin > inicio) {
                meses = 1;
            }
            
            duracionTexto = `${meses} ${meses === 1 ? 'mes' : 'meses'}`;
        } else {
            tipoDuracion = 'dias';
            duracionTexto = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
        }
        
        tipoCalculado = tipoDuracion;
        
        // Mostrar vista previa
        const duracionEl = document.getElementById('duracionTexto');
        const fechaInicioEl = document.getElementById('fechaInicioTexto');
        const fechaFinEl = document.getElementById('fechaFinTexto');
        
        if (duracionEl) duracionEl.textContent = duracionTexto;
        if (fechaInicioEl) fechaInicioEl.textContent = inicio.toLocaleDateString('es-MX');
        if (fechaFinEl) fechaFinEl.textContent = fin.toLocaleDateString('es-MX');
        
        mostrarDuracion();
        
        console.log('✅ Duración calculada:', { diasTotales, tipoDuracion, duracionTexto });
    }

    // ========================================
    // 🔵 CREAR TRABAJADOR (BOTÓN PRINCIPAL)
    // ========================================
    if (btnCrear) {
        btnCrear.addEventListener('click', function() {
            console.log('🚀 Iniciando creación de trabajador...');
            
            const estatus = estatusSelect?.value;
            const fechaInicio = fechaInicioInput?.value;
            const fechaFin = fechaFinInput?.value;
            
            // Validaciones
            if (!validarEstado(estatus)) return;
            if (!validarFechas(fechaInicio, fechaFin)) return;
            if (!tipoCalculado) {
                mostrarError('Error en el cálculo de la duración del contrato');
                return;
            }
            
            // Estado de carga
            mostrarCargando();
            
            try {
                // Agregar campos al formulario
                agregarCampoOculto('estatus', estatus);
                agregarCampoOculto('fecha_inicio_contrato', fechaInicio);
                agregarCampoOculto('fecha_fin_contrato', fechaFin);
                agregarCampoOculto('tipo_duracion', tipoCalculado);
                
                console.log('✅ Campos agregados, enviando formulario:', {
                    estatus,
                    fechaInicio,
                    fechaFin,
                    tipo: tipoCalculado
                });
                
                // Enviar formulario después de un pequeño delay
                setTimeout(() => {
                    form.submit();
                }, 100);
                
            } catch (error) {
                console.error('❌ Error al crear trabajador:', error);
                mostrarError('Error al procesar los datos. Inténtalo de nuevo.');
                ocultarCargando();
            }
        });
    }

    // ========================================
    // 🔧 FUNCIONES AUXILIARES
    // ========================================
    
    function validarEstado(estatus) {
        const errorEstatus = document.getElementById('errorEstatus');
        
        if (!estatus) {
            if (errorEstatus) {
                errorEstatus.textContent = 'Por favor selecciona el estado inicial';
                errorEstatus.style.display = 'block';
            }
            estatusSelect.classList.add('is-invalid');
            estatusSelect.focus();
            return false;
        }
        
        if (!['activo', 'prueba'].includes(estatus)) {
            if (errorEstatus) {
                errorEstatus.textContent = 'Estado no válido';
                errorEstatus.style.display = 'block';
            }
            estatusSelect.classList.add('is-invalid');
            return false;
        }
        
        if (errorEstatus) errorEstatus.style.display = 'none';
        estatusSelect.classList.remove('is-invalid');
        return true;
    }
    
    function validarFechas(fechaInicio, fechaFin) {
        if (!fechaInicio || !fechaFin) {
            mostrarError('Por favor completa ambas fechas del contrato');
            return false;
        }
        
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            mostrarError('La fecha de fin debe ser posterior a la de inicio');
            return false;
        }
        
        ocultarError();
        return true;
    }
    
    function agregarCampoOculto(nombre, valor) {
        // Remover campo existente
        const existente = form.querySelector(`input[name="${nombre}"]`);
        if (existente) {
            existente.value = valor;
            return;
        }
        
        // Crear nuevo campo
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
        
        console.log(`✅ Campo agregado: ${nombre} = ${valor}`);
    }
    
    function mostrarCargando() {
        const textoNormal = document.getElementById('btnTextoNormal');
        const textoCargando = document.getElementById('btnTextoCargando');
        
        if (textoNormal) textoNormal.style.display = 'none';
        if (textoCargando) textoCargando.style.display = 'inline-flex';
        btnCrear.disabled = true;
    }
    
    function ocultarCargando() {
        const textoNormal = document.getElementById('btnTextoNormal');
        const textoCargando = document.getElementById('btnTextoCargando');
        
        if (textoNormal) textoNormal.style.display = 'inline-flex';
        if (textoCargando) textoCargando.style.display = 'none';
        btnCrear.disabled = false;
    }
    
    function mostrarDuracion() {
        const preview = document.getElementById('duracionPreview');
        if (preview) preview.style.display = 'block';
    }
    
    function ocultarDuracion() {
        const preview = document.getElementById('duracionPreview');
        if (preview) preview.style.display = 'none';
    }
    
    function mostrarError(mensaje) {
        const errorDiv = document.getElementById('errorFechas');
        const errorTexto = document.getElementById('errorFechasTexto');
        
        if (errorDiv && errorTexto) {
            errorTexto.textContent = mensaje;
            errorDiv.style.display = 'block';
        }
    }
    
    function ocultarError() {
        const errorDiv = document.getElementById('errorFechas');
        if (errorDiv) errorDiv.style.display = 'none';
    }
    
    function mostrarToast(mensaje, tipo) {
        const alertaExistente = document.querySelector('.toast-alert');
        if (alertaExistente) alertaExistente.remove();

        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show toast-alert position-fixed`;
        alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) alerta.remove();
        }, 5000);
    }

    

});
</script>