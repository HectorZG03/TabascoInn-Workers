// ========================================
// 📋 GESTIÓN DE CONTRATOS
// ========================================

window.initContratos = function() {
    let contratosLoaded = false;
    
    const contratosTab = document.getElementById('nav-contratos-tab');
    if (!contratosTab) return;
    
    contratosTab.addEventListener('shown.bs.tab', function() {
        if (!contratosLoaded) {
            cargarContratos();
            contratosLoaded = true;
        }
    });
    
    // ========================================
    // 🔄 CARGA DE CONTRATOS
    // ========================================
    
    const cargarContratos = async () => {
        const contentDiv = document.getElementById('contratos-content');
        if (!contentDiv) return;
        
        const trabajadorId = window.PerfilUtils.getTrabajadorId();
        if (!trabajadorId) {
            mostrarErrorContratos('ID de trabajador no encontrado');
            return;
        }
        
        // Mostrar loading
        contentDiv.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5 class="text-muted">Cargando contratos...</h5>
            </div>
        `;
        
        try {
            const html = await window.PerfilUtils.fetchHTML(`${window.PERFIL_CONFIG.endpoints.contratos}${trabajadorId}/contratos`);
            contentDiv.innerHTML = html;
            inicializarEventosContratos();
            console.log('✅ Contratos cargados');
        } catch (error) {
            console.error('Error cargando contratos:', error);
            mostrarErrorContratos(error.message);
        }
    };
    
    const mostrarErrorContratos = (errorMessage) => {
        const contentDiv = document.getElementById('contratos-content');
        if (contentDiv) {
            contentDiv.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-danger">Error al cargar contratos</h5>
                    <div class="alert alert-danger d-inline-block">
                        <strong>Error:</strong> ${errorMessage}
                    </div>
                    <button class="btn btn-outline-primary mt-3" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Reintentar
                    </button>
                </div>
            `;
        }
    };
    
    // ========================================
    // 🎛️ EVENTOS DE CONTRATOS
    // ========================================
    
    const inicializarEventosContratos = () => {
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
    };

    // ========================================
    // 📝 CONFIGURADORES DE MODALES
    // ========================================
    
    const configurarDetalleModal = (event) => {
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
    };

    const configurarRenovarModal = (event) => {
        const button = event.relatedTarget;
        const form = document.getElementById('formRenovarContrato');
        const contratoId = button.getAttribute('data-contrato-id');
        const contratoFin = button.getAttribute('data-contrato-fin');
        
        form.action = `${window.PERFIL_CONFIG.endpoints.contratos}${window.PerfilUtils.getTrabajadorId()}/contratos/${contratoId}/renovar`;
        
        // Configurar fechas
        const fechaMin = new Date(contratoFin);
        fechaMin.setDate(fechaMin.getDate() + 1);
        const fechaFinDefault = new Date(fechaMin);
        fechaFinDefault.setMonth(fechaFinDefault.getMonth() + 6);
        
        form.querySelector('input[name="fecha_inicio"]').value = fechaMin.toISOString().split('T')[0];
        form.querySelector('input[name="fecha_fin"]').value = fechaFinDefault.toISOString().split('T')[0];
        form.querySelector('textarea[name="observaciones_renovacion"]').value = '';
    };

    const configurarEliminarModal = (event) => {
        const button = event.relatedTarget;
        const form = document.getElementById('formEliminarContrato');
        const contratoId = button.getAttribute('data-contrato-id');
        const contratoInfo = button.getAttribute('data-contrato-info');
        
        form.action = `${window.PERFIL_CONFIG.endpoints.contratos}${window.PerfilUtils.getTrabajadorId()}/contratos/${contratoId}/eliminar`;
        document.getElementById('contrato-periodo-info').textContent = contratoInfo;
        form.querySelector('textarea[name="motivo_eliminacion"]').value = '';
    };

    const configurarCrearModal = () => {
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
    };

    const validarEliminarForm = (e) => {
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
    };
    
    console.log('📋 Contratos inicializados');
};