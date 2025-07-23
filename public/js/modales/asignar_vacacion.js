/**
 * asignar_vacacion.js - Modal Simplificado con Formato Global
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
        
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no disponible');
            return;
        }
        
        this.bindEvents();
        this.setupValidaciones();
        this.initialized = true;
        console.log('✅ Modal inicializado');
    }

    bindEvents() {
        $('#asignarVacacionesModal').on('show.bs.modal', () => this.initModal());
        $('#form-asignar-vacaciones').on('submit', (e) => this.handleSubmit(e));
        $('#dias_solicitados').on('input', () => this.calcularFechaFin());
        $('#fecha_inicio').on('input blur', () => this.calcularFechaFin());
        $('#observaciones').on('input', () => this.updateObservacionesCount());
    }

    setupValidaciones() {
        if (!window.FormatoGlobal) return;
        
        const originalValidar = window.FormatoGlobal.validarRestriccionesFecha;
        
        window.FormatoGlobal.validarRestriccionesFecha = (campo, fecha) => {
            const errorOriginal = originalValidar.call(window.FormatoGlobal, campo, fecha);
            if (errorOriginal) return errorOriginal;
            
            // Validaciones específicas para vacaciones
            if (campo.id === 'fecha_inicio' && campo.closest('#asignarVacacionesModal')) {
                const fechaObj = window.FormatoGlobal.convertirFechaADate(fecha);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaObj < hoy) return 'Las vacaciones no pueden iniciarse en el pasado';
            }
            
            if (campo.id === 'fecha_fin' && campo.closest('#asignarVacacionesModal')) {
                const fechaInicio = $('#fecha_inicio').val();
                if (fechaInicio && window.FormatoGlobal.validarFormatoFecha(fechaInicio)) {
                    const fechaInicioObj = window.FormatoGlobal.convertirFechaADate(fechaInicio);
                    const fechaFinObj = window.FormatoGlobal.convertirFechaADate(fecha);
                    
                    if (fechaFinObj <= fechaInicioObj) return 'La fecha de fin debe ser posterior al inicio';
                }
            }
            
            return null;
        };
    }

    // ✅ INICIALIZACIÓN DEL MODAL SIMPLIFICADA
    async initModal() {
        try {
            console.log('🔄 Cargando datos del modal...');
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/calcular-dias`);
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    $('#dias-disponibles').text(data.dias_restantes);
                    $('#max-dias-texto').text(data.dias_restantes);
                    $('#dias_solicitados').attr('max', data.dias_restantes);
                    $('#trabajador-antiguedad').text(data.antiguedad);
                    
                    if (!data.puede_tomar_vacaciones) {
                        this.showAlert('El trabajador no puede tomar vacaciones en este momento.', 'warning');
                    }
                } else {
                    throw new Error(data.message || 'Error al obtener días disponibles');
                }
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('Error loading vacation data:', error);
            this.showAlert('Error al cargar información: ' + error.message, 'danger');
        }
        
        this.resetForm();
    }

    resetForm() {
        $('#form-asignar-vacaciones')[0].reset();
        $('#form-asignar-vacaciones .is-invalid, #form-asignar-vacaciones .is-valid').removeClass('is-invalid is-valid');
        $('#resumen-vacacion, #alert-vacaciones').hide();
        this.updateObservacionesCount();
    }

    // ✅ CONVERSIÓN DE FECHAS SIMPLIFICADA
    convertirDDMMYYYYaISO(fechaDDMMYYYY) {
        if (!fechaDDMMYYYY || !window.FormatoGlobal.validarFormatoFecha(fechaDDMMYYYY)) return null;
        
        const [dia, mes, año] = fechaDDMMYYYY.split('/').map(Number);
        const fecha = new Date(año, mes - 1, dia);
        
        if (fecha.getFullYear() !== año || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia) return null;
        
        return `${año}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    }

    // ✅ CÁLCULO DE FECHAS SIMPLIFICADO
    async calcularFechaFin() {
        const diasSolicitados = parseInt($('#dias_solicitados').val()) || 0;
        const fechaInicioDDMM = $('#fecha_inicio').val();
        
        if (!fechaInicioDDMM || diasSolicitados <= 0 || !window.FormatoGlobal.validarFormatoFecha(fechaInicioDDMM)) {
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
            return;
        }
        
        try {
            const fechaInicioISO = this.convertirDDMMYYYYaISO(fechaInicioDDMM);
            if (!fechaInicioISO) return;
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/calcular-fechas`);
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    fecha_inicio: fechaInicioISO,
                    dias_solicitados: diasSolicitados
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    const calculo = result.calculo;
                    $('#fecha_fin').val(calculo.fecha_fin_formatted);
                    this.updateResumen(calculo);
                } else {
                    throw new Error(result.message);
                }
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
            
        } catch (error) {
            console.error('Error calculando fechas:', error);
            this.calcularFechaFinTradicional(diasSolicitados, fechaInicioDDMM);
        }
    }

    // ✅ ACTUALIZAR RESUMEN SIMPLIFICADO
    updateResumen(calculo) {
        const diasSolicitados = $('#dias_solicitados').val();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        if (diasSolicitados && fechaInicio && fechaFin) {
            $('#resumen-duracion').text(`${diasSolicitados} días laborables`);
            $('#resumen-fechas').text(`${fechaInicio} - ${fechaFin}`);
            
            if (calculo.explicacion) {
                const infoAdicional = `<div class="mt-2 small text-muted"><i class="bi bi-info-circle"></i> ${calculo.explicacion}</div>`;
                $('#resumen-vacacion .card-body').append(infoAdicional);
            }
            
            $('#resumen-vacacion').show();
        } else {
            $('#resumen-vacacion').hide();
        }
    }

    calcularFechaFinTradicional(diasSolicitados, fechaInicioDDMM) {
        try {
            const fechaInicioDate = window.FormatoGlobal.convertirFechaADate(fechaInicioDDMM);
            if (!fechaInicioDate) return;
            
            const fechaFinDate = new Date(fechaInicioDate);
            fechaFinDate.setDate(fechaFinDate.getDate() + diasSolicitados - 1);
            
            const fechaFinDDMM = `${String(fechaFinDate.getDate()).padStart(2, '0')}/${String(fechaFinDate.getMonth() + 1).padStart(2, '0')}/${fechaFinDate.getFullYear()}`;
            
            $('#fecha_fin').val(fechaFinDDMM);
            $('#resumen-duracion').text(`${diasSolicitados} días calendario`);
            $('#resumen-fechas').text(`${fechaInicioDDMM} - ${fechaFinDDMM}`);
            $('#resumen-vacacion').show();
            
        } catch (error) {
            console.error('Error en cálculo tradicional:', error);
            $('#fecha_fin').val('');
            $('#resumen-vacacion').hide();
        }
    }

    updateObservacionesCount() {
        $('#observaciones-count').text($('#observaciones').val().length);
    }

    // ✅ ENVÍO SIMPLIFICADO
    async handleSubmit(e) {
        e.preventDefault();
        
        try {
            this.setLoadingState(true);
            
            const formData = new FormData($('#form-asignar-vacaciones')[0]);
            const data = Object.fromEntries(formData.entries());
            
            if (!this.validarFormulario(data)) {
                this.setLoadingState(false);
                return;
            }
            
            const fechaInicioISO = this.convertirDDMMYYYYaISO(data.fecha_inicio);
            const fechaFinISO = this.convertirDDMMYYYYaISO(data.fecha_fin);
            
            if (!fechaInicioISO || !fechaFinISO) {
                this.showAlert('Error en el formato de fechas', 'danger');
                this.setLoadingState(false);
                return;
            }
            
            const dataParaBackend = { ...data, fecha_inicio: fechaInicioISO, fecha_fin: fechaFinISO };
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/asignar`);
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
            
            if (result.success) {
                $('#asignarVacacionesModal').modal('hide');
                this.notifySuccess(result);
                this.showNotification('success', 'Vacaciones asignadas correctamente');
            } else {
                this.handleFormErrors(result.errors);
                this.showAlert(result.message || 'Error al asignar vacaciones', 'danger');
            }
            
        } catch (error) {
            console.error('Error assigning vacation:', error);
            this.showAlert('Error de conexión: ' + error.message, 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    // ✅ VALIDACIÓN SIMPLIFICADA
    validarFormulario(data) {
        let isValid = true;
        
        const dias = parseInt(data.dias_solicitados);
        if (!dias || dias <= 0) {
            this.showFieldError('dias_solicitados', 'Debe ingresar días válidos');
            isValid = false;
        }
        
        if (!data.fecha_inicio || !window.FormatoGlobal.validarFormatoFecha(data.fecha_inicio)) {
            this.showFieldError('fecha_inicio', 'Fecha de inicio requerida (DD/MM/YYYY)');
            isValid = false;
        }
        
        if (!data.fecha_fin || !window.FormatoGlobal.validarFormatoFecha(data.fecha_fin)) {
            this.showFieldError('fecha_fin', 'Fecha de fin requerida (DD/MM/YYYY)');
            isValid = false;
        }
        
        return isValid;
    }

    showFieldError(fieldId, message) {
        const $field = $(`#${fieldId}`);
        $field.addClass('is-invalid');
        
        let $feedback = $field.siblings('.invalid-feedback');
        if ($feedback.length === 0) {
            $feedback = $('<div class="invalid-feedback"></div>');
            $field.after($feedback);
        }
        $feedback.text(message);
    }

    // ✅ UTILIDADES CONSOLIDADAS
    setLoadingState(loading) {
        const $btn = $('#btn-asignar-vacaciones');
        $btn.find('.btn-text').toggle(!loading);
        $btn.find('.btn-loading').toggle(loading);
        $btn.prop('disabled', loading);
    }

    handleFormErrors(errors) {
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        
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
        $('#alert-vacaciones')
            .removeClass('alert-info alert-success alert-warning alert-danger')
            .addClass(`alert-${type}`)
            .find('#alert-mensaje').text(message);
        $('#alert-vacaciones').show();
    }

    notifySuccess(result) {
        document.dispatchEvent(new CustomEvent('vacacionAsignada', {
            detail: {
                vacacion: result.vacacion,
                trabajador_estatus: result.trabajador_estatus,
                message: result.message
            }
        }));
    }

    showNotification(type, message) {
        if (window.vacacionesApp?.showNotification) {
            window.vacacionesApp.showNotification(type, message);
        } else {
            this.createToast(type, message);
        }
    }

    createToast(type, message) {
        const toastType = type === 'success' ? 'success' : 'danger';
        const toast = $(`
            <div class="toast align-items-center text-bg-${toastType} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        let container = $('#toast-container');
        if (!container.length) {
            container = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
            $('body').append(container);
        }
        
        container.append(toast);
        new bootstrap.Toast(toast[0]).show();
        toast.on('hidden.bs.toast', () => toast.remove());
    }

    // ✅ MÉTODOS PÚBLICOS
    open() { $('#asignarVacacionesModal').modal('show'); }
    close() { $('#asignarVacacionesModal').modal('hide'); }
    isOpen() { return $('#asignarVacacionesModal').hasClass('show'); }
    reset() { this.resetForm(); }
}

// ✅ INICIALIZACIÓN SIMPLIFICADA
$(document).ready(function() {
    console.log('🚀 Iniciando modal de vacaciones...');
    
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no disponible');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    if (trabajadorId) {
        if (window.FormatoGlobal) {
            window.asignarVacacionModal = new AsignarVacacionModal(trabajadorId);
            console.log(`✅ Modal iniciado para trabajador: ${trabajadorId}`);
        } else {
            console.error('❌ FormatoGlobal no disponible');
        }
    } else {
        console.error('❌ ID trabajador no encontrado');
    }
});