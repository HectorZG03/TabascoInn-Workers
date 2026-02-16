{{-- resources/views/trabajadores/modales/crear_contrato.blade.php - CON FORMATO GLOBAL --}}

<div class="modal fade" id="modalCrearContrato" tabindex="-1" aria-labelledby="modalCrearContratoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCrearContratoLabel">
                    <i class="bi bi-file-earmark-plus"></i>
                    Crear Nuevo Contrato modal secc
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formCrearContrato" action="{{ route('trabajadores.contratos.crear', $trabajador) }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Información del trabajador --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Trabajador:</strong> {{ $trabajador->nombre_completo }}<br>
                                <strong>Categoría:</strong> {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'No especificada' }}<br>
                                <strong>Área:</strong> {{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'No especificada' }}
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Tipo de Contrato --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="tipo_contrato" class="form-label">
                                <i class="bi bi-calendar-range"></i>
                                Tipo de Contrato <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_contrato" id="tipo_contrato" class="form-select" required>
                                <option value="">Seleccione el tipo de contrato</option>
                                <option value="determinado">
                                    📅 Por Tiempo Determinado (con fecha de fin)
                                </option>
                                <option value="indeterminado">
                                    ♾️ Por Tiempo Indeterminado (sin fecha de fin)
                                </option>
                            </select>
                            <div class="form-text">
                                <strong>Determinado:</strong> Contratos con fecha específica de inicio y terminación<br>
                                <strong>Indeterminado:</strong> Contratos sin fecha de terminación definida
                            </div>
                        </div>
                    </div>

                    {{-- ✅ FECHAS CON FORMATO GLOBAL --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio_contrato" class="form-label">
                                    <i class="bi bi-calendar-event"></i>
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="fecha_inicio_contrato" 
                                       id="fecha_inicio_contrato"
                                       class="form-control formato-fecha"
                                       placeholder="DD/MM/YYYY"
                                       maxlength="10"
                                       required>
                                <div class="form-text">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Se permiten fechas pasadas, presentes y futuras
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        {{-- ✅ Fecha de fin (SOLO PARA DETERMINADOS) --}}
                        <div class="col-md-6" id="fecha_fin_container" style="display: none;">
                            <div class="mb-3">
                                <label for="fecha_fin_contrato" class="form-label">
                                    <i class="bi bi-calendar-x"></i>
                                    Fecha de Fin <span class="text-danger" id="fecha_fin_required">*</span>
                                </label>
                                <input type="text" 
                                       name="fecha_fin_contrato" 
                                       id="fecha_fin_contrato"
                                       class="form-control formato-fecha"
                                       placeholder="DD/MM/YYYY"
                                       maxlength="10">
                                <div class="form-text">Fecha cuando terminará el contrato</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Información de duración (SOLO PARA DETERMINADOS) --}}
                    <div class="row" id="duracion_container" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock"></i>
                                    Tipo de Duración
                                </label>
                                <div class="form-control bg-light d-flex align-items-center">
                                    <span id="tipo_duracion_texto" class="text-muted">Seleccione el tipo de contrato</span>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-gear"></i>
                                    Se determina automáticamente: > 30 días = meses, ≤ 30 días = días
                                </div>
                                <input type="hidden" name="tipo_duracion" id="tipo_duracion_hidden">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-calculator"></i>
                                    Duración Calculada
                                </label>
                                <div class="form-control bg-light d-flex align-items-center">
                                    <span id="duracion_calculada" class="text-muted">Seleccione el tipo de contrato</span>
                                </div>
                                <div class="form-text">Se calcula automáticamente según las fechas</div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Información especial para contratos indeterminados --}}
                    <div id="indeterminado_info" class="row mb-3" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-success border-success">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-infinity me-3" style="font-size: 2rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">
                                            <i class="bi bi-check-circle"></i> 
                                            Contrato por Tiempo Indeterminado
                                        </h6>
                                        <p class="mb-0">
                                            Este contrato <strong>no tiene fecha de terminación</strong> y continuará vigente 
                                            hasta que sea terminado por alguna de las partes conforme a la ley laboral.
                                        </p>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-info-circle"></i>
                                            Los campos de fecha de fin y duración no son requeridos para este tipo de contrato.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Resumen del Contrato --}}
                    <div id="resumen_contrato" class="row mb-3" style="display: none;">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-check"></i> 
                                        <span id="resumen_titulo">Resumen del Contrato</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Tipo:</small>
                                            <div class="fw-bold" id="resumen_tipo">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Inicio:</small>
                                            <div class="fw-bold" id="resumen_inicio">-</div>
                                        </div>
                                        <div class="col-md-3" id="resumen_fin_col">
                                            <small class="text-muted">Fin:</small>
                                            <div class="fw-bold" id="resumen_fin">-</div>
                                        </div>
                                        <div class="col-md-3" id="resumen_duracion_col">
                                            <small class="text-muted">Duración:</small>
                                            <div class="fw-bold text-success" id="resumen_duracion">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vista previa de información laboral --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-eye"></i>
                                        Vista Previa - Datos Laborales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Sueldo Diario:</small>
                                            <div class="fw-bold text-success">
                                                ${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Horario:</small>
                                            <div class="fw-bold">
                                                {{ $trabajador->fichaTecnica->hora_entrada ?? '08:00' }} - 
                                                {{ $trabajador->fichaTecnica->hora_salida ?? '17:00' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="text-muted">Turno:</small>
                                            <div class="fw-bold">
                                                {{ ucfirst($trabajador->fichaTecnica->turno ?? 'Por definir') }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Horas Semanales:</small>
                                            <div class="fw-bold">
                                                {{ $trabajador->fichaTecnica->horas_semanales ?? 'Por calcular' }}h
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">
                                    <i class="bi bi-chat-text"></i>
                                    Observaciones (Opcional)
                                </label>
                                <textarea name="observaciones" 
                                          id="observaciones"
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Detalles adicionales sobre el contrato, condiciones especiales, etc."></textarea>
                                <div class="form-text">Máximo 500 caracteres</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnCrearContrato">
                        <i class="bi bi-file-earmark-plus"></i>
                        Crear Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT COMPLETAMENTE ACTUALIZADO PARA FORMATO GLOBAL --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalCrearContrato = document.getElementById('modalCrearContrato');
    
    if (modalCrearContrato) {
        console.log('🔧 Inicializando Modal Crear Contrato - Con FormatoGlobal');
        
        // ✅ VERIFICAR QUE FORMATO GLOBAL ESTÉ DISPONIBLE
        if (typeof FormatoGlobal === 'undefined') {
            console.error('❌ FormatoGlobal no está disponible');
            return;
        }
        
        // ✅ ELEMENTOS DEL FORMULARIO
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        const fechaFinContainer = document.getElementById('fecha_fin_container');
        const fechaFinRequired = document.getElementById('fecha_fin_required');
        const duracionContainer = document.getElementById('duracion_container');
        const indeterminadoInfo = document.getElementById('indeterminado_info');
        
        // ✅ ELEMENTOS DE CÁLCULO
        const tipoDuracionTexto = document.getElementById('tipo_duracion_texto');
        const tipoDuracionHidden = document.getElementById('tipo_duracion_hidden');
        const duracionCalculadaSpan = document.getElementById('duracion_calculada');
        
        // ✅ ELEMENTOS DE RESUMEN
        const resumenContrato = document.getElementById('resumen_contrato');
        const resumenTitulo = document.getElementById('resumen_titulo');
        const resumenTipo = document.getElementById('resumen_tipo');
        const resumenInicio = document.getElementById('resumen_inicio');
        const resumenFin = document.getElementById('resumen_fin');
        const resumenFinCol = document.getElementById('resumen_fin_col');
        const resumenDuracion = document.getElementById('resumen_duracion');
        const resumenDuracionCol = document.getElementById('resumen_duracion_col');
        
        // ✅ FORMULARIO Y BOTÓN
        const formCrearContrato = document.getElementById('formCrearContrato');
        const btnCrearContrato = document.getElementById('btnCrearContrato');

        // ✅ FUNCIÓN PRINCIPAL: MANEJAR CAMBIO DE TIPO DE CONTRATO
        tipoContratoSelect.addEventListener('change', function() {
            const tipoContrato = this.value;
            console.log('📋 Tipo de contrato seleccionado:', tipoContrato);
            
            if (tipoContrato === 'indeterminado') {
                configurarParaIndeterminado();
            } else if (tipoContrato === 'determinado') {
                configurarParaDeterminado();
            } else {
                limpiarFormulario();
            }
        });

        // ✅ CONFIGURAR PARA CONTRATO INDETERMINADO
        function configurarParaIndeterminado() {
            console.log('♾️ Configurando para contrato indeterminado');
            
            // Ocultar campos innecesarios
            fechaFinContainer.style.display = 'none';
            duracionContainer.style.display = 'none';
            
            // Mostrar información específica
            indeterminadoInfo.style.display = 'block';
            
            // Limpiar y deshabilitar campos
            fechaFinInput.value = '';
            fechaFinInput.removeAttribute('required');
            tipoDuracionHidden.value = '';
            
            // Limpiar validaciones
            FormatoGlobal.limpiarValidacion(fechaFinInput);
            
            // Mostrar resumen
            mostrarResumenIndeterminado();
        }

        // ✅ CONFIGURAR PARA CONTRATO DETERMINADO
        function configurarParaDeterminado() {
            console.log('📅 Configurando para contrato determinado');
            
            // Mostrar campos necesarios
            fechaFinContainer.style.display = 'block';
            duracionContainer.style.display = 'block';
            
            // Ocultar información de indeterminado
            indeterminadoInfo.style.display = 'none';
            
            // Hacer fecha fin requerida
            fechaFinInput.setAttribute('required', 'required');
            
            // Calcular fecha fin por defecto si no hay fecha
            if (!fechaFinInput.value && fechaInicioInput.value) {
                calcularFechaFinPorDefecto();
            }
            
            // Actualizar cálculos
            setTimeout(() => calcularDuracionConFormatoGlobal(), 100);
        }

        // ✅ LIMPIAR FORMULARIO
        function limpiarFormulario() {
            console.log('🧹 Limpiando formulario');
            
            // Mostrar todos los campos pero deshabilitar validaciones
            fechaFinContainer.style.display = 'block';
            duracionContainer.style.display = 'block';
            indeterminadoInfo.style.display = 'none';
            
            // Limpiar campos
            fechaFinInput.value = '';
            fechaFinInput.removeAttribute('required');
            tipoDuracionHidden.value = '';
            
            // Limpiar validaciones
            FormatoGlobal.limpiarValidacion(fechaInicioInput);
            FormatoGlobal.limpiarValidacion(fechaFinInput);
            
            // Resetear textos
            tipoDuracionTexto.textContent = 'Seleccione el tipo de contrato';
            tipoDuracionTexto.className = 'text-muted';
            duracionCalculadaSpan.textContent = 'Seleccione el tipo de contrato';
            duracionCalculadaSpan.className = 'text-muted';
            
            // Ocultar resumen
            ocultarResumen();
        }

        // ✅ CALCULAR FECHA FIN POR DEFECTO (6 meses después)
        function calcularFechaFinPorDefecto() {
            const fechaInicio = fechaInicioInput.value;
            if (fechaInicio && FormatoGlobal.validarFormatoFecha(fechaInicio) && !fechaFinInput.value) {
                // Usar función de FormatoGlobal para agregar meses
                const fechaFin = FormatoGlobal.agregarMeses(fechaInicio, 6);
                if (fechaFin) {
                    fechaFinInput.value = fechaFin;
                    console.log('📅 Fecha fin calculada por defecto:', fechaFin);
                }
            }
        }

        // ✅ CALCULAR DURACIÓN CON FORMATO GLOBAL
        function calcularDuracionConFormatoGlobal() {
            const tipoContrato = tipoContratoSelect.value;
            
            if (tipoContrato !== 'determinado') return;
            
            const fechaInicio = fechaInicioInput.value;
            const fechaFin = fechaFinInput.value;
            
            if (!fechaInicio || !fechaFin) {
                resetearCalculos('Seleccione ambas fechas');
                return;
            }

            // ✅ VALIDAR FORMATOS CON FORMATO GLOBAL
            if (!FormatoGlobal.validarFormatoFecha(fechaInicio) || !FormatoGlobal.validarFormatoFecha(fechaFin)) {
                resetearCalculos('Formato de fecha inválido', 'danger');
                return;
            }
            
            // ✅ CALCULAR DIFERENCIA DE DÍAS CON FORMATO GLOBAL
            const diasTotales = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
            
            if (diasTotales === null || diasTotales <= 0) {
                resetearCalculos('Fecha fin debe ser posterior al inicio', 'danger');
                return;
            }

            console.log('📊 Días totales calculados:', diasTotales);
            
            let tipoDuracion, duracionMostrar, tipoTexto, colorClass;
            
            if (diasTotales > 30) {
                tipoDuracion = 'meses';
                tipoTexto = '📅 Por meses';
                colorClass = 'text-info fw-bold';
                
                // Calcular meses aproximados
                const meses = Math.ceil(diasTotales / 30);
                duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
            } else {
                tipoDuracion = 'dias';
                tipoTexto = '📋 Por días';
                colorClass = 'text-primary fw-bold';
                duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
            }
            
            // ✅ Actualizar interfaz
            tipoDuracionTexto.textContent = tipoTexto;
            tipoDuracionTexto.className = colorClass;
            duracionCalculadaSpan.textContent = duracionMostrar;
            duracionCalculadaSpan.className = 'text-success fw-bold';
            tipoDuracionHidden.value = tipoDuracion;
            
            console.log('✅ Duración calculada:', { tipoDuracion, duracionMostrar });
            
            // ✅ Mostrar resumen
            mostrarResumenDeterminado(fechaInicio, fechaFin, duracionMostrar);
        }

        // ✅ RESETEAR CÁLCULOS
        function resetearCalculos(mensaje, tipo = 'muted') {
            tipoDuracionTexto.textContent = mensaje;
            tipoDuracionTexto.className = `text-${tipo}`;
            duracionCalculadaSpan.textContent = mensaje;
            duracionCalculadaSpan.className = `text-${tipo}`;
            tipoDuracionHidden.value = '';
            ocultarResumen();
        }

        // ✅ MOSTRAR RESUMEN PARA DETERMINADO
        function mostrarResumenDeterminado(fechaInicio, fechaFin, duracion) {
            resumenTitulo.textContent = '📅 Resumen - Contrato Determinado';
            resumenTipo.textContent = 'Por Tiempo Determinado';
            resumenInicio.textContent = fechaInicio; // Ya está en formato DD/MM/YYYY
            resumenFin.textContent = fechaFin; // Ya está en formato DD/MM/YYYY
            resumenDuracion.textContent = duracion;
            
            // Mostrar todas las columnas
            resumenFinCol.style.display = 'block';
            resumenDuracionCol.style.display = 'block';
            
            resumenContrato.style.display = 'block';
            console.log('✅ Resumen determinado mostrado');
        }

        // ✅ MOSTRAR RESUMEN PARA INDETERMINADO
        function mostrarResumenIndeterminado() {
            resumenTitulo.textContent = '♾️ Resumen - Contrato Indeterminado';
            resumenTipo.textContent = 'Por Tiempo Indeterminado';
            resumenInicio.textContent = fechaInicioInput.value || 'Sin seleccionar';
            
            // Ocultar columnas no aplicables
            resumenFinCol.style.display = 'none';
            resumenDuracionCol.style.display = 'none';
            
            resumenContrato.style.display = 'block';
            console.log('✅ Resumen indeterminado mostrado');
        }

        // ✅ OCULTAR RESUMEN
        function ocultarResumen() {
            if (resumenContrato) {
                resumenContrato.style.display = 'none';
            }
        }

        // ✅ EVENT LISTENERS CON FORMATO GLOBAL
        fechaInicioInput.addEventListener('blur', function() {
            console.log('📅 Fecha inicio validada:', this.value);
            const tipoContrato = tipoContratoSelect.value;
            
            if (tipoContrato === 'determinado') {
                calcularFechaFinPorDefecto();
                setTimeout(() => calcularDuracionConFormatoGlobal(), 100);
            } else if (tipoContrato === 'indeterminado') {
                mostrarResumenIndeterminado();
            }
        });
        
        fechaFinInput.addEventListener('blur', function() {
            console.log('📅 Fecha fin validada:', this.value);
            const tipoContrato = tipoContratoSelect.value;
            
            if (tipoContrato === 'determinado') {
                setTimeout(() => calcularDuracionConFormatoGlobal(), 100);
            }
        });

        // ✅ VALIDACIÓN MEJORADA DEL FORMULARIO CON FORMATO GLOBAL
        formCrearContrato.addEventListener('submit', function(e) {
            console.log('📤 Enviando formulario...');
            
            const tipoContrato = tipoContratoSelect.value;
            
            if (!tipoContrato) {
                e.preventDefault();
                alert('❌ Por favor, seleccione el tipo de contrato');
                tipoContratoSelect.focus();
                return false;
            }
            
            const fechaInicio = fechaInicioInput.value;
            if (!fechaInicio) {
                e.preventDefault();
                alert('❌ Por favor, seleccione la fecha de inicio');
                fechaInicioInput.focus();
                return false;
            }

            // ✅ VALIDAR FORMATO DE FECHA INICIO
            if (!FormatoGlobal.validarFormatoFecha(fechaInicio)) {
                e.preventDefault();
                alert('❌ La fecha de inicio tiene formato inválido. Use DD/MM/YYYY');
                fechaInicioInput.focus();
                return false;
            }
            
            // ✅ VALIDACIONES ESPECÍFICAS PARA DETERMINADO
            if (tipoContrato === 'determinado') {
                const fechaFin = fechaFinInput.value;
                
                if (!fechaFin) {
                    e.preventDefault();
                    alert('❌ Por favor, seleccione la fecha de fin para el contrato determinado');
                    fechaFinInput.focus();
                    return false;
                }

                // ✅ VALIDAR FORMATO DE FECHA FIN
                if (!FormatoGlobal.validarFormatoFecha(fechaFin)) {
                    e.preventDefault();
                    alert('❌ La fecha de fin tiene formato inválido. Use DD/MM/YYYY');
                    fechaFinInput.focus();
                    return false;
                }
                
                // ✅ VALIDAR QUE FECHA FIN SEA POSTERIOR CON FORMATO GLOBAL
                const diferenciaDias = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
                
                if (diferenciaDias === null || diferenciaDias <= 0) {
                    e.preventDefault();
                    alert('❌ La fecha de fin debe ser posterior a la fecha de inicio');
                    fechaFinInput.focus();
                    return false;
                }

                if (diferenciaDias < 1) {
                    e.preventDefault();
                    alert('❌ El contrato debe tener al menos 1 día de duración');
                    return false;
                }
                
                if (!tipoDuracionHidden.value) {
                    e.preventDefault();
                    alert('❌ Error en el cálculo de duración. Verifique las fechas.');
                    return false;
                }
            }

            // ✅ Para indeterminados, limpiar campos que no se usan
            if (tipoContrato === 'indeterminado') {
                fechaFinInput.value = '';
                tipoDuracionHidden.value = '';
                console.log('♾️ Campos limpiados para contrato indeterminado');
            }

            // Deshabilitar botón para evitar doble envío
            btnCrearContrato.disabled = true;
            btnCrearContrato.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando contrato...';
            
            console.log('✅ Formulario válido, enviando...', {
                tipo: tipoContrato,
                fechaInicio: fechaInicio,
                fechaFin: tipoContrato === 'determinado' ? fechaFinInput.value : 'N/A'
            });
            
            // Re-habilitar después de 10 segundos si no se redirige
            setTimeout(() => {
                btnCrearContrato.disabled = false;
                btnCrearContrato.innerHTML = '<i class="bi bi-file-earmark-plus"></i> Crear Contrato';
            }, 10000);
        });

        // ✅ INICIALIZAR AL ABRIR EL MODAL CON FORMATO GLOBAL
        modalCrearContrato.addEventListener('show.bs.modal', function() {
            console.log('🔓 Modal abierto - Inicializando con FormatoGlobal...');
            
            // Resetear completamente
            formCrearContrato.reset();
            limpiarFormulario();
            
            // ✅ ESTABLECER FECHA DE INICIO POR DEFECTO CON FORMATO DD/MM/YYYY
            fechaInicioInput.value = FormatoGlobal.obtenerFechaHoy();
            
            // Enfocar primer campo después de un delay
            setTimeout(() => {
                tipoContratoSelect.focus();
            }, 500);
            
            console.log('✅ Modal inicializado con FormatoGlobal');
        });

        // ✅ LIMPIAR AL CERRAR
        modalCrearContrato.addEventListener('hidden.bs.modal', function() {
            console.log('🔒 Modal cerrado - Limpiando...');
            
            formCrearContrato.reset();
            limpiarFormulario();
            
            // Restaurar botón
            btnCrearContrato.disabled = false;
            btnCrearContrato.innerHTML = '<i class="bi bi-file-earmark-plus"></i> Crear Contrato';
            
            // Restaurar fecha de inicio con formato DD/MM/YYYY
            fechaInicioInput.value = FormatoGlobal.obtenerFechaHoy();
            
            console.log('✅ Modal limpiado correctamente');
        });

        console.log('✅ Modal Crear Contrato inicializado con FormatoGlobal - V3.0');
    } else {
        console.error('❌ No se encontró el modal #modalCrearContrato');
    }
});
</script>