// js/perfil_trabajador/contratos.js - SIN FUNCIÓN DE ELIMINACIÓN
window.initContratos = function() {
    // ✅ VERIFICAR DEPENDENCIAS
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para cargar contratos');
        return;
    }

    if (typeof FormatoGlobal === 'undefined') {
        console.error('❌ FormatoGlobal no está disponible para contratos');
        return;
    }

    const contratosTab = document.getElementById('nav-contratos-tab');
    const contratosContent = document.getElementById('contratos-content');
    
    if (!contratosTab || !contratosContent) {
        console.warn('⚠️ Elementos de contratos no encontrados');
        return;
    }

    let contratosLoaded = false;

    // ✅ CARGAR CONTRATOS AL ACTIVAR LA PESTAÑA
    contratosTab.addEventListener('shown.bs.tab', async function(event) {
        if (contratosLoaded) return;

        const trabajadorId = window.PerfilUtils.getTrabajadorId();
        if (!trabajadorId) {
            console.error('❌ No se pudo obtener el ID del trabajador');
            mostrarErrorContratos('ID de trabajador no encontrado');
            return;
        }

        try {
            const url = AppRoutes.url(`trabajadores/${trabajadorId}/contratos`);
            console.log('🔄 Cargando contratos desde:', url);

            const html = await window.PerfilUtils.fetchHTML(url);
            contratosContent.innerHTML = html;
            contratosLoaded = true;

            // ✅ INICIALIZAR EVENTOS DESPUÉS DE CARGAR
            inicializarEventosContratos();
            setTimeout(() => {
                inicializarCalculosContratos();
            }, 500);
            console.log('✅ Contratos cargados exitosamente');

        } catch (error) {
            console.error('❌ Error cargando contratos:', error);
            mostrarErrorContratos(error.message);
        }
    });

    // ========================================
    // ✅ FUNCIÓN: INICIALIZAR CÁLCULOS CON FORMATO GLOBAL
    // ========================================
    const inicializarCalculosContratos = () => {
        console.log('🔄 Inicializando cálculos de contratos con FormatoGlobal...');
        
        // ✅ ASEGURAR QUE LOS NUEVOS CAMPOS TENGAN FORMATO GLOBAL
        setTimeout(() => {
            const camposFechaNuevos = document.querySelectorAll('#contratos-content .formato-fecha');
            camposFechaNuevos.forEach(campo => {
                if (!campo.hasAttribute('data-formato-inicializado')) {
                    FormatoGlobal.configurarCampoFecha(campo);
                    campo.setAttribute('data-formato-inicializado', 'true');
                }
            });
        }, 100);
        
        // ✅ CONFIGURAR CÁLCULOS PARA CREAR CONTRATO
        const tipoContratoCrear = document.getElementById('tipo_contrato');
        const fechaInicioCrear = document.getElementById('fecha_inicio_contrato');
        const fechaFinCrear = document.getElementById('fecha_fin_contrato');
        
        if (tipoContratoCrear && fechaInicioCrear && fechaFinCrear) {
            console.log('✅ Configurando cálculos para crear contrato');
            
            tipoContratoCrear.addEventListener('change', () => {
                setTimeout(() => manejarTipoContrato('crear'), 100);
            });
            fechaInicioCrear.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionContrato('crear'), 100);
            });
            fechaFinCrear.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionContrato('crear'), 100);
            });
        }

        // ✅ CONFIGURAR CÁLCULOS PARA RENOVAR CONTRATO
        const fechaInicioRenovar = document.getElementById('fecha_inicio_renovar');
        const fechaFinRenovar = document.getElementById('fecha_fin_renovar');
        
        if (fechaInicioRenovar && fechaFinRenovar) {
            console.log('✅ Configurando cálculos para renovar contrato');
            fechaInicioRenovar.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionContrato('renovar'), 100);
            });
            fechaFinRenovar.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionContrato('renovar'), 100);
            });
        }

        console.log('✅ Cálculos de contratos inicializados con FormatoGlobal');
    };

    // ========================================
    // ✅ MANEJAR TIPO DE CONTRATO
    // ========================================
    const manejarTipoContrato = (tipo) => {
        console.log(`🔄 Manejando tipo de contrato para: ${tipo}`);
        
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        const fechaFinContainer = document.getElementById('fecha_fin_container');
        const duracionContainer = document.getElementById('duracion_container');
        const indeterminadoInfo = document.getElementById('indeterminado_info');
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        
        if (!tipoContratoSelect) return;
        
        const tipoContrato = tipoContratoSelect.value;
        
        if (tipoContrato === 'indeterminado') {
            if (fechaFinContainer) fechaFinContainer.style.display = 'none';
            if (duracionContainer) duracionContainer.style.display = 'none';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'block';
            
            if (fechaFinInput) {
                fechaFinInput.value = '';
                fechaFinInput.removeAttribute('required');
                FormatoGlobal.limpiarValidacion(fechaFinInput);
            }
            
            mostrarResumenIndeterminado(tipo);
            
        } else if (tipoContrato === 'determinado') {
            if (fechaFinContainer) fechaFinContainer.style.display = 'block';
            if (duracionContainer) duracionContainer.style.display = 'block';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'none';
            
            if (fechaFinInput) fechaFinInput.setAttribute('required', 'required');
            
            setTimeout(() => calcularDuracionContrato(tipo), 100);
            
        } else {
            if (fechaFinContainer) fechaFinContainer.style.display = 'block';
            if (duracionContainer) duracionContainer.style.display = 'block';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'none';
            
            ocultarResumen(tipo);
        }
    };

    // ========================================
    // ✅ CALCULAR DURACIÓN CON FORMATO GLOBAL
    // ========================================
    const calcularDuracionContrato = (tipo) => {
        console.log(`🔄 Calculando duración para: ${tipo} con FormatoGlobal`);
        
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        if (tipoContratoSelect && tipoContratoSelect.value === 'indeterminado') {
            mostrarResumenIndeterminado(tipo);
            return;
        }
        
        let fechaInicioId, fechaFinId, tipoDuracionElId, duracionElId, tipoDuracionHiddenId;
        
        if (tipo === 'crear') {
            fechaInicioId = 'fecha_inicio_contrato';
            fechaFinId = 'fecha_fin_contrato';
            tipoDuracionElId = 'tipo_duracion_texto';
            duracionElId = 'duracion_calculada';
            tipoDuracionHiddenId = 'tipo_duracion_hidden';
        } else {
            fechaInicioId = 'fecha_inicio_renovar';
            fechaFinId = 'fecha_fin_renovar';
            tipoDuracionElId = 'tipo-duracion-renovar';
            duracionElId = 'duracion-renovar';
            tipoDuracionHiddenId = 'tipo_duracion_renovar';
        }
        
        const fechaInicioInput = document.getElementById(fechaInicioId);
        const fechaFinInput = document.getElementById(fechaFinId);
        const tipoDuracionEl = document.getElementById(tipoDuracionElId);
        const duracionEl = document.getElementById(duracionElId);
        const tipoDuracionHidden = document.getElementById(tipoDuracionHiddenId);
        
        if (!fechaInicioInput || !fechaFinInput || !tipoDuracionEl || !duracionEl) {
            console.warn(`⚠️ Elementos no encontrados para ${tipo}`);
            return;
        }
        
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        
        if (!fechaInicio || !fechaFin) {
            resetearCalculos(tipoDuracionEl, duracionEl, tipoDuracionHidden, 'Seleccione las fechas');
            ocultarResumen(tipo);
            return;
        }

        if (!FormatoGlobal.validarFormatoFecha(fechaInicio) || !FormatoGlobal.validarFormatoFecha(fechaFin)) {
            resetearCalculos(tipoDuracionEl, duracionEl, tipoDuracionHidden, 'Formato de fecha inválido', 'danger');
            ocultarResumen(tipo);
            return;
        }

        const diasTotales = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
        
        if (diasTotales === null || diasTotales <= 0) {
            resetearCalculos(tipoDuracionEl, duracionEl, tipoDuracionHidden, 'Fecha fin debe ser posterior al inicio', 'danger');
            ocultarResumen(tipo);
            return;
        }

        let tipoDuracion, duracionMostrar, tipoTexto;
        
        if (diasTotales > 30) {
            tipoDuracion = 'meses';
            const fechaInicioObj = FormatoGlobal.convertirFechaADate(fechaInicio);
            const fechaFinObj = FormatoGlobal.convertirFechaADate(fechaFin);
            
            let meses = (fechaFinObj.getFullYear() - fechaInicioObj.getFullYear()) * 12 + 
                       (fechaFinObj.getMonth() - fechaInicioObj.getMonth());
            
            if (fechaFinObj.getDate() < fechaInicioObj.getDate()) {
                meses--;
            }
            
            if (meses <= 0 && fechaFinObj > fechaInicioObj) {
                meses = 1;
            }
            
            duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
            tipoTexto = 'Por meses';
        } else {
            tipoDuracion = 'dias';
            duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
            tipoTexto = 'Por días';
        }
        
        tipoDuracionEl.textContent = tipoTexto;
        tipoDuracionEl.className = 'text-success fw-bold';
        duracionEl.textContent = duracionMostrar;
        duracionEl.className = 'text-success fw-bold';
        if (tipoDuracionHidden) tipoDuracionHidden.value = tipoDuracion;
        
        mostrarResumenDeterminado(tipo, fechaInicio, fechaFin, duracionMostrar);
        
        console.log(`✅ Duración calculada para ${tipo}: ${duracionMostrar} (${tipoTexto})`);
    };

    // ========================================
    // ✅ FUNCIONES AUXILIARES
    // ========================================
    const resetearCalculos = (tipoDuracionEl, duracionEl, tipoDuracionHidden, mensaje, tipo = 'muted') => {
        tipoDuracionEl.textContent = mensaje;
        tipoDuracionEl.className = `text-${tipo}`;
        duracionEl.textContent = mensaje;
        duracionEl.className = `text-${tipo}`;
        if (tipoDuracionHidden) tipoDuracionHidden.value = '';
    };

    const mostrarResumenDeterminado = (tipo, fechaInicio, fechaFin, duracion) => {
        const resumenId = tipo === 'crear' ? 'resumen_contrato' : 'resumen-renovacion';
        const tipoId = tipo === 'crear' ? 'resumen_tipo' : null;
        const inicioId = tipo === 'crear' ? 'resumen_inicio' : 'resumen-inicio-renovar';
        const finId = tipo === 'crear' ? 'resumen_fin' : 'resumen-fin-renovar';
        const finColId = tipo === 'crear' ? 'resumen_fin_col' : null;
        const duracionId = tipo === 'crear' ? 'resumen_duracion' : 'resumen-duracion-renovar';
        const duracionColId = tipo === 'crear' ? 'resumen_duracion_col' : null;
        
        const resumenEl = document.getElementById(resumenId);
        
        if (resumenEl) {
            const tipoEl = document.getElementById(tipoId);
            const inicioEl = document.getElementById(inicioId);
            const finEl = document.getElementById(finId);
            const finColEl = document.getElementById(finColId);
            const duracionEl = document.getElementById(duracionId);
            const duracionColEl = document.getElementById(duracionColId);
            
            if (tipoEl) tipoEl.textContent = 'Por Tiempo Determinado';
            if (inicioEl) inicioEl.textContent = fechaInicio;
            if (finEl) finEl.textContent = fechaFin;
            if (duracionEl) duracionEl.textContent = duracion;
            
            if (finColEl) finColEl.style.display = 'block';
            if (duracionColEl) duracionColEl.style.display = 'block';
            
            resumenEl.style.display = 'block';
        }
    };

    const mostrarResumenIndeterminado = (tipo) => {
        const resumenId = tipo === 'crear' ? 'resumen_contrato' : 'resumen-renovacion';
        const tipoId = tipo === 'crear' ? 'resumen_tipo' : null;
        const inicioId = tipo === 'crear' ? 'resumen_inicio' : 'resumen-inicio-renovar';
        const finColId = tipo === 'crear' ? 'resumen_fin_col' : null;
        const duracionColId = tipo === 'crear' ? 'resumen_duracion_col' : null;
        const fechaInicioInput = document.getElementById(tipo === 'crear' ? 'fecha_inicio_contrato' : 'fecha_inicio_renovar');
        
        const resumenEl = document.getElementById(resumenId);
        
        if (resumenEl) {
            const tipoEl = document.getElementById(tipoId);
            const inicioEl = document.getElementById(inicioId);
            const finColEl = document.getElementById(finColId);
            const duracionColEl = document.getElementById(duracionColId);
            
            if (tipoEl) tipoEl.textContent = 'Por Tiempo Indeterminado';
            if (inicioEl) {
                inicioEl.textContent = fechaInicioInput && fechaInicioInput.value ? 
                    fechaInicioInput.value : '-';
            }
            
            if (finColEl) finColEl.style.display = 'none';
            if (duracionColEl) duracionColEl.style.display = 'none';
            
            resumenEl.style.display = 'block';
        }
    };

    const ocultarResumen = (tipo) => {
        const resumenId = tipo === 'crear' ? 'resumen_contrato' : 'resumen-renovacion';
        const resumenEl = document.getElementById(resumenId);
        if (resumenEl) {
            resumenEl.style.display = 'none';
        }
    };

    const mostrarErrorContratos = (errorMessage) => {
        contratosContent.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-danger">Error al cargar contratos</h5>
                <div class="alert alert-danger d-inline-block">
                    <strong>Error:</strong> ${errorMessage}
                </div>
                <div class="mt-3">
                    <button class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Recargar Página
                    </button>
                    <button class="btn btn-outline-secondary ms-2" onclick="window.initContratos()">
                        <i class="bi bi-arrow-repeat"></i> Reintentar Carga
                    </button>
                </div>
            </div>
        `;
    };

    // ✅ EVENTOS SIMPLIFICADOS (sin eliminación)
    const inicializarEventosContratos = () => {
        try {
            const modalConfigs = [
                { modalId: 'detalleContratoModal', handler: configurarDetalleModal },
                { modalId: 'modalRenovarContrato', handler: configurarRenovarModal },
                { modalId: 'modalCrearContrato', handler: configurarCrearModal }
            ];

            modalConfigs.forEach(config => {
                const modal = document.getElementById(config.modalId);
                if (modal) {
                    modal.addEventListener('show.bs.modal', config.handler);
                }
            });

            inicializarValidacionesContratos();
            console.log('✅ Eventos de contratos inicializados (eliminación manejada por servidor)');
        } catch (error) {
            console.error('❌ Error inicializando eventos de contratos:', error);
        }
    };

    // ========================================
    // 📝 CONFIGURADORES DE MODALES
    // ========================================
    
    const configurarCrearModal = () => {
        try {
            const form = document.querySelector('#modalCrearContrato form');
            if (!form) return;
            
            form.reset();
            
            const tipoContratoSelect = form.querySelector('select[name="tipo_contrato"]');
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio_contrato"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin_contrato"]');
            
            if (tipoContratoSelect) tipoContratoSelect.value = '';
            
            if (fechaInicioInput) {
                fechaInicioInput.value = FormatoGlobal.obtenerFechaHoy();
            }
            if (fechaFinInput) {
                fechaFinInput.value = '';
                fechaFinInput.removeAttribute('required');
            }

            const fechaFinContainer = document.getElementById('fecha_fin_container');
            const duracionContainer = document.getElementById('duracion_container');
            const indeterminadoInfo = document.getElementById('indeterminado_info');
            
            if (fechaFinContainer) fechaFinContainer.style.display = 'block';
            if (duracionContainer) duracionContainer.style.display = 'block';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'none';
            
            ocultarResumen('crear');
        } catch (error) {
            console.error('Error configurando modal de creación:', error);
        }
    };

   const configurarRenovarModal = (event) => {
    try {
        const button = event.relatedTarget;
        const form = document.getElementById('formRenovarContrato');
        const contratoId = button.getAttribute('data-contrato-id');
        const contratoFin = button.getAttribute('data-contrato-fin');
        const trabajadorId = window.PerfilUtils.getTrabajadorId();
        
        const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/contratos/${contratoId}/renovar`);
        form.action = actionUrl;
        
        console.log('🔄 Configurando renovación, URL:', actionUrl);
        
        // ✅ LIMPIAR FORMULARIO
        form.reset();
        
        // ✅ CONFIGURAR FECHAS POR DEFECTO
        const fechaFinCarbon = new Date(contratoFin);
        const fechaMin = new Date(fechaFinCarbon);
        fechaMin.setDate(fechaMin.getDate() + 1);
        const fechaFinDefault = new Date(fechaMin);
        fechaFinDefault.setMonth(fechaFinDefault.getMonth() + 6);
        
        const fechaInicioFormato = FormatoGlobal.convertirFechaDeISO(fechaMin.toISOString().split('T')[0]);
        const fechaFinFormato = FormatoGlobal.convertirFechaDeISO(fechaFinDefault.toISOString().split('T')[0]);
        
        const fechaInicioInput = form.querySelector('input[name="fecha_inicio"]');
        const fechaFinInput = form.querySelector('input[name="fecha_fin"]');
        const tipoContratoSelect = form.querySelector('select[name="tipo_contrato_renovacion"]');
        
        if (fechaInicioInput) {
            fechaInicioInput.value = fechaInicioFormato;
            FormatoGlobal.configurarCampoFecha(fechaInicioInput);
        }
        if (fechaFinInput) {
            fechaFinInput.value = fechaFinFormato;
            FormatoGlobal.configurarCampoFecha(fechaFinInput);
        }
        
        // ✅ CONFIGURAR EVENTOS PARA TIPO DE CONTRATO
        if (tipoContratoSelect) {
            tipoContratoSelect.value = 'determinado'; // Por defecto determinado
            
            tipoContratoSelect.addEventListener('change', function() {
                manejarTipoContratoRenovar(this.value);
            });
        }
        
        // ✅ CONFIGURAR EVENTOS PARA FECHAS
        if (fechaInicioInput) {
            fechaInicioInput.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionRenovar(), 100);
            });
        }
        if (fechaFinInput) {
            fechaFinInput.addEventListener('blur', () => {
                setTimeout(() => calcularDuracionRenovar(), 100);
            });
        }
        
        // ✅ CONFIGURAR ESTADO INICIAL
        manejarTipoContratoRenovar('determinado');
        setTimeout(() => calcularDuracionRenovar(), 500);
        
    } catch (error) {
        console.error('Error configurando modal de renovación:', error);
    }
};

// ✅ NUEVA FUNCIÓN: Manejar tipo de contrato en renovación
const manejarTipoContratoRenovar = (tipoContrato) => {
    const fechaFinContainer = document.getElementById('fecha_fin_renovar_container');
    const fechaFinRequerida = document.getElementById('fecha_fin_requerida_renovar');
    const fechaFinOpcional = document.getElementById('fecha_fin_opcional_renovar');
    const fechaFinTexto = document.getElementById('fecha_fin_texto_renovar');
    const fechaFinInput = document.getElementById('fecha_fin_renovar');
    const duracionContainer = document.getElementById('duracion_renovar_container');
    const alertaIndeterminado = document.getElementById('alerta-indeterminado-renovar');
    const alertaCambioStatus = document.getElementById('alerta-cambio-status-renovar');
    
    if (tipoContrato === 'indeterminado') {
        // ✅ OCULTAR CAMPOS DE FECHA FIN Y DURACIÓN
        if (fechaFinContainer) fechaFinContainer.style.display = 'none';
        if (duracionContainer) duracionContainer.style.display = 'none';
        if (alertaIndeterminado) alertaIndeterminado.style.display = 'block';
        if (alertaCambioStatus) alertaCambioStatus.style.display = 'block';
        
        if (fechaFinInput) {
            fechaFinInput.value = '';
            fechaFinInput.removeAttribute('required');
        }
        
        // ✅ MOSTRAR RESUMEN PARA INDETERMINADO
        mostrarResumenIndeterminadoRenovar();
        
    } else if (tipoContrato === 'determinado') {
        // ✅ MOSTRAR CAMPOS DE FECHA FIN Y DURACIÓN
        if (fechaFinContainer) fechaFinContainer.style.display = 'block';
        if (duracionContainer) duracionContainer.style.display = 'block';
        if (alertaIndeterminado) alertaIndeterminado.style.display = 'none';
        if (alertaCambioStatus) alertaCambioStatus.style.display = 'none';
        
        if (fechaFinRequerida) fechaFinRequerida.style.display = 'inline';
        if (fechaFinOpcional) fechaFinOpcional.style.display = 'none';
        if (fechaFinTexto) fechaFinTexto.textContent = 'Formato: DD/MM/YYYY';
        
        if (fechaFinInput) {
            fechaFinInput.setAttribute('required', 'required');
        }
        
        // ✅ CALCULAR DURACIÓN
        setTimeout(() => calcularDuracionRenovar(), 100);
        
    } else {
        // ✅ SIN SELECCIÓN
        if (fechaFinContainer) fechaFinContainer.style.display = 'block';
        if (duracionContainer) duracionContainer.style.display = 'block';
        if (alertaIndeterminado) alertaIndeterminado.style.display = 'none';
        if (alertaCambioStatus) alertaCambioStatus.style.display = 'none';
        
        ocultarResumenRenovar();
    }
};

// ✅ NUEVA FUNCIÓN: Calcular duración para renovar
const calcularDuracionRenovar = () => {
    const tipoContratoSelect = document.getElementById('tipo_contrato_renovacion');
    const fechaInicioInput = document.getElementById('fecha_inicio_renovar');
    const fechaFinInput = document.getElementById('fecha_fin_renovar');
    const tipoDuracionEl = document.getElementById('tipo-duracion-renovar');
    const duracionEl = document.getElementById('duracion-renovar');
    const tipoDuracionHidden = document.getElementById('tipo_duracion_renovar');
    
    if (!tipoContratoSelect || !fechaInicioInput || !tipoDuracionEl || !duracionEl) return;
    
    const tipoContrato = tipoContratoSelect.value;
    
    if (tipoContrato === 'indeterminado') {
        mostrarResumenIndeterminadoRenovar();
        return;
    }
    
    if (tipoContrato !== 'determinado') {
        tipoDuracionEl.textContent = 'Seleccione tipo de contrato';
        tipoDuracionEl.className = 'text-muted';
        duracionEl.textContent = 'Seleccione tipo de contrato';
        duracionEl.className = 'text-muted';
        if (tipoDuracionHidden) tipoDuracionHidden.value = '';
        ocultarResumenRenovar();
        return;
    }
    
    const fechaInicio = fechaInicioInput.value;
    const fechaFin = fechaFinInput ? fechaFinInput.value : '';
    
    if (!fechaInicio || !fechaFin) {
        tipoDuracionEl.textContent = 'Seleccione las fechas';
        tipoDuracionEl.className = 'text-muted';
        duracionEl.textContent = 'Seleccione las fechas';
        duracionEl.className = 'text-muted';
        if (tipoDuracionHidden) tipoDuracionHidden.value = '';
        ocultarResumenRenovar();
        return;
    }

    if (!FormatoGlobal.validarFormatoFecha(fechaInicio) || !FormatoGlobal.validarFormatoFecha(fechaFin)) {
        tipoDuracionEl.textContent = 'Formato de fecha inválido';
        tipoDuracionEl.className = 'text-danger';
        duracionEl.textContent = 'Formato de fecha inválido';
        duracionEl.className = 'text-danger';
        if (tipoDuracionHidden) tipoDuracionHidden.value = '';
        ocultarResumenRenovar();
        return;
    }

    const diasTotales = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
    
    if (diasTotales === null || diasTotales <= 0) {
        tipoDuracionEl.textContent = 'Fecha fin debe ser posterior al inicio';
        tipoDuracionEl.className = 'text-danger';
        duracionEl.textContent = 'Fecha fin debe ser posterior al inicio';
        duracionEl.className = 'text-danger';
        if (tipoDuracionHidden) tipoDuracionHidden.value = '';
        ocultarResumenRenovar();
        return;
    }

    let tipoDuracion, duracionMostrar, tipoTexto;
    
    if (diasTotales > 30) {
        tipoDuracion = 'meses';
        const fechaInicioObj = FormatoGlobal.convertirFechaADate(fechaInicio);
        const fechaFinObj = FormatoGlobal.convertirFechaADate(fechaFin);
        
        let meses = (fechaFinObj.getFullYear() - fechaInicioObj.getFullYear()) * 12 + 
                   (fechaFinObj.getMonth() - fechaInicioObj.getMonth());
        
        if (fechaFinObj.getDate() < fechaInicioObj.getDate()) {
            meses--;
        }
        
        if (meses <= 0 && fechaFinObj > fechaInicioObj) {
            meses = 1;
        }
        
        duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
        tipoTexto = 'Por meses';
    } else {
        tipoDuracion = 'dias';
        duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
        tipoTexto = 'Por días';
    }
    
    tipoDuracionEl.textContent = tipoTexto;
    tipoDuracionEl.className = 'text-success fw-bold';
    duracionEl.textContent = duracionMostrar;
    duracionEl.className = 'text-success fw-bold';
    if (tipoDuracionHidden) tipoDuracionHidden.value = tipoDuracion;
    
    mostrarResumenDeterminadoRenovar(fechaInicio, fechaFin, duracionMostrar);
};

// ✅ NUEVAS FUNCIONES DE RESUMEN
const mostrarResumenDeterminadoRenovar = (fechaInicio, fechaFin, duracion) => {
    const resumenEl = document.getElementById('resumen-renovacion');
    const tipoEl = document.getElementById('resumen-tipo-renovar');
    const inicioEl = document.getElementById('resumen-inicio-renovar');
    const finEl = document.getElementById('resumen-fin-renovar');
    const finColEl = document.getElementById('resumen-fin-col-renovar');
    const duracionEl = document.getElementById('resumen-duracion-renovar');
    const duracionRowEl = document.getElementById('resumen-duracion-row-renovar');
    const statusRowEl = document.getElementById('resumen-status-row-renovar');
    
    if (resumenEl) {
        if (tipoEl) tipoEl.textContent = 'Por Tiempo Determinado';
        if (inicioEl) inicioEl.textContent = fechaInicio;
        if (finEl) finEl.textContent = fechaFin;
        if (duracionEl) duracionEl.textContent = duracion;
        
        if (finColEl) finColEl.style.display = 'block';
        if (duracionRowEl) duracionRowEl.style.display = 'block';
        if (statusRowEl) statusRowEl.style.display = 'none';
        
        resumenEl.style.display = 'block';
    }
};

const mostrarResumenIndeterminadoRenovar = () => {
    const resumenEl = document.getElementById('resumen-renovacion');
    const tipoEl = document.getElementById('resumen-tipo-renovar');
    const inicioEl = document.getElementById('resumen-inicio-renovar');
    const finColEl = document.getElementById('resumen-fin-col-renovar');
    const duracionRowEl = document.getElementById('resumen-duracion-row-renovar');
    const statusRowEl = document.getElementById('resumen-status-row-renovar');
    const fechaInicioInput = document.getElementById('fecha_inicio_renovar');
    
    if (resumenEl) {
        if (tipoEl) tipoEl.textContent = 'Por Tiempo Indeterminado';
        if (inicioEl) {
            inicioEl.textContent = fechaInicioInput && fechaInicioInput.value ? 
                fechaInicioInput.value : '-';
        }
        
        if (finColEl) finColEl.style.display = 'none';
        if (duracionRowEl) duracionRowEl.style.display = 'none';
        if (statusRowEl) statusRowEl.style.display = 'block';
        
        resumenEl.style.display = 'block';
    }
};

const ocultarResumenRenovar = () => {
    const resumenEl = document.getElementById('resumen-renovacion');
    if (resumenEl) {
        resumenEl.style.display = 'none';
    }
};

    const configurarDetalleModal = (event) => {
        try {
            const contratoData = JSON.parse(event.relatedTarget.getAttribute('data-contrato'));
            const contenido = document.getElementById('detalle-contenido');
            
            const html = `
                <div class="row">
                    <div class="col-6"><strong>ID:</strong><br>#${contratoData.id}</div>
                    <div class="col-6"><strong>Estado:</strong><br>
                        <span class="badge bg-${contratoData.estado === 'expirado' ? 'danger' : 
                                               contratoData.estado === 'activo' ? 'success' : 'info'}">
                            ${contratoData.texto_estado}
                        </span>
                    </div>
                </div>
                <hr>
                ${contratoData.es_renovacion ? `<div><strong>Renovación de:</strong> #${contratoData.contrato_anterior_id}</div><hr>` : ''}
                <div class="row">
                    <div class="col-6"><strong>Tipo:</strong><br>${contratoData.tipo_contrato === 'indeterminado' ? 'Por Tiempo Indeterminado' : 'Por Tiempo Determinado'}</div>
                    <div class="col-6"><strong>Inicio:</strong><br>${contratoData.inicio}</div>
                </div>
                ${contratoData.tipo_contrato !== 'indeterminado' ? `
                <div class="row mt-2">
                    <div class="col-6"><strong>Fin:</strong><br>${contratoData.fin}</div>
                    <div class="col-6"><strong>Duración:</strong><br>${contratoData.duracion}</div>
                </div>
                ` : '<div class="alert alert-info mt-2"><i class="bi bi-infinity"></i> Este contrato no tiene fecha de terminación</div>'}
                ${contratoData.observaciones ? `<hr><div><strong>Observaciones:</strong><br><small>${contratoData.observaciones}</small></div>` : ''}
            `;
            
            contenido.innerHTML = html;
        } catch (error) {
            console.error('Error configurando modal de detalles:', error);
        }
    };

    // ========================================
    // ✅ VALIDACIONES
    // ========================================
    
    const inicializarValidacionesContratos = () => {
        const validationConfigs = [
            { formId: 'formCrearContrato', validator: validarCrearForm },
            { formId: 'formRenovarContrato', validator: validarRenovarForm }
        ];

        validationConfigs.forEach(config => {
            const form = document.getElementById(config.formId);
            if (form) {
                form.addEventListener('submit', config.validator);
            }
        });
    };

    const validarCrearForm = (e) => {
        try {
            const form = e.target;
            const tipoContrato = form.querySelector('select[name="tipo_contrato"]').value;

            if (!tipoContrato) {
                e.preventDefault();
                alert('Por favor, seleccione el tipo de contrato');
                return false;
            }

            const fechaInicio = form.querySelector('input[name="fecha_inicio_contrato"]').value;
            
            if (!fechaInicio || !FormatoGlobal.validarFormatoFecha(fechaInicio)) {
                e.preventDefault();
                alert('Por favor, ingrese una fecha de inicio válida (DD/MM/YYYY)');
                return false;
            }

            if (tipoContrato === 'determinado') {
                const fechaFin = form.querySelector('input[name="fecha_fin_contrato"]').value;
                const tipoDuracionHidden = form.querySelector('input[name="tipo_duracion"]');
                const tipoDuracion = tipoDuracionHidden ? tipoDuracionHidden.value : null;

                if (!fechaFin || !FormatoGlobal.validarFormatoFecha(fechaFin)) {
                    e.preventDefault();
                    alert('Por favor, ingrese una fecha de fin válida (DD/MM/YYYY)');
                    return false;
                }

                if (!tipoDuracion) {
                    e.preventDefault();
                    alert('Por favor, seleccione fechas válidas para calcular la duración');
                    return false;
                }

                const diferenciaDias = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
                if (diferenciaDias === null || diferenciaDias < 1) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser posterior a la fecha de inicio y el contrato debe tener al menos 1 día de duración');
                    return false;
                }
            }

            console.log('✅ Validación de creación exitosa');
        } catch (error) {
            console.error('Error validando formulario de creación:', error);
            e.preventDefault();
        }
    };

    const validarRenovarForm = (e) => {
    try {
        const form = e.target;
        const tipoContratoSelect = form.querySelector('select[name="tipo_contrato_renovacion"]');
        const tipoContrato = tipoContratoSelect ? tipoContratoSelect.value : '';
        const fechaInicio = form.querySelector('input[name="fecha_inicio"]').value;
        
        // ✅ VALIDAR TIPO DE CONTRATO
        if (!tipoContrato) {
            e.preventDefault();
            alert('Por favor, seleccione el tipo de contrato');
            return false;
        }
        
        // ✅ VALIDAR FECHA DE INICIO (siempre requerida)
        if (!fechaInicio || !FormatoGlobal.validarFormatoFecha(fechaInicio)) {
            e.preventDefault();
            alert('Por favor, ingrese una fecha de inicio válida (DD/MM/YYYY)');
            return false;
        }

        // ✅ VALIDACIÓN CONDICIONAL PARA CONTRATOS DETERMINADOS
        if (tipoContrato === 'determinado') {
            const fechaFin = form.querySelector('input[name="fecha_fin"]').value;
            const tipoDuracionHidden = form.querySelector('input[name="tipo_duracion"]');
            const tipoDuracion = tipoDuracionHidden ? tipoDuracionHidden.value : null;
            
            if (!fechaFin || !FormatoGlobal.validarFormatoFecha(fechaFin)) {
                e.preventDefault();
                alert('Por favor, ingrese una fecha de fin válida (DD/MM/YYYY) para contratos determinados');
                return false;
            }
            
            if (!tipoDuracion) {
                e.preventDefault();
                alert('Por favor, seleccione fechas válidas para calcular la duración');
                return false;
            }
            
            const diferenciaDias = FormatoGlobal.calcularDiferenciaDias(fechaInicio, fechaFin);
            if (diferenciaDias === null || diferenciaDias < 1) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio y el contrato debe tener al menos 1 día de duración');
                return false;
            }
        }
        // ✅ PARA CONTRATOS INDETERMINADOS: Solo validar fecha de inicio (ya validada arriba)

        console.log('✅ Validación de renovación exitosa para contrato', tipoContrato);
        
        } catch (error) {
            console.error('Error validando formulario de renovación:', error);
            e.preventDefault();
        }
    };

    // ========================================
    // 🔄 UTILIDADES
    // ========================================

    window.recargarContratos = async function() {
        contratosLoaded = false;
        contratosTab.dispatchEvent(new Event('shown.bs.tab'));
    };
    
    console.log('📋 Contratos inicializados - Eliminación manejada completamente por el servidor');
};