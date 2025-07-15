/**
 * helper_pdf_download.js - Helper para descargas de PDF con RUTAS DINÁMICAS
 * Maneja la descarga de PDFs de amortización con loading states
 */
class PDFDownloadHelper {
    constructor() {
        this.isDownloading = false;
        console.log('📄 PDFDownloadHelper inicializado');
    }

    /**
     * Descargar PDF de amortización con loading state
     */
    async downloadAmortizacionPDF(trabajadorId, buttonSelector = null) {
        if (this.isDownloading) {
            console.log('⏳ Descarga ya en progreso');
            return;
        }

        try {
            this.isDownloading = true;
            
            // Mostrar loading en el botón si se proporciona
            if (buttonSelector) {
                this.setButtonLoading(buttonSelector, true);
            }

            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${trabajadorId}/documentos-vacaciones/descargar-pdf`);
            console.log('📥 Descargando PDF desde:', url);

            // Crear elemento de descarga temporal
            const link = document.createElement('a');
            link.href = url;
            link.download = ''; // El servidor definirá el nombre
            link.style.display = 'none';
            
            // Agregar al DOM y hacer click
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Mostrar notificación de éxito
            this.showNotification('success', 'PDF descargado correctamente');
            
            console.log('✅ PDF descargado exitosamente');

        } catch (error) {
            console.error('❌ Error descargando PDF:', error);
            this.showNotification('error', 'Error al descargar el PDF');
        } finally {
            this.isDownloading = false;
            
            // Restaurar botón si se proporciona
            if (buttonSelector) {
                this.setButtonLoading(buttonSelector, false);
            }
        }
    }

    /**
     * Descargar documento existente
     */
    async downloadDocument(documentUrl, documentName) {
        try {
            console.log('📥 Descargando documento:', documentName);

            // Crear elemento de descarga temporal
            const link = document.createElement('a');
            link.href = documentUrl;
            link.download = documentName;
            link.style.display = 'none';
            
            // Agregar al DOM y hacer click
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            console.log('✅ Documento descargado exitosamente');

        } catch (error) {
            console.error('❌ Error descargando documento:', error);
            this.showNotification('error', 'Error al descargar el documento');
        }
    }

    /**
     * Abrir documento en nueva ventana
     */
    viewDocument(documentUrl) {
        try {
            window.open(documentUrl, '_blank');
            console.log('👁️ Documento abierto en nueva ventana');
        } catch (error) {
            console.error('❌ Error abriendo documento:', error);
            this.showNotification('error', 'Error al abrir el documento');
        }
    }

    /**
     * Verificar si hay vacaciones pendientes antes de descargar
     */
    async checkVacacionesPendientes(trabajadorId) {
        try {
            // ✅ USAR RUTAS DINÁMICAS
            const url = AppRoutes.trabajadores(`${trabajadorId}/vacaciones/api`);
            
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
                const vacacionesPendientes = data.vacaciones.filter(v => v.estado === 'pendiente');
                return vacacionesPendientes.length > 0;
            }

            return false;

        } catch (error) {
            console.error('❌ Error verificando vacaciones pendientes:', error);
            return false;
        }
    }

    /**
     * Descargar con verificación previa
     */
    async downloadWithCheck(trabajadorId, buttonSelector = null) {
        const tieneVacacionesPendientes = await this.checkVacacionesPendientes(trabajadorId);
        
        if (!tieneVacacionesPendientes) {
            this.showNotification('warning', 'No hay vacaciones pendientes para generar documento');
            return;
        }

        await this.downloadAmortizacionPDF(trabajadorId, buttonSelector);
    }

    // =================================
    // MÉTODOS DE UTILIDAD
    // =================================

    setButtonLoading(selector, loading) {
        const $button = $(selector);
        
        if (loading) {
            $button.prop('disabled', true);
            $button.data('original-html', $button.html());
            $button.html('<span class="spinner-border spinner-border-sm me-2"></span>Descargando...');
        } else {
            $button.prop('disabled', false);
            const originalHtml = $button.data('original-html');
            if (originalHtml) {
                $button.html(originalHtml);
            }
        }
    }

    showNotification(type, message) {
        // Usar el sistema de notificaciones global si existe
        if (window.documentosVacacionesApp && window.documentosVacacionesApp.showNotification) {
            window.documentosVacacionesApp.showNotification(type, message);
        } else if (window.vacacionesApp && window.vacacionesApp.showNotification) {
            window.vacacionesApp.showNotification(type, message);
        } else {
            // Fallback: crear toast propio
            this.createToast(type, message);
        }
    }

    createToast(type, message) {
        const toastType = type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger';
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
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        toast.on('hidden.bs.toast', () => toast.remove());
    }
}

// =================================
// FUNCIÓN GLOBAL DE CONVENIENCIA
// =================================

/**
 * Función global para descargar PDF de amortización
 */
window.downloadAmortizacionPDF = function(trabajadorId, buttonSelector = null) {
    if (!window.pdfDownloadHelper) {
        window.pdfDownloadHelper = new PDFDownloadHelper();
    }
    
    return window.pdfDownloadHelper.downloadWithCheck(trabajadorId, buttonSelector);
};

/**
 * Función global para ver documento
 */
window.viewDocument = function(documentUrl) {
    if (!window.pdfDownloadHelper) {
        window.pdfDownloadHelper = new PDFDownloadHelper();
    }
    
    return window.pdfDownloadHelper.viewDocument(documentUrl);
};

/**
 * Función global para descargar documento
 */
window.downloadDocument = function(documentUrl, documentName) {
    if (!window.pdfDownloadHelper) {
        window.pdfDownloadHelper = new PDFDownloadHelper();
    }
    
    return window.pdfDownloadHelper.downloadDocument(documentUrl, documentName);
};

// =================================
// INICIALIZACIÓN AUTOMÁTICA
// =================================

$(document).ready(function() {
    // Inicializar helper global
    window.pdfDownloadHelper = new PDFDownloadHelper();
    
    // Vincular eventos automáticamente a botones con data attributes
    $(document).on('click', '[data-download-pdf]', function(e) {
        e.preventDefault();
        const trabajadorId = $(this).data('trabajador-id');
        const buttonSelector = `#${this.id}`;
        
        if (trabajadorId) {
            window.downloadAmortizacionPDF(trabajadorId, buttonSelector);
        }
    });
    
    console.log('✅ PDFDownloadHelper inicializado globalmente');
});