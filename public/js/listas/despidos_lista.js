// ========================================
// 🎯 LISTA DE DESPIDOS - CON RUTAS DINÁMICAS
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para lista de despidos');
        return;
    }

    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    console.log('✅ Script de lista de despidos con rutas dinámicas inicializado');
});

// ========================================
// 📋 FUNCIÓN PARA VER HISTORIAL COMPLETO
// ========================================
async function verHistorialTrabajador(trabajadorId) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para ver historial');
        alert('Error: Sistema de rutas no disponible. Recargue la página e intente nuevamente.');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    const content = document.getElementById('historialContent');
    
    // Mostrar modal con loading
    modal.show();
    
    // Resetear contenido a loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando historial...</p>
        </div>
    `;
    
    try {
        // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
        const historialUrl = AppRoutes.despidos(`trabajador/${trabajadorId}/historial`);
        
        console.log('🔄 Cargando historial desde:', historialUrl);
        
        const response = await fetch(historialUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        let html = `
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-person-circle"></i> ${data.trabajador}
                        </h6>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <strong>${data.total_bajas}</strong><br>
                                <small>Total de Bajas</small>
                            </div>
                            <div class="col-md-4">
                                <strong class="text-danger">${data.bajas_activas}</strong><br>
                                <small>Bajas Activas</small>
                            </div>
                            <div class="col-md-4">
                                <strong class="text-success">${data.bajas_canceladas}</strong><br>
                                <small>Bajas Canceladas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (data.historial.length === 0) {
            html += `
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">Sin historial de bajas</h5>
                    <p class="text-muted">Este trabajador no tiene registros de bajas anteriores.</p>
                </div>
            `;
        } else {
            html += `
                <div class="timeline">
                    ${data.historial.map(baja => `
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker me-3 mt-1">
                                    <div class="rounded-circle ${baja.estado === 'Activo' ? 'bg-danger' : 'bg-success'}" 
                                         style="width: 12px; height: 12px;"></div>
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <div class="card ${baja.estado === 'Activo' ? 'border-danger' : 'border-success'} mb-0">
                                        <div class="card-header ${baja.estado === 'Activo' ? 'bg-danger text-white' : 'bg-success text-white'} py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><strong>Baja #${baja.id}</strong></span>
                                                <span class="badge ${baja.estado === 'Activo' ? 'bg-light text-dark' : 'bg-dark'}">${baja.estado}</span>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Fecha de Baja:</strong> ${baja.fecha_baja}<br>
                                                    <strong>Condición:</strong> ${baja.condicion_salida}
                                                </div>
                                                <div class="col-md-6">
                                                    ${baja.fecha_cancelacion ? `
                                                        <strong>Cancelado:</strong> ${baja.fecha_cancelacion}<br>
                                                        <strong>Por:</strong> ${baja.cancelado_por}
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <strong>Motivo:</strong><br>
                                            <div class="bg-light p-2 rounded small">${baja.motivo}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        content.innerHTML = html;
        console.log('✅ Historial cargado exitosamente');
        
    } catch (error) {
        console.error('❌ Error al cargar historial:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error al cargar el historial</strong><br>
                <small class="text-muted">${error.message}</small><br>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="verHistorialTrabajador(${trabajadorId})">
                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// ========================================
// 🔄 FUNCIÓN PARA REACTIVAR TRABAJADOR
// ========================================
function reactivarTrabajador(despidoId, nombreTrabajador) {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para reactivar trabajador');
        alert('Error: Sistema de rutas no disponible. Recargue la página e intente nuevamente.');
        return;
    }

    if (confirm(`¿Está seguro de reactivar a ${nombreTrabajador}?\n\nEsta acción:\n• Cancelará la baja actual\n• Reactivará al trabajador\n• Marcará la baja como "Cancelada"\n\n¿Continuar?`)) {
        // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
        const actionUrl = AppRoutes.despidos(`${despidoId}/cancelar`);
        
        console.log('🔄 Reactivando trabajador, URL:', actionUrl);
        
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
        
        console.log('✅ Enviando formulario de reactivación');
        form.submit();
    }
}

// ========================================
// 🛠️ UTILIDADES DE DEBUG
// ========================================

/**
 * Función de debug para verificar rutas de despidos
 */
function debugRutasDespidos() {
    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no disponible para debug');
        return;
    }

    console.group('🔍 Debug Rutas de Despidos');
    console.log('Base URL:', AppRoutes.getBaseUrl());
    console.log('Rutas de despidos:');
    console.log('- Lista:', AppRoutes.despidos());
    console.log('- Historial trabajador 1:', AppRoutes.despidos('trabajador/1/historial'));
    console.log('- Cancelar despido 1:', AppRoutes.despidos('1/cancelar'));
    console.log('- Ver detalles despido 1:', AppRoutes.despidos('1'));
    console.groupEnd();
}

// ========================================
// 🌐 EXPONER FUNCIONES GLOBALMENTE
// ========================================

// Asegurar que las funciones estén disponibles globalmente
window.verHistorialTrabajador = verHistorialTrabajador;
window.reactivarTrabajador = reactivarTrabajador;
window.debugRutasDespidos = debugRutasDespidos;x