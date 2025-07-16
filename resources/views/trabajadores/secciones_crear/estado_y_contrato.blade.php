<!-- ✅ SECCIÓN: ESTADO Y CONTRATO -->
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
        </div>

        <div class="row">
            <!-- Fecha de Fin del Contrato -->
            <div class="col-md-6 mb-3">
                <label for="fecha_fin_contrato" class="form-label">
                    <i class="bi bi-calendar-x"></i> Fecha de Fin del Contrato *
                </label>
                <input type="text" 
                       class="form-control formato-fecha @error('fecha_fin_contrato') is-invalid @enderror" 
                       id="fecha_fin_contrato" 
                       name="fecha_fin_contrato" 
                       value="{{ old('fecha_fin_contrato') }}" 
                       placeholder="DD/MM/YYYY"
                       maxlength="10"
                       required>
                <div class="form-text">Formato: DD/MM/YYYY (debe ser posterior al inicio)</div>
                @error('fecha_fin_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tipo de Duración (calculado automáticamente) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    <i class="bi bi-clock-history"></i> Duración del Contrato
                </label>
                <div class="form-control bg-light d-flex align-items-center">
                    <span id="duracionTexto" class="text-muted">Seleccione las fechas</span>
                </div>
                <div class="form-text">Se calcula automáticamente</div>
                <!-- Campo oculto para el tipo de duración -->
                <input type="hidden" id="tipo_duracion" name="tipo_duracion">
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
                            <div class="col-md-4">
                                <small class="text-muted">Inicio:</small>
                                <div class="fw-bold" id="resumenInicio">-</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Fin:</small>
                                <div class="fw-bold" id="resumenFin">-</div>
                            </div>
                            <div class="col-md-4">
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

        <!-- Información adicional -->
        <div class="alert alert-info mt-3 mb-0">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle me-2 mt-1"></i>
                <div>
                    <strong>Información importante:</strong>
                    <ul class="mb-0 mt-1">
                        <li>El contrato se generará automáticamente al crear el trabajador</li>
                        <li>La duración se calcula automáticamente (días si es menor a 30 días, meses si es mayor)</li>
                        <li>El estado inicial puede cambiarse después desde el perfil del trabajador</li>
                        <li>Use formato DD/MM/YYYY para las fechas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT ESPECÍFICO PARA ESTADO Y CONTRATO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estatusSelect = document.getElementById('estatus');
    const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
    const fechaFinInput = document.getElementById('fecha_fin_contrato');
    const tipoDuracionInput = document.getElementById('tipo_duracion');
    
    // Manejar cambio de estado
    estatusSelect.addEventListener('change', function() {
        mostrarVistaEstado();
        actualizarResumen();
    });

    // Manejar cambios de fechas
    fechaInicioInput.addEventListener('input', function() {
        calcularDuracion();
        actualizarResumen();
    });

    fechaFinInput.addEventListener('input', function() {
        calcularDuracion();
        actualizarResumen();
    });

    function mostrarVistaEstado() {
        const estadoSeleccionado = estatusSelect.value;
        const estadoPreview = document.getElementById('estadoPreview');
        const estadoAlert = document.getElementById('estadoAlert');
        const estadoIcon = document.getElementById('estadoIcon');
        const estadoTexto = document.getElementById('estadoTexto');
        const estadoDescripcion = document.getElementById('estadoDescripcion');
        
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

    function calcularDuracion() {
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        const duracionTexto = document.getElementById('duracionTexto');
        
        if (!fechaInicio || !fechaFin) {
            duracionTexto.textContent = 'Seleccione las fechas';
            duracionTexto.className = 'text-muted';
            tipoDuracionInput.value = '';
            return;
        }

        // Validar formato
        const formatoFecha = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!formatoFecha.test(fechaInicio) || !formatoFecha.test(fechaFin)) {
            duracionTexto.textContent = 'Formato inválido (DD/MM/YYYY)';
            duracionTexto.className = 'text-danger';
            tipoDuracionInput.value = '';
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
            tipoDuracionInput.value = '';
            return;
        }
        
        if (fin <= inicio) {
            duracionTexto.textContent = 'Fecha fin debe ser posterior al inicio';
            duracionTexto.className = 'text-danger';
            tipoDuracionInput.value = '';
            return;
        }

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
        tipoDuracionInput.value = tipoDuracion;
    }

    function actualizarResumen() {
        const resumenContrato = document.getElementById('resumenContrato');
        const estado = estatusSelect.value;
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        const duracion = document.getElementById('duracionTexto').textContent;
        
        if (!estado || !fechaInicio || !fechaFin || duracion === 'Seleccione las fechas') {
            resumenContrato.style.display = 'none';
            return;
        }

        // Actualizar resumen
        document.getElementById('resumenInicio').textContent = fechaInicio || '-';
        document.getElementById('resumenFin').textContent = fechaFin || '-';
        document.getElementById('resumenDuracion').textContent = duracion;
        document.getElementById('resumenEstado').textContent = estado === 'activo' ? 
            'Activo' : 'Período de Prueba';
        
        resumenContrato.style.display = 'block';
    }
});
</script>