// ========================================
// 🚀 PERFIL TRABAJADOR - SCRIPT PRINCIPAL CORREGIDO
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 🔧 CONFIGURACIÓN GLOBAL ACTUALIZADA
    // ========================================
    
    window.PERFIL_CONFIG = {
        fileMaxSize: 10 * 1024 * 1024, // 10MB
        allowedTypes: ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
        // ✅ CORREGIDO: Usar funciones que retornen URLs válidas
        endpoints: {
            categorias: () => AppRoutes.api('categorias'),
            contratos: () => AppRoutes.url('trabajadores'),
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
        // ✅ CORREGIDO: Método mejorado para obtener ID del trabajador
        getTrabajadorId: () => {
            // Intentar obtener del atributo data-trabajador-id
            const elemento = document.querySelector('[data-trabajador-id]');
            if (elemento) {
                const id = elemento.getAttribute('data-trabajador-id');
                if (id && id.trim()) {
                    console.log('✅ ID trabajador obtenido del DOM:', id);
                    return id.trim();
                }
            }
            
            // Fallback: extraer de la URL
            const urlMatch = window.location.pathname.match(/trabajadores\/(\d+)/);
            if (urlMatch && urlMatch[1]) {
                console.log('✅ ID trabajador obtenido de URL:', urlMatch[1]);
                return urlMatch[1];
            }
            
            console.error('❌ No se pudo obtener el ID del trabajador');
            return null;
        },
        
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
        
        // ✅ MÉTODO MEJORADO QUE USA RUTAS DINÁMICAS CORRECTAMENTE
        fetchHTML: async (url) => {
            try {
                // ✅ CORREGIDO: Si la URL no es absoluta, construir correctamente
                let finalUrl;
                
                if (url.startsWith('http')) {
                    // URL absoluta, usar tal como está
                    finalUrl = url;
                } else if (url.startsWith('/')) {
                    // URL relativa desde raíz
                    if (url.startsWith(AppRoutes.getBaseUrl())) {
                        finalUrl = url;
                    } else {
                        finalUrl = AppRoutes.getBaseUrl() + url;
                    }
                } else {
                    // URL relativa, usar AppRoutes
                    finalUrl = AppRoutes.url(url);
                }
                
                console.log('🌐 Fetching HTML desde:', finalUrl);
                
                const response = await fetch(finalUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                    }
                });
                
                if (!response.ok) {
                    console.error(`❌ HTTP ${response.status} - ${response.statusText} para ${finalUrl}`);
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const html = await response.text();
                console.log('✅ HTML obtenido exitosamente');
                return html;
                
            } catch (error) {
                console.error('❌ Error en fetchHTML:', error);
                throw error;
            }
        },

        // ✅ MÉTODO PARA HACER PETICIONES API
        fetchAPI: async (endpoint, options = {}) => {
            try {
                const url = AppRoutes.api(endpoint);
                console.log('🌐 Fetching API desde:', url);
                
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                if (!response.ok) {
                    console.error(`❌ HTTP ${response.status} - ${response.statusText} para ${url}`);
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                console.log('✅ API response obtenida exitosamente');
                return data;
                
            } catch (error) {
                console.error('❌ Error en fetchAPI:', error);
                throw error;
            }
        }
    };

    // ========================================
    // 🔧 FUNCIÓN DE DEBUG PARA VERIFICAR RUTAS
    // ========================================
    
    window.debugRutas = function() {
        console.group('🔍 Debug de Rutas - Perfil Trabajador');
        console.log('Base URL detectada:', AppRoutes.getBaseUrl());
        console.log('URL actual:', window.location.href);
        console.log('Path actual:', window.location.pathname);
        
        const trabajadorId = window.PerfilUtils.getTrabajadorId();
        console.log('ID Trabajador:', trabajadorId);
        
        if (trabajadorId) {
            console.log('Endpoints configurados:');
            console.log('- Categorías:', PERFIL_CONFIG.endpoints.categorias());
            console.log('- Contratos base:', PERFIL_CONFIG.endpoints.contratos());
            console.log('- Contratos específico:', AppRoutes.url(`trabajadores/${trabajadorId}/contratos`));
            console.log('- Motivos:', PERFIL_CONFIG.endpoints.motivos());
            console.log('- Estadísticas:', PERFIL_CONFIG.endpoints.estadisticas());
        } else {
            console.warn('⚠️ No se pudo obtener el ID del trabajador para debug');
        }
        
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

    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE ANTES DE INICIALIZAR
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible. Asegúrate de cargar app-routes.js antes.');
        return;
    }

    // ✅ VERIFICAR QUE PODAMOS OBTENER EL ID DEL TRABAJADOR
    const trabajadorId = window.PerfilUtils.getTrabajadorId();
    if (!trabajadorId) {
        console.error('❌ No se pudo obtener el ID del trabajador en la inicialización');
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
        setTimeout(() => window.debugRutas(), 1000);
    }

    console.log('✅ Perfil Trabajador - Script principal inicializado con rutas dinámicas corregidas');
});