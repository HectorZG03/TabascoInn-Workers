/**
 * helper_pdf_firmas.js - Gestión de selección libre de 3 firmas para PDF de amortización
 */
class PDFConFirmasHelper {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.modal = null;
        this.form = null;
        
        console.log(`PDF Helper iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    init() {
        if (typeof AppRoutes === 'undefined') {
            console.error('AppRoutes no está disponible para PDF con firmas');
            return;
        }

        this.modal = document.getElementById('seleccionFirmasModal');
        this.form = document.getElementById('form-seleccion-firmas');
        
        if (!this.modal || !this.form) {
            console.error('Modal o formulario de selección de firmas no encontrado');
            return;
        }

        this.bindEvents();
        console.log('PDF Helper inicializado correctamente');
    }

    bindEvents() {
        // Evento del formulario
        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Escuchar cuando se abre el modal
        this.modal.addEventListener('show.bs.modal', () => this.loadModalData());
        
        // Escuchar clicks en botones de descarga PDF
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-download-pdf]') || e.target.closest('[data-download-pdf]')) {
                e.preventDefault();
                this.showModal();
            }
        });
    }

    showModal() {
        const bsModal = new bootstrap.Modal(this.modal);
        bsModal.show();
    }

    hideModal() {
        const bsModal = bootstrap.Modal.getInstance(this.modal);
        if (bsModal) {
            bsModal.hide();
        }
    }

    async loadModalData() {
        try {
            console.log('Cargando datos del modal...');
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/documentos-vacaciones/seleccion-firmas`);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.success) {
                this.updateModalData(result.data);
                console.log('Datos del modal cargados correctamente');
            } else {
                this.showAlert('error', result.message || 'Error al cargar datos del modal');
            }

        } catch (error) {
            console.error('Error cargando datos del modal:', error);
            this.showAlert('error', 'Error de conexión al cargar datos');
        }
    }

    updateModalData(data) {
        // Actualizar información del trabajador
        document.getElementById('trabajador-nombre').textContent = data.trabajador.nombre_completo;
        document.getElementById('trabajador-categoria').textContent = data.trabajador.categoria;
        document.getElementById('total-vacaciones-pendientes').textContent = data.vacaciones_pendientes;
        document.getElementById('total-dias-pendientes').textContent = data.total_dias;
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        // Limpiar errores previos
        this.clearValidationErrors();
        this.showAlert('info', 'Generando PDF, por favor espere...', false);
        this.setFormLoading(true);

        try {
            const formData = new FormData(this.form);
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/documentos-vacaciones/descargar-pdf`);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                if (result.download_url) {
                    this.showAlert('success', 'PDF generado correctamente. Iniciando descarga...', true);
                    
                    // Crear enlace temporal para descarga
                    const link = document.createElement('a');
                    link.href = result.download_url;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Cerrar modal después de un momento
                    setTimeout(() => {
                        this.hideModal();
                    }, 2000);
                } else {
                    this.showAlert('error', 'Error: No se pudo generar el enlace de descarga');
                }
            } else {
                this.handleValidationErrors(result);
            }

        } catch (error) {
            console.error('Error generando PDF:', error);
            this.showAlert('error', 'Error de conexión al generar PDF');
        } finally {
            this.setFormLoading(false);
        }
    }

    handleValidationErrors(result) {
        if (result.errors) {
            // Mostrar errores de validación en los campos
            Object.keys(result.errors).forEach(field => {
                const fieldElement = document.getElementById(field);
                if (fieldElement) {
                    fieldElement.classList.add('is-invalid');
                    const feedback = fieldElement.parentNode.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.textContent = result.errors[field][0];
                    }
                }
            });
        }
        
        this.showAlert('error', result.message || 'Error al generar PDF');
    }

    clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    setFormLoading(loading) {
        const submitBtn = document.getElementById('btn-generar-pdf');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        if (loading) {
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
        } else {
            submitBtn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
        }
    }

    showAlert(type, message, autoHide = true) {
        const alertContainer = document.getElementById('alert-seleccion-firmas');
        const alertMessage = document.getElementById('alert-mensaje-firmas');
        
        // Limpiar clases previas
        alertContainer.className = 'alert';
        
        // Agregar nueva clase según tipo
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'info' ? 'alert-info' : 'alert-danger';
        alertContainer.classList.add(alertClass);
        
        // Actualizar mensaje
        alertMessage.textContent = message;
        
        // Mostrar alerta
        alertContainer.style.display = 'block';
        
        // Auto ocultar si es necesario
        if (autoHide && type === 'success') {
            setTimeout(() => {
                alertContainer.style.display = 'none';
            }, 3000);
        }
    }

    openModal() {
        this.showModal();
    }

    isReady() {
        return this.modal && this.form && typeof AppRoutes !== 'undefined';
    }
}

// Inicialización automática
$(document).ready(function() {
    console.log('Iniciando helper de PDF con firmas libres...');
    
    if (typeof AppRoutes === 'undefined') {
        console.error('AppRoutes no está disponible para PDF con firmas');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.pdfConFirmasHelper = new PDFConFirmasHelper(trabajadorId);
        console.log(`Helper PDF con firmas libres iniciado para trabajador: ${trabajadorId}`);
    } else {
        console.error('No se pudo obtener el ID del trabajador para PDF con firmas');
    }
});