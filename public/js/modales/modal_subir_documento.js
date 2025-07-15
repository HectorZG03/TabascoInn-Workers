/**
 * modal_subir_documento.js - Modal para subir documentos con RUTAS DINÁMICAS
 * Maneja la subida de archivos PDF y asociación con vacaciones
 */
class SubirDocumentoModal {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.initialized = false;
        this.maxFileSize = 2 * 1024 * 1024; // 2MB
        
        console.log(`📤 SubirDocumentoModal iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    init() {
        if (this.initialized) return;
        
        // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no está disponible para el modal de subir documentos');
            return;
        }
        
        this.bindEvents();
        this.initialized = true;
        console.log('✅ Modal de subir documentos inicializado con rutas dinámicas');
    }

    bindEvents() {
        // Modal events
        $('#subirDocumentoModal').on('show.bs.modal', () => this.initModal());
        $('#form-subir-documento').on('submit', (e) => this.handleSubmit(e));
        
        // File input events
        $('#documento').on('change', (e) => this.handleFileSelect(e));
        $('#btn-clear-file').on('click', () => this.clearFile());
        
        // Checkbox events para vacaciones
        $('input[name="vacaciones_ids[]"]').on('change', () => this.updateResumenSeleccion());
        
        console.log('🔗 Eventos del modal vinculados correctamente');
    }

    // =================================
    // INICIALIZACIÓN DEL MODAL
    // =================================

    initModal() {
        console.log('🔄 Inicializando modal de subir documentos...');
        this.resetForm();
        this.updateResumenSeleccion();
    }

    resetForm() {
        $('#form-subir-documento')[0].reset();
        $('#form-subir-documento .is-invalid').removeClass('is-invalid');
        $('#form-subir-documento .is-valid').removeClass('is-valid');
        $('#resumen-seleccion').hide();
        $('#alert-subir-documento').hide();
        
        // Limpiar información del archivo
        this.clearFileInfo();
        
        console.log('📋 Formulario reseteado');
    }

    // =================================
    // MANEJO DE SELECCIÓN DE ARCHIVO
    // =================================

    handleFileSelect(e) {
        const file = e.target.files[0];
        const $fileInput = $('#documento');
        
        if (!file) {
            this.clearFileInfo();
            return;
        }
        
        // Validar archivo
        const errores = this.validateFile(file);
        
        if (errores.length > 0) {
            this.showFieldError('documento', errores[0]);
            $fileInput.val(''); // Limpiar selección
            this.clearFileInfo();
        } else {
            this.showFieldSuccess('documento');
            this.showFileInfo(file);
        }
    }

    validateFile(file) {
        const errores = [];
        
        // Validar tipo de archivo
        if (file.type !== 'application/pdf') {
            errores.push('Solo se permiten archivos PDF');
        }
        
        // Validar tamaño
        if (file.size > this.maxFileSize) {
            errores.push('El archivo no puede ser mayor a 2MB');
        }
        
        // Validar nombre
        if (file.name.length > 200) {
            errores.push('El nombre del archivo es demasiado largo');
        }
        
        return errores;
    }

    showFileInfo(file) {
        const sizeText = this.formatFileSize(file.size);
        const info = `
            <div class="file-info mt-2 p-2 bg-light border rounded">
                <small class="text-muted">
                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                    <strong>${file.name}</strong> (${sizeText})
                </small>
            </div>
        `;
        
        $('#documento').after(info);
    }

    clearFile() {
        const $fileInput = $('#documento');
        $fileInput.val('');
        $fileInput.removeClass('is-valid is-invalid');
        this.clearFileInfo();
        $fileInput.siblings('.invalid-feedback').remove();
        console.log('🗑️ Archivo limpiado');
    }

    clearFileInfo() {
        $('.file-info').remove();
    }

    formatFileSize(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }

    // =================================
    // MANEJO DE SELECCIÓN DE VACACIONES
    // =================================

    updateResumenSeleccion() {
        const $checkboxes = $('input[name="vacaciones_ids[]"]:checked');
        const totalSeleccionadas = $checkboxes.length;
        
        if (totalSeleccionadas > 0) {
            let diasTotal = 0;
            
            $checkboxes.each(function() {
                const $label = $(this).next('label');
                const $badge = $label.find('.badge');
                if ($badge.length) {
                    const diasText = $badge.text();
                    const dias = parseInt(diasText.match(/\d+/)[0]) || 0;
                    diasTotal += dias;
                }
            });
            
            $('#total-vacaciones-seleccionadas').text(totalSeleccionadas);
            $('#total-dias-seleccionados').text(diasTotal);
            $('#resumen-seleccion').show();
        } else {
            $('#resumen-seleccion').hide();
        }
    }

    // =================================
    // ENVÍO DEL FORMULARIO CON RUTAS DINÁMICAS
    // =================================

    async handleSubmit(e) {
        e.preventDefault();
        
        console.log('📤 Enviando formulario de subir documento con rutas dinámicas...');
        
        try {
            this.setLoadingState(true);
            
            // Validar formulario
            if (!this.validateForm()) {
                this.setLoadingState(false);
                return;
            }
            
            // Crear FormData
            const formData = new FormData($('#form-subir-documento')[0]);
            
            console.log('📋 Datos del formulario:', {
                archivo: formData.get('documento').name,
                vacaciones: formData.getAll('vacaciones_ids[]')
            });
            
            // ✅ USAR RUTAS DINÁMICAS PARA ENVIAR AL SERVIDOR
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/documentos-vacaciones/subir`);
            console.log('📤 Enviando a URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            console.log('📥 Respuesta del servidor:', result);
            
            if (result.success) {
                // Cerrar modal
                $('#subirDocumentoModal').modal('hide');
                
                // Notificar éxito al componente principal
                this.notifySuccess(result);
                
                // Mostrar notificación
                this.showNotification('success', 'Documento subido y asociado correctamente');
                
                console.log('✅ Documento subido exitosamente');
            } else {
                // Manejar errores de validación
                this.handleFormErrors(result.errors);
                this.showAlert(result.message || 'Error al subir documento', 'danger');
            }
        } catch (error) {
            console.error('❌ Error subiendo documento:', error);
            this.showAlert('Error de conexión al subir documento: ' + error.message, 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    validateForm() {
        let isValid = true;
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar archivo
        const file = $('#documento')[0].files[0];
        if (!file) {
            this.showFieldError('documento', 'Debe seleccionar un archivo');
            isValid = false;
        } else {
            const errores = this.validateFile(file);
            if (errores.length > 0) {
                this.showFieldError('documento', errores[0]);
                isValid = false;
            }
        }
        
        // Validar selección de vacaciones
        const vacacionesSeleccionadas = $('input[name="vacaciones_ids[]"]:checked').length;
        if (vacacionesSeleccionadas === 0) {
            this.showFieldError('vacaciones-error', 'Debe seleccionar al menos una vacación');
            isValid = false;
        }
        
        return isValid;
    }

    showFieldError(fieldId, message) {
        const $field = $(`#${fieldId}`);
        let $feedback = $field.siblings('.invalid-feedback');
        
        if (fieldId === 'vacaciones-error') {
            // Caso especial para vacaciones
            $feedback = $(`#${fieldId}`);
            $feedback.text(message).show();
        } else {
            $field.addClass('is-invalid');
            if ($feedback.length === 0) {
                $field.after(`<div class="invalid-feedback">${message}</div>`);
            } else {
                $feedback.text(message);
            }
        }
    }

    showFieldSuccess(fieldId) {
        const $field = $(`#${fieldId}`);
        $field.removeClass('is-invalid').addClass('is-valid');
        $field.siblings('.invalid-feedback').remove();
    }

    // =================================
    // MANEJO DE ESTADOS Y ERRORES
    // =================================

    setLoadingState(loading) {
        const $btn = $('#btn-subir-documento');
        
        if (loading) {
            $btn.find('.btn-text').hide();
            $btn.find('.btn-loading').show();
            $btn.prop('disabled', true);
        } else {
            $btn.find('.btn-loading').hide();
            $btn.find('.btn-text').show();
            $btn.prop('disabled', false);
        }
    }

    handleFormErrors(errors) {
        // Limpiar errores previos
        $('#form-subir-documento .is-invalid').removeClass('is-invalid');
        
        // Mostrar nuevos errores
        if (errors) {
            Object.keys(errors).forEach(field => {
                const $field = $(`#${field}`);
                const errorMessage = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                
                if (field === 'vacaciones_ids') {
                    this.showFieldError('vacaciones-error', errorMessage);
                } else {
                    this.showFieldError(field, errorMessage);
                }
            });
        }
    }

    showAlert(message, type) {
        const $alert = $('#alert-subir-documento');
        $alert.removeClass('alert-info alert-success alert-warning alert-danger')
              .addClass(`alert-${type}`)
              .find('#alert-mensaje-subir').text(message);
        $alert.show();
    }

    // =================================
    // COMUNICACIÓN CON EL COMPONENTE PRINCIPAL
    // =================================

    notifySuccess(result) {
        // Disparar evento personalizado para que el componente principal recargue
        const event = new CustomEvent('documentoSubido', {
            detail: {
                documento: result.documento,
                message: result.message
            }
        });
        
        document.dispatchEvent(event);
        console.log('📡 Evento documentoSubido disparado');
    }

    showNotification(type, message) {
        // Usar el sistema de notificaciones del componente principal si existe
        if (window.documentosVacacionesApp && window.documentosVacacionesApp.showNotification) {
            window.documentosVacacionesApp.showNotification(type, message);
        } else {
            // Fallback: crear toast propio
            this.createToast(type, message);
        }
    }

    createToast(type, message) {
        const toastType = type === 'success' ? 'success' : 'danger';
        const toast = $(`
            <div class="toast align-items-center text-bg-${toastType} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        let container = $('#toast-container');
        if (!container.length) {
            container = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
            $('body').append(container);
        }
        
        container.append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        toast.on('hidden.bs.toast', () => toast.remove());
    }

    // =================================
    // MÉTODOS PÚBLICOS
    // =================================

    /**
     * Abrir el modal programáticamente
     */
    open() {
        $('#subirDocumentoModal').modal('show');
    }

    /**
     * Cerrar el modal programáticamente
     */
    close() {
        $('#subirDocumentoModal').modal('hide');
    }

    /**
     * Verificar si el modal está abierto
     */
    isOpen() {
        return $('#subirDocumentoModal').hasClass('show');
    }

    /**
     * Resetear el formulario externamente
     */
    reset() {
        this.resetForm();
    }
}

// =================================
// AUTO-INICIALIZACIÓN
// =================================

// Inicializar automáticamente cuando el DOM esté listo
$(document).ready(function() {
    console.log('🚀 Iniciando modal de subir documentos con rutas dinámicas...');
    
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ CRÍTICO: AppRoutes no está disponible para el modal de documentos');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.subirDocumentoModal = new SubirDocumentoModal(trabajadorId);
        console.log(`✅ Modal de subir documentos con rutas dinámicas iniciado para trabajador: ${trabajadorId}`);
        console.log(`🔧 Base URL: ${AppRoutes.getBaseUrl()}`);
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador para el modal');
    }
});