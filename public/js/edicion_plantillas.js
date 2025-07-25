/**
 * ✅ EDITOR DE PLANTILLAS UNIFICADO - VERSIÓN CORREGIDA
 * Archivo JavaScript principal para gestión completa del editor de plantillas de contrato
 */

class PlantillasEditor {
    constructor(options = {}) {
        this.options = {
            editorSelector: '#editorContenido',
            previewRoute: options.previewRoute || '/configuracion/plantillas-contrato/preview',
            variablesRoute: options.variablesRoute || '/configuracion/plantillas-contrato/api/variables',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            debug: options.debug || false,
            ...options
        };
        
        this.editorInstance = null;
        this.modalPreview = null;
        this.variablesCache = null;
        
        this.init();
    }

    /**
     * ✅ INICIALIZAR EDITOR
     */
    init() {
        this.log('🚀 Inicializando Editor de Plantillas Unificado');
        
        // Verificar dependencias
        if (!this.verificarDependencias()) {
            return;
        }
        
        this.initTinyMCE();
        this.initEventListeners();
        this.initModalPreview();
        this.initBuscadorVariables();
        this.initValidacionFormulario();
        this.cargarVariables();

        this.log('✅ Editor de Plantillas inicializado correctamente');
    }

    /**
     * ✅ VERIFICAR DEPENDENCIAS
     */
    verificarDependencias() {
        if (typeof tinymce === 'undefined') {
            console.error('❌ TinyMCE no está cargado');
            return false;
        }
        
        if (typeof bootstrap === 'undefined') {
            console.error('❌ Bootstrap no está cargado');
            return false;
        }
        
        if (!this.options.csrfToken) {
            console.error('❌ CSRF Token no encontrado');
            return false;
        }
        
        return true;
    }

    /**
     * ✅ CONFIGURAR TINYMCE - MEJORADO
     */
    initTinyMCE() {
        const self = this;
        
        // Remover instancia existente si existe
        if (tinymce.get(this.options.editorSelector.replace('#', ''))) {
            tinymce.remove(this.options.editorSelector);
        }
        
        tinymce.init({
            selector: this.options.editorSelector,
            height: 800, // ✅ MÁS GRANDE
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount',
                'pagebreak', 'nonbreaking', 'paste'
            ],
            toolbar: [
                'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify',
                'bullist numlist outdent indent | removeformat | table | link image | code fullscreen | help'
            ].join(' | '),
            content_style: `
                body { 
                    font-family: 'DejaVu Sans', Arial, sans-serif; 
                    font-size: 12px; 
                    line-height: 1.5; 
                    margin: 40px; 
                    color: #000; 
                    background: white;
                }
                .clausula-numero { 
                    font-weight: bold; 
                    text-decoration: underline; 
                    margin-top: 15px;
                }
                .bold { font-weight: bold; }
                .center { text-align: center; }
                .uppercase { text-transform: uppercase; }
                .variable-highlight { 
                    background-color: #fff3cd; 
                    border: 1px dashed #ffc107; 
                    padding: 2px 4px; 
                    border-radius: 3px;
                    font-weight: bold;
                }
                table { border-collapse: collapse; width: 100%; }
                table td, table th { border: 1px solid #ddd; padding: 8px; }
            `,
            setup: function(editor) {
                editor.on('init', function() {
                    self.editorInstance = editor;
                    self.log('✅ TinyMCE inicializado correctamente');
                    
                    // Destacar variables existentes
                    setTimeout(() => self.highlightVariables(), 500);
                });
                
                editor.on('input change', function() {
                    // Debounce para evitar demasiadas llamadas
                    clearTimeout(self.highlightTimeout);
                    self.highlightTimeout = setTimeout(() => {
                        self.highlightVariables();
                    }, 1000);
                });
                
                editor.on('focus', function() {
                    self.log('📝 Editor enfocado');
                });
            },
            language: 'es',
            branding: false,
            promotion: false,
            paste_as_text: true,
            paste_word_valid_elements: "b,strong,i,em,u,s,a,p,br,div,h1,h2,h3,h4,h5,h6,ul,ol,li,table,thead,tbody,tr,td,th"
        });
    }

    /**
     * ✅ DESTACAR VARIABLES EN EL EDITOR - MEJORADO
     */
    highlightVariables() {
        if (!this.editorInstance) return;
        
        try {
            let content = this.editorInstance.getContent();
            const originalContent = content;
            
            // Remover highlighting previo
            content = content.replace(/<span class="variable-highlight">(.*?)<\/span>/g, '$1');
            
            // Aplicar nuevo highlighting
            content = content.replace(/\{\{([^}]+)\}\}/g, '<span class="variable-highlight">{{$1}}</span>');
            
            // Solo actualizar si hay cambios
            if (content !== originalContent) {
                const cursorPos = this.editorInstance.selection.getBookmark();
                this.editorInstance.setContent(content);
                this.editorInstance.selection.moveToBookmark(cursorPos);
            }
        } catch (error) {
            this.log('⚠️ Error destacando variables:', error);
        }
    }

    /**
     * ✅ CONFIGURAR EVENT LISTENERS - MEJORADO
     */
    initEventListeners() {
        const self = this;
        
        // ✅ INSERTAR VARIABLES - DELEGACIÓN DE EVENTOS
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-insertar-variable');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const variable = btn.dataset.variable;
                if (variable) {
                    self.insertarVariable(variable);
                }
            }
        });

        // ✅ COPIAR VARIABLES AL HACER CLICK EN CÓDIGO
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('variable-codigo')) {
                e.preventDefault();
                e.stopPropagation();
                self.copiarVariable(e.target);
            }
        });

        // ✅ VISTA PREVIA
        const btnPreview = document.getElementById('btnPreview');
        if (btnPreview) {
            btnPreview.addEventListener('click', function(e) {
                e.preventDefault();
                self.mostrarVistaPrevia();
            });
        }

        // ✅ ACTUALIZAR VISTA PREVIA
        const btnActualizarPreview = document.getElementById('btnActualizarPreview');
        if (btnActualizarPreview) {
            btnActualizarPreview.addEventListener('click', function(e) {
                e.preventDefault();
                self.generarVistaPrevia();
            });
        }

        // ✅ TOGGLE VISTA PREVIA/HTML
        const radioVistas = document.querySelectorAll('input[name="vistaOptions"]');
        radioVistas.forEach(radio => {
            radio.addEventListener('change', function() {
                self.toggleVista();
            });
        });

        // ✅ CAMBIOS EN SELECTS DE PREVIEW
        const trabajadorPreview = document.getElementById('trabajadorPreview');
        const tipoContratoPreview = document.getElementById('tipoContratoPreview');
        
        if (trabajadorPreview) {
            trabajadorPreview.addEventListener('change', function() {
                if (self.modalPreview && self.modalPreview._isShown) {
                    self.generarVistaPrevia();
                }
            });
        }
        
        if (tipoContratoPreview) {
            tipoContratoPreview.addEventListener('change', function() {
                if (self.modalPreview && self.modalPreview._isShown) {
                    self.generarVistaPrevia();
                }
            });
        }
    }

    /**
     * ✅ INSERTAR VARIABLE EN EL EDITOR - CORREGIDO
     */
    insertarVariable(variable) {
        if (!this.editorInstance) {
            this.mostrarError('Editor no disponible');
            return;
        }

        if (!variable) {
            this.mostrarError('Variable no especificada');
            return;
        }

        try {
            // Asegurar que la variable tenga el formato correcto
            const variableFormateada = variable.startsWith('{{') ? variable : `{{${variable}}}`;
            
            // Insertar en la posición del cursor
            this.editorInstance.insertContent(variableFormateada + ' ');
            
            // Enfocar el editor
            this.editorInstance.focus();
            
            // Feedback visual en el botón
            const btn = document.querySelector(`[data-variable="${variable}"]`);
            if (btn) {
                this.mostrarFeedbackBoton(btn, 'success', '<i class="bi bi-check"></i> Insertado', 1500);
            }
            
            // Destacar variables después de insertar
            setTimeout(() => this.highlightVariables(), 100);
            
            this.log('📝 Variable insertada:', variableFormateada);
            
        } catch (error) {
            console.error('❌ Error insertando variable:', error);
            this.mostrarError('Error al insertar la variable');
        }
    }

    /**
     * ✅ COPIAR VARIABLE AL CLIPBOARD - MEJORADO
     */
    async copiarVariable(elemento) {
        const variable = elemento.dataset.variable;
        
        if (!variable) {
            this.mostrarError('No se encontró variable para copiar');
            return;
        }

        try {
            await navigator.clipboard.writeText(variable);
            
            // Feedback visual mejorado
            const originalBg = elemento.style.backgroundColor;
            const originalColor = elemento.style.color;
            
            elemento.style.backgroundColor = '#d4edda';
            elemento.style.color = '#155724';
            elemento.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                elemento.style.backgroundColor = originalBg;
                elemento.style.color = originalColor;
            }, 800);
            
            this.mostrarTooltip(elemento, 'Copiado!');
            this.log('📋 Variable copiada:', variable);
            
        } catch (error) {
            console.error('❌ Error copiando variable:', error);
            this.mostrarTooltip(elemento, 'Error al copiar');
        }
    }

    /**
     * ✅ MOSTRAR FEEDBACK EN BOTÓN
     */
    mostrarFeedbackBoton(btn, tipo, contenido, duracion = 1000) {
        const claseOriginal = btn.className;
        const contenidoOriginal = btn.innerHTML;
        
        // Aplicar feedback
        btn.className = btn.className.replace(/btn-outline-\w+/, `btn-${tipo}`);
        btn.innerHTML = contenido;
        btn.disabled = true;
        
        // Restaurar después del tiempo especificado
        setTimeout(() => {
            btn.className = claseOriginal;
            btn.innerHTML = contenidoOriginal;
            btn.disabled = false;
        }, duracion);
    }

    /**
     * ✅ MOSTRAR TOOLTIP TEMPORAL
     */
    mostrarTooltip(elemento, mensaje) {
        // Remover tooltip existente
        const tooltipExistente = elemento.querySelector('.tooltip-temp');
        if (tooltipExistente) {
            tooltipExistente.remove();
        }

        // Crear nuevo tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip-temp position-absolute bg-dark text-white px-2 py-1 rounded small';
        tooltip.style.cssText = 'top: -35px; left: 50%; transform: translateX(-50%); z-index: 9999; white-space: nowrap; font-size: 11px;';
        tooltip.textContent = mensaje;
        
        // Posicionar relativo al elemento
        elemento.style.position = 'relative';
        elemento.appendChild(tooltip);
        
        // Remover después de 2 segundos
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.remove();
            }
        }, 2000);
    }

    /**
     * ✅ INICIALIZAR MODAL DE VISTA PREVIA
     */
    initModalPreview() {
        const modalElement = document.getElementById('modalPreview');
        if (modalElement) {
            this.modalPreview = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            
            // Event listeners para el modal
            modalElement.addEventListener('shown.bs.modal', () => {
                this.log('👀 Modal de vista previa mostrado');
                // Generar vista previa automáticamente al mostrar
                setTimeout(() => this.generarVistaPrevia(), 300);
            });
        }
    }

    /**
     * ✅ MOSTRAR VISTA PREVIA
     */
    mostrarVistaPrevia() {
        if (this.modalPreview) {
            this.modalPreview.show();
        } else {
            // Si no hay modal, generar vista previa inline
            this.generarVistaPrevia();
        }
    }

    /**
     * ✅ GENERAR VISTA PREVIA - CORREGIDO
     */
    async generarVistaPrevia() {
        if (!this.editorInstance) {
            this.mostrarError('Editor no disponible para vista previa');
            return;
        }

        const contenidoPreview = document.getElementById('contenidoPreview');
        if (!contenidoPreview) {
            this.mostrarError('Contenedor de vista previa no encontrado');
            return;
        }

        // Obtener datos del formulario
        const contenidoHtml = this.editorInstance.getContent();
        const trabajadorId = document.getElementById('trabajadorPreview')?.value || null;
        const tipoContrato = document.getElementById('tipoContratoPreview')?.value || 'determinado';
        
        if (!contenidoHtml.trim()) {
            contenidoPreview.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    El contenido del editor está vacío
                </div>
            `;
            return;
        }
        
        // Mostrar loading
        contenidoPreview.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div class="h6 text-muted">Generando vista previa...</div>
                <small class="text-muted">Procesando variables y contenido</small>
            </div>
        `;

        try {
            this.log('🔄 Enviando solicitud de vista previa...');
            
            const response = await fetch(this.options.previewRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    contenido_html: contenidoHtml,
                    trabajador_id: trabajadorId,
                    tipo_contrato: tipoContrato
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                contenidoPreview.innerHTML = data.contenido_html;
                this.log('👀 Vista previa generada exitosamente');
                
                // Mostrar información adicional si está disponible
                if (data.trabajador_nombre) {
                    this.mostrarInfoPreview(data);
                }
            } else {
                throw new Error(data.error || 'Error desconocido en la vista previa');
            }

        } catch (error) {
            console.error('❌ Error generando vista previa:', error);
            contenidoPreview.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Error generando vista previa:</strong><br>
                    <small>${error.message}</small>
                    <hr>
                    <small class="text-muted">
                        • Verifica tu conexión a internet<br>
                        • Asegúrate de que el contenido tenga variables válidas<br>
                        • Recarga la página si persiste el problema
                    </small>
                </div>
            `;
        }
    }

    /**
     * ✅ MOSTRAR INFORMACIÓN DE LA PREVIEW
     */
    mostrarInfoPreview(data) {
        const infoContainer = document.getElementById('infoPreview');
        if (infoContainer) {
            infoContainer.innerHTML = `
                <div class="small text-muted mb-2">
                    <i class="bi bi-info-circle"></i>
                    Datos de ejemplo: ${data.trabajador_nombre || 'Trabajador ficticio'} |
                    Variables procesadas: ${data.variables_utilizadas?.length || 0}
                </div>
            `;
        }
    }

    /**
     * ✅ INICIALIZAR BUSCADOR DE VARIABLES
     */
    initBuscadorVariables() {
        const buscarVariable = document.getElementById('buscarVariable');
        if (!buscarVariable) return;

        let searchTimeout;
        
        buscarVariable.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            
            // Debounce la búsqueda
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const variables = document.querySelectorAll('.variable-item');
                let encontrados = 0;
                
                variables.forEach(variable => {
                    const etiqueta = variable.dataset.etiqueta?.toLowerCase() || '';
                    const nombre = variable.dataset.variable?.toLowerCase() || '';
                    const ejemplo = variable.dataset.ejemplo?.toLowerCase() || '';
                    const descripcion = variable.querySelector('.text-muted')?.textContent?.toLowerCase() || '';
                    
                    const coincide = !busqueda || 
                                   etiqueta.includes(busqueda) || 
                                   nombre.includes(busqueda) || 
                                   ejemplo.includes(busqueda) ||
                                   descripcion.includes(busqueda);
                    
                    variable.style.display = coincide ? 'block' : 'none';
                    
                    if (coincide) encontrados++;
                });
                
                // Mostrar contador de resultados
                this.updateSearchResults(encontrados, variables.length);
                
            }, 300);
        });
    }

    /**
     * ✅ ACTUALIZAR RESULTADOS DE BÚSQUEDA
     */
    updateSearchResults(encontrados, total) {
        let contador = document.getElementById('contadorBusqueda');
        if (!contador) {
            contador = document.createElement('small');
            contador.id = 'contadorBusqueda';
            contador.className = 'text-muted d-block mt-1';
            document.getElementById('buscarVariable').parentNode.appendChild(contador);
        }
        
        contador.textContent = `${encontrados} de ${total} variables`;
    }

    /**
     * ✅ TOGGLE ENTRE VISTA HTML Y PREVIEW
     */
    toggleVista() {
        const vistaHTML = document.getElementById('vistaHTML');
        const contenidoHTML = document.getElementById('contenidoHTML');
        const contenidoPreview = document.getElementById('contenidoPreview');
        
        if (!vistaHTML || !contenidoHTML || !contenidoPreview) return;

        if (vistaHTML.checked) {
            contenidoHTML.style.display = 'block';
            contenidoPreview.style.display = 'none';
        } else {
            contenidoHTML.style.display = 'none';
            contenidoPreview.style.display = 'block';
            this.generarVistaPrevia();
        }
    }

    /**
     * ✅ VALIDACIÓN DEL FORMULARIO
     */
    initValidacionFormulario() {
        const formularios = [
            document.getElementById('formPlantilla'),
            document.getElementById('formEditarPlantilla')
        ];

        formularios.forEach(form => {
            if (!form) return;

            form.addEventListener('submit', (e) => {
                if (!this.validarFormulario(form, e)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    /**
     * ✅ VALIDAR FORMULARIO ANTES DE ENVÍO
     */
    validarFormulario(form, event) {
        // Guardar contenido del editor
        if (this.editorInstance) {
            this.editorInstance.save();
        }
        
        // Validar contenido no vacío
        const contenido = document.getElementById('editorContenido')?.value;
        if (!contenido || !contenido.trim()) {
            this.mostrarError('El contenido de la plantilla no puede estar vacío');
            return false;
        }

        // Validar nombre de plantilla
        const nombre = document.getElementById('nombre_plantilla')?.value;
        if (!nombre || !nombre.trim()) {
            this.mostrarError('El nombre de la plantilla es obligatorio');
            document.getElementById('nombre_plantilla')?.focus();
            return false;
        }

        // Validar tipo de contrato
        const tipo = document.getElementById('tipo_contrato')?.value;
        if (!tipo) {
            this.mostrarError('Debes seleccionar un tipo de contrato');
            document.getElementById('tipo_contrato')?.focus();
            return false;
        }

        // Validar descripción para ediciones
        const descripcion = document.getElementById('descripcion');
        if (descripcion && form.id === 'formEditarPlantilla') {
            if (!descripcion.value || !descripcion.value.trim()) {
                this.mostrarError('Debes describir qué cambios hiciste en esta versión');
                descripcion.focus();
                return false;
            }
        }

        // Confirmación para ediciones
        if (form.id === 'formEditarPlantilla') {
            const version = document.querySelector('[data-nueva-version]')?.dataset.nuevaVersion;
            if (!confirm(`¿Crear nueva versión ${version || ''} y activarla automáticamente?`)) {
                return false;
            }
        }

        this.log('💾 Formulario validado, enviando...');
        return true;
    }

    /**
     * ✅ CARGAR VARIABLES DISPONIBLES
     */
    async cargarVariables() {
        if (this.variablesCache) {
            return this.variablesCache;
        }

        try {
            const response = await fetch(this.options.variablesRoute);
            if (response.ok) {
                const data = await response.json();
                this.variablesCache = data.variables || [];
                this.log('✅ Variables cargadas:', this.variablesCache.length);
                return this.variablesCache;
            }
        } catch (error) {
            this.log('⚠️ No se pudieron cargar variables dinámicamente:', error);
        }
        
        return [];
    }

    /**
     * ✅ MOSTRAR ERROR
     */
    mostrarError(mensaje) {
        // Crear o actualizar alerta de error
        let alerta = document.getElementById('alerta-editor');
        
        if (!alerta) {
            alerta = document.createElement('div');
            alerta.id = 'alerta-editor';
            alerta.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            document.body.appendChild(alerta);
        }

        alerta.innerHTML = `
            <i class="bi bi-exclamation-circle me-2"></i><strong>Error:</strong> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alerta && alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }

    /**
     * ✅ MOSTRAR ÉXITO
     */
    mostrarExito(mensaje) {
        let alerta = document.createElement('div');
        alerta.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        alerta.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta && alerta.parentNode) {
                alerta.remove();
            }
        }, 3000);
    }

    /**
     * ✅ LOGGING CONDICIONAL
     */
    log(...args) {
        if (this.options.debug) {
            console.log(...args);
        }
    }

    /**
     * ✅ DESTRUIR EDITOR (CLEANUP)
     */
    destroy() {
        if (this.editorInstance) {
            tinymce.remove(this.options.editorSelector);
            this.editorInstance = null;
        }

        if (this.modalPreview) {
            this.modalPreview.dispose();
            this.modalPreview = null;
        }

        // Limpiar timeouts
        if (this.highlightTimeout) {
            clearTimeout(this.highlightTimeout);
        }

        this.log('🗑️ Editor de plantillas destruido');
    }
}

// ✅ CLASE PARA FILTROS Y UTILIDADES
class PlantillasUtilidades {
    /**
     * Filtrar categorías de variables
     */
    static initFiltroCategoria() {
        const filtroCategoria = document.getElementById('filtroCategoria');
        const categoriasGrupos = document.querySelectorAll('.categoria-grupo');
        
        if (!filtroCategoria) return;

        filtroCategoria.addEventListener('change', function() {
            const categoriaSeleccionada = this.value;
            let visibles = 0;
            
            categoriasGrupos.forEach(grupo => {
                if (!categoriaSeleccionada || grupo.dataset.categoria === categoriaSeleccionada) {
                    grupo.style.display = 'block';
                    visibles++;
                } else {
                    grupo.style.display = 'none';
                }
            });
            
            // Mostrar contador
            PlantillasUtilidades.actualizarContadorFiltro(visibles, categoriasGrupos.length);
            
            console.log('🔍 Filtro aplicado:', categoriaSeleccionada || 'Todas');
        });
    }

    /**
     * Actualizar contador de filtro
     */
    static actualizarContadorFiltro(visibles, total) {
        let contador = document.getElementById('contadorFiltro');
        if (!contador) {
            contador = document.createElement('small');
            contador.id = 'contadorFiltro';
            contador.className = 'text-muted ms-2';
            document.getElementById('filtroCategoria').parentNode.appendChild(contador);
        }
        contador.textContent = `(${visibles}/${total})`;
    }

    /**
     * Confirmaciones para acciones importantes
     */
    static initConfirmaciones() {
        const botonesActivar = document.querySelectorAll('form[action*="toggle"] button');
        
        botonesActivar.forEach(boton => {
            boton.addEventListener('click', function(e) {
                const accion = this.textContent.trim();
                const confirmMessage = accion.includes('Desactivar') ? 
                    '¿Desactivar esta plantilla? Los nuevos contratos no la usarán.' :
                    '¿Activar esta plantilla? Se desactivarán otras plantillas del mismo tipo.';
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Mejorar interfaz con loading states
     */
    static initLoadingStates() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Guardando...';
                    submitBtn.disabled = true;
                    
                    // Restaurar después de 10 segundos como fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
    }
}

// ✅ AUTO-INICIALIZACIÓN GLOBAL MEJORADA
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando módulo de plantillas de contrato...');
    
    // Solo inicializar si estamos en una página de plantillas
    const editorContainer = document.getElementById('editorContenido');
    
    if (editorContainer) {
        // Esperar a que TinyMCE esté disponible
        const initEditor = () => {
            if (typeof tinymce !== 'undefined') {
                window.plantillasEditor = new PlantillasEditor({
                    debug: true, // Activar debug en desarrollo
                    previewRoute: window.location.origin + '/configuracion/plantillas-contrato/preview',
                    variablesRoute: window.location.origin + '/configuracion/plantillas-contrato/api/variables'
                });
            } else {
                console.warn('⏳ Esperando a que TinyMCE se cargue...');
                setTimeout(initEditor, 500);
            }
        };
        
        initEditor();
    }

    // Inicializar utilidades siempre
    PlantillasUtilidades.initFiltroCategoria();
    PlantillasUtilidades.initConfirmaciones();
    PlantillasUtilidades.initLoadingStates();
    
    console.log('✅ Módulo de plantillas inicializado');
});

// ✅ MANEJO DE ERRORES GLOBALES
window.addEventListener('error', function(e) {
    if (e.message.includes('tinymce') || e.message.includes('plantillas')) {
        console.error('❌ Error en el editor de plantillas:', e.error);
        
        // Mostrar error al usuario si hay un editor activo
        if (window.plantillasEditor) {
            window.plantillasEditor.mostrarError('Ha ocurrido un error inesperado en el editor');
        }
    }
});

// ✅ EXPORTAR PARA USO MODULAR
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PlantillasEditor, PlantillasUtilidades };
}