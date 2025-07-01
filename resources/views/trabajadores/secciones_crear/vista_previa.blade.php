<!-- 🔘 Botón para ocultar/mostrar la vista previa -->
<div class="mb-2 text-end">
    <button id="toggleVistaPrevia" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye-slash"></i> Ocultar Vista Previa
    </button>
</div>

<!-- ✅ SECCIÓN: VISTA PREVIA ACTUALIZADA -->
<div id="seccionVistaPrevia" class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-eye"></i> Vista Previa
        </h5>
    </div>
    <div class="card-body">
        <!-- Información Básica -->
        <div class="text-center mb-3">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                 style="width: 60px; height: 60px;">
                <i class="bi bi-person-circle fs-1 text-secondary"></i>
            </div>
            <h6 class="mt-2 mb-1 text-uppercase" id="preview-nombre">Nombre del Trabajador</h6>
            <small class="text-muted text-uppercase" id="preview-categoria">Categoría - Área</small>
        </div>

        <!-- Datos Principales -->
        <div class="row g-2 text-uppercase">
            <!-- Edad -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Edad</small>
                    <span class="fw-bold" id="preview-edad">-- años</span>
                </div>
            </div>

            <!-- Sueldo -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Sueldo Diario</small>
                    <span class="fw-bold text-success" id="preview-sueldo">$0.00</span>
                </div>
            </div>

            <!-- Horas de Trabajo -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Horas/Día</small>
                    <span class="fw-bold" id="preview-horas">-- hrs</span>
                </div>
            </div>

            <!-- Turno -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Turno</small>
                    <span class="fw-bold" id="preview-turno">--</span>
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="mt-3 text-uppercase">
            <div class="bg-light rounded p-2">
                <small class="text-muted d-block">
                    <i class="bi bi-geo-alt me-1"></i>Ubicación
                </small>
                <span id="preview-ubicacion">No especificada</span>
            </div>
        </div>

        <!-- ✅ ESTADO - ACTUALIZADO -->
        <div class="mt-3 text-uppercase">
            <div class="border border-primary rounded p-2 bg-primary bg-opacity-10">
                <small class="text-primary d-block">
                    <i class="bi bi-person-gear me-1"></i>Estado del Trabajador
                </small>
                <span id="preview-estado" class="text-primary fw-bold">
                    Se configurará en el siguiente paso
                </span>
            </div>
        </div>

        <!-- ✅ NUEVO: Resumen de Horarios -->
        <div class="mt-3 text-uppercase">
            <div class="card border-0 bg-light">
                <div class="card-body p-2">
                    <h6 class="card-title mb-2 text-center">
                        <i class="bi bi-clock me-1"></i>Resumen Horario
                    </h6>
                    <div class="row g-1 text-center">
                        <div class="col-3">
                            <small class="text-muted d-block">Horas/Día</small>
                            <span class="fw-bold" id="horas-diarias">-</span>
                        </div>
                        <div class="col-3">
                            <small class="text-muted d-block">Horas/Sem</small>
                            <span class="fw-bold" id="horas-semanales">-</span>
                        </div>
                        <div class="col-3">
                            <small class="text-muted d-block">Días Lab.</small>
                            <span class="fw-bold" id="dias-laborables-count">-</span>
                        </div>
                        <div class="col-3">
                            <small class="text-muted d-block">Turno</small>
                            <span class="fw-bold" id="turno-calculado">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nota informativa -->
        <div class="alert alert-info mt-3 mb-0">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Siguiente paso:</strong> Configurar estado inicial y generar contrato automáticamente.
            </small>
        </div>
    </div>
</div>

<script>
    // 👁️ Mostrar/Ocultar vista previa
const btnToggleVista = document.getElementById('toggleVistaPrevia');
const seccionVistaPrevia = document.getElementById('seccionVistaPrevia');

if (btnToggleVista && seccionVistaPrevia) {
    let visible = true;

    seccionVistaPrevia.style.transition = 'max-height 0.4s ease, opacity 0.4s ease';
    seccionVistaPrevia.style.overflow = 'hidden';
    seccionVistaPrevia.style.maxHeight = '1000px';
    seccionVistaPrevia.style.opacity = '1';

    btnToggleVista.addEventListener('click', () => {
        visible = !visible;
        if (visible) {
            seccionVistaPrevia.style.maxHeight = '1000px';
            seccionVistaPrevia.style.opacity = '1';
            btnToggleVista.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar Vista Previa';
        } else {
            seccionVistaPrevia.style.maxHeight = '0';
            seccionVistaPrevia.style.opacity = '0';
            btnToggleVista.innerHTML = '<i class="bi bi-eye"></i> Mostrar Vista Previa';
        }
    });
}

</script>