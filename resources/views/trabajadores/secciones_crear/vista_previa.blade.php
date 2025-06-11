<!-- ✅ SECCIÓN: VISTA PREVIA COMPACTA CON TOGGLE -->
<div class="card shadow mb-4 sticky-top" id="vistaPreviewCard">
    <div class="card-header bg-info text-white py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 small">
                <i class="bi bi-eye"></i> Vista Previa
            </h6>
            <button type="button" class="btn btn-sm btn-outline-light" id="togglePreview" data-bs-toggle="collapse" data-bs-target="#previewContent" aria-expanded="true">
                <i class="bi bi-chevron-up" id="toggleIcon"></i>
            </button>
        </div>
    </div>
    <div class="collapse show" id="previewContent">
        <div class="card-body p-3">
            <!-- Header Compacto -->
            <div class="text-center mb-2">
                <i class="bi bi-person-circle text-muted" style="font-size: 2.5rem;"></i>
                <div class="mt-1">
                    <h6 id="preview-nombre" class="mb-0 small text-muted">Nombre del Trabajador</h6>
                    <small id="preview-categoria" class="text-muted">Categoría - Área</small>
                </div>
            </div>
            
            <!-- Info Principal en 2x2 Grid -->
            <div class="row text-center g-2 mb-2">
                <div class="col-6">
                    <div class="border rounded p-2">
                        <i class="bi bi-cash text-success small"></i>
                        <div class="fw-bold text-success small" id="preview-sueldo">$0.00</div>
                        <tiny class="text-muted">Sueldo</tiny>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-2">
                        <i class="bi bi-calendar text-primary small"></i>
                        <div class="fw-bold text-primary small" id="preview-edad">-- años</div>
                        <tiny class="text-muted">Edad</tiny>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-2">
                        <i class="bi bi-clock text-warning small"></i>
                        <div class="fw-bold text-warning small" id="preview-horas">-- hrs</div>
                        <tiny class="text-muted">Jornada</tiny>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-2">
                        <i class="bi bi-sun text-info small"></i>
                        <div class="fw-bold text-info small" id="preview-turno">--</div>
                        <tiny class="text-muted">Turno</tiny>
                    </div>
                </div>
            </div>

            <!-- Ubicación Compacta -->
            <div class="text-center">
                <div class="bg-light rounded p-2">
                    <i class="bi bi-geo-alt text-secondary small"></i>
                    <span class="fw-bold text-secondary small" id="preview-ubicacion">No especificada</span>
                    <br><tiny class="text-muted">Ubicación Actual</tiny>
                </div>
            </div>

            <!-- Status Horario Compacto -->
            <div class="mt-2">
                <div id="preview-horario-status" class="alert alert-info py-1 px-2 d-none">
                    <tiny><i class="bi bi-info-circle"></i> <span id="preview-horario-texto"></span></tiny>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
tiny {
    font-size: 0.7rem;
    line-height: 1;
}

#togglePreview {
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.3s ease;
}

#togglePreview:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.5);
}

#toggleIcon {
    transition: transform 0.3s ease;
}

#previewContent.collapsing #toggleIcon,
#previewContent.show #toggleIcon {
    transform: rotate(0deg);
}

#previewContent:not(.show) ~ .card-header #toggleIcon {
    transform: rotate(180deg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePreview');
    const toggleIcon = document.getElementById('toggleIcon');
    const previewContent = document.getElementById('previewContent');
    
    // Manejar el cambio de icono cuando se colapsa/expande
    previewContent.addEventListener('hidden.bs.collapse', function () {
        toggleIcon.classList.remove('bi-chevron-up');
        toggleIcon.classList.add('bi-chevron-down');
    });
    
    previewContent.addEventListener('shown.bs.collapse', function () {
        toggleIcon.classList.remove('bi-chevron-down');
        toggleIcon.classList.add('bi-chevron-up');
    });
    
    // Guardar estado en localStorage para recordar preferencia
    previewContent.addEventListener('hidden.bs.collapse', function () {
        localStorage.setItem('vistaPreviewCollapsed', 'true');
    });
    
    previewContent.addEventListener('shown.bs.collapse', function () {
        localStorage.setItem('vistaPreviewCollapsed', 'false');
    });
    
    // Restaurar estado previo al cargar la página
    const wasCollapsed = localStorage.getItem('vistaPreviewCollapsed') === 'true';
    if (wasCollapsed) {
        previewContent.classList.remove('show');
        toggleIcon.classList.remove('bi-chevron-up');
        toggleIcon.classList.add('bi-chevron-down');
    }
});
</script>