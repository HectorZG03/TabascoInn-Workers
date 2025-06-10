<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalContratoLabel">
                    <i class="bi bi-file-earmark-text"></i> Generar Contrato de Trabajo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Resumen del Trabajador -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-person-circle"></i> Resumen del Trabajador
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nombre:</strong> <span id="modal-trabajador-nombre">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha de Nacimiento:</strong> <span id="modal-trabajador-nacimiento">-</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Dirección:</strong> <span id="modal-trabajador-direccion">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ DATOS DEL CONTRATO - SIN campo duración manual -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-range"></i> Datos del Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fecha de Inicio -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_inicio_contrato" class="form-label">
                                    <i class="bi bi-calendar-check"></i> Fecha de Inicio *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_inicio_contrato" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ date('Y-m-d') }}"
                                       required>
                                <div class="form-text">Fecha en que inicia el contrato</div>
                            </div>

                            <!-- Fecha de Fin -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_fin_contrato" class="form-label">
                                    <i class="bi bi-calendar-x"></i> Fecha de Fin *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_fin_contrato"
                                       required>
                                <div class="form-text">Fecha en que termina el contrato</div>
                            </div>

                            <!-- Tipo de Duración -->
                            <div class="col-md-4 mb-3">
                                <label for="tipo_duracion" class="form-label">
                                    <i class="bi bi-clock-history"></i> Tipo de Contrato *
                                </label>
                                <select class="form-select" id="tipo_duracion" required>
                                    <option value="meses" selected>Por Meses</option>
                                    <option value="dias">Por Días</option>
                                </select>
                                <div class="form-text">Tipo de duración del contrato</div>
                            </div>
                        </div>

                        <!-- Preview de Duración Calculada -->
                        <div id="duracionPreview" class="alert alert-info d-none">
                            <i class="bi bi-calculator"></i>
                            <strong>Duración Calculada:</strong> <span id="duracionCalculada">-</span>
                            <br><strong>Desde:</strong> <span id="fechaInicioCalculada">-</span>
                            <strong>Hasta:</strong> <span id="fechaFinCalculada">-</span>
                        </div>

                        <!-- Botón Generar Contrato -->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" id="btnGenerarContrato">
                                <i class="bi bi-file-earmark-pdf"></i> Generar Contrato PDF
                            </button>
                            <div id="loadingSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;" role="status">
                                <span class="visually-hidden">Generando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Contrato Generado -->
                <div id="contratoGeneradoInfo" class="card mb-4" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-check-circle-fill"></i> Contrato Generado Exitosamente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Trabajador:</strong> <span id="contratoTrabajadorNombre">-</span><br>
                                <strong>Fecha de Inicio:</strong> <span id="contratoFechaInicio">-</span><br>
                                <strong>Fecha de Fin:</strong> <span id="contratoFechaFin">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Duración:</strong> <span id="contratoDuracion">-</span><br>
                                <strong>Tipo:</strong> <span id="contratoTipo">-</span><br>
                                <div class="mt-2">
                                    <a href="#" id="btnDescargarPreview" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-download"></i> Descargar Vista Previa
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>¡Perfecto!</strong> El contrato ha sido generado. Puedes descargarlo para revisarlo o proceder a guardar el trabajador.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnGuardarFinal" disabled>
                    <i class="bi bi-save"></i> Guardar Trabajador y Contrato
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * ✅ JAVASCRIPT LIMPIO - SIN validaciones de campo "duracion"
 */
class ContratoModal {
    constructor() {
        this.form = null;
        this.modal = null;
        this.datosFormulario = {};
        this.contratoGenerado = false;
        this.hashContrato = null;
        
        this.init();
    }

    /**
     * Inicializa el modal y sus eventos
     */
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.form = document.getElementById('formTrabajador');
            this.modal = new bootstrap.Modal(document.getElementById('modalContrato'));
            
            if (!this.form) {
                console.error('❌ No se encontró el formulario #formTrabajador');
                return;
            }
            
            this.bindEvents();
            console.log('✅ ContratoModal inicializado correctamente');
        });
    }

    /**
     * Vincula todos los eventos necesarios
     */
    bindEvents() {
        // Interceptar envío del formulario principal
        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Eventos del modal
        document.getElementById('btnGenerarContrato')?.addEventListener('click', () => this.handleGenerarContrato());
        document.getElementById('btnGuardarFinal')?.addEventListener('click', () => this.handleGuardarFinal());
        
        // Validaciones automáticas cuando cambien las fechas
        document.getElementById('fecha_inicio_contrato')?.addEventListener('change', () => this.handleFechaInicioChange());
        document.getElementById('fecha_fin_contrato')?.addEventListener('change', () => this.actualizarCalculosContrato());
        document.getElementById('tipo_duracion')?.addEventListener('change', () => this.actualizarCalculosContrato());
    }

    /**
     * Maneja el cambio de fecha de inicio
     */
    handleFechaInicioChange() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato').value;
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        
        if (fechaInicio && fechaFinInput) {
            // Establecer fecha mínima para fecha fin (día siguiente)
            const minDate = new Date(fechaInicio);
            minDate.setDate(minDate.getDate() + 1);
            fechaFinInput.min = minDate.toISOString().split('T')[0];
            
            // Si la fecha fin actual es menor que la nueva fecha mínima, limpiarla
            if (fechaFinInput.value && fechaFinInput.value <= fechaInicio) {
                fechaFinInput.value = '';
            }
        }
        
        this.actualizarCalculosContrato();
        this.resetearEstadoContrato();
    }

    /**
     * Maneja el envío del formulario principal
     */
    handleFormSubmit(e) {
        e.preventDefault();
        
        // ✅ SIMPLIFICADO: Solo validar formulario básico
        if (!this.form.checkValidity()) {
            this.form.classList.add('was-validated');
            this.mostrarAlerta('Por favor completa todos los campos obligatorios', 'warning');
            return;
        }

        // Recopilar datos y abrir modal
        this.recopilarDatosFormulario();
        this.modal.show();
    }

    /**
     * Recopila todos los datos del formulario - SIN validar duración
     */
    recopilarDatosFormulario() {
        const formData = new FormData(this.form);
        this.datosFormulario = {};
        
        for (let [key, value] of formData.entries()) {
            // ✅ FILTRAR: No incluir campos de duración manual si existen
            if (key !== 'duracion' && key !== 'duracion_meses') {
                this.datosFormulario[key] = value;
            }
        }
        
        this.actualizarPreviewModal();
        console.log('✅ Datos recopilados:', this.datosFormulario);
    }

    /**
     * Actualiza la vista previa en el modal
     */
    actualizarPreviewModal() {
        const nombreCompleto = `${this.datosFormulario.nombre_trabajador} ${this.datosFormulario.ape_pat} ${this.datosFormulario.ape_mat || ''}`.trim();
        
        this.setElementText('modal-trabajador-nombre', nombreCompleto);
        this.setElementText('modal-trabajador-nacimiento', this.formatearFecha(this.datosFormulario.fecha_nacimiento));
        this.setElementText('modal-trabajador-direccion', this.datosFormulario.direccion || 'No especificada');
        
        // Resetear estado del contrato
        this.resetearEstadoContrato();
    }

    /**
     * ✅ SIMPLIFICADO: Maneja la generación del contrato preview - SIN validar duración
     */
    handleGenerarContrato() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const tipoDuracion = document.getElementById('tipo_duracion')?.value;
        
        // ✅ SOLO validar los 3 campos necesarios
        if (!fechaInicio || !fechaFin || !tipoDuracion) {
            this.mostrarAlerta('Por favor completa la fecha de inicio, fecha de fin y tipo de contrato', 'warning');
            return;
        }

        // Validar que fecha fin sea posterior a fecha inicio
        if (new Date(fechaFin) <= new Date(fechaInicio)) {
            this.mostrarAlerta('La fecha de fin debe ser posterior a la fecha de inicio', 'warning');
            return;
        }

        this.generarContratoPreview(fechaInicio, fechaFin, tipoDuracion);
    }

    /**
     * ✅ Genera el contrato preview via AJAX - SIN enviar duración manual
     */
    async generarContratoPreview(fechaInicio, fechaFin, tipoDuracion) {
        const btnGenerar = document.getElementById('btnGenerarContrato');
        const spinner = document.getElementById('loadingSpinner');
        
        try {
            // Mostrar estado de carga
            this.setLoadingState(btnGenerar, spinner, true);
            
            // ✅ LIMPIO: Preparar datos para el preview SIN duracion manual
            const datosPreview = {
                ...this.datosFormulario,
                fecha_inicio_contrato: fechaInicio,
                fecha_fin_contrato: fechaFin,
                tipo_duracion: tipoDuracion,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            };

            // ✅ ELIMINAR cualquier campo de duración manual que pueda existir
            delete datosPreview.duracion;
            delete datosPreview.duracion_meses;

            console.log('✅ Enviando datos limpios:', datosPreview);

            // Realizar petición AJAX
            const response = await fetch('/ajax/contratos/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': datosPreview._token
                },
                body: JSON.stringify(datosPreview)
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarContratoGenerado(result.data);
                this.mostrarAlerta('Contrato generado exitosamente', 'success');
            } else {
                throw new Error(result.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('❌ Error al generar contrato:', error);
            this.mostrarAlerta(`Error al generar contrato: ${error.message}`, 'danger');
            
        } finally {
            // Ocultar estado de carga
            this.setLoadingState(btnGenerar, spinner, false);
        }
    }

    /**
     * Muestra la información del contrato generado
     */
    mostrarContratoGenerado(data) {
        this.contratoGenerado = true;
        this.hashContrato = data.hash;
        
        // Actualizar elementos con los datos del contrato
        this.setElementText('contratoTrabajadorNombre', data.trabajador_nombre);
        this.setElementText('contratoFechaInicio', data.fecha_inicio);
        this.setElementText('contratoFechaFin', data.fecha_fin);
        this.setElementText('contratoDuracion', data.duracion_texto);
        this.setElementText('contratoTipo', data.tipo_duracion === 'dias' ? 'Por Días' : 'Por Meses');
        
        // Configurar enlace de descarga
        const btnDescargar = document.getElementById('btnDescargarPreview');
        if (btnDescargar) {
            btnDescargar.href = data.download_url;
        }
        
        // Mostrar sección de contrato generado y habilitar guardado
        this.showElement('contratoGeneradoInfo');
        this.enableElement('btnGuardarFinal');
    }

    /**
     * ✅ LIMPIO: Maneja el guardado final - SIN enviar duración manual
     */
    handleGuardarFinal() {
        if (!this.contratoGenerado) {
            this.mostrarAlerta('Primero debes generar el contrato', 'warning');
            return;
        }

        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const tipoDuracion = document.getElementById('tipo_duracion')?.value;
        
        // ✅ SOLO agregar los 3 campos necesarios al formulario
        this.addHiddenInput('fecha_inicio_contrato', fechaInicio);
        this.addHiddenInput('fecha_fin_contrato', fechaFin);
        this.addHiddenInput('tipo_duracion', tipoDuracion);
        
        // Mostrar loading y enviar formulario
        const btnGuardar = document.getElementById('btnGuardarFinal');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        }
        
        console.log('✅ Enviando formulario final con datos limpios');
        this.form.submit();
    }

    /**
     * Actualiza los cálculos del contrato
     */
    actualizarCalculosContrato() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const tipoDuracion = document.getElementById('tipo_duracion')?.value;
        
        if (fechaInicio && fechaFin && tipoDuracion) {
            // Validar que las fechas sean lógicas
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            if (fin <= inicio) {
                this.hideElement('duracionPreview');
                return;
            }
            
            // Calcular duración según el tipo
            let duracionCalculada;
            let duracionTexto;
            
            if (tipoDuracion === 'dias') {
                duracionCalculada = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
                duracionTexto = `${duracionCalculada} ${duracionCalculada === 1 ? 'día' : 'días'}`;
            } else {
                // Para meses, calcular diferencia aproximada
                const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
                
                // Ajustar si el día del mes final es menor
                duracionCalculada = meses;
                if (fin.getDate() < inicio.getDate()) {
                    duracionCalculada--;
                }
                
                // Asegurar que sea al menos 1 mes si hay diferencia
                if (duracionCalculada <= 0 && fin > inicio) {
                    duracionCalculada = 1;
                }
                
                duracionTexto = `${duracionCalculada} ${duracionCalculada === 1 ? 'mes' : 'meses'}`;
            }
            
            // Mostrar información calculada
            this.setElementText('duracionCalculada', duracionTexto);
            this.setElementText('fechaInicioCalculada', inicio.toLocaleDateString('es-MX'));
            this.setElementText('fechaFinCalculada', fin.toLocaleDateString('es-MX'));
            this.showElement('duracionPreview');
            
        } else {
            this.hideElement('duracionPreview');
        }
        
        // Resetear estado del contrato si cambian los datos
        if (this.contratoGenerado) {
            this.resetearEstadoContrato();
        }
    }

    /**
     * Resetea el estado del contrato
     */
    resetearEstadoContrato() {
        this.contratoGenerado = false;
        this.hashContrato = null;
        this.hideElement('contratoGeneradoInfo');
        this.disableElement('btnGuardarFinal');
    }

    /**
     * Configura el estado de carga de los elementos
     */
    setLoadingState(button, spinner, loading) {
        if (button) {
            button.disabled = loading;
        }
        if (spinner) {
            spinner.style.display = loading ? 'inline-block' : 'none';
        }
    }

    /**
     * Agrega un campo oculto al formulario
     */
    addHiddenInput(name, value) {
        // ✅ EVITAR duplicados
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

    /**
     * Muestra una alerta temporal
     */
    mostrarAlerta(mensaje, tipo) {
        // Remover alertas existentes
        const alertasExistentes = document.querySelectorAll('#modalContrato .alert-dismissible');
        alertasExistentes.forEach(alerta => alerta.remove());
        
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const modalBody = document.querySelector('#modalContrato .modal-body');
        if (modalBody) {
            modalBody.insertBefore(alerta, modalBody.firstChild);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 5000);
        }
    }

    /**
     * Formatea una fecha para mostrar
     */
    formatearFecha(fecha) {
        if (!fecha) return 'No especificada';
        return new Date(fecha).toLocaleDateString('es-MX');
    }

    // ===== HELPERS DE DOM =====

    setElementText(id, text) {
        const element = document.getElementById(id);
        if (element) element.textContent = text;
    }

    showElement(id) {
        const element = document.getElementById(id);
        if (element) element.style.display = 'block';
    }

    hideElement(id) {
        const element = document.getElementById(id);
        if (element) element.style.display = 'none';
    }

    enableElement(id) {
        const element = document.getElementById(id);
        if (element) element.disabled = false;
    }

    disableElement(id) {
        const element = document.getElementById(id);
        if (element) element.disabled = true;
    }
}

// ✅ Inicializar el modal automáticamente
new ContratoModal();
</script>