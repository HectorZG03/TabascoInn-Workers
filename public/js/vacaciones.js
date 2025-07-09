/**
 * vacaciones.js - Gestión Independiente de Vacaciones
 * Script para la vista dedicada de vacaciones del trabajador
 */

class VacacionesManager {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.vacaciones = [];
        this.estadisticas = {};
        this.trabajadorData = {};
        this.initialized = false;
        
        console.log(`🏖️ VacacionesManager iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    async init() {
        if (this.initialized) return;
        
        try {
            this.bindEvents();
            await this.loadVacaciones();
            this.initialized = true;
            console.log('✅ VacacionesManager inicializado correctamente');
        } catch (error) {
            console.error('❌ Error al inicializar VacacionesManager:', error);
            this.showError('Error al inicializar el sistema de vacaciones');
        }
    }

    bindEvents() {
        // Botones principales
        $('#refresh-vacaciones').on('click', () => this.loadVacaciones());
        $('#retry-vacaciones').on('click', () => this.loadVacaciones());
        
        // Modal de asignar vacaciones
        $('#asignarVacacionesModal').on('show.bs.modal', () => this.initAsignarModal());
        $('#form-asignar-vacaciones').on('submit', (e) => this.handleAsignarSubmit(e));
        
        // Formulario de asignación
        $('#dias_solicitados').on('input', () => this.updateResumen());
        $('#fecha_inicio').on('change', () => this.updateFechaFin());
        $('#fecha_fin').on('change', () => this.updateResumen());
        $('#año_correspondiente').on('change', () => this.updatePeriodo());
        $('#observaciones').on('input', () => this.updateObservacionesCount());
        
        // Filtros
        $('#filtro-estado').on('change', () => this.filterVacaciones());
        $('#filtro-periodo').on('change', () => this.filterVacaciones());
        
        console.log('🔗 Eventos vinculados correctamente');
    }

    async loadVacaciones() {
        try {
            this.showLoading();
            console.log(`🔄 Cargando vacaciones para trabajador: ${this.trabajadorId}`);
            
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/api`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log(`📡 Respuesta del servidor: ${response.status}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 Datos recibidos:', data);
            
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
        $('#trabajador-estado-vacaciones').hide();
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
        $('#trabajador-estado-vacaciones').hide();
        $('#error-mensaje').text(message);
        $('#vacaciones-error').show();
    }

    renderEstadisticas() {
        // Estadísticas principales
        $('#stat-dias-correspondientes').text(this.estadisticas.dias_correspondientes_año_actual || 0);
        $('#stat-dias-restantes').text(this.estadisticas.dias_restantes_año_actual || 0);
        $('#stat-total-tomados').text(this.estadisticas.total_dias_tomados || 0);
        $('#stat-vacaciones-activas').text(this.estadisticas.vacaciones_activas || 0);
        
        // Actualizar header
        $('#header-dias-correspondientes').text(this.estadisticas.dias_correspondientes_año_actual || 0);
        $('#header-dias-restantes').text(this.estadisticas.dias_restantes_año_actual || 0);
        $('#header-vacaciones-activas').text(this.estadisticas.vacaciones_activas || 0);
        $('#header-total-tomadas').text(this.estadisticas.total_dias_tomados || 0);
        
        // Mostrar estado del trabajador
        this.updateTrabajadorEstado();
    }

    updateTrabajadorEstado() {
        const estado = this.trabajadorData.estatus;
        const $estadoDiv = $('#trabajador-estado-vacaciones');
        
        if (estado === 'vacaciones') {
            $estadoDiv.removeClass('alert-info alert-warning').addClass('alert-success');
            $('#estado-mensaje').html('<strong>¡En Vacaciones!</strong> El trabajador está actualmente disfrutando sus vacaciones.');
            $estadoDiv.show();
        } else if (this.estadisticas.vacaciones_pendientes > 0) {
            $estadoDiv.removeClass('alert-success alert-warning').addClass('alert-info');
            $('#estado-mensaje').html(`<strong>Vacaciones Pendientes:</strong> Hay ${this.estadisticas.vacaciones_pendientes} vacación(es) programada(s).`);
            $estadoDiv.show();
        } else if (this.estadisticas.dias_restantes_año_actual === 0) {
            $estadoDiv.removeClass('alert-success alert-info').addClass('alert-warning');
            $('#estado-mensaje').html('<strong>Sin días disponibles:</strong> El trabajador ha agotado sus días de vacaciones para este año.');
            $estadoDiv.show();
        } else {
            $estadoDiv.hide();
        }
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
        
        // Fechas y duración
        const fechaInicio = new Date(vacacion.fecha_inicio).toLocaleDateString('es-MX');
        const fechaFin = new Date(vacacion.fecha_fin).toLocaleDateString('es-MX');
        $template.find('.fechas-texto').text(`${fechaInicio} - ${fechaFin}`);
        $template.find('.duracion-texto').text(`${vacacion.duracion_dias || 0} días de duración`);
        
        // Días
        $template.find('.dias-solicitados').text(vacacion.dias_solicitados);
        $template.find('.dias-disfrutados').text(vacacion.dias_disfrutados);
        $template.find('.dias-restantes').text(vacacion.dias_restantes);
        
        // Progreso
        const progreso = Math.round((vacacion.dias_disfrutados / vacacion.dias_solicitados) * 100);
        $template.find('.progress-bar')
            .css('width', `${progreso}%`)
            .addClass(progreso === 100 ? 'bg-success' : progreso > 0 ? 'bg-info' : 'bg-primary');
        
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

    addActionButtons($template, vacacion) {
        const $acciones = $template.find('.acciones-vacacion');
        $acciones.empty();
        
        const currentUser = window.currentUser || {};
        const canManage = currentUser.tipo === 'Gerencia' || currentUser.tipo === 'Recursos_Humanos';
        
        if (vacacion.estado === 'pendiente') {
            if (canManage) {
                $acciones.append(`
                    <button class="btn btn-success btn-sm" onclick="vacacionesApp.iniciarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-play"></i> Iniciar
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="vacacionesApp.cancelarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-x"></i> Cancelar
                    </button>
                `);
            }
        } else if (vacacion.estado === 'activa') {
            if (canManage) {
                $acciones.append(`
                    <button class="btn btn-warning btn-sm" onclick="vacacionesApp.finalizarVacacion(${vacacion.id_vacacion})">
                        <i class="bi bi-stop"></i> Finalizar
                    </button>
                `);
            }
        }
        
        // Botón de detalles (siempre visible)
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
        // Actualizar filtro de períodos
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

    // Modal de asignar vacaciones
    async initAsignarModal() {
        try {
            // Cargar días disponibles
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/calcular-dias`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                $('#dias-disponibles').text(data.dias_restantes);
                $('#max-dias-texto').text(data.dias_restantes);
                $('#dias_solicitados').attr('max', data.dias_restantes);
                $('#trabajador-antiguedad').text(data.antiguedad);
                
                // Verificar si puede tomar vacaciones
                if (!data.puede_tomar_vacaciones) {
                    this.showModalAlert('El trabajador no puede tomar vacaciones en este momento.', 'warning');
                }
            }
        } catch (error) {
            console.error('Error loading vacation data:', error);
            this.showModalAlert('Error al cargar información de vacaciones', 'danger');
        }
        
        this.resetForm();
        this.updatePeriodo();
        this.updateObservacionesCount();
    }

    resetForm() {
        $('#form-asignar-vacaciones')[0].reset();
        $('#form-asignar-vacaciones .is-invalid').removeClass('is-invalid');
        $('#resumen-vacacion').hide();
        $('#alert-vacaciones').hide();
        
        // Establecer fecha mínima como hoy
        const today = new Date().toISOString().split('T')[0];
        $('#fecha_inicio, #fecha_fin').attr('min', today);
    }

    updateFechaFin() {
        const fechaInicio = $('#fecha_inicio').val();
        const diasSolicitados = parseInt($('#dias_solicitados').val()) || 0;
        
        if (fechaInicio && diasSolicitados > 0) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(inicio);
            fin.setDate(fin.getDate() + diasSolicitados - 1);
            
            $('#fecha_fin').val(fin.toISOString().split('T')[0]);
            this.updateResumen();
        }
    }

    updatePeriodo() {
        const año = $('#año_correspondiente').val();
        if (año) {
            const periodo = `${año}-${parseInt(año) + 1}`;
            $('#periodo-display').text(periodo);
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
            $('#resumen-duracion').text(`${diasSolicitados} días`);
            
            const inicioFormat = new Date(fechaInicio).toLocaleDateString('es-MX');
            const finFormat = new Date(fechaFin).toLocaleDateString('es-MX');
            $('#resumen-fechas').text(`${inicioFormat} - ${finFormat}`);
            
            // Verificar si inicia hoy
            const today = new Date().toISOString().split('T')[0];
            const iniciaHoy = fechaInicio === today;
            $('#resumen-inicio-auto').text(iniciaHoy ? 'Sí (se iniciará automáticamente)' : 'No');
            
            $('#resumen-vacacion').show();
        } else {
            $('#resumen-vacacion').hide();
        }
    }

    async handleAsignarSubmit(e) {
        e.preventDefault();
        
        const $btn = $('#btn-asignar-vacaciones');
        const $form = $('#form-asignar-vacaciones');
        
        try {
            // Mostrar loading
            $btn.find('.btn-text').hide();
            $btn.find('.btn-loading').show();
            $btn.prop('disabled', true);
            
            // Preparar datos
            const formData = new FormData($form[0]);
            const data = Object.fromEntries(formData.entries());
            
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
            
            if (result.success) {
                // Cerrar modal y recargar datos
                $('#asignarVacacionesModal').modal('hide');
                await this.loadVacaciones();
                
                // Mostrar notificación
                this.showNotification('success', 'Vacaciones asignadas correctamente');
                
                // Actualizar estatus del trabajador si cambió
                if (result.trabajador_estatus) {
                    this.updateTrabajadorStatus(result.trabajador_estatus);
                }
            } else {
                this.handleFormErrors(result.errors);
                this.showModalAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error assigning vacation:', error);
            this.showModalAlert('Error al asignar vacaciones', 'danger');
        } finally {
            // Restaurar botón
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
                const $feedback = $field.siblings('.invalid-feedback');
                
                $field.addClass('is-invalid');
                $feedback.text(Array.isArray(errors[field]) ? errors[field][0] : errors[field]);
            });
        }
    }

    showModalAlert(message, type) {
        const $alert = $('#alert-vacaciones');
        $alert.removeClass('alert-info alert-success alert-warning alert-danger')
              .addClass(`alert-${type}`)
              .find('#alert-mensaje').text(message);
        $alert.show();
    }

    // Acciones sobre vacaciones
    async iniciarVacacion(vacacionId) {
        if (!confirm('¿Está seguro de iniciar estas vacaciones?')) return;
        
        try {
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/${vacacionId}/iniciar`, {
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
                this.updateTrabajadorStatus(result.trabajador_estatus);
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
        if (motivo === null) return; // Usuario canceló
        
        try {
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/${vacacionId}/finalizar`, {
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
                this.updateTrabajadorStatus(result.trabajador_estatus);
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
            const response = await fetch(`/trabajadores/${this.trabajadorId}/vacaciones/${vacacionId}/cancelar`, {
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
        
        // Mostrar detalles en un modal o expandir la tarjeta
        console.log('Ver detalles de vacación:', vacacion);
        alert(`Detalles de vacación:\n\nPeríodo: ${vacacion.periodo_vacacional}\nDías: ${vacacion.dias_solicitados}\nEstado: ${vacacion.estado}\nFechas: ${vacacion.fecha_inicio} - ${vacacion.fecha_fin}`);
    }

    updateTrabajadorStatus(nuevoEstatus) {
        // Actualizar el estatus en la interfaz principal
        const $estatusBadge = $('.trabajador-estatus-badge');
        if ($estatusBadge.length) {
            $estatusBadge.removeClass().addClass(`badge bg-${this.getEstatusColor(nuevoEstatus)}`);
            $estatusBadge.text(this.getEstatusTexto(nuevoEstatus));
        }
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

    showNotification(type, message) {
        // Integrar con el sistema de notificaciones existente
        if (window.mostrarNotificacion) {
            window.mostrarNotificacion(type, message);
        } else {
            // Fallback simple con toasts de Bootstrap o alert
            console.log(`${type.toUpperCase()}: ${message}`);
            
            if (type === 'success') {
                // Crear toast de éxito
                this.createToast('Éxito', message, 'success');
            } else if (type === 'error') {
                this.createToast('Error', message, 'danger');
            }
        }
    }

    createToast(title, message, type) {
        // Crear toast simple si no existe sistema de notificaciones
        const toastHtml = `
            <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Agregar al contenedor de toasts (crear si no existe)
        let $container = $('#toast-container');
        if (!$container.length) {
            $container = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
            $('body').append($container);
        }
        
        const $toast = $(toastHtml);
        $container.append($toast);
        
        // Mostrar el toast
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        // Remover después de que se oculte
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
}

// Inicialización automática cuando se carga la página
$(document).ready(function() {
    console.log('🚀 Iniciando aplicación de vacaciones...');
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.vacacionesApp = new VacacionesManager(trabajadorId);
        console.log(`✅ Aplicación de vacaciones iniciada para trabajador: ${trabajadorId}`);
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador');
    }
});