// ========================================
// 🎯 LISTA DE PERMISOS - CON RUTAS DINÁMICAS Y CANCELACIÓN
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para lista de permisos');
        return;
    }

    console.log('✅ Script de lista de permisos con cancelación inicializado');
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
// 🗑️ FUNCIÓN PARA ELIMINAR PERMISO DEFINITIVAMENTE
// ========================================
function eliminarPermiso(permisoId, nombreTrabajador) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para eliminar permiso');
        alert('Error: Sistema de rutas no disponible. Recargue la página e intente nuevamente.');
        return;
    }

    // ✅ CONFIRMACIÓN MÁS ESTRICTA PARA ELIMINACIÓN DEFINITIVA
    const confirmacion = confirm(
        `⚠️ ELIMINAR DEFINITIVAMENTE\n\n` +
        `¿Está seguro de ELIMINAR PERMANENTEMENTE el permiso de ${nombreTrabajador}?\n\n` +
        `ADVERTENCIA:\n` +
        `• Este permiso será BORRADO de la base de datos\n` +
        `• El trabajador será reactivado\n` +
        `• Esta acción NO SE PUEDE DESHACER\n` +
        `• Para cancelar sin borrar, use la opción "Cancelar"\n\n` +
        `¿Continuar con la eliminación definitiva?`
    );

    if (confirmacion) {
        // ✅ SEGUNDA CONFIRMACIÓN PARA ELIMINACIÓN
        const segundaConfirmacion = confirm(
            `ÚLTIMA CONFIRMACIÓN\n\n` +
            `¿Realmente desea BORRAR DEFINITIVAMENTE este permiso?\n\n` +
            `Esta es la última oportunidad para cancelar la operación.`
        );

        if (segundaConfirmacion) {
            const actionUrl = AppRoutes.url(`permisos/${permisoId}/eliminar`);
            
            console.log('🔄 Eliminando permiso definitivamente, URL:', actionUrl);
            
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
            
            console.log('✅ Enviando formulario de eliminación definitiva');
            form.submit();
        }
    }
}

// ========================================
// 🛑 FUNCIÓN PARA CANCELAR PERMISO (ABRE MODAL)
// ========================================
// NOTA: La cancelación ahora se maneja mediante modales de Bootstrap
// La función de cancelación está en el modal, no necesita JavaScript adicional
// ya que usa formularios HTML estándar

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
// 📝 VALIDACIÓN DE FORMULARIOS DE CANCELACIÓN
// ========================================

/**
 * Validar formulario de cancelación en tiempo real
 */
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todos los textareas de motivo de cancelación
    const motivoCancelacionInputs = document.querySelectorAll('textarea[name="motivo_cancelacion"]');
    
    motivoCancelacionInputs.forEach(function(textarea) {
        const modal = textarea.closest('.modal');
        const submitButton = modal.querySelector('button[type="submit"]');
        const charCounter = document.createElement('div');
        charCounter.className = 'form-text text-end';
        textarea.parentNode.appendChild(charCounter);
        
        // Función para actualizar el contador y validar
        function updateValidation() {
            const length = textarea.value.length;
            charCounter.textContent = `${length}/500 caracteres`;
            
            if (length < 10) {
                charCounter.className = 'form-text text-end text-danger';
                charCounter.textContent = `${length}/500 caracteres (mínimo 10)`;
                submitButton.disabled = true;
                textarea.classList.add('is-invalid');
            } else if (length > 500) {
                charCounter.className = 'form-text text-end text-danger';
                submitButton.disabled = true;
                textarea.classList.add('is-invalid');
            } else {
                charCounter.className = 'form-text text-end text-success';
                submitButton.disabled = false;
                textarea.classList.remove('is-invalid');
                textarea.classList.add('is-valid');
            }
        }
        
        // Eventos para validación en tiempo real
        textarea.addEventListener('input', updateValidation);
        textarea.addEventListener('keyup', updateValidation);
        
        // Validación inicial
        updateValidation();
    });
});

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
    console.log('- Cancelar permiso 1 (PATCH):', AppRoutes.permisos('1/cancelar'));
    console.log('- Eliminar permiso 1 (DELETE):', AppRoutes.url('permisos/1/eliminar'));
    console.log('- Descargar permiso 1:', AppRoutes.url('permisos/1/descargar'));
    console.log('- Subir archivo permiso 1:', AppRoutes.url('permisos/1/subir-archivo'));
    console.groupEnd();
}

/**
 * Función de debug para verificar modales de cancelación
 */
function debugModalesCancelacion() {
    const modales = document.querySelectorAll('[id^="modalCancelar"]');
    console.group('🔍 Debug Modales de Cancelación');
    console.log(`Total de modales encontrados: ${modales.length}`);
    
    modales.forEach((modal, index) => {
        const permisoId = modal.id.replace('modalCancelar', '');
        const form = modal.querySelector('form');
        const textarea = modal.querySelector('textarea[name="motivo_cancelacion"]');
        
        console.log(`Modal ${index + 1}:`, {
            id: modal.id,
            permisoId: permisoId,
            formAction: form?.action,
            textareaPresent: !!textarea
        });
    });
    
    console.groupEnd();
}

// ========================================
// 🌐 EXPONER FUNCIONES GLOBALMENTE
// ========================================

// Asegurar que las funciones estén disponibles globalmente
window.finalizarPermiso = finalizarPermiso;
window.eliminarPermiso = eliminarPermiso; // ✅ NUEVA FUNCIÓN
window.subirArchivoPermiso = subirArchivoPermiso;
window.descargarArchivoPermiso = descargarArchivoPermiso;
window.debugRutasPermisos = debugRutasPermisos;
window.debugModalesCancelacion = debugModalesCancelacion; // ✅ NUEVA FUNCIÓN DE DEBUG