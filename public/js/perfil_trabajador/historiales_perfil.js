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

// Eventos de filtros
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

// Cargar al mostrar la pestaña
document.addEventListener('DOMContentLoaded', function() {
    const permisosTab = document.getElementById('nav-permisos-tab');
    
    permisosTab.addEventListener('shown.bs.tab', function() {
        cargarHistorialPermisos();
    });
});