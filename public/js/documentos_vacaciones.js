/**
 * documentos_vacaciones.js - Gestión de Documentos de Amortización con RUTAS DINÁMICAS
 * Maneja la lista de documentos y sus acciones
 */
class DocumentosVacacionesManager {
    constructor(trabajadorId) {
        this.trabajadorId = trabajadorId;
        this.documentos = [];
        
        console.log(`📄 DocumentosVacacionesManager iniciado para trabajador: ${trabajadorId}`);
        this.init();
    }

    async init() {
        // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
        if (typeof AppRoutes === 'undefined') {
            console.error('❌ AppRoutes no está disponible para documentos de vacaciones');
            this.showError('Error de configuración: Sistema de rutas no disponible');
            return;
        }

        this.bindEvents();
        await this.loadDocumentos();
        console.log('✅ DocumentosVacacionesManager inicializado correctamente');
    }

    bindEvents() {
        // Eventos principales
        $('#refresh-documentos').on('click', () => this.loadDocumentos());
        $('#retry-documentos').on('click', () => this.loadDocumentos());
        
        // Escuchar evento cuando se sube un documento
        document.addEventListener('documentoSubido', (e) => this.handleDocumentoSubido(e.detail));
        
        console.log('🔗 Eventos de documentos vinculados correctamente');
    }

    // =================================
    // COMUNICACIÓN CON EL MODAL
    // =================================

    async handleDocumentoSubido(detail) {
        console.log('📥 Documento subido desde modal, recargando lista...', detail);
        
        // Recargar la lista de documentos
        await this.loadDocumentos();
        
        console.log('✅ Lista actualizada después de subir documento');
    }

    // =================================
    // CARGA Y GESTIÓN DE DATOS CON RUTAS DINÁMICAS
    // =================================

    async loadDocumentos() {
        try {
            this.showLoading();
            console.log(`🔄 Cargando documentos para trabajador: ${this.trabajadorId}`);
            
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/documentos-vacaciones/api/documentos`);
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
                this.documentos = data.documentos || [];
                
                this.renderDocumentos();
                this.showContent();
                
                console.log(`✅ ${this.documentos.length} documentos cargados`);
            } else {
                throw new Error(data.message || 'Error al cargar documentos');
            }
        } catch (error) {
            console.error('❌ Error loading documentos:', error);
            this.showError(error.message || 'Error de conexión al cargar documentos');
        }
    }

    showLoading() {
        $('#documentos-loading').show();
        $('#documentos-lista, #documentos-vacio, #documentos-error').hide();
    }

    showContent() {
        $('#documentos-loading').hide();
        
        if (this.documentos.length > 0) {
            $('#documentos-lista').show();
            $('#documentos-vacio').hide();
        } else {
            $('#documentos-vacio').show();
            $('#documentos-lista').hide();
        }
        
        $('#documentos-error').hide();
    }

    showError(message) {
        $('#documentos-loading, #documentos-lista, #documentos-vacio').hide();
        $('#error-mensaje').text(message);
        $('#documentos-error').show();
    }

    // =================================
    // RENDERIZADO DE COMPONENTES
    // =================================

    renderDocumentos() {
        const $lista = $('#documentos-lista');
        $lista.empty();
        
        this.documentos.forEach(documento => {
            const $item = this.createDocumentoItem(documento);
            $lista.append($item);
        });
    }

    createDocumentoItem(documento) {
        const $template = $('#template-documento-item').contents().clone();
        
        // Configurar datos básicos
        $template.find('.documento-item').attr('data-documento-id', documento.id);
        $template.find('.nombre-documento').text(documento.nombre_original);
        $template.find('.fecha-subida').text(documento.created_at);
        $template.find('.tamaño-archivo').text(documento.tamaño);
        
        // Configurar enlaces
        $template.find('.btn-ver-documento').attr('data-url', documento.url);
        $template.find('.btn-descargar-documento').attr('data-url', documento.url);
        $template.find('.btn-descargar-documento').attr('data-name', documento.nombre_original);
        
        // Vacaciones asociadas
        const $vacacionesBadges = $template.find('.vacaciones-badges');
        documento.vacaciones.forEach(vacacion => {
            const badge = $(`
                <span class="badge bg-primary me-1 mb-1" title="${vacacion.fecha_inicio} - ${vacacion.fecha_fin}">
                    ${vacacion.dias_solicitados} días
                </span>
            `);
            $vacacionesBadges.append(badge);
        });
        
        // Botón eliminar (solo para usuarios autorizados)
        const $btnEliminar = $template.find('.btn-eliminar-documento');
        if ($btnEliminar.length && this.canManageDocuments()) {
            $btnEliminar.on('click', () => this.eliminarDocumento(documento.id));
        } else {
            $btnEliminar.remove();
        }
        
        return $template;
    }

    canManageDocuments() {
        const currentUser = window.currentUser || {};
        return currentUser.tipo === 'Gerencia' || currentUser.tipo === 'Recursos_Humanos';
    }

    // =================================
    // ACCIONES SOBRE DOCUMENTOS CON RUTAS DINÁMICAS
    // =================================

    async eliminarDocumento(documentoId) {
        if (!confirm('¿Está seguro de eliminar este documento? Esta acción no se puede deshacer.')) {
            return;
        }
        
        try {
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${this.trabajadorId}/documentos-vacaciones/${documentoId}/eliminar`);
            console.log('🗑️ Eliminando documento desde:', url);
            
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                await this.loadDocumentos();
                this.showNotification('success', 'Documento eliminado correctamente');
                console.log('✅ Documento eliminado exitosamente');
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            console.error('❌ Error eliminando documento:', error);
            this.showNotification('error', 'Error al eliminar documento');
        }
    }

    // =================================
    // UTILIDADES Y HELPERS
    // =================================

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
        await this.loadDocumentos();
    }

    /**
     * Obtener datos de documentos
     */
    getDocumentos() {
        return this.documentos;
    }

    /**
     * Obtener cantidad de documentos
     */
    getTotalDocumentos() {
        return this.documentos.length;
    }
}

// =================================
// INICIALIZACIÓN AUTOMÁTICA
// =================================

$(document).ready(function() {
    console.log('🚀 Iniciando aplicación de documentos de vacaciones con rutas dinámicas...');
    
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ CRÍTICO: AppRoutes no está disponible para documentos de vacaciones');
        alert('Error: Sistema de rutas no configurado. Recarga la página.');
        return;
    }
    
    const trabajadorId = $('[data-trabajador-id]').data('trabajador-id');
    
    if (trabajadorId) {
        window.documentosVacacionesApp = new DocumentosVacacionesManager(trabajadorId);
        console.log(`✅ Documentos de vacaciones con rutas dinámicas iniciado para trabajador: ${trabajadorId}`);
        console.log(`🔧 Base URL: ${AppRoutes.getBaseUrl()}`);
    } else {
        console.error('❌ No se pudo obtener el ID del trabajador');
    }
});