// ========================================
// 🔗 NAVEGACIÓN Y PESTAÑAS
// ========================================

window.initNavegacion = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    // Cargar pestaña activa desde URL
    if (activeTab && activeTab !== 'contratos') {
        const tabElement = document.querySelector(`[data-bs-target="#nav-${activeTab}"]`);
        if (tabElement) {
            new bootstrap.Tab(tabElement).show();
        }
    }

    // Manejar pestaña de contratos especialmente (carga dinámica)
    if (activeTab === 'contratos') {
        const tabElement = document.querySelector('[data-bs-target="#nav-contratos"]');
        if (tabElement) {
            new bootstrap.Tab(tabElement).show();
        }
    }

    // Actualizar URL al cambiar pestañas
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function(event) {
            const targetTab = event.target.getAttribute('data-bs-target').replace('#nav-', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState(null, '', url);
        });
    });
    
    console.log('🔗 Navegación inicializada');
};