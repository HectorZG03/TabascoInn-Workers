// ========================================
// 🚀 PERFIL TRABAJADOR - SCRIPT PRINCIPAL
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 🔧 CONFIGURACIÓN GLOBAL ACTUALIZADA
    // ========================================
    
    window.PERFIL_CONFIG = {
        fileMaxSize: 10 * 1024 * 1024, // 10MB
        allowedTypes: ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
        // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE RUTAS ABSOLUTAS
        endpoints: {
            // ❌ ANTES: categorias: '/api/categorias/',
            // ✅ AHORA: Usar función dinámica
            categorias: () => AppRoutes.api('categorias/'),
            contratos: () => AppRoutes.trabajadores(''),
            // ✅ NUEVOS ENDPOINTS DINÁMICOS
            motivos: () => AppRoutes.api('motivos'),
            estadisticas: () => AppRoutes.api('estadisticas'),
            // Para compatibilidad con código existente
            get categoriasUrl() { return this.categorias(); },
            get contratosUrl() { return this.contratos(); }
        }
    };

    window.DIAS_SEMANA = {
        'lunes': 'Lunes', 'martes': 'Martes', 'miercoles': 'Miércoles',
        'jueves': 'Jueves', 'viernes': 'Viernes', 'sabado': 'Sábado', 'domingo': 'Domingo'
    };

    // ========================================
    // 🛠️ UTILIDADES GLOBALES MEJORADAS
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
        
        // ✅ MÉTODO MEJORADO QUE USA RUTAS DINÁMICAS
        fetchHTML: async (url) => {
            // Si la URL no es absoluta y no tiene el prefijo, usar AppRoutes
            if (!url.startsWith('http') && !url.startsWith(AppRoutes.getBaseUrl())) {
                url = AppRoutes.url(url.replace(/^\/+/, ''));
            }
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.text();
        },

        // ✅ NUEVO: Método para hacer peticiones API
        fetchAPI: async (endpoint, options = {}) => {
            const url = AppRoutes.api(endpoint);
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                },
                ...options
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        }
    };

    // ========================================
    // 🔧 FUNCIÓN DE DEBUG PARA VERIFICAR RUTAS
    // ========================================
    
    window.debugRutas = function() {
        console.group('🔍 Debug de Rutas - Perfil Trabajador');
        console.log('Base URL detectada:', AppRoutes.getBaseUrl());
        console.log('Endpoints configurados:');
        console.log('- Categorías:', PERFIL_CONFIG.endpoints.categorias());
        console.log('- Contratos:', PERFIL_CONFIG.endpoints.contratos());
        console.log('- Motivos:', PERFIL_CONFIG.endpoints.motivos());
        console.log('- Estadísticas:', PERFIL_CONFIG.endpoints.estadisticas());
        console.log('URL actual:', window.location.href);
        console.log('Path actual:', window.location.pathname);
        console.groupEnd();
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

    // Verificar que AppRoutes esté disponible antes de inicializar
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible. Asegúrate de cargar app-routes.js antes.');
        return;
    }

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

    // ✅ EJECUTAR DEBUG EN DESARROLLO
    if (typeof window.APP_DEBUG !== 'undefined' && window.APP_DEBUG) {
        window.debugRutas();
    }

    console.log('✅ Perfil Trabajador - Script principal inicializado con rutas dinámicas');
});