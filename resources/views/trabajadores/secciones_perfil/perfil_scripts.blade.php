{{-- resources/views/trabajadores/secciones_perfil/perfil_scripts.blade.php --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 🔧 FUNCIONALIDAD GENERAL
    // ========================================
    
    // ✅ CARGAR CATEGORÍAS (Datos Laborales)
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    if (areaSelect && categoriaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
            categoriaSelect.disabled = true;
            
            if (areaId) {
                fetch(`/api/categorias/${areaId}`)
                    .then(response => response.json())
                    .then(categorias => {
                        categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                        categorias.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.id_categoria;
                            option.textContent = categoria.nombre_categoria;
                            categoriaSelect.appendChild(option);
                        });
                        categoriaSelect.disabled = false;
                    })
                    .catch(() => {
                        categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                        categoriaSelect.disabled = false;
                    });
            } else {
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                categoriaSelect.disabled = false;
            }
        });
    }
    
    // ✅ VALIDACIÓN DE FORMULARIOS (simplificada)
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        });
    });

    // ========================================
    // 📄 FUNCIONALIDAD DE DOCUMENTOS
    // ========================================
    
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const modalTitle = uploadModal.querySelector('.modal-title');
            const tipoInput = uploadModal.querySelector('#tipo_documento');
            
            modalTitle.textContent = `Subir ${button.getAttribute('data-nombre')}`;
            tipoInput.value = button.getAttribute('data-tipo');
        });
    }

    const fileInput = document.getElementById('archivo');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            
            // Validar tamaño (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Archivo muy grande. Máximo 10MB.');
                this.value = '';
                return;
            }
            
            // Validar tipo
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Tipo no válido. Solo PDF, JPG, PNG.');
                this.value = '';
                return;
            }
        });
    }

    // ========================================
    // 📋 FUNCIONALIDAD DE CONTRATOS
    // ========================================
    
    // ✅ CARGA DINÁMICA DE CONTRATOS
    const contratosTab = document.getElementById('nav-contratos-tab');
    let contratosLoaded = false;
    
    if (contratosTab) {
        contratosTab.addEventListener('shown.bs.tab', function() {
            if (!contratosLoaded) {
                loadContratos();
                contratosLoaded = true;
            }
        });
    }
    
    function loadContratos() {
        const contentDiv = document.getElementById('contratos-content');
        if (!contentDiv) return;
        
        contentDiv.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5 class="text-muted">Cargando contratos...</h5>
            </div>
        `;
        
        const trabajadorId = document.querySelector('[data-trabajador-id]')?.getAttribute('data-trabajador-id') || 
                           window.location.pathname.match(/trabajadores\/(\d+)/)?.[1];
        
        if (!trabajadorId) {
            mostrarErrorContratos('ID de trabajador no encontrado');
            return;
        }
        
        fetch(`/trabajadores/${trabajadorId}/contratos`)
            .then(response => response.text())
            .then(html => {
                contentDiv.innerHTML = html;
                initContratosEvents();
                console.log('✅ Contratos cargados');
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarErrorContratos(error.message);
            });
    }
    
    function mostrarErrorContratos(errorMessage) {
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
    }
    
    // ✅ EVENTOS ESPECÍFICOS DE CONTRATOS (después de cargar AJAX)
    function initContratosEvents() {
        // Modal de detalles (simplificado)
        const detalleModal = document.getElementById('detalleContratoModal');
        if (detalleModal) {
            detalleModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const contratoData = JSON.parse(button.getAttribute('data-contrato'));
                
                const contenido = document.getElementById('detalle-contenido');
                // En la función initContratosEvents(), modificar el modal de detalles:
               // Buscar y reemplazar ESTA sección en initContratosEvents()
                contenido.innerHTML = `
                    <div class="row">
                        <div class="col-6"><strong>Inicio:</strong><br>${contratoData.inicio}</div>
                        <div class="col-6"><strong>Fin:</strong><br>${contratoData.fin}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6"><strong>Duración:</strong><br>${contratoData.duracion}</div>
                        <div class="col-6"><strong>Estado:</strong><br>
                            <span class="badge bg-${contratoData.estado === 'expirado' ? 'danger' : 'success'}">
                                ${contratoData.estado === 'expirado' ? 'Expirado' : 'Vigente'}
                            </span>
                        </div>
                    </div>
                    <hr>
                    ${contratoData.estado !== 'expirado' ? 
                        `<div><strong>Días Restantes:</strong> ${contratoData.dias_restantes} días</div>` : 
                        `<div class="text-danger"><strong>Contrato expirado</strong></div>`
                    }
                `;
            });
        }

        // Modal de renovación
        const renovarModal = document.getElementById('modalRenovarContrato');
        if (renovarModal) {
            renovarModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const contratoId = button.getAttribute('data-contrato-id');
                const contratoFin = button.getAttribute('data-contrato-fin');
                
                const form = document.getElementById('formRenovarContrato');
                const trabajadorId = form.getAttribute('data-trabajador-id');
                form.action = `/trabajadores/${trabajadorId}/contratos/${contratoId}/renovar`;
                
                // Configurar fechas
                const fechaMin = new Date(contratoFin);
                fechaMin.setDate(fechaMin.getDate() + 1);
                
                const fechaInicioInput = form.querySelector('input[name="fecha_inicio"]');
                const fechaFinInput = form.querySelector('input[name="fecha_fin"]');
                
                fechaInicioInput.value = fechaMin.toISOString().split('T')[0];
                fechaInicioInput.min = fechaMin.toISOString().split('T')[0];
                
                // 6 meses después por defecto
                const fechaFinDefault = new Date(fechaMin);
                fechaFinDefault.setMonth(fechaFinDefault.getMonth() + 6);
                fechaFinInput.value = fechaFinDefault.toISOString().split('T')[0];
            });
        }

        // ✅ NUEVO: Modal de terminar contrato
        const terminarModal = document.getElementById('modalTerminarContrato');
        if (terminarModal) {
            terminarModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const contratoId = button.getAttribute('data-contrato-id');
                
                const form = document.getElementById('formTerminarContrato');
                const trabajadorId = form.getAttribute('data-trabajador-id');
                form.action = `/trabajadores/${trabajadorId}/contratos/${contratoId}/terminar`;
            });
        }

        // Modal de crear contrato
        const crearModal = document.getElementById('modalCrearContrato');
        if (crearModal) {
            crearModal.addEventListener('show.bs.modal', function() {
                const form = crearModal.querySelector('form');
                if (form) {
                    form.reset();
                    
                    const fechaInicioInput = form.querySelector('input[name="fecha_inicio_contrato"]');
                    const fechaFinInput = form.querySelector('input[name="fecha_fin_contrato"]');
                    
                    if (fechaInicioInput) {
                        const hoy = new Date().toISOString().split('T')[0];
                        fechaInicioInput.min = hoy;
                        fechaInicioInput.value = hoy;
                    }
                    
                    if (fechaFinInput) {
                        const fechaDefault = new Date();
                        fechaDefault.setMonth(fechaDefault.getMonth() + 6);
                        fechaFinInput.value = fechaDefault.toISOString().split('T')[0];
                    }
                }
            });
        }

        console.log('✅ Eventos de contratos inicializados');
    }
    
    // ✅ CARGAR CONTRATOS SI ES LA PESTAÑA ACTIVA
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || '{{ session("activeTab") }}';
    
    if (activeTab === 'contratos' && !contratosLoaded) {
        const contratosTabElement = document.querySelector('[data-bs-target="#nav-contratos"]');
        if (contratosTabElement) {
            const tab = new bootstrap.Tab(contratosTabElement);
            tab.show();
        }
        
        setTimeout(() => {
            loadContratos();
            contratosLoaded = true;
        }, 100);
    }

    // ========================================
    // 🔗 NAVEGACIÓN ENTRE PESTAÑAS
    // ========================================
    
    if (activeTab && activeTab !== 'contratos') {
        const tabElement = document.querySelector(`[data-bs-target="#nav-${activeTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const targetTab = event.target.getAttribute('data-bs-target').replace('#nav-', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState(null, '', url);
        });
    });

    // ========================================
    // 💼 DATOS LABORALES - DÍAS LABORABLES
    // ========================================
    
    const diasLaborablesCheckboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
    const diasDescansoContainer = document.querySelector('.dias-descanso-container p');
    
    if (diasLaborablesCheckboxes.length > 0 && diasDescansoContainer) {
        const diasSemana = {
            'lunes': 'Lunes', 'martes': 'Martes', 'miercoles': 'Miércoles',
            'jueves': 'Jueves', 'viernes': 'Viernes', 'sabado': 'Sábado', 'domingo': 'Domingo'
        };
        
        function actualizarDiasDescanso() {
            const diasSeleccionados = Array.from(diasLaborablesCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            const todosDias = Object.keys(diasSemana);
            const diasDescanso = todosDias.filter(dia => !diasSeleccionados.includes(dia));
            
            diasDescansoContainer.textContent = diasDescanso.length > 0 ? 
                diasDescanso.map(dia => diasSemana[dia]).join(', ') : 
                'No calculados';
        }
        
        diasLaborablesCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', actualizarDiasDescanso);
        });
        
        actualizarDiasDescanso();
    }

    // ========================================
    // 👤 VALIDACIONES DATOS PERSONALES
    // ========================================
    
    // CURP (18 caracteres)
    const curpInput = document.getElementById('curp');
    if (curpInput) {
        curpInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().substring(0, 18);
        });
    }
    
    // RFC (13 caracteres)
    const rfcInput = document.getElementById('rfc');
    if (rfcInput) {
        rfcInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().substring(0, 13);
        });
    }
    
    // Teléfono (solo números, 10 dígitos)
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });
    }
    
    // NSS (solo números, 11 dígitos)
    const nssInput = document.getElementById('no_nss');
    if (nssInput) {
        nssInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 11);
        });
    }

    console.log('✅ Perfil Trabajador - Scripts optimizados inicializados');
});
</script>