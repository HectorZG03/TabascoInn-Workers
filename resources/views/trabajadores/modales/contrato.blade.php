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

                <!-- ✅ DATOS DEL CONTRATO - SIN select de tipo de duración -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-range"></i> Datos del Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fecha de Inicio -->
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin_contrato" class="form-label">
                                    <i class="bi bi-calendar-x"></i> Fecha de Fin *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_fin_contrato"
                                       required>
                                <div class="form-text">Fecha en que termina el contrato</div>
                            </div>
                        </div>

                        <!-- ✅ NUEVO: Preview de Duración Calculada Automáticamente -->
                        <div id="duracionPreview" class="alert alert-info d-none">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <i class="bi bi-calculator text-primary"></i>
                                    <div><strong>Duración Total:</strong></div>
                                    <div class="h5 text-primary mb-0" id="duracionCalculada">-</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-tag text-success"></i>
                                    <div><strong>Tipo:</strong></div>
                                    <div class="h6 text-success mb-0" id="tipoCalculado">-</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-calendar-check text-info"></i>
                                    <div><strong>Inicio:</strong></div>
                                    <div class="small text-info mb-0" id="fechaInicioCalculada">-</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-calendar-x text-warning"></i>
                                    <div><strong>Fin:</strong></div>
                                    <div class="small text-warning mb-0" id="fechaFinCalculada">-</div>
                                </div>
                            </div>
                            
                            <!-- Explicación del cálculo -->
                            <hr class="my-3">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Lógica de cálculo:</strong> 
                                        <span id="logicaCalculo">Selecciona las fechas para ver el cálculo</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Generar Contrato -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success btn-lg" id="btnGenerarContrato">
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
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * ✅ JAVASCRIPT ACTUALIZADO - Cálculo automático de tipo de duración
 */
class ContratoModal {
    constructor() {
        this.form = null;
        this.modal = null;
        this.datosFormulario = {};
        this.contratoGenerado = false;
        this.hashContrato = null;
        this.tipoCalculado = null; // ✅ NUEVO: Almacenar tipo calculado
        
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
        
        // ✅ ACTUALIZADO: Eventos para cálculo automático
        document.getElementById('fecha_inicio_contrato')?.addEventListener('change', () => this.handleFechaInicioChange());
        document.getElementById('fecha_fin_contrato')?.addEventListener('change', () => this.actualizarCalculosContrato());
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
     * Recopila todos los datos del formulario
     */
    recopilarDatosFormulario() {
        const formData = new FormData(this.form);
        this.datosFormulario = {};
        
        for (let [key, value] of formData.entries()) {
            this.datosFormulario[key] = value;
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
     * ✅ ACTUALIZADO: Maneja la generación del contrato preview - Solo valida fechas
     */
    handleGenerarContrato() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        
        // ✅ SOLO validar las 2 fechas necesarias
        if (!fechaInicio || !fechaFin) {
            this.mostrarAlerta('Por favor completa la fecha de inicio y fecha de fin', 'warning');
            return;
        }

        // Validar que fecha fin sea posterior a fecha inicio
        if (new Date(fechaFin) <= new Date(fechaInicio)) {
            this.mostrarAlerta('La fecha de fin debe ser posterior a la fecha de inicio', 'warning');
            return;
        }

        // ✅ NUEVO: Obtener tipo calculado automáticamente
        if (!this.tipoCalculado) {
            this.mostrarAlerta('Error en el cálculo automático del tipo de contrato', 'danger');
            return;
        }

        this.generarContratoPreview(fechaInicio, fechaFin, this.tipoCalculado);
    }

    /**
     * ✅ ACTUALIZADO: Genera el contrato preview con tipo calculado automáticamente
     */
    async generarContratoPreview(fechaInicio, fechaFin, tipoDuracion) {
        const btnGenerar = document.getElementById('btnGenerarContrato');
        const spinner = document.getElementById('loadingSpinner');
        
        try {
            // Mostrar estado de carga
            this.setLoadingState(btnGenerar, spinner, true);
            
            // ✅ ACTUALIZADO: Preparar datos con tipo calculado
            const datosPreview = {
                ...this.datosFormulario,
                fecha_inicio_contrato: fechaInicio,
                fecha_fin_contrato: fechaFin,
                tipo_duracion: tipoDuracion, // ✅ Usar tipo calculado automáticamente
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            };

            console.log('✅ Enviando datos con tipo calculado:', { tipoDuracion, fechaInicio, fechaFin });

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
     * ✅ ACTUALIZADO: Maneja el guardado final con tipo calculado
     */
    handleGuardarFinal() {
        if (!this.contratoGenerado) {
            this.mostrarAlerta('Primero debes generar el contrato', 'warning');
            return;
        }

        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        
        // ✅ ACTUALIZADO: Usar tipo calculado automáticamente
        this.addHiddenInput('fecha_inicio_contrato', fechaInicio);
        this.addHiddenInput('fecha_fin_contrato', fechaFin);
        this.addHiddenInput('tipo_duracion', this.tipoCalculado); // ✅ Tipo calculado
        
        // Mostrar loading y enviar formulario
        const btnGuardar = document.getElementById('btnGuardarFinal');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        }
        
        console.log('✅ Enviando formulario final con tipo calculado:', this.tipoCalculado);
        this.form.submit();
    }

    /**
     * ✅ NUEVO: Actualiza los cálculos del contrato automáticamente
     */
    actualizarCalculosContrato() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        
        if (fechaInicio && fechaFin) {
            // Validar que las fechas sean lógicas
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            if (fin <= inicio) {
                this.hideElement('duracionPreview');
                this.tipoCalculado = null;
                return;
            }
            
            // ✅ LÓGICA DE CÁLCULO AUTOMÁTICO
            const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
            
            let tipoDuracion;
            let duracionCalculada;
            let duracionTexto;
            let logicaTexto;
            
            // ✅ REGLA: Si > 30 días, calcular en meses. Si <= 30 días, calcular en días
            if (diasTotales > 30) {
                tipoDuracion = 'meses';
                
                // Calcular meses de manera más precisa
                let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
                
                // Ajustar si el día del mes final es menor al inicial
                if (fin.getDate() < inicio.getDate()) {
                    meses--;
                }
                
                // Asegurar que sea al menos 1 mes si hay diferencia
                if (meses <= 0 && fin > inicio) {
                    meses = 1;
                }
                
                duracionCalculada = meses;
                duracionTexto = `${meses} ${meses === 1 ? 'mes' : 'meses'}`;
                logicaTexto = `${diasTotales} días > 30 días → Se calcula en <strong>meses</strong>`;
                
            } else {
                tipoDuracion = 'dias';
                duracionCalculada = diasTotales;
                duracionTexto = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
                logicaTexto = `${diasTotales} días ≤ 30 días → Se calcula en <strong>días</strong>`;
            }
            
            // ✅ ALMACENAR el tipo calculado para usar posteriormente
            this.tipoCalculado = tipoDuracion;
            
            // Mostrar información calculada
            this.setElementText('duracionCalculada', duracionTexto);
            this.setElementText('tipoCalculado', tipoDuracion === 'dias' ? 'Por Días' : 'Por Meses');
            this.setElementText('fechaInicioCalculada', inicio.toLocaleDateString('es-MX'));
            this.setElementText('fechaFinCalculada', fin.toLocaleDateString('es-MX'));
            this.setElementHTML('logicaCalculo', logicaTexto);
            this.showElement('duracionPreview');
            
            console.log('✅ Cálculo automático:', { diasTotales, tipoDuracion, duracionCalculada });
            
        } else {
            this.hideElement('duracionPreview');
            this.tipoCalculado = null;
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

    setElementHTML(id, html) {
        const element = document.getElementById(id);
        if (element) element.innerHTML = html;
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