/**
 * ✅ SCRIPT GLOBAL DE RUTAS DINÁMICAS - MEJORADO
 * Detecta automáticamente la base URL y proporciona funciones para construir rutas
 * app-routes.js
 */

window.AppRoutes = {
    
    // =================================
    // 🎯 INICIALIZACIÓN Y DETECCIÓN
    // =================================
    
    baseUrl: '',
    initialized: false,
    
    init() {
        if (this.initialized) return;
        
        this.detectBaseUrl();
        this.initialized = true;
        console.log('🚀 AppRoutes inicializado - Base URL:', this.baseUrl);
        
        // ✅ VERIFICAR FUNCIONALIDAD
        this.selfTest();
    },

    detectBaseUrl() {
        // Obtener la URL actual
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        
        console.log('🔍 Detectando base URL desde:', currentPath);
        
        // Si estamos en una ruta como /rh/dashboard, extraer /rh/
        // Si estamos en raíz como /dashboard, la base será vacía
        
        const pathSegments = currentPath.split('/').filter(segment => segment);
        
        // Buscar patrones conocidos de Laravel para determinar la base
        const laravelRoutes = ['dashboard', 'trabajadores', 'login', 'api', 'configuracion', 'permisos', 'despidos'];
        
        let baseSegments = [];
        
        for (let i = 0; i < pathSegments.length; i++) {
            if (laravelRoutes.includes(pathSegments[i])) {
                break;
            }
            baseSegments.push(pathSegments[i]);
        }
        
        // Construir la base URL
        this.baseUrl = baseSegments.length > 0 ? '/' + baseSegments.join('/') : '';
        
        // Asegurar que termine sin slash para concatenación correcta
        if (this.baseUrl.endsWith('/')) {
            this.baseUrl = this.baseUrl.slice(0, -1);
        }
        
        console.log('✅ Base URL detectada:', this.baseUrl);
    },

    // ✅ NUEVO: Test de funcionalidad
    selfTest() {
        try {
            const testPaths = [
                'dashboard',
                'trabajadores/123',
                'api/categorias',
                'trabajadores/123/contratos'
            ];
            
            console.group('🧪 Test de AppRoutes');
            testPaths.forEach(path => {
                const url = this.url(path);
                console.log(`- ${path} → ${url}`);
            });
            console.groupEnd();
            
        } catch (error) {
            console.error('❌ Error en selfTest de AppRoutes:', error);
        }
    },

    // =================================
    // 🛠️ FUNCIONES PÚBLICAS
    // =================================

    /**
     * Construye una URL completa para una ruta
     * @param {string} path - Ruta sin el slash inicial (ej: 'api/categorias/1')
     * @returns {string} - URL completa
     */
    url(path) {
        if (!path) return this.baseUrl || '/';
        
        // Limpiar el path de slashes iniciales
        const cleanPath = path.replace(/^\/+/, '');
        
        if (!cleanPath) return this.baseUrl || '/';
        
        const finalUrl = `${this.baseUrl}/${cleanPath}`;
        
        // ✅ DEBUG en desarrollo
        if (window.APP_DEBUG) {
            console.log(`🔗 AppRoutes.url('${path}') → '${finalUrl}'`);
        }
        
        return finalUrl;
    },

    /**
     * Construye una URL para rutas API
     * @param {string} endpoint - Endpoint sin 'api/' (ej: 'categorias/1')
     * @returns {string} - URL completa de API
     */
    api(endpoint) {
        if (!endpoint) return this.url('api');
        
        const cleanEndpoint = endpoint.replace(/^\/+/, '');
        return this.url(`api/${cleanEndpoint}`);
    },

    /**
     * Construye una URL para rutas de trabajadores
     * @param {string} path - Ruta dentro de trabajadores (ej: 'crear', '1/perfil')
     * @returns {string} - URL completa
     */
    trabajadores(path = '') {
        if (!path) return this.url('trabajadores');
        
        const cleanPath = path.replace(/^\/+/, '');
        return this.url(`trabajadores/${cleanPath}`);
    },

    /**
     * Construye una URL para rutas de configuración
     * @param {string} path - Ruta dentro de configuración
     * @returns {string} - URL completa
     */
    configuracion(path = '') {
        if (!path) return this.url('configuracion');
        
        const cleanPath = path.replace(/^\/+/, '');
        return this.url(`configuracion/${cleanPath}`);
    },

    /**
     * Construye una URL para rutas de permisos
     * @param {string} path - Ruta dentro de permisos
     * @returns {string} - URL completa
     */
    permisos(path = '') {
        if (!path) return this.url('permisos');
        
        const cleanPath = path.replace(/^\/+/, '');
        return this.url(`permisos/${cleanPath}`);
    },

    /**
     * Construye una URL para rutas de despidos
     * @param {string} path - Ruta dentro de despidos
     * @returns {string} - URL completa
     */
    despidos(path = '') {
        if (!path) return this.url('despidos');
        
        const cleanPath = path.replace(/^\/+/, '');
        return this.url(`despidos/${cleanPath}`);
    },

    notificaciones() {
    return this.url('notificaciones/eventos');
    },

    // =================================
    // 🔧 UTILIDADES
    // =================================

    /**
     * Obtiene la base URL actual
     * @returns {string}
     */
    getBaseUrl() {
        return this.baseUrl;
    },

    /**
     * Verifica si una URL es de la aplicación actual
     * @param {string} url 
     * @returns {boolean}
     */
    isAppUrl(url) {
        if (!url) return false;
        return url.startsWith(this.baseUrl) || url.startsWith('/');
    },

    /**
     * Redirige a una ruta de la aplicación
     * @param {string} path - Ruta a redireccionar
     */
    redirect(path) {
        const url = this.url(path);
        console.log('🔄 Redirigiendo a:', url);
        window.location.href = url;
    },

    /**
     * ✅ NUEVO: Obtener información de debug
     */
    getDebugInfo() {
        return {
            baseUrl: this.baseUrl,
            currentPath: window.location.pathname,
            currentUrl: window.location.href,
            initialized: this.initialized
        };
    }
};

// =================================
// 🚀 AUTO-INICIALIZACIÓN
// =================================

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AppRoutes.init());
} else {
    AppRoutes.init();
}

// =================================
// 🌐 FUNCIONES GLOBALES DE CONVENIENCIA
// =================================

// Exponer funciones globales para fácil acceso
window.appUrl = (path) => AppRoutes.url(path);
window.apiUrl = (endpoint) => AppRoutes.api(endpoint);
window.trabajadoresUrl = (path) => AppRoutes.trabajadores(path);
window.configuracionUrl = (path) => AppRoutes.configuracion(path);
window.permisosUrl = (path) => AppRoutes.permisos(path);
window.despidosUrl = (path) => AppRoutes.despidos(path);

// ✅ NUEVO: Función global de debug
window.debugAppRoutes = () => {
    console.table(AppRoutes.getDebugInfo());
};