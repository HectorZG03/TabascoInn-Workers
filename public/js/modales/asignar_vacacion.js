/**
 * asignar_vacacion.js - Modal de Asignación de Vacaciones INDEPENDIENTE
 * Maneja exclusivamente la funcionalidad del modal de asignar vacaciones
 */
class AsignarVacacionModal {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.initialized = false;
        
        console.log(`📝 AsignarVacacionModal iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    init() {
        if (this.initialized) return;
        
        this.bindEvents();
        this.initialized = true;
        console.log('✅ Modal de asignar vacaciones inicializado');
    }

    bindEvents() {
        // Modal events
        $('#asignarVacacionesModal').on('show.bs.modal', () => this.initModal());
        $('#form-asignar-vacaciones').on('submit', (e) => this.handleSubmit(e));
        
        // Form interactions
        $('#dias_solicitados').on('input', () => this.calcularFechaFin());
        $('#fecha_inicio').on('change', () => this.calcularFechaFin());
        $('#observaciones').on('input', () => this.updateObservacionesCount());
        
        console.log('🔗 Eventos del modal vinculados correctamente');
    }

    // =================================
    // INICIALIZACIÓN DEL MODAL
    // =================================

    async initModal() {
        try {
            console.log('🔄 Inicializando modal de asignar vacaciones...');
            
            // Cargar días disponibles del trabajador
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/calcular-dias`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    $('#dias-disponibles').text(data.dias_restantes);
                    $('#max-dias-texto').text(data.dias_restantes);
                    $('#dias_solicitados').attr('max', data.dias_restantes);
                    $('#trabajador-antiguedad').text(data.antiguedad);
                    
                    console.log(`✅ Días disponibles cargados: ${data.dias_restantes}`);
                    
                    // Verificar si puede tomar vacaciones
                    if (!data.puede_tomar_vacaciones) {
                        this.showAlert('El trabajador no puede tomar vacaciones en este momento.', 'warning');
                    }
                }
            }
        } catch (error) {
            console.error('Error loading vacation data:', error);
            this.showAlert('Error al cargar información de vacaciones', 'danger');
        }
        
        this.resetForm();
    }

    resetForm() {
        $('#form-asignar-vacaciones')[0].reset();
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        $('#resumen-vacacion').hide();
        $('#alert-vacaciones').hide();
        this.updateObservacionesCount();
        
        console.log('📋 Formulario reseteado');
    }

    // =================================
    // CÁLCULOS Y VALIDACIONES
    // =================================

    calcularFechaFin() {
        const diasSolicitados = parseInt($('#dias_solicitados').val()) || 0;
        const fechaInicio = $('#fecha_inicio').val();
        
        console.log('🔢 Calculando fecha fin:', { diasSolicitados, fechaInicio });
        
        // Limpiar fecha fin si no hay datos suficientes
        if (!fechaInicio || diasSolicitados <= 0) {
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
            return;
        }
        
        try {
            const inicio = new Date(fechaInicio);
            const fin = new Date(inicio);
            fin.setDate(fin.getDate() + diasSolicitados - 1);
            
            // Formatear fecha para el input type="date" (YYYY-MM-DD)
            const fechaFinFormatted = fin.toISOString().split('T')[0];
            $('#fecha_fin').val(fechaFinFormatted);
            
            console.log('✅ Fecha fin calculada:', fechaFinFormatted);
            
            this.updateResumen();
        } catch (error) {
            console.error('Error calculating end date:', error);
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
        }
    }

    updateObservacionesCount() {
        const texto = $('#observaciones').val();
        $('#observaciones-count').text(texto.length);
    }

    updateResumen() {
        const diasSolicitados = $('#dias_solicitados').val();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        if (diasSolicitados && fechaInicio && fechaFin) {
            // Formatear fechas para mostrar (DD/MM/YYYY)
            const fechaInicioMostrar = this.formatearFechaParaMostrar(fechaInicio);
            const fechaFinMostrar = this.formatearFechaParaMostrar(fechaFin);
            
            $('#resumen-duracion').text(`${diasSolicitados} días`);
            $('#resumen-fechas').text(`${fechaInicioMostrar} - ${fechaFinMostrar}`);
            
            // Verificar si inicia hoy
            const hoy = new Date().toISOString().split('T')[0];
            const iniciaHoy = fechaInicio === hoy;
            $('#resumen-inicio-auto').text(iniciaHoy ? 'Sí (se iniciará automáticamente)' : 'No');
            
            $('#resumen-vacacion').show();
            console.log('📋 Resumen actualizado');
        } else {
            $('#resumen-vacacion').hide();
        }
    }

    formatearFechaParaMostrar(fechaISO) {
        if (!fechaISO) return '';
        
        try {
            // Convertir YYYY-MM-DD a DD/MM/YYYY
            const [year, month, day] = fechaISO.split('-');
            return `${day}/${month}/${year}`;
        } catch (error) {
            console.error('Error formatting date for display:', error);
            return fechaISO;
        }
    }

    // =================================
    // ENVÍO DEL FORMULARIO
    // =================================

    async handleSubmit(e) {
        e.preventDefault();
        
        console.log('📤 Enviando formulario de asignación...');
        
        try {
            this.setLoadingState(true);
            
            // Obtener datos del formulario
            const formData = new FormData($('#form-asignar-vacaciones')[0]);
            const data = Object.fromEntries(formData.entries());
            
            console.log('📋 Datos a enviar:', data);
            
            // Validación básica en el frontend
            if (!this.validarFormulario(data)) {
                this.setLoadingState(false);
                return;
            }
            
            // Enviar al servidor
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/asignar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            console.log('📥 Respuesta del servidor:', result);
            
            if (result.success) {
                // Cerrar modal
                $('#asignarVacacionesModal').modal('hide');
                
                // Notificar éxito al componente principal
                this.notifySuccess(result);
                
                // Mostrar notificación
                this.showNotification('success', 'Vacaciones asignadas correctamente');
                
                console.log('✅ Vacaciones asignadas exitosamente');
            } else {
                // Manejar errores de validación
                this.handleFormErrors(result.errors);
                this.showAlert(result.message || 'Error al asignar vacaciones', 'danger');
            }
        } catch (error) {
            console.error('❌ Error assigning vacation:', error);
            this.showAlert('Error de conexión al asignar vacaciones', 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    validarFormulario(data) {
        let isValid = true;
        
        // Validar días solicitados
        const dias = parseInt(data.dias_solicitados);
        if (!dias || dias <= 0) {
            this.showFieldError('dias_solicitados', 'Debe ingresar días válidos');
            isValid = false;
        }
        
        // Validar fechas
        if (!data.fecha_inicio) {
            this.showFieldError('fecha_inicio', 'La fecha de inicio es requerida');
            isValid = false;
        }
        
        if (!data.fecha_fin) {
            this.showFieldError('fecha_fin', 'La fecha de fin es requerida');
            isValid = false;
        }
        
        return isValid;
    }

    showFieldError(fieldId, message) {
        const $field = $(`#${fieldId}`);
        const $feedback = $field.siblings('.invalid-feedback');
        
        $field.addClass('is-invalid');
        if ($feedback.length === 0) {
            $field.after(`<div class="invalid-feedback">${message}</div>`);
        } else {
            $feedback.text(message);
        }
    }

    // =================================
    // MANEJO DE ESTADOS Y ERRORES
    // =================================

    setLoadingState(loading) {
        const $btn = $('#btn-asignar-vacaciones');
        
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
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        
        // Mostrar nuevos errores
        if (errors) {
            Object.keys(errors).forEach(field => {
                const $field = $(`#${field}`);
                const errorMessage = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                
                $field.addClass('is-invalid');
                
                let $feedback = $field.siblings('.invalid-feedback');
                if ($feedback.length === 0) {
                    $feedback = $('<div class="invalid-feedback"></div>');
                    $field.after($feedback);
                }
                $feedback.text(errorMessage);
            });
        }
    }

    showAlert(message, type) {
        const $alert = $('#alert-vacaciones');
        $alert.removeClass('alert-info alert-success alert-warning alert-danger')
              .addClass(`alert-${type}`)
              .find('#alert-mensaje').text(message);
        $alert.show();
    }

    // =================================
    // COMUNICACIÓN CON EL COMPONENTE PRINCIPAL
    // =================================

    notifySuccess(result) {
        // Disparar evento personalizado para que el componente principal recargue
        const event = new CustomEvent('vacacionAsignada', {
            detail: {
                vacacion: result.vacacion,
                trabajador_estatus: result.trabajador_estatus,
                message: result.message
            }
        });
        
        document.dispatchEvent(event);
        console.log('📡 Evento vacacionAsignada disparado');
    }

    showNotification(type, message) {
        // Usar el sistema de notificaciones del componente principal si existe
        if (window.vacacionesApp && window.vacacionesApp.showNotification) {
            window.vacacionesApp.showNotification(type, message);
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
        $('#asignarVacacionesModal').modal('show');
    }

    /**
     * Cerrar el modal programáticamente
     */
    close() {
        $('#asignarVacacionesModal').modal('hide');
    }

    /**
     * Verificar si el modal está abierto
     */
    isOpen() {
        return $('#asignarVacacionesModal').hasClass('show');
    }

    /**
     * Obtener datos del formulario actual
     */
    getFormData() {
        const formData = new FormData($('#form-asignar-vacaciones')[0]);
        return Object.fromEntries(formData.entries());
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
    console.log('🚀 Iniciando modal de asignar vacaciones...');
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.asignarVacacionModal = new AsignarVacacionModal(trabajadorId);
        console.log(`✅ Modal de asignar vacaciones iniciado para trabajador: ${trabajadorId}`);
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador para el modal');
    }
});