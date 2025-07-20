<!-- ✅ SECCIÓN: ESTADO Y CONTRATO DETERMINADO/INDETERMINADO -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-text-fill"></i> Estado y Contrato
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Estado Inicial -->
            <div class="col-md-6 mb-3">
                <label for="estatus" class="form-label">
                    <i class="bi bi-person-check"></i> Estado Inicial del Trabajador *
                </label>
                <select class="form-select @error('estatus') is-invalid @enderror" 
                        id="estatus" 
                        name="estatus" 
                        required>
                    <option value="">Seleccionar estado...</option>
                    <option value="activo" {{ old('estatus') == 'activo' ? 'selected' : '' }}>
                        <i class="bi bi-check-circle"></i> Activo - Operará normalmente
                    </option>
                    <option value="prueba" {{ old('estatus') == 'prueba' ? 'selected' : '' }}>
                        <i class="bi bi-hourglass"></i> Período de Prueba - En evaluación
                    </option>
                </select>
                @error('estatus')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <!-- Vista previa del estado -->
                <div id="estadoPreview" class="mt-2" style="display: none;">
                    <div class="alert mb-0" id="estadoAlert">
                        <div class="d-flex align-items-center">
                            <i id="estadoIcon" class="me-2 fs-5"></i>
                            <div>
                                <strong id="estadoTexto"></strong>
                                <div id="estadoDescripcion" class="small"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ NUEVO: Tipo de Contrato -->
            <div class="col-md-6 mb-3">
                <label for="tipo_contrato" class="form-label">
                    <i class="bi bi-file-earmark-text"></i> Tipo de Contrato *
                </label>
                <select class="form-select @error('tipo_contrato') is-invalid @enderror" 
                        id="tipo_contrato" 
                        name="tipo_contrato" 
                        required>
                    <option value="">Seleccionar tipo...</option>
                    <option value="determinado" {{ old('tipo_contrato', 'determinado') == 'determinado' ? 'selected' : '' }}>
                        <i class="bi bi-calendar-range"></i> Por Tiempo Determinado - Con fecha de fin
                    </option>
                    <option value="indeterminado" {{ old('tipo_contrato') == 'indeterminado' ? 'selected' : '' }}>
                        <i class="bi bi-infinity"></i> Por Tiempo Indeterminado - Sin fecha de fin
                    </option>
                </select>
                @error('tipo_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <!-- Vista previa del tipo de contrato -->
                <div id="tipoContratoPreview" class="mt-2" style="display: none;">
                    <div class="alert mb-0" id="tipoContratoAlert">
                        <div class="d-flex align-items-center">
                            <i id="tipoContratoIcon" class="me-2 fs-5"></i>
                            <div>
                                <strong id="tipoContratoTexto"></strong>
                                <div id="tipoContratoDescripcion" class="small"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Fecha de Inicio del Contrato -->
            <div class="col-md-6 mb-3">
                <label for="fecha_inicio_contrato" class="form-label">
                    <i class="bi bi-calendar-plus"></i> Fecha de Inicio del Contrato *
                </label>
                <input type="text" 
                       class="form-control formato-fecha @error('fecha_inicio_contrato') is-invalid @enderror" 
                       id="fecha_inicio_contrato" 
                       name="fecha_inicio_contrato" 
                       value="{{ old('fecha_inicio_contrato') }}" 
                       placeholder="DD/MM/YYYY"
                       maxlength="10"
                       required>
                <div class="form-text">Formato: DD/MM/YYYY (fecha real de inicio del contrato)</div>
                @error('fecha_inicio_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Fecha de Fin del Contrato (condicional) -->
            <div class="col-md-6 mb-3" id="fechaFinContainer">
                <label for="fecha_fin_contrato" class="form-label">
                    <i class="bi bi-calendar-x"></i> Fecha de Fin del Contrato 
                    <span id="fechaFinRequerida" class="text-danger">*</span>
                    <span id="fechaFinOpcional" class="text-muted" style="display: none;">(No aplica)</span>
                </label>
                <input type="text" 
                       class="form-control formato-fecha @error('fecha_fin_contrato') is-invalid @enderror" 
                       id="fecha_fin_contrato" 
                       name="fecha_fin_contrato" 
                       value="{{ old('fecha_fin_contrato') }}" 
                       placeholder="DD/MM/YYYY"
                       maxlength="10">
                <div class="form-text" id="fechaFinTexto">Formato: DD/MM/YYYY (debe ser posterior al inicio)</div>
                @error('fecha_fin_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <!-- Tipo de Duración (calculado automáticamente para determinados) -->
            <div class="col-md-6 mb-3" id="duracionContainer">
                <label class="form-label">
                    <i class="bi bi-clock-history"></i> Duración del Contrato
                </label>
                <div class="form-control bg-light d-flex align-items-center">
                    <span id="duracionTexto" class="text-muted">Seleccione el tipo y fechas</span>
                </div>
                <div class="form-text" id="duracionAyuda">Se calcula automáticamente para contratos determinados</div>
                <!-- Campo oculto para el tipo de duración -->
                <input type="hidden" id="tipo_duracion" name="tipo_duracion">
            </div>

            <!-- Información adicional -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    <i class="bi bi-info-circle"></i> Información
                </label>
                <div class="form-control bg-light d-flex align-items-center">
                    <span id="infoTexto" class="text-muted">Seleccione el tipo de contrato</span>
                </div>
                <div class="form-text">Información sobre el tipo de contrato seleccionado</div>
            </div>
        </div>

        <!-- Resumen del Contrato -->
        <div id="resumenContrato" class="row mt-3" style="display: none;">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-check"></i> Resumen del Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">Tipo:</small>
                                <div class="fw-bold" id="resumenTipo">-</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Inicio:</small>
                                <div class="fw-bold" id="resumenInicio">-</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Fin:</small>
                                <div class="fw-bold" id="resumenFin">-</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Duración:</small>
                                <div class="fw-bold text-success" id="resumenDuracion">-</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Estado del Trabajador:</small>
                            <div class="fw-bold" id="resumenEstado">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información adicional actualizada -->
        <div class="alert alert-info mt-3 mb-0">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle me-2 mt-1"></i>
                <div>
                    <strong>Información importante:</strong>
                    <ul class="mb-0 mt-1">
                        <li>El contrato se generará automáticamente al crear el trabajador</li>
                        <li><strong>Determinado:</strong> Con fecha de fin específica, duración se calcula automáticamente</li>
                        <li><strong>Indeterminado:</strong> Sin fecha de fin, vigencia indefinida</li>
                        <li>El estado inicial puede cambiarse después desde el perfil del trabajador</li>
                        <li>Use formato DD/MM/YYYY para las fechas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ✅ SCRIPT COMPLETO MEJORADO - Reemplaza todo el <script> existente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando estado y contrato determinado/indeterminado');
    
    // Elementos del formulario
    const estatusSelect = document.getElementById('estatus');
    const tipoContratoSelect = document.getElementById('tipo_contrato');
    const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
    const fechaFinInput = document.getElementById('fecha_fin_contrato');
    const tipoDuracionInput = document.getElementById('tipo_duracion');
    
    // Elementos del UI
    const fechaFinContainer = document.getElementById('fechaFinContainer');
    const fechaFinRequerida = document.getElementById('fechaFinRequerida');
    const fechaFinOpcional = document.getElementById('fechaFinOpcional');
    const fechaFinTexto = document.getElementById('fechaFinTexto');
    const duracionContainer = document.getElementById('duracionContainer');
    const duracionAyuda = document.getElementById('duracionAyuda');
    
    // ✅ Verificar que los elementos existan antes de continuar
    if (!estatusSelect || !tipoContratoSelect || !fechaInicioInput || !fechaFinInput) {
        console.warn('⚠️ Algunos elementos del formulario no se encontraron');
        return;
    }

    // ✅ FUNCIÓN SEGURA PARA ACTUALIZAR VISTA PREVIA
    function actualizarVistaPreviaSafe() {
        try {
            if (typeof window.actualizarVistaPrevia === 'function') {
                window.actualizarVistaPrevia();
            }
        } catch (error) {
            console.warn('⚠️ Error al actualizar vista previa:', error);
        }
    }

    // =================================
    // 📋 EVENT LISTENERS PRINCIPALES
    // =================================
    
    // Manejar cambio de estado
    if (estatusSelect) {
        estatusSelect.addEventListener('change', function() {
            mostrarVistaEstado();
            actualizarResumen();
        });
    }

    // Manejar cambio de tipo de contrato
    if (tipoContratoSelect) {
        tipoContratoSelect.addEventListener('change', function() {
            console.log('🔄 Cambio de tipo de contrato:', this.value);
            mostrarVistaTipoContrato();
            configurarCamposSegunTipo();
            calcularDuracion();
            actualizarResumen();
        });
    }

    // Manejar cambios de fechas
    if (fechaInicioInput) {
        fechaInicioInput.addEventListener('input', function() {
            console.log('🔄 Cambio en fecha inicio:', this.value);
            calcularDuracion();
            actualizarResumen();
        });
    }

    if (fechaFinInput) {
        fechaFinInput.addEventListener('input', function() {
            console.log('🔄 Cambio en fecha fin:', this.value);
            calcularDuracion();
            actualizarResumen();
        });
    }

    // =================================
    // 🎨 FUNCIONES DE VISTA PREVIA
    // =================================

    function mostrarVistaEstado() {
        const estadoSeleccionado = estatusSelect.value;
        const estadoPreview = document.getElementById('estadoPreview');
        const estadoAlert = document.getElementById('estadoAlert');
        const estadoIcon = document.getElementById('estadoIcon');
        const estadoTexto = document.getElementById('estadoTexto');
        const estadoDescripcion = document.getElementById('estadoDescripcion');
        
        // ✅ Verificar que los elementos existan
        if (!estadoPreview || !estadoAlert || !estadoIcon || !estadoTexto || !estadoDescripcion) {
            return;
        }
        
        if (!estadoSeleccionado) {
            estadoPreview.style.display = 'none';
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
                estadoPreview.style.display = 'none';
                return;
        }
        
        estadoAlert.className = `alert ${alertClass} mb-0`;
        estadoIcon.className = `${iconClass} me-2 fs-5`;
        estadoTexto.textContent = textoEstado;
        estadoDescripcion.textContent = descripcionEstado;
        estadoPreview.style.display = 'block';
    }

    function mostrarVistaTipoContrato() {
        const tipoSeleccionado = tipoContratoSelect.value;
        const tipoPreview = document.getElementById('tipoContratoPreview');
        const tipoAlert = document.getElementById('tipoContratoAlert');
        const tipoIcon = document.getElementById('tipoContratoIcon');
        const tipoTexto = document.getElementById('tipoContratoTexto');
        const tipoDescripcion = document.getElementById('tipoContratoDescripcion');
        
        // ✅ Verificar que los elementos existan
        if (!tipoPreview || !tipoAlert || !tipoIcon || !tipoTexto || !tipoDescripcion) {
            return;
        }
        
        if (!tipoSeleccionado) {
            tipoPreview.style.display = 'none';
            return;
        }
        
        let alertClass, iconClass, textoTipo, descripcionTipo;
        
        switch (tipoSeleccionado) {
            case 'determinado':
                alertClass = 'alert-primary';
                iconClass = 'bi-calendar-range-fill text-primary';
                textoTipo = 'Contrato por Tiempo Determinado';
                descripcionTipo = 'Con fecha de inicio y fin específicas. La duración se calcula automáticamente.';
                break;
            case 'indeterminado':
                alertClass = 'alert-success';
                iconClass = 'bi-infinity text-success';
                textoTipo = 'Contrato por Tiempo Indeterminado';
                descripcionTipo = 'Sin fecha de fin. Vigencia indefinida hasta terminación por las partes.';
                break;
            default:
                tipoPreview.style.display = 'none';
                return;
        }
        
        tipoAlert.className = `alert ${alertClass} mb-0`;
        tipoIcon.className = `${iconClass} me-2 fs-5`;
        tipoTexto.textContent = textoTipo;
        tipoDescripcion.textContent = descripcionTipo;
        tipoPreview.style.display = 'block';
    }

    // =================================
    // ⚙️ FUNCIONES DE CONFIGURACIÓN
    // =================================

    function configurarCamposSegunTipo() {
        const tipoSeleccionado = tipoContratoSelect.value;
        console.log('⚙️ Configurando campos para tipo:', tipoSeleccionado);
        
        // ✅ Verificar que los elementos de UI existan
        if (!fechaFinContainer || !fechaFinRequerida || !fechaFinOpcional || !fechaFinTexto || !duracionAyuda) {
            console.warn('⚠️ Algunos elementos de UI no se encontraron');
            return;
        }
        
        if (tipoSeleccionado === 'indeterminado') {
            // ✅ OCULTAR COMPLETAMENTE el contenedor de fecha fin
            fechaFinContainer.style.display = 'none';
            
            // Limpiar y remover atributos
            fechaFinInput.value = '';
            fechaFinInput.removeAttribute('required');
            fechaFinInput.classList.remove('is-invalid', 'is-valid');
            
            // Actualizar textos informativos
            duracionAyuda.textContent = 'No aplica para contratos indeterminados';
            const infoTexto = document.getElementById('infoTexto');
            if (infoTexto) {
                infoTexto.textContent = 'Contrato sin fecha de fin';
                infoTexto.className = 'text-success fw-bold';
            }
            
            console.log('✅ Campo de fecha fin completamente oculto para indeterminado');
            
        } else if (tipoSeleccionado === 'determinado') {
            // ✅ MOSTRAR el contenedor y hacer el campo requerido
            fechaFinContainer.style.display = 'block';
            fechaFinInput.disabled = false;
            fechaFinInput.setAttribute('required', 'required');
            fechaFinInput.style.backgroundColor = '';
            
            // Mostrar indicadores requeridos
            fechaFinRequerida.style.display = 'inline';
            fechaFinOpcional.style.display = 'none';
            fechaFinTexto.textContent = 'Formato: DD/MM/YYYY (debe ser posterior al inicio)';
            fechaFinTexto.className = 'form-text';
            
            duracionAyuda.textContent = 'Se calcula automáticamente para contratos determinados';
            const infoTexto = document.getElementById('infoTexto');
            if (infoTexto) {
                infoTexto.textContent = 'Contrato con fecha de fin específica';
                infoTexto.className = 'text-primary fw-bold';
            }
            
            console.log('✅ Campo de fecha fin visible y requerido para determinado');
            
        } else {
            // ✅ MOSTRAR el campo pero sin required (cuando no hay selección)
            fechaFinContainer.style.display = 'block';
            fechaFinInput.disabled = false;
            fechaFinInput.removeAttribute('required');
            fechaFinInput.style.backgroundColor = '';
            
            fechaFinRequerida.style.display = 'none';
            fechaFinOpcional.style.display = 'inline';
            fechaFinTexto.textContent = 'Depende del tipo de contrato seleccionado';
            fechaFinTexto.className = 'form-text text-muted';
            
            duracionAyuda.textContent = 'Se calcula según el tipo de contrato';
            const infoTexto = document.getElementById('infoTexto');
            if (infoTexto) {
                infoTexto.textContent = 'Seleccione el tipo de contrato';
                infoTexto.className = 'text-muted';
            }
            
            console.log('⚠️ Sin tipo seleccionado - campo visible pero opcional');
        }
    }

    // =================================
    // 🧮 FUNCIONES DE CÁLCULO
    // =================================

    function calcularDuracion() {
        const tipoContrato = tipoContratoSelect.value;
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        const duracionTexto = document.getElementById('duracionTexto');
        
        // ✅ Verificar que el elemento exista
        if (!duracionTexto) return;
        
        console.log('🧮 Calculando duración:', { tipoContrato, fechaInicio, fechaFin });
        
        // Para contratos indeterminados
        if (tipoContrato === 'indeterminado') {
            duracionTexto.textContent = 'Tiempo Indeterminado';
            duracionTexto.className = 'text-success fw-bold';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            console.log('✅ Duración: Indeterminado');
            return;
        }
        
        // Para contratos determinados
        if (!tipoContrato || tipoContrato !== 'determinado') {
            duracionTexto.textContent = 'Seleccione tipo de contrato';
            duracionTexto.className = 'text-muted';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            return;
        }
        
        if (!fechaInicio || !fechaFin) {
            duracionTexto.textContent = 'Seleccione las fechas';
            duracionTexto.className = 'text-muted';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            return;
        }

        // Validar formato
        const formatoFecha = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!formatoFecha.test(fechaInicio) || !formatoFecha.test(fechaFin)) {
            duracionTexto.textContent = 'Formato inválido (DD/MM/YYYY)';
            duracionTexto.className = 'text-danger';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            return;
        }

        // Convertir fechas DD/MM/YYYY a objeto Date
        const [diaIni, mesIni, añoIni] = fechaInicio.split('/');
        const [diaFin, mesFin, añoFin] = fechaFin.split('/');
        
        const inicio = new Date(añoIni, mesIni - 1, diaIni);
        const fin = new Date(añoFin, mesFin - 1, diaFin);
        
        if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
            duracionTexto.textContent = 'Fechas inválidas';
            duracionTexto.className = 'text-danger';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            return;
        }
        
        if (fin <= inicio) {
            duracionTexto.textContent = 'Fecha fin debe ser posterior al inicio';
            duracionTexto.className = 'text-danger';
            if (tipoDuracionInput) tipoDuracionInput.value = '';
            return;
        }

        // Cálculo de duración
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        
        let tipoDuracion, duracionMostrar;
        
        if (diasTotales > 30) {
            tipoDuracion = 'meses';
            let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
            
            if (fin.getDate() < inicio.getDate()) {
                meses--;
            }
            
            if (meses <= 0 && fin > inicio) {
                meses = 1;
            }
            
            duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
        } else {
            tipoDuracion = 'dias';
            duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
        }
        
        duracionTexto.textContent = duracionMostrar;
        duracionTexto.className = 'text-success fw-bold';
        if (tipoDuracionInput) tipoDuracionInput.value = tipoDuracion;
        
        console.log('✅ Duración calculada:', { tipoDuracion, duracionMostrar, diasTotales });
    }

    function actualizarResumen() {
        const resumenContrato = document.getElementById('resumenContrato');
        
        // ✅ Verificar que el elemento exista
        if (!resumenContrato) return;
        
        const estado = estatusSelect.value;
        const tipoContrato = tipoContratoSelect.value;
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        const duracionTexto = document.getElementById('duracionTexto');
        const duracion = duracionTexto ? duracionTexto.textContent : '';
        
        console.log('📋 Actualizando resumen:', { estado, tipoContrato, fechaInicio, fechaFin, duracion });
        
        if (!estado || !tipoContrato || !fechaInicio) {
            resumenContrato.style.display = 'none';
            return;
        }

        // ✅ VALIDACIÓN SIMPLIFICADA: Para determinados, solo revisar si el campo está visible
        if (tipoContrato === 'determinado' && fechaFinContainer && fechaFinContainer.style.display !== 'none') {
            if (!fechaFin || duracion === 'Seleccione las fechas' || duracion.includes('inválido')) {
                resumenContrato.style.display = 'none';
                return;
            }
        }

        // Actualizar resumen
        const resumenTipo = document.getElementById('resumenTipo');
        const resumenInicio = document.getElementById('resumenInicio');
        const resumenFinEl = document.getElementById('resumenFin');
        const resumenDuracionEl = document.getElementById('resumenDuracion');
        const resumenEstado = document.getElementById('resumenEstado');
        
        if (resumenTipo) {
            resumenTipo.textContent = tipoContrato === 'determinado' ? 'Tiempo Determinado' : 'Tiempo Indeterminado';
        }
        
        if (resumenInicio) {
            resumenInicio.textContent = fechaInicio || '-';
        }
        
        // ✅ Para indeterminados, siempre mostrar "Sin fecha de fin"
        if (resumenFinEl) {
            resumenFinEl.textContent = tipoContrato === 'indeterminado' ? 'Sin fecha de fin' : (fechaFin || '-');
        }
        
        if (resumenDuracionEl) {
            resumenDuracionEl.textContent = duracion;
        }
        
        if (resumenEstado) {
            resumenEstado.textContent = estado === 'activo' ? 'Activo' : 'Período de Prueba';
        }
        
        resumenContrato.style.display = 'block';
        console.log('✅ Resumen actualizado y mostrado');
        
        // ✅ Actualizar vista previa principal
        actualizarVistaPreviaSafe();
    }

    // =================================
    // 🚀 INICIALIZACIÓN
    // =================================
    
    // ✅ Configurar estado inicial con delay
    setTimeout(() => {
        configurarCamposSegunTipo();
        mostrarVistaEstado();
        mostrarVistaTipoContrato();
        calcularDuracion();
        actualizarResumen();
        
        // Si hay valores guardados (old), procesarlos
        if (tipoContratoSelect.value) {
            console.log('🔄 Procesando valores guardados...');
            setTimeout(() => {
                configurarCamposSegunTipo();
                calcularDuracion();
                actualizarResumen();
            }, 100);
        }
        
        console.log('✅ Estado y contrato inicializado correctamente');
    }, 150);
});
</script>