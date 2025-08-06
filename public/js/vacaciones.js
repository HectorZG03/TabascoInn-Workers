/**
 * vacaciones.js - Gestión Simplificada de Vacaciones
 */
class VacacionesManager {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.vacaciones = [];
        this.estadisticas = {};
        
        console.log(`🏖️ VacacionesManager iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    async init() {
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no disponible');
            this.showError('Error de configuración: Sistema de rutas no disponible');
            return;
        }

        this.bindEvents();
        await this.loadVacaciones();
        console.log('✅ VacacionesManager inicializado');
    }

    bindEvents() {
        $('#refresh-vacaciones').on('click', () => this.loadVacaciones());
        $('#retry-vacaciones').on('click', () => this.loadVacaciones());
        $('#filtro-estado, #filtro-periodo').on('change', () => this.filterVacaciones());
        document.addEventListener('vacacionAsignada', (e) => this.handleVacacionAsignada(e.detail));
    }

    async handleVacacionAsignada(detail) {
        console.log('📥 Vacación asignada, recargando...', detail);
        await this.loadVacaciones();
        if (detail.trabajador_estatus) this.updateTrabajadorStatus(detail.trabajador_estatus);
    }

    // ✅ CARGA DE DATOS SIMPLIFICADA
    async loadVacaciones() {
        try {
            this.showLoading();
            
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/vacaciones/api`);
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            
            const data = await response.json();
            
            if (data.success) {
                this.vacaciones = data.vacaciones || [];
                this.estadisticas = data.estadisticas || {};
                this.trabajadorData = data.trabajador || {};
                
                this.renderAll();
                console.log(`✅ ${this.vacaciones.length} vacaciones cargadas`);
            } else {
                throw new Error(data.message || 'Error al cargar vacaciones');
            }
        } catch (error) {
            console.error('❌ Error loading vacaciones:', error);
            this.showError(error.message || 'Error de conexión');
        }
    }

    // ✅ RENDERIZADO CONSOLIDADO
    renderAll() {
        this.renderEstadisticas();
        this.renderVacaciones();
        this.updateFilters();
        this.showContent();
    }

    renderEstadisticas() {
        const stats = this.estadisticas;
        $('#stat-dias-correspondientes, #header-dias-correspondientes').text(stats.dias_correspondientes_año_actual || 0);
        $('#stat-dias-restantes, #header-dias-restantes').text(stats.dias_restantes_año_actual || 0);
        $('#stat-total-tomados, #header-total-tomadas').text(stats.total_dias_tomados || 0);
        $('#stat-vacaciones-activas, #header-vacaciones-activas').text(stats.vacaciones_activas || 0);
    }

    renderVacaciones() {
        const $lista = $('#vacaciones-lista');
        $lista.empty();
        this.vacaciones.forEach(vacacion => $lista.append(this.createVacacionItem(vacacion)));
    }

    // ✅ CREACIÓN DE ITEMS SIMPLIFICADA
    createVacacionItem(vacacion) {
        const $template = $('#template-vacacion-item').contents().clone();
        const estadoInfo = this.getEstadoInfo(vacacion.estado);
        
        // Datos básicos
        $template.find('.vacacion-item')
            .attr('data-vacacion-id', vacacion.id_vacacion)
            .attr('data-estado', vacacion.estado);
        
        // Estado y fechas
        $template.find('.estado-badge').addClass(`bg-${estadoInfo.color}`).text(estadoInfo.texto);
        $template.find('.periodo-texto').text(vacacion.periodo_vacacional);
        $template.find('.creado-por').text(`Creado por ${vacacion.creado_por?.nombre || 'Sistema'}`);
        $template.find('.fechas-texto').text(`${vacacion.fecha_inicio_formatted} - ${vacacion.fecha_fin_formatted}`);
        $template.find('.duracion-texto').text(`${vacacion.duracion_dias || 0} días`);
        
        // Días
        $template.find('.dias-solicitados').text(vacacion.dias_solicitados);
        $template.find('.dias-disfrutados').text(vacacion.dias_disfrutados);
        $template.find('.dias-restantes').text(vacacion.dias_restantes);
        
        // Observaciones
        if (vacacion.observaciones) {
            $template.find('.observaciones-texto').html(`<strong>Observaciones:</strong> ${vacacion.observaciones}`).show();
        }
        
        // Botones de acción
        this.addActionButtons($template, vacacion);
        
        return $template;
    }

    // ✅ BOTONES DE ACCIÓN SIMPLIFICADOS
    addActionButtons($template, vacacion) {
        const $acciones = $template.find('.acciones-vacacion');
        $acciones.empty();
        

            if (vacacion.estado === 'pendiente') {
                $acciones.append(`
                    <button class="btn btn-success btn-sm" onclick="vacacionesApp.iniciarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-play"></i> Iniciar
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="vacacionesApp.cancelarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                `);
            } else if (vacacion.estado === 'activa') {
                const puedeFinalizarse = new Date() >= new Date(vacacion.fecha_fin);
                
                if (puedeFinalizarse) {
                    $acciones.append(`
                        <button class="btn btn-primary btn-sm" onclick="vacacionesApp.finalizarVacacion(${vacacion.id_vacacion})">
                            <i class="bi bi-check-circle"></i> Finalizar
                        </button>
                    `);
                }
                
                $acciones.append(`
                    <button class="btn btn-warning btn-sm" onclick="vacacionesApp.cancelarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                `);
            }
        $acciones.append(`
            <button class="btn btn-outline-info btn-sm" onclick="vacacionesApp.verDetalles(${vacacion.id_vacacion})">
                <i class="bi bi-eye"></i> Detalles
            </button>
        `);
    }

    // ✅ ACCIONES SIMPLIFICADAS
    async iniciarVacacion(vacacionId) {
        if (!confirm('¿Iniciar estas vacaciones?')) return;
        await this.executeAction(`${this.trabajadorId}/vacaciones/${vacacionId}/iniciar`, 'PATCH', 'Vacaciones iniciadas');
    }

    async finalizarVacacion(vacacionId) {
        if (!confirm('¿Finalizar estas vacaciones?')) return;
        const motivo = prompt('Motivo (opcional):') || 'Finalización normal';
        await this.executeAction(`${this.trabajadorId}/vacaciones/${vacacionId}/finalizar`, 'PATCH', 'Vacaciones finalizadas', { motivo_finalizacion: motivo });
    }

    async cancelarVacacion(vacacionId) {
        const vacacion = this.vacaciones.find(v => v.id_vacacion === vacacionId);
        if (!vacacion) return;
        
        if (!confirm(`¿Cancelar vacaciones? Se devolverán ${vacacion.dias_solicitados} días.`)) return;
        
        let motivo = '';
        while (!motivo || motivo.length < 10) {
            motivo = prompt('Motivo de cancelación (mínimo 10 caracteres):');
            if (motivo === null) return;
            if (!motivo || motivo.length < 10) alert('El motivo debe tener al menos 10 caracteres.');
        }
        
        await this.executeAction(`${this.trabajadorId}/vacaciones/${vacacionId}/cancelar`, 'DELETE', 'Vacaciones canceladas', { motivo_cancelacion: motivo });
    }

    // ✅ EJECUTOR DE ACCIONES UNIFICADO
    async executeAction(endpoint, method, successMessage, body = null) {
        try {
            const url = AppRoutes.trabajadores(endpoint);
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                ...(body && { body: JSON.stringify(body) })
            });
            
            const result = await response.json();
            
            if (result.success) {
                await this.loadVacaciones();
                this.showNotification('success', successMessage);
                if (result.trabajador_estatus) this.updateTrabajadorStatus(result.trabajador_estatus);
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            console.error('Error executing action:', error);
            this.showNotification('error', 'Error de conexión');
        }
    }

    verDetalles(vacacionId) {
        const vacacion = this.vacaciones.find(v => v.id_vacacion === vacacionId);
        if (!vacacion) return;
        
        let mensaje = `=== DETALLES DE VACACIÓN ===\n\n` +
                     `Estado: ${this.getEstadoInfo(vacacion.estado).texto}\n` +
                     `Período: ${vacacion.periodo_vacacional}\n` +
                     `Días solicitados: ${vacacion.dias_solicitados}\n` +
                     `Fechas: ${vacacion.fecha_inicio_formatted} - ${vacacion.fecha_fin_formatted}\n` +
                     `Creado por: ${vacacion.creado_por?.nombre || 'Sistema'}`;
        
        if (vacacion.observaciones) mensaje += `\nObservaciones: ${vacacion.observaciones}`;
        
        if (vacacion.estado === 'cancelada') {
            mensaje += `\n\n=== CANCELACIÓN ===\n` +
                      `Motivo: ${vacacion.motivo_cancelacion || 'No especificado'}\n` +
                      `Días devueltos: ${vacacion.dias_restantes}`;
        }
        
        alert(mensaje);
    }

    // ✅ UTILIDADES CONSOLIDADAS
    getEstadoInfo(estado) {
        const estados = {
            'pendiente': { texto: 'Pendiente', color: 'warning' },
            'activa': { texto: 'Activa', color: 'success' },
            'finalizada': { texto: 'Finalizada', color: 'secondary' },
            'cancelada': { texto: 'Cancelada', color: 'danger' }
        };
        return estados[estado] || estados['pendiente'];
    }

    updateFilters() {
        const periodos = [...new Set(this.vacaciones.map(v => v.periodo_vacacional))];
        const $filtroPeriodo = $('#filtro-periodo');
        $filtroPeriodo.find('option:not(:first)').remove();
        periodos.forEach(periodo => $filtroPeriodo.append(`<option value="${periodo}">${periodo}</option>`));
    }

    filterVacaciones() {
        const estadoFiltro = $('#filtro-estado').val();
        const periodoFiltro = $('#filtro-periodo').val();
        
        $('.vacacion-item').each(function() {
            const $item = $(this);
            const estado = $item.attr('data-estado');
            const periodo = $item.find('.periodo-texto').text();
            
            const mostrar = (!estadoFiltro || estado === estadoFiltro) && (!periodoFiltro || periodo === periodoFiltro);
            $item.closest('.col-12').toggle(mostrar);
        });
    }

    updateTrabajadorStatus(nuevoEstatus) {
        const $badge = $('.trabajador-estatus-badge');
        if ($badge.length) {
            const colores = { 'activo': 'success', 'vacaciones': 'primary', 'permiso': 'info', 'suspendido': 'danger', 'inactivo': 'secondary' };
            const iconos = { 'activo': 'bi-person-check', 'vacaciones': 'bi-calendar-heart', 'permiso': 'bi-calendar-event', 'suspendido': 'bi-exclamation-triangle', 'inactivo': 'bi-person-x' };
            const textos = { 'activo': 'Activo', 'vacaciones': 'En Vacaciones', 'permiso': 'Con Permiso', 'suspendido': 'Suspendido', 'inactivo': 'Inactivo' };
            
            $badge.removeClass().addClass(`badge bg-${colores[nuevoEstatus] || 'secondary'}`);
            $badge.html(`<i class="${iconos[nuevoEstatus] || 'bi-person'}"></i> ${textos[nuevoEstatus] || nuevoEstatus}`);
        }
    }

    // ✅ ESTADOS DE UI SIMPLIFICADOS
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

    showNotification(type, message) {
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
        new bootstrap.Toast(toast[0]).show();
        toast.on('hidden.bs.toast', () => toast.remove());
    }

    // ✅ MÉTODOS PÚBLICOS
    async reload() { await this.loadVacaciones(); }
    getVacaciones() { return this.vacaciones; }
    getEstadisticas() { return this.estadisticas; }
}

// ✅ INICIALIZACIÓN SIMPLIFICADA
$(document).ready(function() {
    console.log('🚀 Iniciando vacaciones...');
    
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no disponible');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    if (trabajadorId) {
        window.vacacionesApp = new VacacionesManager(trabajadorId);
        console.log(`✅ Vacaciones iniciado para trabajador: ${trabajadorId}`);
    } else {
        console.error('❌ ID trabajador no encontrado');
    }
});