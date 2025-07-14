/**
 * vacaciones.js - Gestión de Lista de Vacaciones con RUTAS DINÁMICAS
 * Maneja exclusivamente la lista de vacaciones y sus acciones
 * El modal está separado en asignar_vacacion.js
 */
class VacacionesManager {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.vacaciones = [];
        this.estadisticas = {};
        this.trabajadorData = {};
        
        console.log(`🏖️ VacacionesManager iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    async init() {
        // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no está disponible para cargar vacaciones');
            this.showError('Error de configuración: Sistema de rutas no disponible');
            return;
        }

        this.bindEvents();
        await this.loadVacaciones();
        console.log('✅ VacacionesManager inicializado correctamente con rutas dinámicas');
    }

    bindEvents() {
        // Eventos principales de la lista
        $('#refresh-vacaciones').on('click', () => this.loadVacaciones());
        $('#retry-vacaciones').on('click', () => this.loadVacaciones());
        
        // Filtros
        $('#filtro-estado').on('change', () => this.filterVacaciones());
        $('#filtro-periodo').on('change', () => this.filterVacaciones());
        
        // Escuchar evento del modal cuando se asigna una nueva vacación
        document.addEventListener('vacacionAsignada', (e) => this.handleVacacionAsignada(e.detail));
        
        console.log('🔗 Eventos de la lista vinculados correctamente');
    }

    // =================================
    // COMUNICACIÓN CON EL MODAL
    // =================================

    async handleVacacionAsignada(detail) {
        console.log('📥 Vacación asignada desde modal, recargando lista...', detail);
        
        // Recargar la lista de vacaciones
        await this.loadVacaciones();
        
        // Actualizar estado del trabajador si viene en la respuesta
        if (detail.trabajador_estatus) {
            this.updateTrabajadorStatus(detail.trabajador_estatus);
        }
        
        console.log('✅ Lista actualizada después de asignar vacación');
    }

    // =================================
    // CARGA Y GESTIÓN DE DATOS CON RUTAS DINÁMICAS
    // =================================

    async loadVacaciones() {
        try {
            this.showLoading();
            console.log(`🔄 Cargando vacaciones para trabajador: ${this.trabajadorId}`);
            
            // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE RUTAS ABSOLUTAS
            // ❌ ANTES: const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/api`, {
            // ✅ AHORA: Usar AppRoutes para construir la URL correcta
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/api`);
            
            console.log('🔄 Cargando desde URL:', url);
            
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
            
            const data = await response.json();
            
            if (data.success) {
                this.vacaciones = data.vacaciones || [];
                this.estadisticas = data.estadisticas || {};
                this.trabajadorData = data.trabajador || {};
                
                this.renderVacaciones();
                this.renderEstadisticas();
                this.updateFilters();
                this.showContent();
                
                console.log(`✅ ${this.vacaciones.length} vacaciones cargadas`);
            } else {
                throw new Error(data.message || 'Error al cargar vacaciones');
            }
        } catch (error) {
            console.error('❌ Error loading vacaciones:', error);
            this.showError(error.message || 'Error de conexión al cargar vacaciones');
        }
    }

    showLoading() {
        $('#vacaciones-loading').show();
        $('#vacaciones-estadisticas, #vacaciones-filtros, #vacaciones-lista, #vacaciones-vacio, #vacaciones-error').hide();
    }

    showContent() {
        $('#vacaciones-loading').hide();
        
        if (this.vacaciones.length > 0) {
            $('#vacaciones-estadisticas, #vacaciones-filtros, #vacaciones-lista').show();
            $('#vacaciones-vacio').hide();
        } else {
            $('#vacaciones-vacio').show();
            $('#vacaciones-estadisticas, #vacaciones-filtros, #vacaciones-lista').hide();
        }
        
        $('#vacaciones-error').hide();
    }

    showError(message) {
        $('#vacaciones-loading, #vacaciones-estadisticas, #vacaciones-filtros, #vacaciones-lista, #vacaciones-vacio').hide();
        $('#error-mensaje').text(message);
        $('#vacaciones-error').show();
    }

    // =================================
    // RENDERIZADO DE COMPONENTES
    // =================================

    renderEstadisticas() {
        // Estadísticas principales
        $('#stat-dias-correspondientes').text(this.estadisticas.dias_correspondientes_año_actual || 0);
        $('#stat-dias-restantes').text(this.estadisticas.dias_restantes_año_actual || 0);
        $('#stat-total-tomados').text(this.estadisticas.total_dias_tomados || 0);
        $('#stat-vacaciones-activas').text(this.estadisticas.vacaciones_activas || 0);
        
        // Header
        $('#header-dias-correspondientes').text(this.estadisticas.dias_correspondientes_año_actual || 0);
        $('#header-dias-restantes').text(this.estadisticas.dias_restantes_año_actual || 0);
        $('#header-vacaciones-activas').text(this.estadisticas.vacaciones_activas || 0);
        $('#header-total-tomadas').text(this.estadisticas.total_dias_tomados || 0);
    }

    renderVacaciones() {
        const $lista = $('#vacaciones-lista');
        $lista.empty();
        
        this.vacaciones.forEach(vacacion => {
            const $item = this.createVacacionItem(vacacion);
            $lista.append($item);
        });
    }

    createVacacionItem(vacacion) {
        const $template = $('#template-vacacion-item').contents().clone();
        
        // Configurar datos básicos
        $template.find('.vacacion-item').attr('data-vacacion-id', vacacion.id_vacacion);
        $template.find('.vacacion-item').attr('data-estado', vacacion.estado);
        
        // Estado y período
        $template.find('.estado-badge')
            .addClass(`bg-${this.getEstadoColor(vacacion.estado)}`)
            .text(this.getEstadoTexto(vacacion.estado));
            
        $template.find('.periodo-texto').text(vacacion.periodo_vacacional);
        $template.find('.creado-por').text(`Creado por ${vacacion.creado_por?.nombre || 'Sistema'}`);
        
        // Fechas - USAR FECHAS YA FORMATEADAS DESDE EL BACKEND O FORMATEAR LOCALMENTE
        const fechaInicio = vacacion.fecha_inicio_formatted || this.formatearFecha(vacacion.fecha_inicio);
        const fechaFin = vacacion.fecha_fin_formatted || this.formatearFecha(vacacion.fecha_fin);
        $template.find('.fechas-texto').text(`${fechaInicio} - ${fechaFin}`);
        $template.find('.duracion-texto').text(`${vacacion.duracion_dias || 0} días de duración`);
        
        // Días
        $template.find('.dias-solicitados').text(vacacion.dias_solicitados);
        $template.find('.dias-disfrutados').text(vacacion.dias_disfrutados);
        $template.find('.dias-restantes').text(vacacion.dias_restantes);
        
        // Observaciones
        if (vacacion.observaciones) {
            $template.find('.observaciones-texto')
                .html(`<strong>Observaciones:</strong> ${vacacion.observaciones}`)
                .show();
        }
        
        // Botones de acción
        this.addActionButtons($template, vacacion);
        
        return $template;
    }

    // FORMATO DE FECHAS SIN CONVERSIÓN TIMEZONE
    formatearFecha(fecha) {
        if (!fecha) return '';
        
        try {
            // Si viene en formato ISO (YYYY-MM-DD), convertir directamente a DD/MM/YYYY
            if (typeof fecha === 'string' && fecha.match(/^\d{4}-\d{2}-\d{2}/)) {
                const [year, month, day] = fecha.split('T')[0].split('-');
                return `${day}/${month}/${year}`;
            }
            
            // Para otros formatos, usar Date pero con UTC para evitar timezone issues
            const date = new Date(fecha + 'T00:00:00Z');
            return date.toLocaleDateString('es-ES', { timeZone: 'UTC' });
        } catch (error) {
            console.error('Error formatting date:', error);
            return fecha;
        }
    }

    addActionButtons($template, vacacion) {
        const $acciones = $template.find('.acciones-vacacion');
        $acciones.empty();
        
        const currentUser = window.currentUser || {};
        const canManage = currentUser.tipo === 'Gerencia' || currentUser.tipo === 'Recursos_Humanos';
        
        if (vacacion.estado === 'pendiente' && canManage) {
            $acciones.append(`
                <button class="btn btn-success btn-sm" onclick="vacacionesApp.iniciarVacacion(${vacacion.id_vacacion})">
                    <i class="bi bi-play"></i> Iniciar
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="vacacionesApp.cancelarVacacion(${vacacion.id_vacacion})">
                    <i class="bi bi-x"></i> Cancelar
                </button>
            `);
        } else if (vacacion.estado === 'activa' && canManage) {
            $acciones.append(`
                <button class="btn btn-warning btn-sm" onclick="vacacionesApp.finalizarVacacion(${vacacion.id_vacacion})">
                    <i class="bi bi-stop"></i> Finalizar
                </button>
            `);
        }
        
        // Botón de detalles
        $acciones.append(`
            <button class="btn btn-outline-info btn-sm" onclick="vacacionesApp.verDetalles(${vacacion.id_vacacion})">
                <i class="bi bi-eye"></i> Detalles
            </button>
        `);
    }

    getEstadoColor(estado) {
        const colores = {
            'pendiente': 'warning',
            'activa': 'success',
            'finalizada': 'secondary'
        };
        return colores[estado] || 'secondary';
    }

    getEstadoTexto(estado) {
        const textos = {
            'pendiente': 'Pendiente',
            'activa': 'Activa',
            'finalizada': 'Finalizada'
        };
        return textos[estado] || estado;
    }

    updateFilters() {
        const periodos = [...new Set(this.vacaciones.map(v => v.periodo_vacacional))];
        const $filtroPeriodo = $('#filtro-periodo');
        
        $filtroPeriodo.find('option:not(:first)').remove();
        periodos.forEach(periodo => {
            $filtroPeriodo.append(`<option value="${periodo}">${periodo}</option>`);
        });
    }

    filterVacaciones() {
        const estadoFiltro = $('#filtro-estado').val();
        const periodoFiltro = $('#filtro-periodo').val();
        
        $('.vacacion-item').each(function() {
            const $item = $(this);
            const estado = $item.attr('data-estado');
            const periodo = $item.find('.periodo-texto').text();
            
            const mostrarEstado = !estadoFiltro || estado === estadoFiltro;
            const mostrarPeriodo = !periodoFiltro || periodo === periodoFiltro;
            
            $item.closest('.col-12').toggle(mostrarEstado && mostrarPeriodo);
        });
    }

    // =================================
    // ACCIONES SOBRE VACACIONES EXISTENTES CON RUTAS DINÁMICAS
    // =================================

    async iniciarVacacion(vacacionId) {
        if (!confirm('¿Está seguro de iniciar estas vacaciones?')) return;
        
        try {
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/${vacacionId}/iniciar`);
            console.log('🔄 Iniciando vacación desde:', url);
            
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                await this.loadVacaciones();
                this.showNotification('success', 'Vacaciones iniciadas correctamente');
                
                if (result.trabajador_estatus) {
                    this.updateTrabajadorStatus(result.trabajador_estatus);
                }
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            console.error('Error starting vacation:', error);
            this.showNotification('error', 'Error al iniciar vacaciones');
        }
    }

    async finalizarVacacion(vacacionId) {
        const motivo = prompt('Motivo de finalización (opcional):');
        if (motivo === null) return;
        
        try {
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/${vacacionId}/finalizar`);
            console.log('🔄 Finalizando vacación desde:', url);
            
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ motivo_finalizacion: motivo })
            });
            
            const result = await response.json();
            
            if (result.success) {
                await this.loadVacaciones();
                this.showNotification('success', 'Vacaciones finalizadas correctamente');
                
                if (result.trabajador_estatus) {
                    this.updateTrabajadorStatus(result.trabajador_estatus);
                }
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            console.error('Error ending vacation:', error);
            this.showNotification('error', 'Error al finalizar vacaciones');
        }
    }

    async cancelarVacacion(vacacionId) {
        const motivo = prompt('Motivo de cancelación:');
        if (!motivo || motivo.trim() === '') return;
        
        try {
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/${vacacionId}/cancelar`);
            console.log('🔄 Cancelando vacación desde:', url);
            
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ motivo_cancelacion: motivo })
            });
            
            const result = await response.json();
            
            if (result.success) {
                await this.loadVacaciones();
                this.showNotification('success', 'Vacaciones canceladas correctamente');
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            console.error('Error canceling vacation:', error);
            this.showNotification('error', 'Error al cancelar vacaciones');
        }
    }

    verDetalles(vacacionId) {
        const vacacion = this.vacaciones.find(v => v.id_vacacion === vacacionId);
        if (!vacacion) return;
        
        const fechaInicio = this.formatearFecha(vacacion.fecha_inicio);
        const fechaFin = this.formatearFecha(vacacion.fecha_fin);
        
        alert(`Detalles de vacación:\n\nPeríodo: ${vacacion.periodo_vacacional}\nDías: ${vacacion.dias_solicitados}\nEstado: ${vacacion.estado}\nFechas: ${fechaInicio} - ${fechaFin}`);
    }

    // =================================
    // UTILIDADES Y HELPERS
    // =================================

    updateTrabajadorStatus(nuevoEstatus) {
        const $estatusBadge = $('.trabajador-estatus-badge');
        if ($estatusBadge.length) {
            $estatusBadge.removeClass().addClass(`badge bg-${this.getEstatusColor(nuevoEstatus)}`);
            $estatusBadge.html(`<i class="${this.getEstatusIcono(nuevoEstatus)}"></i> ${this.getEstatusTexto(nuevoEstatus)}`);
        }
        
        console.log(`👤 Estado del trabajador actualizado: ${nuevoEstatus}`);
    }

    getEstatusColor(estatus) {
        const colores = {
            'activo': 'success',
            'vacaciones': 'primary',
            'permiso': 'info',
            'suspendido': 'danger',
            'inactivo': 'secondary'
        };
        return colores[estatus] || 'secondary';
    }

    getEstatusTexto(estatus) {
        const textos = {
            'activo': 'Activo',
            'vacaciones': 'En Vacaciones',
            'permiso': 'Con Permiso',
            'suspendido': 'Suspendido',
            'inactivo': 'Inactivo'
        };
        return textos[estatus] || estatus;
    }

    getEstatusIcono(estatus) {
        const iconos = {
            'activo': 'bi-person-check',
            'vacaciones': 'bi-calendar-heart',
            'permiso': 'bi-calendar-event',
            'suspendido': 'bi-exclamation-triangle',
            'inactivo': 'bi-person-x'
        };
        return iconos[estatus] || 'bi-person';
    }

    showNotification(type, message) {
        // Toast simple
        const toast = $(`
            <div class="toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
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
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        toast.on('hidden.bs.toast', () => toast.remove());
    }

    // =================================
    // MÉTODOS PÚBLICOS PARA INTEGRACIÓN
    // =================================

    /**
     * Recargar la lista externamente
     */
    async reload() {
        await this.loadVacaciones();
    }

    /**
     * Obtener datos de vacaciones
     */
    getVacaciones() {
        return this.vacaciones;
    }

    /**
     * Obtener estadísticas
     */
    getEstadisticas() {
        return this.estadisticas;
    }

    /**
     * Obtener datos del trabajador
     */
    getTrabajadorData() {
        return this.trabajadorData;
    }
}

// =================================
// INICIALIZACIÓN AUTOMÁTICA
// =================================

$(document).ready(function() {
    console.log('🚀 Iniciando aplicación de lista de vacaciones con rutas dinámicas...');
    
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ CRÍTICO: AppRoutes no está disponible para vacaciones');
        alert('Error: Sistema de rutas no configurado. Recarga la página.');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.vacacionesApp = new VacacionesManager(trabajadorId);
        console.log(`✅ Lista de vacaciones con rutas dinámicas iniciada para trabajador: ${trabajadorId}`);
        console.log(`🔧 Base URL: ${AppRoutes.getBaseUrl()}`);
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador');
    }
});