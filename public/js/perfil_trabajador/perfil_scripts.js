// ========================================
// 🚀 PERFIL TRABAJADOR - SCRIPT PRINCIPAL
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 🔧 CONFIGURACIÓN GLOBAL
    // ========================================
    
    window.PERFIL_CONFIG = {
        fileMaxSize: 10 * 1024 * 1024, // 10MB
        allowedTypes: ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
        endpoints: {
            categorias: '/api/categorias/',
            contratos: '/trabajadores/'
        }
    };

    window.DIAS_SEMANA = {
        'lunes': 'Lunes', 'martes': 'Martes', 'miercoles': 'Miércoles',
        'jueves': 'Jueves', 'viernes': 'Viernes', 'sabado': 'Sábado', 'domingo': 'Domingo'
    };

    // ========================================
    // 🛠️ UTILIDADES GLOBALES
    // ========================================
    
    window.PerfilUtils = {
        getTrabajadorId: () => document.querySelector('[data-trabajador-id]')?.getAttribute('data-trabajador-id') || 
                              window.location.pathname.match(/trabajadores\/(\d+)/)?.[1],
        
        showLoading: (element, text = 'Procesando...') => {
            if (element) {
                element.disabled = true;
                element.dataset.originalText = element.innerHTML;
                element.innerHTML = `<i class="bi bi-hourglass-split"></i> ${text}`;
            }
        },
        
        hideLoading: (element) => {
            if (element && element.dataset.originalText) {
                element.disabled = false;
                element.innerHTML = element.dataset.originalText;
            }
        },
        
        fetchHTML: async (url) => {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.text();
        }
    };

    // ========================================
    // 🚀 INICIALIZACIÓN DE MÓDULOS
    // ========================================
    
    const modulos = [
        'initAreasyCategorias',
        'initDocumentos', 
        'initContratos',
        'initDiasLaborables',
        'initValidacionesCampos',
        'initNavegacion',
        'initNotificaciones'
    ];

    // Inicializar cada módulo si existe
    modulos.forEach(modulo => {
        if (typeof window[modulo] === 'function') {
            try {
                window[modulo]();
                console.log(`✅ ${modulo} inicializado`);
            } catch (error) {
                console.warn(`⚠️ Error al inicializar ${modulo}:`, error);
            }
        }
    });

    console.log('✅ Perfil Trabajador - Script principal inicializado');
});