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
    // 📋 FUNCIONALIDAD DE CONTRATOS ACTUALIZADA
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
    
    // ✅ EVENTOS ESPECÍFICOS DE CONTRATOS (actualizados)
    function initContratosEvents() {
        // Modal de detalles (actualizado con nueva información)
        const detalleModal = document.getElementById('detalleContratoModal');
        if (detalleModal) {
            detalleModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const contratoData = JSON.parse(button.getAttribute('data-contrato'));
                
                const contenido = document.getElementById('detalle-contenido');
                
                let observacionesHtml = '';
                if (contratoData.observaciones) {
                    observacionesHtml = `
                        <hr>
                        <div><strong>Observaciones:</strong><br>
                        <small class="text-muted">${contratoData.observaciones}</small></div>
                    `;
                }
                
                let renovacionHtml = '';
                if (contratoData.es_renovacion) {
                    renovacionHtml = `
                        <div><strong>Renovación de:</strong> Contrato #${contratoData.contrato_anterior_id}</div>
                        <hr>
                    `;
                }

                // ✅ ACTUALIZADO: Información de estado más detallada
                let estadoDetalladoHtml = '';
                if (contratoData.esta_activo) {
                    if (contratoData.esta_pendiente_iniciar) {
                        estadoDetalladoHtml = `
                            <div><strong>Estado Detallado:</strong> Programado (inicia en ${contratoData.dias_restantes} días)</div>
                            <hr>
                        `;
                    } else if (contratoData.esta_en_periodo_vigente) {
                        estadoDetalladoHtml = `
                            <div><strong>Estado Detallado:</strong> En período vigente (${contratoData.dias_restantes} días restantes)</div>
                            <hr>
                        `;
                    }
                }
                
                contenido.innerHTML = `
                    <div class="row">
                        <div class="col-6"><strong>ID del Contrato:</strong><br>#${contratoData.id}</div>
                        <div class="col-6"><strong>Estado:</strong><br>
                            <span class="badge bg-${contratoData.estado === 'expirado' ? 'danger' : 
                                                   contratoData.estado === 'activo' ? 'success' : 
                                                   contratoData.estado === 'renovado' ? 'info' : 'secondary'}">
                                ${contratoData.texto_estado}
                            </span>
                        </div>
                    </div>
                    <hr>
                    ${renovacionHtml}
                    ${estadoDetalladoHtml}
                    <div class="row">
                        <div class="col-6"><strong>Inicio:</strong><br>${contratoData.inicio}</div>
                        <div class="col-6"><strong>Fin:</strong><br>${contratoData.fin}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12"><strong>Duración:</strong><br>${contratoData.duracion}</div>
                    </div>
                    ${observacionesHtml}
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
                
                // Configurar fechas mejoradas
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
                
                // Limpiar observaciones
                const observacionesInput = form.querySelector('textarea[name="observaciones_renovacion"]');
                if (observacionesInput) {
                    observacionesInput.value = '';
                }
            });
        }

        // ✅ NUEVO: Modal de eliminar contrato
        const eliminarModal = document.getElementById('modalEliminarContrato');
        if (eliminarModal) {
            eliminarModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const contratoId = button.getAttribute('data-contrato-id');
                const contratoInfo = button.getAttribute('data-contrato-info');
                
                const form = document.getElementById('formEliminarContrato');
                const trabajadorId = form.getAttribute('data-trabajador-id');
                form.action = `/trabajadores/${trabajadorId}/contratos/${contratoId}/eliminar`;
                
                // Mostrar información del contrato
                const periodoInfo = document.getElementById('contrato-periodo-info');
                if (periodoInfo) {
                    periodoInfo.textContent = contratoInfo;
                }
                
                // Limpiar el motivo
                const motivoInput = form.querySelector('textarea[name="motivo_eliminacion"]');
                if (motivoInput) {
                    motivoInput.value = '';
                }
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

        // ✅ VALIDACIONES ACTUALIZADAS para formularios de contratos
        const formRenovar = document.getElementById('formRenovarContrato');
        if (formRenovar) {
            formRenovar.addEventListener('submit', function(e) {
                const fechaInicio = new Date(this.querySelector('input[name="fecha_inicio"]').value);
                const fechaFin = new Date(this.querySelector('input[name="fecha_fin"]').value);
                
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
            });
        }

        // ✅ NUEVO: Validaciones para eliminar contrato
        const formEliminar = document.getElementById('formEliminarContrato');
        if (formEliminar) {
            formEliminar.addEventListener('submit', function(e) {
                const motivo = this.querySelector('textarea[name="motivo_eliminacion"]').value.trim();
                
                if (!motivo) {
                    e.preventDefault();
                    alert('Debe especificar un motivo para eliminar el contrato');
                    return false;
                }
                
                if (motivo.length < 10) {
                    e.preventDefault();
                    alert('El motivo debe tener al menos 10 caracteres');
                    return false;
                }
                
                const confirmMessage = '⚠️ ¿Está seguro de que desea ELIMINAR PERMANENTEMENTE este contrato?\n\n' +
                                     '• Esta acción NO se puede deshacer\n' +
                                     '• Se eliminará el registro y el archivo PDF\n' +
                                     '• Se registrará en el historial del sistema\n\n' +
                                     'Escriba "ELIMINAR" para confirmar:';
                
                const confirmation = prompt(confirmMessage);
                if (confirmation !== 'ELIMINAR') {
                    e.preventDefault();
                    alert('Eliminación cancelada. Debe escribir exactamente "ELIMINAR" para confirmar.');
                    return false;
                }
            });
        }

        console.log('✅ Eventos de contratos inicializados (solo 3 estados esenciales)');
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

    // ========================================
    // 🚨 NOTIFICACIONES Y FEEDBACK
    // ========================================
    
    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });

    console.log('✅ Perfil Trabajador - Scripts simplificados con 3 estados esenciales inicializados');
});
</script>