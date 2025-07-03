// ========================================
// 📋 HISTORIAL DE PERMISOS
// ========================================

// Cargar historial de permisos
function cargarHistorialPermisos() {
    const contenedor = document.getElementById('permisos-content');
    const trabajadorId = document.querySelector('[data-trabajador-id]').getAttribute('data-trabajador-id');
    
    fetch(`/trabajadores/${trabajadorId}/permisos/historial`)
        .then(response => response.json())
        .then(data => {
            contenedor.innerHTML = data.html;
            initPermisosEvents();
        })
        .catch(error => {
            console.error('Error:', error);
            contenedor.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i> 
                    Error al cargar el historial de permisos
                </div>
            `;
        });
}

// Eventos de filtros de permisos
function initPermisosEvents() {
    const btnFiltrar = document.getElementById('filtrar-permisos');
    
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function() {
            const tipo = document.getElementById('tipo-permiso').value;
            const estado = document.getElementById('estado-permiso').value;
            const desde = document.getElementById('fecha-desde').value;
            const hasta = document.getElementById('fecha-hasta').value;
            
            const params = new URLSearchParams({
                tipo: tipo,
                estado: estado,
                desde: desde,
                hasta: hasta
            });
            
            const trabajadorId = document.querySelector('[data-trabajador-id]').getAttribute('data-trabajador-id');
            const contenedor = document.getElementById('contenedor-permisos');
            
            contenedor.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Filtrando permisos...</p>
                </div>
            `;
            
            fetch(`/trabajadores/${trabajadorId}/permisos/historial?${params}`)
                .then(response => response.json())
                .then(data => {
                    contenedor.innerHTML = data.html;
                    initPermisosEvents(); // Reinicializar eventos después del filtrado
                })
                .catch(error => {
                    console.error('Error:', error);
                    contenedor.innerHTML = `
                        <div class="alert alert-danger">
                            Error al aplicar filtros
                        </div>
                    `;
                });
        });
    }
}

// ✅ Función para ver detalle de permiso (solo llenar datos)
function verDetallePermiso(permisoId) {
    // Mostrar loading y ocultar contenido/error
    document.getElementById('permiso-loading').style.display = 'block';
    document.getElementById('permiso-content').style.display = 'none';
    document.getElementById('permiso-error').style.display = 'none';
    
    fetch(`/permisos/${permisoId}/detalle`)
        .then(response => response.json())
        .then(data => {
            const permiso = data.permiso;
            
            // Llenar los campos del modal
            document.getElementById('permiso-id').textContent = '#' + permiso.id;
            document.getElementById('permiso-trabajador').textContent = permiso.trabajador;
            document.getElementById('permiso-tipo').textContent = permiso.tipo;
            
            const estadoBadge = document.getElementById('permiso-estado');
            estadoBadge.textContent = permiso.estado;
            estadoBadge.className = `badge bg-${permiso.estado_clase}`;
            
            document.getElementById('permiso-fecha-inicio').textContent = permiso.fecha_inicio;
            document.getElementById('permiso-fecha-fin').textContent = permiso.fecha_fin;
            document.getElementById('permiso-dias').textContent = permiso.dias_de_permiso;
            document.getElementById('permiso-fecha-solicitud').textContent = permiso.fecha_solicitud;
            document.getElementById('permiso-motivo').textContent = permiso.motivo;
            
            // Mostrar/ocultar observaciones
            const observacionesContainer = document.getElementById('permiso-observaciones-container');
            if (permiso.observaciones) {
                document.getElementById('permiso-observaciones').textContent = permiso.observaciones;
                observacionesContainer.style.display = 'block';
            } else {
                observacionesContainer.style.display = 'none';
            }
            
            // Ocultar loading y mostrar contenido
            document.getElementById('permiso-loading').style.display = 'none';
            document.getElementById('permiso-content').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            // Ocultar loading y mostrar error
            document.getElementById('permiso-loading').style.display = 'none';
            document.getElementById('permiso-error').style.display = 'block';
        });
}

// ========================================
// 🚫 HISTORIAL DE BAJAS
// ========================================

// Cargar historial de bajas
function cargarHistorialBajas() {
    const contenedor = document.getElementById('bajas-content');
    const trabajadorId = document.querySelector('[data-trabajador-id]').getAttribute('data-trabajador-id');
    
    fetch(`/trabajadores/${trabajadorId}/bajas/historial`)
        .then(response => response.json())
        .then(data => {
            contenedor.innerHTML = data.html;
            initBajasEvents();
        })
        .catch(error => {
            console.error('Error al cargar historial de bajas:', error);
            contenedor.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i> 
                    Error al cargar el historial de bajas
                </div>
            `;
        });
}

// Eventos de filtros de bajas
function initBajasEvents() {
    const btnFiltrar = document.getElementById('filtrar-bajas');
    
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function() {
            const estado = document.getElementById('estado-baja').value;
            const condicion = document.getElementById('condicion-baja').value;
            const tipoBaja = document.getElementById('tipo-baja-filtro').value;
            const desde = document.getElementById('fecha-desde-baja').value;
            const hasta = document.getElementById('fecha-hasta-baja').value;
            
            const params = new URLSearchParams({
                estado: estado,
                condicion: condicion,
                tipo_baja: tipoBaja,
                desde: desde,
                hasta: hasta
            });
            
            const trabajadorId = document.querySelector('[data-trabajador-id]').getAttribute('data-trabajador-id');
            const contenedor = document.getElementById('contenedor-bajas');
            
            contenedor.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Filtrando bajas...</p>
                </div>
            `;
            
            fetch(`/trabajadores/${trabajadorId}/bajas/historial?${params}`)
                .then(response => response.json())
                .then(data => {
                    contenedor.innerHTML = data.html;
                    initBajasEvents(); // Reinicializar eventos después del filtrado
                })
                .catch(error => {
                    console.error('Error:', error);
                    contenedor.innerHTML = `
                        <div class="alert alert-danger">
                            Error al aplicar filtros de bajas
                        </div>
                    `;
                });
        });
    }
}

// ✅ Función para ver detalle de baja (solo llenar datos)
function verDetalleBaja(bajaId) {
    // Mostrar loading y ocultar contenido/error
    document.getElementById('baja-loading').style.display = 'block';
    document.getElementById('baja-content').style.display = 'none';
    document.getElementById('baja-error').style.display = 'none';
    
    fetch(`/despidos/${bajaId}/detalle`)
        .then(response => response.json())
        .then(data => {
            const baja = data.baja;
            
            // Llenar campos básicos
            document.getElementById('baja-id').textContent = '#' + baja.id;
            document.getElementById('baja-trabajador').textContent = baja.trabajador;
            document.getElementById('baja-fecha').textContent = baja.fecha_baja;
            document.getElementById('baja-fecha-relativa').textContent = baja.fecha_baja_relativa;
            document.getElementById('baja-condicion').textContent = baja.condicion_salida;
            document.getElementById('baja-tipo').textContent = baja.tipo_baja;
            document.getElementById('baja-fecha-creacion').textContent = baja.fecha_creacion;
            document.getElementById('baja-motivo').textContent = baja.motivo;
            
            // Estado con clase dinámica
            const estadoBadge = document.getElementById('baja-estado');
            estadoBadge.textContent = baja.estado;
            estadoBadge.className = `badge bg-${baja.estado_clase}`;
            
            // Fecha de reintegro (opcional)
            const reintegroRow = document.getElementById('baja-reintegro-row');
            if (baja.fecha_reintegro) {
                document.getElementById('baja-reintegro').textContent = baja.fecha_reintegro;
                document.getElementById('baja-reintegro-relativa').textContent = baja.fecha_reintegro_relativa;
                reintegroRow.style.display = 'table-row';
            } else {
                reintegroRow.style.display = 'none';
            }
            
            // Observaciones (opcional)
            const observacionesContainer = document.getElementById('baja-observaciones-container');
            if (baja.observaciones) {
                document.getElementById('baja-observaciones').textContent = baja.observaciones;
                observacionesContainer.style.display = 'block';
            } else {
                observacionesContainer.style.display = 'none';
            }
            
            // Información de cancelación (opcional)
            const cancelacionContainer = document.getElementById('baja-cancelacion-container');
            if (baja.fecha_cancelacion) {
                document.getElementById('baja-fecha-cancelacion').textContent = baja.fecha_cancelacion;
                
                const motivoCancelacionContainer = document.getElementById('baja-motivo-cancelacion-container');
                if (baja.motivo_cancelacion) {
                    document.getElementById('baja-motivo-cancelacion').textContent = baja.motivo_cancelacion;
                    motivoCancelacionContainer.style.display = 'block';
                } else {
                    motivoCancelacionContainer.style.display = 'none';
                }
                
                cancelacionContainer.style.display = 'block';
            } else {
                cancelacionContainer.style.display = 'none';
            }
            
            // Ocultar loading y mostrar contenido
            document.getElementById('baja-loading').style.display = 'none';
            document.getElementById('baja-content').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            // Ocultar loading y mostrar error
            document.getElementById('baja-loading').style.display = 'none';
            document.getElementById('baja-error').style.display = 'block';
        });
}

// ========================================
// 🚀 INICIALIZACIÓN
// ========================================

// Cargar historial al mostrar las pestañas
document.addEventListener('DOMContentLoaded', function() {
    // Pestaña de permisos
    const permisosTab = document.getElementById('nav-permisos-tab');
    if (permisosTab) {
        permisosTab.addEventListener('shown.bs.tab', function() {
            cargarHistorialPermisos();
        });
    }
    
    // Pestaña de bajas
    const bajasTab = document.getElementById('nav-bajas-tab');
    if (bajasTab) {
        bajasTab.addEventListener('shown.bs.tab', function() {
            cargarHistorialBajas();
        });
    }
});