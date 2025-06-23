<!-- ✅ MODAL MEJORADO PARA CONTRATO CON ESTADO -->
<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Header más limpio -->
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="modalContratoLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Finalizar Registro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Mensaje introductorio -->
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-3 fs-4"></i>
                        <div>
                            <h6 class="alert-heading mb-1">¡Ya casi terminamos!</h6>
                            <p class="mb-0">Configura el estado inicial del trabajador y las fechas del contrato laboral.</p>
                        </div>
                    </div>
                </div>

                <!-- ✅ NUEVA SECCIÓN: Estado del Trabajador -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>
                            Estado Inicial del Trabajador
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="estatus" class="form-label fw-semibold">
                                    <i class="bi bi-gear me-1"></i>
                                    Seleccionar Estado Inicial
                                </label>
                                <select class="form-select form-select-lg @error('estatus') is-invalid @enderror" 
                                        id="estatus" 
                                        name="estatus" 
                                        required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="activo" {{ old('estatus', 'activo') == 'activo' ? 'selected' : '' }}>
                                        ✅ Activo - Trabajador operativo completo
                                    </option>
                                    <option value="prueba" {{ old('estatus') == 'prueba' ? 'selected' : '' }}>
                                        🟡 En Prueba - Período de evaluación inicial
                                    </option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <strong>Activo:</strong> El trabajador opera normalmente desde el primer día.<br>
                                        <strong>En Prueba:</strong> Período de evaluación (generalmente 30-90 días).
                                    </small>
                                </div>
                                @error('estatus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Vista previa del estado seleccionado -->
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

                <!-- Configuración del contrato -->
                <div class="card border-0 shadow-sm">
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
                                    Fecha de Inicio
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_inicio_contrato" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ date('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Cuando inicia a trabajar</small>
                            </div>

                            <!-- Fecha de fin -->
                            <div class="col-md-6">
                                <label for="fecha_fin_contrato" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x text-warning me-1"></i>
                                    Fecha de Finalización
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_fin_contrato"
                                       required>
                                <small class="text-muted">Cuando termina el contrato</small>
                            </div>
                        </div>

                        <!-- Vista previa automática de duración -->
                        <div id="duracionPreview" class="mt-4" style="display: none;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="bg-light rounded-3 p-3 text-center">
                                        <div class="row g-3">
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
                            </div>
                        </div>

                        <!-- Mensaje de error de fechas -->
                        <div id="errorFechas" class="alert alert-warning mt-3" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="errorFechasTexto">Por favor verifica las fechas</span>
                        </div>
                    </div>
                </div>

                <!-- Confirmación de acciones -->
                <div class="mt-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-success mb-3">
                                <i class="bi bi-check-circle me-2"></i>
                                Al confirmar se creará:
                            </h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-plus text-primary me-2"></i>
                                        <small>Perfil del trabajador</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-briefcase text-success me-2"></i>
                                        <small>Ficha técnica laboral</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <small>Contrato en PDF</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-check text-info me-2"></i>
                                        <small>Estado inicial</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer simplificado -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-primary" id="btnGenerarPreview">
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    Generar PDF (Opcional)
                </button>

                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-success btn-lg px-4" id="btnCrearTodo">
                    <span id="btnCrearTexto">
                        <i class="bi bi-rocket-takeoff me-2"></i>
                        Crear Trabajador y Contrato
                    </span>
                    <span id="btnCrearLoading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Creando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

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
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    border-radius: 10px;
}

.btn-lg {
    border-radius: 10px;
    font-weight: 600;
}

.form-select-lg option {
    padding: 8px;
}
</style>

<script>
/**
 * ✅ JAVASCRIPT MEJORADO - Con manejo de estado del trabajador
 */
class ContratoModalMejorado {
    constructor() {
        this.form = null;
        this.modal = null;
        this.datosFormulario = {};
        this.tipoCalculado = null;
        
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.form = document.getElementById('formTrabajador');
            this.modal = new bootstrap.Modal(document.getElementById('modalContrato'));
            
            if (!this.form) {
                console.error('❌ No se encontró el formulario #formTrabajador');
                return;
            }
            
            this.bindEvents();
            console.log('✅ ContratoModalMejorado inicializado');
        });
    }

    bindEvents() {
        // Interceptar envío del formulario principal
        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Botón para generar la vista previa
        document.getElementById('btnGenerarPreview')?.addEventListener('click', () => this.generarYDescargarPreview());
        
        // Evento para crear todo de una vez
        document.getElementById('btnCrearTodo')?.addEventListener('click', () => this.handleCrearTodo());
        
        // Eventos para cálculo automático de fechas
        document.getElementById('fecha_inicio_contrato')?.addEventListener('change', () => this.handleFechaChange());
        document.getElementById('fecha_fin_contrato')?.addEventListener('change', () => this.handleFechaChange());
        
        // ✅ NUEVO: Event listener para cambio de estado
        document.getElementById('estatus')?.addEventListener('change', () => this.handleEstadoChange());
        
        // Inicializar vista previa de estado
        this.handleEstadoChange();
    }

    // ✅ NUEVO: Manejar cambio de estado
    handleEstadoChange() {
        const estatusSelect = document.getElementById('estatus');
        const estadoPreview = document.getElementById('estadoPreview');
        const previewAlert = document.getElementById('estadoPreviewAlert');
        const previewIcon = document.getElementById('estadoPreviewIcon');
        const previewTexto = document.getElementById('estadoPreviewTexto');
        const previewDescripcion = document.getElementById('estadoPreviewDescripcion');
        
        if (!estatusSelect) return;
        
        const estadoSeleccionado = estatusSelect.value;
        
        if (!estadoSeleccionado) {
            estadoPreview.style.display = 'none';
            return;
        }
        
        // Configurar vista previa según el estado
        let alertClass, iconClass, textoEstado, descripcionEstado;
        
        switch (estadoSeleccionado) {
            case 'activo':
                alertClass = 'alert-success';
                iconClass = 'bi-check-circle-fill text-success';
                textoEstado = 'Trabajador Activo';
                descripcionEstado = 'El trabajador operará con todos los derechos y responsabilidades desde el primer día.';
                break;
            case 'prueba':
                alertClass = 'alert-warning';
                iconClass = 'bi-hourglass-split text-warning';
                textoEstado = 'En Período de Prueba';
                descripcionEstado = 'El trabajador estará en evaluación durante un período determinado (generalmente 30-90 días).';
                break;
            default:
                estadoPreview.style.display = 'none';
                return;
        }
        
        // Actualizar elementos
        previewAlert.className = `alert ${alertClass} mb-0`;
        previewIcon.className = `${iconClass} me-2 fs-5`;
        previewTexto.textContent = textoEstado;
        previewDescripcion.textContent = descripcionEstado;
        
        // Mostrar vista previa
        estadoPreview.style.display = 'block';
        
        console.log('✅ Estado actualizado:', estadoSeleccionado);
    }

    async generarYDescargarPreview() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const estatus = document.getElementById('estatus')?.value;

        // Validación de campos
        if (!estatus) {
            this.mostrarError('Por favor selecciona el estado inicial del trabajador');
            return;
        }
        
        if (!fechaInicio || !fechaFin) {
            this.mostrarError('Antes de generar, asigna las fechas del contrato');
            return;
        }
        
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            this.mostrarError('Fechas no válidas, la fecha de fin debe ser posterior a la de inicio');
            return;
        }

        // Animación de estado de carga
        const btnPreview = document.getElementById('btnGenerarPreview');
        const originalBtnHTML = btnPreview.innerHTML;
        btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generando...';
        btnPreview.disabled = true;

        try {
            const formData = new FormData(this.form);
            // Datos del Contrato
            formData.append('fecha_inicio_contrato', fechaInicio);
            formData.append('fecha_fin_contrato', fechaFin);
            formData.append('tipo_duracion', this.tipoCalculado || 'meses');
            formData.append('estatus', estatus);

            // Enviar solicitud al servidor
            const response = await fetch('{{ route("ajax.contratos.preview") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error al generar el preview');
            }

            // Descargar el PDF
            window.location.href = data.data.download_url;
            
        } catch (error) {
            console.error('Error generando preview:', error);
            this.mostrarError(error.message || 'Error al generar la vista previa');
        } finally {
            // Restaurar estado del botón
            btnPreview.innerHTML = originalBtnHTML;
            btnPreview.disabled = false;
        }
    }

    handleFormSubmit(e) {
        e.preventDefault();
        
        if (!this.form.checkValidity()) {
            this.form.classList.add('was-validated');
            this.mostrarToast('Por favor completa todos los campos obligatorios', 'warning');
            return;
        }

        // Recopilar datos y abrir modal
        this.recopilarDatosFormulario();
        this.modal.show();
    }

    recopilarDatosFormulario() {
        const formData = new FormData(this.form);
        this.datosFormulario = {};
        
        for (let [key, value] of formData.entries()) {
            this.datosFormulario[key] = value;
        }
        
        console.log('✅ Datos recopilados para el modal');
    }

    handleFechaChange() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        
        // Limpiar errores previos
        this.ocultarError();
        
        if (!fechaInicio) {
            this.ocultarPreview();
            return;
        }

        // Configurar fecha mínima para fecha fin
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        if (fechaFinInput) {
            const minDate = new Date(fechaInicio);
            minDate.setDate(minDate.getDate() + 1);
            fechaFinInput.min = minDate.toISOString().split('T')[0];
        }

        if (!fechaFin) {
            this.ocultarPreview();
            return;
        }

        // Validar fechas
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            this.mostrarError('La fecha de finalización debe ser posterior a la fecha de inicio');
            this.ocultarPreview();
            return;
        }

        // Calcular y mostrar duración
        this.calcularYMostrarDuracion(inicio, fin);
    }

    calcularYMostrarDuracion(inicio, fin) {
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        
        let tipoDuracion;
        let duracionTexto;
        
        // Lógica simple: > 30 días = meses, <= 30 días = días
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
        
        // Guardar tipo calculado
        this.tipoCalculado = tipoDuracion;
        
        // Actualizar vista previa
        this.setElementText('duracionTexto', duracionTexto);
        this.setElementText('fechaInicioTexto', inicio.toLocaleDateString('es-MX'));
        this.setElementText('fechaFinTexto', fin.toLocaleDateString('es-MX'));
        
        this.mostrarPreview();
        
        console.log('✅ Duración calculada:', { diasTotales, tipoDuracion, duracionTexto });
    }

    async handleCrearTodo() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const estatus = document.getElementById('estatus')?.value;
        
        // Validaciones
        if (!estatus) {
            this.mostrarError('Por favor selecciona el estado inicial del trabajador');
            return;
        }
        
        if (!fechaInicio || !fechaFin) {
            this.mostrarError('Por favor completa ambas fechas del contrato');
            return;
        }

        if (!this.tipoCalculado) {
            this.mostrarError('Error en el cálculo de la duración del contrato');
            return;
        }

        // Validar fechas una vez más
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            this.mostrarError('Las fechas del contrato no son válidas');
            return;
        }

        // Mostrar estado de carga
        this.setLoadingState(true);

        try {
            // Agregar datos del contrato y estado al formulario
            this.addHiddenInput('fecha_inicio_contrato', fechaInicio);
            this.addHiddenInput('fecha_fin_contrato', fechaFin);
            this.addHiddenInput('tipo_duracion', this.tipoCalculado);
            this.addHiddenInput('estatus', estatus);
            
            console.log('✅ Enviando formulario con contrato y estado:', {
                fechaInicio,
                fechaFin,
                tipo: this.tipoCalculado,
                estatus
            });

            // Enviar formulario
            this.form.submit();

        } catch (error) {
            console.error('❌ Error al procesar:', error);
            this.mostrarError('Error al procesar los datos. Inténtalo de nuevo.');
            this.setLoadingState(false);
        }
    }

    setLoadingState(loading) {
        const btnTexto = document.getElementById('btnCrearTexto');
        const btnLoading = document.getElementById('btnCrearLoading');
        const btnCrear = document.getElementById('btnCrearTodo');
        
        if (loading) {
            btnTexto.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            btnCrear.disabled = true;
        } else {
            btnTexto.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
            btnCrear.disabled = false;
        }
    }

    mostrarPreview() {
        const preview = document.getElementById('duracionPreview');
        if (preview) {
            preview.style.display = 'block';
        }
    }

    ocultarPreview() {
        const preview = document.getElementById('duracionPreview');
        if (preview) {
            preview.style.display = 'none';
        }
    }

    mostrarError(mensaje) {
        const errorDiv = document.getElementById('errorFechas');
        const errorTexto = document.getElementById('errorFechasTexto');
        
        if (errorDiv && errorTexto) {
            errorTexto.textContent = mensaje;
            errorDiv.style.display = 'block';
        }
    }

    ocultarError() {
        const errorDiv = document.getElementById('errorFechas');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    addHiddenInput(name, value) {
        // Evitar duplicados
        const existingInput = this.form.querySelector(`input[name="${name}"]`);
        if (existingInput && existingInput.type === 'hidden') {
            existingInput.value = value;
            return;
        }
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        this.form.appendChild(input);
    }

    mostrarToast(mensaje, tipo) {
        // Implementación simple de toast/alerta
        const alertaExistente = document.querySelector('.toast-alert');
        if (alertaExistente) {
            alertaExistente.remove();
        }

        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show toast-alert position-fixed`;
        alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }

    // Helpers de DOM
    setElementText(id, text) {
        const element = document.getElementById(id);
        if (element) element.textContent = text;
    }
}

// Inicializar automáticamente
new ContratoModalMejorado();
</script>