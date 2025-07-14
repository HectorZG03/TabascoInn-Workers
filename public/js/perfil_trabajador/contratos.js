// ========================================
// 📄 GESTIÓN DE CONTRATOS - RUTAS DINÁMICAS CORREGIDAS
// ========================================

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
        if (contratosLoaded) return; // No recargar si ya están cargados

        const trabajadorId = window.PerfilUtils.getTrabajadorId();
        if (!trabajadorId) {
            console.error('❌ No se pudo obtener el ID del trabajador');
            mostrarErrorContratos('ID de trabajador no encontrado');
            return;
        }

        try {
            // ✅ CORREGIDO: Construcción correcta de la URL
            const url = AppRoutes.url(`trabajadores/${trabajadorId}/contratos`);
            console.log('🔄 Cargando contratos desde:', url);

            const html = await window.PerfilUtils.fetchHTML(url);
            contratosContent.innerHTML = html;
            contratosLoaded = true;

            // ✅ INICIALIZAR EVENTOS DE CONTRATOS DESPUÉS DE CARGAR
            inicializarEventosContratos();
            console.log('✅ Contratos cargados exitosamente');

        } catch (error) {
            console.error('❌ Error cargando contratos:', error);
            mostrarErrorContratos(error.message);
        }
    });

    console.log('📄 Contratos inicializados con rutas dinámicas');

    // ========================================
    // 🔄 FUNCIÓN DE CARGA DE CONTRATOS
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

    // ========================================
    // 🎛️ EVENTOS DE CONTRATOS
    // ========================================
    
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
    // 📝 CONFIGURADORES DE MODALES CON RUTAS DINÁMICAS
    // ========================================
    
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
                    <div class="col-6"><strong>Inicio:</strong><br>${contratoData.inicio}</div>
                    <div class="col-6"><strong>Fin:</strong><br>${contratoData.fin}</div>
                </div>
                <hr>
                <div><strong>Duración:</strong><br>${contratoData.duracion}</div>
                ${contratoData.observaciones ? `<hr><div><strong>Observaciones:</strong><br><small>${contratoData.observaciones}</small></div>` : ''}
            `;
            
            contenido.innerHTML = html;
        } catch (error) {
            console.error('Error configurando modal de detalles:', error);
        }
    };

    const configurarRenovarModal = (event) => {
        try {
            const button = event.relatedTarget;
            const form = document.getElementById('formRenovarContrato');
            const contratoId = button.getAttribute('data-contrato-id');
            const contratoFin = button.getAttribute('data-contrato-fin');
            const trabajadorId = window.PerfilUtils.getTrabajadorId();
            
            // ✅ CORREGIDO: Usar AppRoutes.url() directamente
            const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/contratos/${contratoId}/renovar`);
            form.action = actionUrl;
            
            console.log('🔄 Configurando renovación, URL:', actionUrl);
            
            // Configurar fechas
            const fechaMin = new Date(contratoFin);
            fechaMin.setDate(fechaMin.getDate() + 1);
            const fechaFinDefault = new Date(fechaMin);
            fechaFinDefault.setMonth(fechaFinDefault.getMonth() + 6);
            
            form.querySelector('input[name="fecha_inicio"]').value = fechaMin.toISOString().split('T')[0];
            form.querySelector('input[name="fecha_fin"]').value = fechaFinDefault.toISOString().split('T')[0];
            form.querySelector('textarea[name="observaciones_renovacion"]').value = '';
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
            
            // ✅ CORREGIDO: Usar AppRoutes.url() directamente
            const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/contratos/${contratoId}/eliminar`);
            form.action = actionUrl;
            
            console.log('🔄 Configurando eliminación, URL:', actionUrl);
            
            document.getElementById('contrato-periodo-info').textContent = contratoInfo;
            form.querySelector('textarea[name="motivo_eliminacion"]').value = '';
        } catch (error) {
            console.error('Error configurando modal de eliminación:', error);
        }
    };

    const configurarCrearModal = () => {
        try {
            const form = document.querySelector('#modalCrearContrato form');
            if (!form) return;
            
            form.reset();
            const hoy = new Date().toISOString().split('T')[0];
            const fechaDefault = new Date();
            fechaDefault.setMonth(fechaDefault.getMonth() + 6);
            
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio_contrato"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin_contrato"]');
            
            if (fechaInicioInput) {
                fechaInicioInput.min = hoy;
                fechaInicioInput.value = hoy;
            }
            if (fechaFinInput) {
                fechaFinInput.value = fechaDefault.toISOString().split('T')[0];
            }
        } catch (error) {
            console.error('Error configurando modal de creación:', error);
        }
    };

    // ========================================
    // ✅ VALIDACIONES DE CONTRATOS
    // ========================================
    
    const inicializarValidacionesContratos = () => {
        const validationConfigs = [
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

    const validarRenovarForm = (e) => {
        try {
            const form = e.target;
            const fechaInicio = new Date(form.querySelector('input[name="fecha_inicio"]').value);
            const fechaFin = new Date(form.querySelector('input[name="fecha_fin"]').value);
            
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
            const motivo = form.querySelector('textarea[name="motivo_eliminacion"]').value.trim();
            
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
    
    console.log('📋 Contratos inicializados con rutas dinámicas corregidas');
};