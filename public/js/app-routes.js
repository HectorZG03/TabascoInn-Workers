/**
 * ✅ SCRIPT GLOBAL DE RUTAS DINÁMICAS
 * Detecta automáticamente la base URL y proporciona funciones para construir rutas
 * app-routes.js
 */

window.AppRoutes = {
    
    // =================================
    // 🎯 INICIALIZACIÓN Y DETECCIÓN
    // =================================
    
    baseUrl: '',
    
    init() {
        this.detectBaseUrl();
        console.log('🚀 AppRoutes inicializado - Base URL:', this.baseUrl);
    },

    detectBaseUrl() {
        // Obtener la URL actual
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        
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
        // Limpiar el path de slashes iniciales
        const cleanPath = path.replace(/^\/+/, '');
        return `${this.baseUrl}/${cleanPath}`;
    },

    /**
     * Construye una URL para rutas API
     * @param {string} endpoint - Endpoint sin 'api/' (ej: 'categorias/1')
     * @returns {string} - URL completa de API
     */
    api(endpoint) {
        const cleanEndpoint = endpoint.replace(/^\/+/, '');
        return this.url(`api/${cleanEndpoint}`);
    },

    /**
     * Construye una URL para rutas de trabajadores
     * @param {string} path - Ruta dentro de trabajadores (ej: 'crear', '1/perfil')
     * @returns {string} - URL completa
     */
    trabajadores(path = '') {
        const cleanPath = path.replace(/^\/+/, '');
        return cleanPath ? this.url(`trabajadores/${cleanPath}`) : this.url('trabajadores');
    },

    /**
     * Construye una URL para rutas de configuración
     * @param {string} path - Ruta dentro de configuración
     * @returns {string} - URL completa
     */
    configuracion(path = '') {
        const cleanPath = path.replace(/^\/+/, '');
        return cleanPath ? this.url(`configuracion/${cleanPath}`) : this.url('configuracion');
    },

    /**
     * Construye una URL para rutas de permisos
     * @param {string} path - Ruta dentro de permisos
     * @returns {string} - URL completa
     */
    permisos(path = '') {
        const cleanPath = path.replace(/^\/+/, '');
        return cleanPath ? this.url(`permisos/${cleanPath}`) : this.url('permisos');
    },

    /**
     * Construye una URL para rutas de despidos
     * @param {string} path - Ruta dentro de despidos
     * @returns {string} - URL completa
     */
    despidos(path = '') {
        const cleanPath = path.replace(/^\/+/, '');
        return cleanPath ? this.url(`despidos/${cleanPath}`) : this.url('despidos');
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
        return url.startsWith(this.baseUrl) || url.startsWith('/');
    },

    /**
     * Redirige a una ruta de la aplicación
     * @param {string} path - Ruta a redireccionar
     */
    redirect(path) {
        window.location.href = this.url(path);
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