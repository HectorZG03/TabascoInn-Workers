// js/perfil_trabajador/contratos.js - CORREGIDO CON SOPORTE INDETERMINADO
window.initContratos = function() {
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para cargar contratos');
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

            // ✅ INICIALIZAR EVENTOS Y CÁLCULOS DESPUÉS DE CARGAR
            inicializarEventosContratos();
            setTimeout(() => {
                inicializarCalculosContratos();
            }, 200); // Pequeño delay para asegurar que el DOM esté listo
            console.log('✅ Contratos cargados exitosamente');

        } catch (error) {
            console.error('❌ Error cargando contratos:', error);
            mostrarErrorContratos(error.message);
        }
    });

    // ========================================
    // ✅ FUNCIÓN CORREGIDA: INICIALIZAR CÁLCULOS DE CONTRATOS CON SOPORTE INDETERMINADO
    // ========================================
    const inicializarCalculosContratos = () => {
        console.log('🔄 Inicializando cálculos de contratos...');
        
        // ✅ CONFIGURAR CÁLCULOS PARA CREAR CONTRATO
        const tipoContratoCrear = document.getElementById('tipo_contrato');
        const fechaInicioCrear = document.getElementById('fecha_inicio_contrato');
        const fechaFinCrear = document.getElementById('fecha_fin_contrato');
        
        if (tipoContratoCrear && fechaInicioCrear && fechaFinCrear) {
            console.log('✅ Configurando cálculos para crear contrato');
            
            tipoContratoCrear.addEventListener('change', () => {
                setTimeout(() => manejarTipoContrato('crear'), 100);
            });
            fechaInicioCrear.addEventListener('change', () => {
                setTimeout(() => calcularDuracionContrato('crear'), 100);
            });
            fechaFinCrear.addEventListener('change', () => {
                setTimeout(() => calcularDuracionContrato('crear'), 100);
            });
        }

        // ✅ CONFIGURAR CÁLCULOS PARA RENOVAR CONTRATO
        const fechaInicioRenovar = document.getElementById('fecha_inicio_renovar');
        const fechaFinRenovar = document.getElementById('fecha_fin_renovar');
        
        if (fechaInicioRenovar && fechaFinRenovar) {
            console.log('✅ Configurando cálculos para renovar contrato');
            fechaInicioRenovar.addEventListener('change', () => {
                setTimeout(() => calcularDuracionContrato('renovar'), 100);
            });
            fechaFinRenovar.addEventListener('change', () => {
                setTimeout(() => calcularDuracionContrato('renovar'), 100);
            });
        }

        console.log('✅ Cálculos de contratos inicializados');
    };

    // ========================================
    // ✅ NUEVA FUNCIÓN: MANEJAR TIPO DE CONTRATO
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
            // ✅ CONFIGURAR PARA INDETERMINADO
            if (fechaFinContainer) fechaFinContainer.style.display = 'none';
            if (duracionContainer) duracionContainer.style.display = 'none';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'block';
            
            // Limpiar campos de fecha fin
            if (fechaFinInput) {
                fechaFinInput.value = '';
                fechaFinInput.removeAttribute('required');
            }
            
            // Mostrar resumen para indeterminado
            mostrarResumenIndeterminado(tipo);
            
        } else if (tipoContrato === 'determinado') {
            // ✅ CONFIGURAR PARA DETERMINADO
            if (fechaFinContainer) fechaFinContainer.style.display = 'block';
            if (duracionContainer) duracionContainer.style.display = 'block';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'none';
            
            // Hacer fecha fin requerida
            if (fechaFinInput) fechaFinInput.setAttribute('required', 'required');
            
            // Calcular duración
            setTimeout(() => calcularDuracionContrato(tipo), 100);
            
        } else {
            // ✅ NO HAY SELECCIÓN
            if (fechaFinContainer) fechaFinContainer.style.display = 'block';
            if (duracionContainer) duracionContainer.style.display = 'block';
            if (indeterminadoInfo) indeterminadoInfo.style.display = 'none';
            
            ocultarResumen(tipo);
        }
    };

    // ========================================
    // ✅ FUNCIÓN ACTUALIZADA: CALCULAR DURACIÓN DEL CONTRATO CON SOPORTE INDETERMINADO
    // ========================================
    const calcularDuracionContrato = (tipo) => {
        console.log(`🔄 Calculando duración para: ${tipo}`);
        
        // ✅ VERIFICAR TIPO DE CONTRATO PRIMERO
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        if (tipoContratoSelect && tipoContratoSelect.value === 'indeterminado') {
            mostrarResumenIndeterminado(tipo);
            return;
        }
        
        // ✅ CORREGIR IDs SEGÚN EL HTML REAL
        let fechaInicioId, fechaFinId, tipoDuracionElId, duracionElId, tipoDuracionHiddenId;
        
        if (tipo === 'crear') {
            fechaInicioId = 'fecha_inicio_contrato';
            fechaFinId = 'fecha_fin_contrato';
            tipoDuracionElId = 'tipo_duracion_texto';        // ✅ CORREGIDO
            duracionElId = 'duracion_calculada';             // ✅ CORREGIDO
            tipoDuracionHiddenId = 'tipo_duracion_hidden';   // ✅ CORREGIDO
        
        } else {
            fechaInicioId = 'fecha_inicio_renovar';
            fechaFinId = 'fecha_fin_renovar';
            tipoDuracionElId = 'tipo-duracion-renovar';      // ✅ CORREGIDO
            duracionElId = 'duracion-renovar';               // ✅ CORREGIDO
            tipoDuracionHiddenId = 'tipo_duracion_renovar';  // ✅ CORREGIDO
        }
        
        const fechaInicioInput = document.getElementById(fechaInicioId);
        const fechaFinInput = document.getElementById(fechaFinId);
        const tipoDuracionEl = document.getElementById(tipoDuracionElId);
        const duracionEl = document.getElementById(duracionElId);
        const tipoDuracionHidden = document.getElementById(tipoDuracionHiddenId);
        
        // ✅ VALIDAR QUE EXISTAN LOS ELEMENTOS
        if (!fechaInicioInput || !fechaFinInput) {
            console.warn(`⚠️ Elementos de fecha no encontrados para ${tipo}`);
            return;
        }
        
        if (!tipoDuracionEl || !duracionEl) {
            console.warn(`⚠️ Elementos de duración no encontrados para ${tipo}:`, {
                tipoDuracionEl: tipoDuracionElId,
                duracionEl: duracionElId,
                found: {
                    tipoDuracionEl: !!tipoDuracionEl,
                    duracionEl: !!duracionEl
                }
            });
            return;
        }
        
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;
        
        if (!fechaInicio || !fechaFin) {
            tipoDuracionEl.textContent = 'Seleccione las fechas';
            tipoDuracionEl.className = 'text-muted';
            duracionEl.textContent = 'Seleccione las fechas';
            duracionEl.className = 'text-muted';
            if (tipoDuracionHidden) tipoDuracionHidden.value = '';
            ocultarResumen(tipo);
            return;
        }

        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            tipoDuracionEl.textContent = 'Fecha fin debe ser posterior al inicio';
            tipoDuracionEl.className = 'text-danger';
            duracionEl.textContent = 'Fechas inválidas';
            duracionEl.className = 'text-danger';
            if (tipoDuracionHidden) tipoDuracionHidden.value = '';
            ocultarResumen(tipo);
            return;
        }

        // ✅ LÓGICA IGUAL A LA CREACIÓN DE TRABAJADORES
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        
        let tipoDuracion, duracionMostrar, tipoTexto;
        
        if (diasTotales > 30) {
            tipoDuracion = 'meses';
            // Calcular meses exactos
            let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
            
            if (fin.getDate() < inicio.getDate()) {
                meses--;
            }
            
            if (meses <= 0 && fin > inicio) {
                meses = 1;
            }
            
            duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
            tipoTexto = 'Por meses';
        } else {
            tipoDuracion = 'dias';
            duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
            tipoTexto = 'Por días';
        }
        
        // ✅ ACTUALIZAR ELEMENTOS DE LA INTERFAZ
        tipoDuracionEl.textContent = tipoTexto;
        tipoDuracionEl.className = 'text-success fw-bold';
        duracionEl.textContent = duracionMostrar;
        duracionEl.className = 'text-success fw-bold';
        if (tipoDuracionHidden) tipoDuracionHidden.value = tipoDuracion;
        
        // ✅ MOSTRAR RESUMEN
        mostrarResumenDeterminado(tipo, fechaInicio, fechaFin, duracionMostrar);
        
        console.log(`✅ Duración calculada para ${tipo}: ${duracionMostrar} (${tipoTexto})`);
    };

    // ========================================
    // ✅ FUNCIONES AUXILIARES ACTUALIZADAS
    // ========================================
    const mostrarResumenDeterminado = (tipo, fechaInicio, fechaFin, duracion) => {
        // ✅ CORREGIR IDs DE RESUMEN
        let resumenId, inicioId, finId, duracionId, tipoId, finColId, duracionColId;
        
        if (tipo === 'crear') {
            resumenId = 'resumen_contrato';           // ✅ CORREGIDO
            tipoId = 'resumen_tipo';                  // ✅ NUEVO
            inicioId = 'resumen_inicio';              // ✅ CORREGIDO
            finId = 'resumen_fin';                    // ✅ CORREGIDO
            finColId = 'resumen_fin_col';             // ✅ NUEVO
            duracionId = 'resumen_duracion';          // ✅ CORREGIDO
            duracionColId = 'resumen_duracion_col';   // ✅ NUEVO
        } else {
            resumenId = 'resumen-renovacion';         // ✅ CORREGIDO
            inicioId = 'resumen-inicio-renovar';      // ✅ CORREGIDO
            finId = 'resumen-fin-renovar';            // ✅ CORREGIDO
            duracionId = 'resumen-duracion-renovar';  // ✅ CORREGIDO
        }
        
        const resumenEl = document.getElementById(resumenId);
        
        if (resumenEl) {
            const tipoEl = document.getElementById(tipoId);
            const inicioEl = document.getElementById(inicioId);
            const finEl = document.getElementById(finId);
            const finColEl = document.getElementById(finColId);
            const duracionEl = document.getElementById(duracionId);
            const duracionColEl = document.getElementById(duracionColId);
            
            if (tipoEl) tipoEl.textContent = 'Por Tiempo Determinado';
            if (inicioEl) inicioEl.textContent = formatearFecha(fechaInicio);
            if (finEl) finEl.textContent = formatearFecha(fechaFin);
            if (duracionEl) duracionEl.textContent = duracion;
            
            // Mostrar columnas de fin y duración si existen
            if (finColEl) finColEl.style.display = 'block';
            if (duracionColEl) duracionColEl.style.display = 'block';
            
            resumenEl.style.display = 'block';
        } else {
            console.warn(`⚠️ Elemento de resumen no encontrado: ${resumenId}`);
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
                    formatearFecha(fechaInicioInput.value) : '-';
            }
            
            // Ocultar columnas de fin y duración si existen
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

    const formatearFecha = (fecha) => {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    // ========================================
    // 🔄 RESTO DE FUNCIONES EXISTENTES
    // ========================================
    
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

    const inicializarEventosContratos = () => {
        try {
            const modalConfigs = [
                { modalId: 'detalleContratoModal', handler: configurarDetalleModal },
                { modalId: 'modalRenovarContrato', handler: configurarRenovarModal },
                { modalId: 'modalEliminarContrato', handler: configurarEliminarModal },
                { modalId: 'modalCrearContrato', handler: configurarCrearModal }
            ];

            modalConfigs.forEach(config => {
                const modal = document.getElementById(config.modalId);
                if (modal) {
                    modal.addEventListener('show.bs.modal', config.handler);
                }
            });

            inicializarValidacionesContratos();
            console.log('✅ Eventos de contratos inicializados');
        } catch (error) {
            console.error('❌ Error inicializando eventos de contratos:', error);
        }
    };

    // ========================================
    // 📝 CONFIGURADORES DE MODALES ACTUALIZADOS
    // ========================================
    
    const configurarCrearModal = () => {
        try {
            const form = document.querySelector('#modalCrearContrato form');
            if (!form) return;
            
            form.reset();
            const hoy = new Date().toISOString().split('T')[0];
            
            const tipoContratoSelect = form.querySelector('select[name="tipo_contrato"]');
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio_contrato"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin_contrato"]');
            
            if (tipoContratoSelect) tipoContratoSelect.value = '';
            if (fechaInicioInput) {
                fechaInicioInput.min = hoy;
                fechaInicioInput.value = hoy;
            }
            if (fechaFinInput) {
                fechaFinInput.value = '';
                fechaFinInput.removeAttribute('required');
            }

            // ✅ LIMPIAR CÁLCULOS Y CONTENEDORES
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
            
            // Configurar fechas
            const fechaMin = new Date(contratoFin);
            fechaMin.setDate(fechaMin.getDate() + 1);
            const fechaFinDefault = new Date(fechaMin);
            fechaFinDefault.setMonth(fechaFinDefault.getMonth() + 6);
            
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin"]');
            
            if (fechaInicioInput) fechaInicioInput.value = fechaMin.toISOString().split('T')[0];
            if (fechaFinInput) fechaFinInput.value = fechaFinDefault.toISOString().split('T')[0];
            
            const observacionesTextarea = form.querySelector('textarea[name="observaciones_renovacion"]');
            if (observacionesTextarea) observacionesTextarea.value = '';

            // ✅ LIMPIAR Y CALCULAR CON DELAY
            ocultarResumen('renovar');
            setTimeout(() => calcularDuracionContrato('renovar'), 200);
        } catch (error) {
            console.error('Error configurando modal de renovación:', error);
        }
    };

    const configurarEliminarModal = (event) => {
        try {
            const button = event.relatedTarget;
            const form = document.getElementById('formEliminarContrato');
            const contratoId = button.getAttribute('data-contrato-id');
            const contratoInfo = button.getAttribute('data-contrato-info');
            const trabajadorId = window.PerfilUtils.getTrabajadorId();
            
            const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/contratos/${contratoId}/eliminar`);
            form.action = actionUrl;
            
            console.log('🔄 Configurando eliminación, URL:', actionUrl);
            
            const periodoInfo = document.getElementById('contrato-periodo-info');
            if (periodoInfo) periodoInfo.textContent = contratoInfo;
            
            const motivoTextarea = form.querySelector('textarea[name="motivo_eliminacion"]');
            if (motivoTextarea) motivoTextarea.value = '';
        } catch (error) {
            console.error('Error configurando modal de eliminación:', error);
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
    // ✅ VALIDACIONES ACTUALIZADAS CON SOPORTE INDETERMINADO
    // ========================================
    
    const inicializarValidacionesContratos = () => {
        const validationConfigs = [
            { formId: 'formCrearContrato', validator: validarCrearForm },
            { formId: 'formRenovarContrato', validator: validarRenovarForm },
            { formId: 'formEliminarContrato', validator: validarEliminarForm }
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

            // ✅ Solo validar fechas para contratos determinados
            if (tipoContrato === 'determinado') {
                const fechaInicio = new Date(form.querySelector('input[name="fecha_inicio_contrato"]').value);
                const fechaFin = new Date(form.querySelector('input[name="fecha_fin_contrato"]').value);
                const tipoDuracionHidden = form.querySelector('input[name="tipo_duracion"]');
                const tipoDuracion = tipoDuracionHidden ? tipoDuracionHidden.value : null;

                if (!tipoDuracion) {
                    e.preventDefault();
                    alert('Por favor, seleccione fechas válidas para calcular la duración');
                    return false;
                }

                if (fechaFin <= fechaInicio) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser posterior a la fecha de inicio');
                    return false;
                }

                const diferenciaDias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24);
                if (diferenciaDias < 1) {
                    e.preventDefault();
                    alert('El contrato debe tener al menos 1 día de duración');
                    return false;
                }
            }
            // ✅ Para contratos indeterminados, solo validar fecha de inicio
            else if (tipoContrato === 'indeterminado') {
                const fechaInicio = form.querySelector('input[name="fecha_inicio_contrato"]').value;
                if (!fechaInicio) {
                    e.preventDefault();
                    alert('Por favor, seleccione la fecha de inicio del contrato');
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
            const fechaInicio = new Date(form.querySelector('input[name="fecha_inicio"]').value);
            const fechaFin = new Date(form.querySelector('input[name="fecha_fin"]').value);
            const tipoDuracionHidden = form.querySelector('input[name="tipo_duracion"]');
            const tipoDuracion = tipoDuracionHidden ? tipoDuracionHidden.value : null;
            
            if (!tipoDuracion) {
                e.preventDefault();
                alert('Por favor, seleccione fechas válidas para calcular la duración');
                return false;
            }
            
            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
            
            const diferenciaDias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24);
            if (diferenciaDias < 1) {
                e.preventDefault();
                alert('El contrato debe tener al menos 1 día de duración');
                return false;
            }

            console.log('✅ Validación de renovación exitosa');
        } catch (error) {
            console.error('Error validando formulario de renovación:', error);
            e.preventDefault();
        }
    };

    const validarEliminarForm = (e) => {
        try {
            const form = e.target;
            const motivoTextarea = form.querySelector('textarea[name="motivo_eliminacion"]');
            const motivo = motivoTextarea ? motivoTextarea.value.trim() : '';
            
            if (!motivo || motivo.length < 10) {
                e.preventDefault();
                alert('Debe especificar un motivo de al menos 10 caracteres');
                return false;
            }
            
            const confirmed = confirm('⚠️ ¿Está seguro de que desea eliminar permanentemente este contrato?\n\nEsta acción NO se puede deshacer.');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }

            console.log('✅ Validación de eliminación exitosa');
        } catch (error) {
            console.error('Error validando formulario de eliminación:', error);
            e.preventDefault();
        }
    };

    // ========================================
    // 🔄 UTILIDADES ADICIONALES
    // ========================================

    // Función para recargar contratos externamente
    window.recargarContratos = async function() {
        contratosLoaded = false;
        contratosTab.dispatchEvent(new Event('shown.bs.tab'));
    };
    
    console.log('📋 Contratos inicializados con soporte completo para contratos indeterminados');
};