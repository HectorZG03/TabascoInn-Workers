/**
 * asignar_vacacion.js - Modal con FORMATO GLOBAL y RUTAS DINÁMICAS integrado
 * Maneja fechas DD/MM/YYYY en frontend, envía YYYY-MM-DD al backend
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
        
        // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no está disponible para el modal de asignar vacaciones');
            return;
        }
        
        this.bindEvents();
        this.setupFormatoGlobalValidations();
        this.initialized = true;
        console.log('✅ Modal de asignar vacaciones inicializado con formato global y rutas dinámicas');
    }

    bindEvents() {
        // Modal events
        $('#asignarVacacionesModal').on('show.bs.modal', () => this.initModal());
        $('#form-asignar-vacaciones').on('submit', (e) => this.handleSubmit(e));
        
        // Form interactions
        $('#dias_solicitados').on('input', () => this.calcularFechaFin());
        $('#fecha_inicio').on('input blur', () => this.calcularFechaFin());
        $('#observaciones').on('input', () => this.updateObservacionesCount());
        
        console.log('🔗 Eventos del modal vinculados correctamente');
    }

    // =================================
    // CONFIGURAR VALIDACIONES ESPECÍFICAS DE VACACIONES
    // =================================

    setupFormatoGlobalValidations() {
        // Extender las validaciones del formato global para fechas de vacaciones
        if (window.FormatoGlobal) {
            // Backup de la función original
            const originalValidarRestricciones = window.FormatoGlobal.validarRestriccionesFecha;
            
            // Extender con validaciones de vacaciones
            window.FormatoGlobal.validarRestriccionesFecha = (campo, fecha) => {
                // Aplicar validaciones originales primero
                const errorOriginal = originalValidarRestricciones.call(window.FormatoGlobal, campo, fecha);
                if (errorOriginal) return errorOriginal;
                
                // Validaciones específicas para vacaciones
                if (campo.id === 'fecha_inicio' && campo.closest('#asignarVacacionesModal')) {
                    const fechaObj = window.FormatoGlobal.convertirFechaADate(fecha);
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    
                    if (fechaObj < hoy) {
                        return 'Las vacaciones no pueden iniciarse en el pasado';
                    }
                }
                
                if (campo.id === 'fecha_fin' && campo.closest('#asignarVacacionesModal')) {
                    const fechaInicio = $('#fecha_inicio').val();
                    if (fechaInicio && window.FormatoGlobal.validarFormatoFecha(fechaInicio)) {
                        const fechaInicioObj = window.FormatoGlobal.convertirFechaADate(fechaInicio);
                        const fechaFinObj = window.FormatoGlobal.convertirFechaADate(fecha);
                        
                        if (fechaFinObj <= fechaInicioObj) {
                            return 'La fecha de fin debe ser posterior al inicio';
                        }
                    }
                }
                
                return null;
            };
            
            console.log('✅ Validaciones de vacaciones integradas con formato global');
        }
    }

    // =================================
    // INICIALIZACIÓN DEL MODAL CON RUTAS DINÁMICAS
    // =================================

    async initModal() {
        try {
            console.log('🔄 Inicializando modal de asignar vacaciones...');
            
            // ✅ USAR RUTAS DINÁMICAS PARA CARGAR DÍAS DISPONIBLES
            // ❌ ANTES: const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/calcular-dias`, {
            // ✅ AHORA: Usar AppRoutes
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/calcular-dias`);
            console.log('🔄 Cargando días disponibles desde:', url);
            
            const response = await fetch(url, {
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
                } else {
                    throw new Error(data.message || 'Error al obtener días disponibles');
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Error loading vacation data:', error);
            this.showAlert('Error al cargar información de vacaciones: ' + error.message, 'danger');
        }
        
        this.resetForm();
    }

    resetForm() {
        $('#form-asignar-vacaciones')[0].reset();
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        $('#form-asignar-vacaciones .is-valid').removeClass('is-valid');
        $('#resumen-vacacion').hide();
        $('#alert-vacaciones').hide();
        this.updateObservacionesCount();
        
        console.log('📋 Formulario reseteado');
    }

    // =================================
    // FUNCIONES DE CONVERSIÓN DE FECHAS
    // =================================

    /**
     * Convertir fecha DD/MM/YYYY a YYYY-MM-DD (para backend)
     */
    convertirDDMMYYYYaISO(fechaDDMMYYYY) {
        if (!fechaDDMMYYYY || !window.FormatoGlobal.validarFormatoFecha(fechaDDMMYYYY)) {
            return null;
        }
        
        const [dia, mes, año] = fechaDDMMYYYY.split('/').map(Number);
        
        // Validar que la fecha sea válida
        const fecha = new Date(año, mes - 1, dia);
        if (fecha.getFullYear() !== año || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia) {
            return null;
        }
        
        // Formatear a YYYY-MM-DD
        const mesStr = String(mes).padStart(2, '0');
        const diaStr = String(dia).padStart(2, '0');
        
        return `${año}-${mesStr}-${diaStr}`;
    }

    /**
     * Convertir fecha YYYY-MM-DD a DD/MM/YYYY (para mostrar)
     */
    convertirISOaDDMMYYYY(fechaISO) {
        if (!fechaISO) return '';
        
        try {
            const [year, month, day] = fechaISO.split('-');
            return `${day}/${month}/${year}`;
        } catch (error) {
            console.error('Error converting ISO to DD/MM/YYYY:', error);
            return fechaISO;
        }
    }

    // =================================
    // CÁLCULOS CON FORMATO DD/MM/YYYY
    // =================================

    calcularFechaFin() {
        const diasSolicitados = parseInt($('#dias_solicitados').val()) || 0;
        const fechaInicioDDMM = $('#fecha_inicio').val();
        
        console.log('🔢 Calculando fecha fin:', { diasSolicitados, fechaInicioDDMM });
        
        // Limpiar fecha fin si no hay datos suficientes
        if (!fechaInicioDDMM || diasSolicitados <= 0) {
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
            return;
        }
        
        // Validar formato de fecha inicio
        if (!window.FormatoGlobal.validarFormatoFecha(fechaInicioDDMM)) {
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
            return;
        }
        
        try {
            // Convertir DD/MM/YYYY a Date object
            const fechaInicioDate = window.FormatoGlobal.convertirFechaADate(fechaInicioDDMM);
            if (!fechaInicioDate) {
                $('#fecha_fin').val('');
                $('#resumen-vacacion').hide();
                return;
            }
            
            // Calcular fecha fin
            const fechaFinDate = new Date(fechaInicioDate);
            fechaFinDate.setDate(fechaFinDate.getDate() + diasSolicitados - 1);
            
            // Convertir de vuelta a DD/MM/YYYY
            const dia = String(fechaFinDate.getDate()).padStart(2, '0');
            const mes = String(fechaFinDate.getMonth() + 1).padStart(2, '0');
            const año = fechaFinDate.getFullYear();
            const fechaFinDDMM = `${dia}/${mes}/${año}`;
            
            $('#fecha_fin').val(fechaFinDDMM);
            
            console.log('✅ Fecha fin calculada:', fechaFinDDMM);
            
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
        
        if (diasSolicitados && fechaInicio && fechaFin && 
            window.FormatoGlobal.validarFormatoFecha(fechaInicio) && 
            window.FormatoGlobal.validarFormatoFecha(fechaFin)) {
            
            $('#resumen-duracion').text(`${diasSolicitados} días`);
            $('#resumen-fechas').text(`${fechaInicio} - ${fechaFin}`);        
            $('#resumen-vacacion').show();
            console.log('📋 Resumen actualizado');
        } else {
            $('#resumen-vacacion').hide();
        }
    }

    // =================================
    // ENVÍO DEL FORMULARIO CON CONVERSIÓN Y RUTAS DINÁMICAS
    // =================================

    async handleSubmit(e) {
        e.preventDefault();
        
        console.log('📤 Enviando formulario de asignación con rutas dinámicas...');
        
        try {
            this.setLoadingState(true);
            
            // Obtener datos del formulario
            const formData = new FormData($('#form-asignar-vacaciones')[0]);
            const data = Object.fromEntries(formData.entries());
            
            console.log('📋 Datos originales (DD/MM/YYYY):', data);
            
            // Validación básica en el frontend
            if (!this.validarFormulario(data)) {
                this.setLoadingState(false);
                return;
            }
            
            // ✅ CONVERTIR FECHAS DD/MM/YYYY A YYYY-MM-DD PARA EL BACKEND
            const fechaInicioISO = this.convertirDDMMYYYYaISO(data.fecha_inicio);
            const fechaFinISO = this.convertirDDMMYYYYaISO(data.fecha_fin);
            
            if (!fechaInicioISO || !fechaFinISO) {
                this.showAlert('Error en el formato de fechas', 'danger');
                this.setLoadingState(false);
                return;
            }
            
            // Preparar datos para el backend con fechas en formato ISO
            const dataParaBackend = {
                ...data,
                fecha_inicio: fechaInicioISO,
                fecha_fin: fechaFinISO
            };
            
            console.log('📤 Datos para backend (YYYY-MM-DD):', dataParaBackend);
            
            // ✅ USAR RUTAS DINÁMICAS PARA ENVIAR AL SERVIDOR
            // ❌ ANTES: const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/asignar`, {
            // ✅ AHORA: Usar AppRoutes
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/asignar`);
            console.log('📤 Enviando a URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(dataParaBackend)
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
            this.showAlert('Error de conexión al asignar vacaciones: ' + error.message, 'danger');
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
        
        // Validar fechas con formato global
        if (!data.fecha_inicio || !window.FormatoGlobal.validarFormatoFecha(data.fecha_inicio)) {
            this.showFieldError('fecha_inicio', 'La fecha de inicio es requerida y debe tener formato DD/MM/YYYY');
            isValid = false;
        }
        
        if (!data.fecha_fin || !window.FormatoGlobal.validarFormatoFecha(data.fecha_fin)) {
            this.showFieldError('fecha_fin', 'La fecha de fin es requerida y debe tener formato DD/MM/YYYY');
            isValid = false;
        }
        
        // Validar que las fechas sean válidas como objetos Date
        if (data.fecha_inicio && window.FormatoGlobal.validarFormatoFecha(data.fecha_inicio)) {
            const fechaInicio = window.FormatoGlobal.convertirFechaADate(data.fecha_inicio);
            if (!fechaInicio) {
                this.showFieldError('fecha_inicio', 'Fecha de inicio inválida');
                isValid = false;
            }
        }
        
        if (data.fecha_fin && window.FormatoGlobal.validarFormatoFecha(data.fecha_fin)) {
            const fechaFin = window.FormatoGlobal.convertirFechaADate(data.fecha_fin);
            if (!fechaFin) {
                this.showFieldError('fecha_fin', 'Fecha de fin inválida');
                isValid = false;
            }
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
     * Obtener datos del formulario actual (en formato DD/MM/YYYY)
     */
    getFormData() {
        const formData = new FormData($('#form-asignar-vacaciones')[0]);
        return Object.fromEntries(formData.entries());
    }

    /**
     * Obtener datos del formulario para backend (en formato YYYY-MM-DD)
     */
    getFormDataForBackend() {
        const data = this.getFormData();
        return {
            ...data,
            fecha_inicio: this.convertirDDMMYYYYaISO(data.fecha_inicio),
            fecha_fin: this.convertirDDMMYYYYaISO(data.fecha_fin)
        };
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
    console.log('🚀 Iniciando modal de asignar vacaciones con formato global y rutas dinámicas...');
    
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ CRÍTICO: AppRoutes no está disponible para el modal de vacaciones');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        // Verificar que el formato global esté disponible
        if (window.FormatoGlobal) {
            window.asignarVacacionModal = new AsignarVacacionModal(trabajadorId);
            console.log(`✅ Modal con formato global y rutas dinámicas iniciado para trabajador: ${trabajadorId}`);
            console.log(`🔧 Base URL: ${AppRoutes.getBaseUrl()}`);
        } else {
            console.error('❌ FormatoGlobal no está disponible. Asegúrate de incluir formato-global.js');
        }
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador para el modal');
    }
});