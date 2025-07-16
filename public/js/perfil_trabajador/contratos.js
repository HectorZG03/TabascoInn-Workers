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

    // ✅ CORREGIDO: Modal de renovar con cálculo automático de fechas
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
            
            // ✅ CORREGIDO: Obtener referencias a los inputs correctamente
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin"]');
            const tipoDuracionSelect = form.querySelector('select[name="tipo_duracion"]');
            const observacionesTextarea = form.querySelector('textarea[name="observaciones_renovacion"]');
            
            if (!fechaInicioInput || !fechaFinInput || !tipoDuracionSelect) {
                console.error('❌ No se encontraron todos los inputs necesarios en el modal');
                return;
            }
            
            // ✅ CONFIGURAR FECHAS
            const fechaFinContrato = new Date(contratoFin);
            const fechaInicioRenovacion = new Date(fechaFinContrato);
            fechaInicioRenovacion.setDate(fechaInicioRenovacion.getDate() + 1);
            
            // Fecha fin por defecto: 6 meses después
            const fechaFinRenovacion = new Date(fechaInicioRenovacion);
            fechaFinRenovacion.setMonth(fechaFinRenovacion.getMonth() + 6);
            
            // ✅ CONFIGURAR VALORES INICIALES
            fechaInicioInput.value = fechaInicioRenovacion.toISOString().split('T')[0];
            fechaInicioInput.min = fechaInicioRenovacion.toISOString().split('T')[0];
            fechaFinInput.value = fechaFinRenovacion.toISOString().split('T')[0];
            fechaFinInput.min = fechaInicioRenovacion.toISOString().split('T')[0];
            
            // ✅ CALCULAR TIPO INICIAL AUTOMÁTICAMENTE
            const diasIniciales = Math.ceil((fechaFinRenovacion - fechaInicioRenovacion) / (1000 * 60 * 60 * 24));
            tipoDuracionSelect.value = diasIniciales >= 30 ? 'meses' : 'dias';
            
            // Limpiar observaciones
            if (observacionesTextarea) {
                observacionesTextarea.value = '';
            }
            
            // ✅ AGREGAR EVENT LISTENERS PARA CÁLCULO AUTOMÁTICO
            const calcularDuracionYTipo = () => {
                const fechaInicio = new Date(fechaInicioInput.value);
                const fechaFin = new Date(fechaFinInput.value);
                
                if (fechaInicio && fechaFin && fechaFin > fechaInicio) {
                    const diferenciaDias = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24));
                    
                    // ✅ LÓGICA AUTOMÁTICA: >= 30 días = meses, < 30 días = días
                    if (diferenciaDias >= 30) {
                        tipoDuracionSelect.value = 'meses';
                    } else {
                        tipoDuracionSelect.value = 'dias';
                    }
                    
                    console.log(`📅 Duración calculada: ${diferenciaDias} días -> Tipo: ${tipoDuracionSelect.value}`);
                }
            };
            
            // ✅ EVENT LISTENER PARA FECHA INICIO
            fechaInicioInput.addEventListener('change', function() {
                const nuevaFechaInicio = new Date(this.value);
                
                // Actualizar fecha mínima para fecha fin
                fechaFinInput.min = this.value;
                
                // Si fecha fin es anterior a la nueva fecha inicio, ajustarla
                const fechaFinActual = new Date(fechaFinInput.value);
                if (fechaFinActual <= nuevaFechaInicio) {
                    const nuevaFechaFin = new Date(nuevaFechaInicio);
                    nuevaFechaFin.setMonth(nuevaFechaFin.getMonth() + 6);
                    fechaFinInput.value = nuevaFechaFin.toISOString().split('T')[0];
                }
                
                calcularDuracionYTipo();
            });
            
            // ✅ EVENT LISTENER PARA FECHA FIN
            fechaFinInput.addEventListener('change', calcularDuracionYTipo);
            
            console.log('✅ Modal de renovación configurado correctamente');
            
        } catch (error) {
            console.error('❌ Error configurando modal de renovación:', error);
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

    // ✅ CORREGIDO: Modal de crear con cálculo automático
    const configurarCrearModal = () => {
        try {
            const form = document.querySelector('#modalCrearContrato form');
            if (!form) return;
            
            // Resetear formulario
            form.reset();
            
            // Configurar fechas
            const hoy = new Date();
            const fechaDefault = new Date();
            fechaDefault.setMonth(fechaDefault.getMonth() + 6);
            
            const fechaInicioInput = form.querySelector('input[name="fecha_inicio_contrato"]');
            const fechaFinInput = form.querySelector('input[name="fecha_fin_contrato"]');
            const tipoDuracionSelect = form.querySelector('select[name="tipo_duracion"]');
            
            if (fechaInicioInput && fechaFinInput) {
                const hoyStr = hoy.toISOString().split('T')[0];
                const fechaDefaultStr = fechaDefault.toISOString().split('T')[0];
                
                fechaInicioInput.min = hoyStr;
                fechaInicioInput.value = hoyStr;
                fechaFinInput.value = fechaDefaultStr;
                fechaFinInput.min = hoyStr;
                
                // ✅ CALCULAR TIPO INICIAL
                if (tipoDuracionSelect) {
                    const diasIniciales = Math.ceil((fechaDefault - hoy) / (1000 * 60 * 60 * 24));
                    tipoDuracionSelect.value = diasIniciales >= 30 ? 'meses' : 'dias';
                    
                    // ✅ AGREGAR LISTENERS PARA CÁLCULO AUTOMÁTICO
                    const calcularDuracionCrear = () => {
                        const fechaInicio = new Date(fechaInicioInput.value);
                        const fechaFin = new Date(fechaFinInput.value);
                        
                        if (fechaInicio && fechaFin && fechaFin > fechaInicio) {
                            const diferenciaDias = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24));
                            tipoDuracionSelect.value = diferenciaDias >= 30 ? 'meses' : 'dias';
                        }
                    };
                    
                    fechaInicioInput.addEventListener('change', function() {
                        fechaFinInput.min = this.value;
                        calcularDuracionCrear();
                    });
                    
                    fechaFinInput.addEventListener('change', calcularDuracionCrear);
                }
            }
            
            console.log('✅ Modal de crear configurado correctamente');
            
        } catch (error) {
            console.error('❌ Error configurando modal de creación:', error);
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
            
            const diferenciaDias = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24));
            if (diferenciaDias < 1) {
                e.preventDefault();
                alert('El contrato debe tener al menos 1 día de duración');
                return false;
            }

            console.log(`✅ Validación de renovación exitosa - Duración: ${diferenciaDias} días`);
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
    
    // ✅ NUEVA: Función de debug para fechas
    window.debugFechasContrato = function() {
        console.group('📅 Debug - Cálculo de Fechas en Contratos');
        
        const modal = document.getElementById('modalRenovarContrato');
        if (modal) {
            const fechaInicio = modal.querySelector('input[name="fecha_inicio"]');
            const fechaFin = modal.querySelector('input[name="fecha_fin"]');
            const tipoDuracion = modal.querySelector('select[name="tipo_duracion"]');
            
            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio.value);
                const fin = new Date(fechaFin.value);
                const dias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
                
                console.log('Fecha inicio:', fechaInicio.value);
                console.log('Fecha fin:', fechaFin.value);
                console.log('Días calculados:', dias);
                console.log('Tipo actual:', tipoDuracion?.value);
                console.log('Tipo sugerido:', dias >= 30 ? 'meses' : 'dias');
            }
        }
        
        console.groupEnd();
    };
    
    console.log('📋 Contratos inicializados con cálculo automático de fechas y tipos');
};