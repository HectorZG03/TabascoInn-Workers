/**
 * asignar_vacacion.js - Modal REFACTORIZADO
 * Entrada manual de año y período + Sin restricciones de fechas
 */
class AsignarVacacionModal {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.initialized = false;
        
        console.log(`📝 AsignarVacacionModal REFACTORIZADO iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    init() {
        if (this.initialized) return;
        
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no disponible');
            return;
        }
        
        this.bindEvents();
        this.setupValidacionesRefactorizadas();
        this.initialized = true;
        console.log('✅ Modal refactorizado inicializado');
    }

    bindEvents() {
        $('#asignarVacacionesModal').on('show.bs.modal', () => this.initModal());
        $('#form-asignar-vacaciones').on('submit', (e) => this.handleSubmit(e));
        
        // ✅ NUEVOS EVENT LISTENERS
        $('#dias_solicitados').on('input', () => this.updateResumen());
        $('#fecha_inicio').on('input blur', () => this.updateResumen());
        $('#fecha_fin').on('input blur', () => this.updateResumen());
        $('#año_correspondiente').on('input', () => this.updateResumen());
        $('#periodo_vacacional').on('input', () => this.updateResumen());
        $('#dias_correspondientes').on('input', () => this.updateResumen());
        $('#observaciones').on('input', () => this.updateObservacionesCount());
        
        // ✅ BOTONES DE UTILIDAD
        $('#btn-calcular-fecha-fin').on('click', () => this.calcularFechaFin());
        $('#btn-generar-periodo').on('click', () => this.generarPeriodo());
        $('#btn-usar-año-actual').on('click', () => this.usarAñoActual());
    }

    // ✅ VALIDACIONES REFACTORIZADAS - SIN RESTRICCIONES DE FECHAS
    setupValidacionesRefactorizadas() {
        if (!window.FormatoGlobal) return;
        
        // ✅ OVERRIDE: Eliminar validaciones de fechas pasadas
        const originalValidar = window.FormatoGlobal.validarRestriccionesFecha;
        
        window.FormatoGlobal.validarRestriccionesFecha = (campo, fecha) => {
            // ✅ PARA VACACIONES: Solo validar formato, NO restricciones temporales
            if (campo.closest('#asignarVacacionesModal')) {
                console.log('🔄 Validación de vacaciones: Solo formato, sin restricciones temporales');
                return null; // Sin restricciones de fecha para vacaciones
            }
            
            // Para otros modales, usar validación original
            return originalValidar.call(window.FormatoGlobal, campo, fecha);
        };

        console.log('✅ Validaciones refactorizadas aplicadas');
    }

    // ✅ INICIALIZACIÓN SIMPLIFICADA DEL MODAL
    async initModal() {
        try {
            console.log('🔄 Cargando datos del modal refactorizado...');
            
            // Solo cargar datos básicos, sin restricciones
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/calcular-dias`);
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    $('#dias-disponibles').text(data.dias_restantes);
                    $('#trabajador-antiguedad').text(data.antiguedad);
                    $('#dias_correspondientes').val(data.dias_correspondientes || 6);
                } else {
                    console.warn('⚠️ Error en datos:', data.message);
                }
            } else {
                console.warn('⚠️ Error HTTP:', response.status);
            }
        } catch (error) {
            console.error('Error loading vacation data:', error);
            // No mostramos error porque ahora es más flexible
        }
        
        this.resetForm();
        this.setupDefaultValues();
    }

    // ✅ NUEVO: Configurar valores por defecto
    setupDefaultValues() {
        const añoActual = new Date().getFullYear();
        const periodoDefault = `${añoActual}-${añoActual + 1}`;
        
        $('#año_correspondiente').val(añoActual);
        $('#periodo_vacacional').val(periodoDefault);
        
        console.log(`✅ Valores por defecto: Año ${añoActual}, Período ${periodoDefault}`);
    }

    resetForm() {
        $('#form-asignar-vacaciones')[0].reset();
        $('#form-asignar-vacaciones .is-invalid, #form-asignar-vacaciones .is-valid').removeClass('is-invalid is-valid');
        $('#resumen-vacacion, #alert-vacaciones').hide();
        this.updateObservacionesCount();
    }

    // ✅ CONVERSIÓN DE FECHAS (sin restricciones)
    convertirDDMMYYYYaISO(fechaDDMMYYYY) {
        if (!fechaDDMMYYYY || !window.FormatoGlobal.validarFormatoFecha(fechaDDMMYYYY)) return null;
        
        const [dia, mes, año] = fechaDDMMYYYY.split('/').map(Number);
        const fecha = new Date(año, mes - 1, dia);
        
        if (fecha.getFullYear() !== año || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia) return null;
        
        return `${año}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    }

    // ✅ CÁLCULO DE FECHA FIN REFACTORIZADO
    async calcularFechaFin() {
        const diasSolicitados = parseInt($('#dias_solicitados').val()) || 0;
        const fechaInicioDDMM = $('#fecha_inicio').val();
        
        if (!fechaInicioDDMM || diasSolicitados <= 0 || !window.FormatoGlobal.validarFormatoFecha(fechaInicioDDMM)) {
            this.showAlert('Ingrese una fecha de inicio válida y días solicitados', 'warning');
            return;
        }
        
        try {
            const fechaInicioISO = this.convertirDDMMYYYYaISO(fechaInicioDDMM);
            if (!fechaInicioISO) {
                this.showAlert('Formato de fecha de inicio inválido', 'warning');
                return;
            }
            
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
                    this.updateResumen();
                    this.showAlert(`Fecha fin calculada: ${calculo.fecha_fin_formatted}`, 'success');
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

    calcularFechaFinTradicional(diasSolicitados, fechaInicioDDMM) {
        try {
            const fechaInicioDate = window.FormatoGlobal.convertirFechaADate(fechaInicioDDMM);
            if (!fechaInicioDate) return;
            
            const fechaFinDate = new Date(fechaInicioDate);
            fechaFinDate.setDate(fechaFinDate.getDate() + diasSolicitados - 1);
            
            const fechaFinDDMM = `${String(fechaFinDate.getDate()).padStart(2, '0')}/${String(fechaFinDate.getMonth() + 1).padStart(2, '0')}/${fechaFinDate.getFullYear()}`;
            
            $('#fecha_fin').val(fechaFinDDMM);
            this.updateResumen();
            this.showAlert(`Fecha fin calculada (tradicional): ${fechaFinDDMM}`, 'info');
            
        } catch (error) {
            console.error('Error en cálculo tradicional:', error);
            this.showAlert('Error al calcular fecha fin', 'danger');
        }
    }

    // ✅ NUEVO: Generar período automáticamente
    generarPeriodo() {
        const año = parseInt($('#año_correspondiente').val());
        if (!año || año < 2000 || año > 2050) {
            this.showAlert('Ingrese un año válido primero', 'warning');
            return;
        }
        
        const periodo = `${año}-${año + 1}`;
        $('#periodo_vacacional').val(periodo);
        this.updateResumen();
        this.showAlert(`Período generado: ${periodo}`, 'success');
    }

    // ✅ NUEVO: Usar año actual
    usarAñoActual() {
        const añoActual = new Date().getFullYear();
        $('#año_correspondiente').val(añoActual);
        this.generarPeriodo();
    }

    // ✅ ACTUALIZAR RESUMEN REFACTORIZADO
    updateResumen() {
        const año = $('#año_correspondiente').val();
        const periodo = $('#periodo_vacacional').val();
        const diasSolicitados = $('#dias_solicitados').val();
        const diasCorrespondientes = $('#dias_correspondientes').val();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        if (año || periodo || diasSolicitados || fechaInicio) {
            $('#resumen-año').text(año || '-');
            $('#resumen-periodo').text(periodo || '-');
            $('#resumen-duracion').text(diasSolicitados ? `${diasSolicitados} días` : '0 días');
            $('#resumen-dias-lft').text(diasCorrespondientes || '0');
            
            if (fechaInicio && fechaFin) {
                $('#resumen-fechas').text(`${fechaInicio} - ${fechaFin}`);
            } else {
                $('#resumen-fechas').text('-');
            }
            
            $('#resumen-vacacion').show();
        } else {
            $('#resumen-vacacion').hide();
        }
    }

    updateObservacionesCount() {
        $('#observaciones-count').text($('#observaciones').val().length);
    }

    // ✅ ENVÍO REFACTORIZADO
    async handleSubmit(e) {
        e.preventDefault();
        
        try {
            this.setLoadingState(true);
            
            const formData = new FormData($('#form-asignar-vacaciones')[0]);
            const data = Object.fromEntries(formData.entries());
            
            if (!this.validarFormularioRefactorizado(data)) {
                this.setLoadingState(false);
                return;
            }
            
            // ✅ CONVERTIR FECHAS SIN VALIDACIONES TEMPORALES
            const fechaInicioISO = this.convertirDDMMYYYYaISO(data.fecha_inicio);
            const fechaFinISO = this.convertirDDMMYYYYaISO(data.fecha_fin);
            
            if (!fechaInicioISO || !fechaFinISO) {
                this.showAlert('Error en el formato de fechas', 'danger');
                this.setLoadingState(false);
                return;
            }
            
            // ✅ DATOS PARA BACKEND REFACTORIZADOS
            const dataParaBackend = {
                ...data,
                fecha_inicio: fechaInicioISO,
                fecha_fin: fechaFinISO,
                año_correspondiente: parseInt(data.año_correspondiente),
                dias_solicitados: parseInt(data.dias_solicitados),
                dias_correspondientes: parseInt(data.dias_correspondientes) || 6
            };
            
            console.log('📤 Enviando datos:', dataParaBackend);
            
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

    // ✅ VALIDACIÓN REFACTORIZADA
    validarFormularioRefactorizado(data) {
        let isValid = true;
        
        // Limpiar errores previos
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        
        // Año correspondiente
        const año = parseInt(data.año_correspondiente);
        if (!año || año < 2000 || año > 2050) {
            this.showFieldError('año_correspondiente', 'Ingrese un año válido (2000-2050)');
            isValid = false;
        }
        
        // Período vacacional
        if (!data.periodo_vacacional || data.periodo_vacacional.trim().length < 3) {
            this.showFieldError('periodo_vacacional', 'Ingrese un período vacacional válido');
            isValid = false;
        }
        
        // Días solicitados
        const dias = parseInt(data.dias_solicitados);
        if (!dias || dias <= 0 || dias > 365) {
            this.showFieldError('dias_solicitados', 'Ingrese días válidos (1-365)');
            isValid = false;
        }
        
        // Días correspondientes
        const diasCorrespondientes = parseInt(data.dias_correspondientes);
        if (!diasCorrespondientes || diasCorrespondientes < 6 || diasCorrespondientes > 50) {
            this.showFieldError('dias_correspondientes', 'Días correspondientes inválidos (6-50)');
            isValid = false;
        }
        
        // ✅ FECHAS - SOLO FORMATO, SIN RESTRICCIONES TEMPORALES
        if (!data.fecha_inicio || !window.FormatoGlobal.validarFormatoFecha(data.fecha_inicio)) {
            this.showFieldError('fecha_inicio', 'Fecha de inicio requerida (DD/MM/YYYY)');
            isValid = false;
        }
        
        if (!data.fecha_fin || !window.FormatoGlobal.validarFormatoFecha(data.fecha_fin)) {
            this.showFieldError('fecha_fin', 'Fecha de fin requerida (DD/MM/YYYY)');
            isValid = false;
        }
        
        // Validar que fecha fin sea posterior a fecha inicio (sin restricciones temporales)
        if (data.fecha_inicio && data.fecha_fin && 
            window.FormatoGlobal.validarFormatoFecha(data.fecha_inicio) && 
            window.FormatoGlobal.validarFormatoFecha(data.fecha_fin)) {
            
            const fechaInicio = window.FormatoGlobal.convertirFechaADate(data.fecha_inicio);
            const fechaFin = window.FormatoGlobal.convertirFechaADate(data.fecha_fin);
            
            if (fechaFin <= fechaInicio) {
                this.showFieldError('fecha_fin', 'La fecha de fin debe ser posterior al inicio');
                isValid = false;
            }
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
        
        // Auto-hide success and info alerts
        if (['success', 'info'].includes(type)) {
            setTimeout(() => $('#alert-vacaciones').fadeOut(), 3000);
        }
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

// ✅ INICIALIZACIÓN REFACTORIZADA
$(document).ready(function() {
    console.log('🚀 Iniciando modal de vacaciones REFACTORIZADO...');
    
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no disponible');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    if (trabajadorId) {
        if (window.FormatoGlobal) {
            window.asignarVacacionModal = new AsignarVacacionModal(trabajadorId);
            console.log(`✅ Modal REFACTORIZADO iniciado para trabajador: ${trabajadorId}`);
        } else {
            console.error('❌ FormatoGlobal no disponible');
        }
    } else {
        console.error('❌ ID trabajador no encontrado');
    }
});