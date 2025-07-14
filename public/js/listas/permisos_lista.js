// ========================================
// 🎯 LISTA DE PERMISOS - CON RUTAS DINÁMICAS
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para lista de permisos');
        return;
    }

    console.log('✅ Script de lista de permisos con rutas dinámicas inicializado');
});

// ========================================
// 🔄 FUNCIÓN PARA FINALIZAR PERMISO
// ========================================
function finalizarPermiso(permisoId, nombreTrabajador) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para finalizar permiso');
        alert('Error: Sistema de rutas no disponible. Recargue la página e intente nuevamente.');
        return;
    }

    if (confirm(`¿Está seguro de finalizar el permiso de ${nombreTrabajador}? El trabajador será reactivado.`)) {
        // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
        const actionUrl = AppRoutes.permisos(`${permisoId}/finalizar`);
        
        console.log('🔄 Finalizando permiso, URL:', actionUrl);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = actionUrl;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        
        console.log('✅ Enviando formulario de finalización de permiso');
        form.submit();
    }
}

// ========================================
// 🗑️ FUNCIÓN PARA CANCELAR/ELIMINAR PERMISO
// ========================================
function cancelarPermiso(permisoId, nombreTrabajador) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para cancelar permiso');
        alert('Error: Sistema de rutas no disponible. Recargue la página e intente nuevamente.');
        return;
    }

    if (confirm(`⚠️ ¿Está seguro de ELIMINAR DEFINITIVAMENTE el permiso de ${nombreTrabajador}?\n\nEsta acción:\n• Eliminará el permiso de la base de datos\n• Reactivará al trabajador\n• NO SE PUEDE DESHACER\n\n¿Continuar?`)) {
        // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
        const actionUrl = AppRoutes.permisos(`${permisoId}/cancelar`);
        
        console.log('🔄 Cancelando permiso, URL:', actionUrl);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = actionUrl;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        
        console.log('✅ Enviando formulario de cancelación de permiso');
        form.submit();
    }
}

// ========================================
// 🔧 FUNCIONES AUXILIARES PARA PERMISOS
// ========================================

/**
 * Función para subir archivo de permiso (si es necesaria)
 * @param {number} permisoId - ID del permiso
 */
function subirArchivoPermiso(permisoId) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para subir archivo');
        return;
    }

    const actionUrl = AppRoutes.url(`permisos/${permisoId}/subir-archivo`);
    console.log('📎 URL para subir archivo:', actionUrl);
    
    // Esta función se puede usar si necesitas manejar la subida de archivos
    // por JavaScript en lugar de formularios estáticos
}

/**
 * Función para descargar archivo de permiso
 * @param {number} permisoId - ID del permiso
 */
function descargarArchivoPermiso(permisoId) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para descargar archivo');
        return;
    }

    const downloadUrl = AppRoutes.url(`permisos/${permisoId}/descargar`);
    console.log('📥 Descargando archivo desde:', downloadUrl);
    
    // Abrir en nueva ventana para descargar
    window.open(downloadUrl, '_blank');
}

// ========================================
// 🛠️ UTILIDADES DE DEBUG
// ========================================

/**
 * Función de debug para verificar rutas de permisos
 */
function debugRutasPermisos() {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para debug');
        return;
    }

    console.group('🔍 Debug Rutas de Permisos');
    console.log('Base URL:', AppRoutes.getBaseUrl());
    console.log('Rutas de permisos:');
    console.log('- Lista:', AppRoutes.permisos());
    console.log('- Finalizar permiso 1:', AppRoutes.permisos('1/finalizar'));
    console.log('- Cancelar permiso 1:', AppRoutes.permisos('1/cancelar'));
    console.log('- Descargar permiso 1:', AppRoutes.url('permisos/1/descargar'));
    console.log('- Subir archivo permiso 1:', AppRoutes.url('permisos/1/subir-archivo'));
    console.groupEnd();
}

// ========================================
// 🌐 EXPONER FUNCIONES GLOBALMENTE
// ========================================

// Asegurar que las funciones estén disponibles globalmente
window.finalizarPermiso = finalizarPermiso;
window.cancelarPermiso = cancelarPermiso;
window.subirArchivoPermiso = subirArchivoPermiso;
window.descargarArchivoPermiso = descargarArchivoPermiso;
window.debugRutasPermisos = debugRutasPermisos;