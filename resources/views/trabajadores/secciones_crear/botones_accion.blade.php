<!-- ✅ BOTONES DE ACCIÓN SIMPLIFICADOS -->
<div class="card shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="button" class="btn btn-outline-warning ms-2" id="btnLimpiar">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar Formulario
                </button>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-success btn-lg px-4" id="btnCrearTrabajador">
                    <span id="btnTextoNormal">
                        <i class="bi bi-person-plus-fill me-2"></i> 
                        Crear Trabajador y Contrato
                    </span>
                    <span id="btnTextoCargando" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Creando trabajador...
                    </span>
                </button>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Se creará automáticamente el trabajador y su contrato
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnLimpiar = document.getElementById('btnLimpiar');
    const form = document.getElementById('formTrabajador');
    const btnCrear = document.getElementById('btnCrearTrabajador');
    const btnTextoNormal = document.getElementById('btnTextoNormal');
    const btnTextoCargando = document.getElementById('btnTextoCargando');

    // Botón limpiar
    btnLimpiar.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todo el formulario?')) {
            limpiarFormulario();
        }
    });

    // Función para limpiar formulario
    function limpiarFormulario() {
        form.reset();
        
        // Limpiar selects
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
        });
        
        // Limpiar clases de validación
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });

        // Limpiar categorías
        const categoriaSelect = document.getElementById('id_categoria');
        if (categoriaSelect) {
            categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
            categoriaSelect.disabled = true;
        }

        // Resetear fecha de ingreso a hoy
        const fechaIngreso = document.getElementById('fecha_ingreso');
        if (fechaIngreso) {
            fechaIngreso.value = new Date().toISOString().split('T')[0];
        }

        // Resetear fecha de inicio de contrato a hoy
        const fechaInicio = document.getElementById('fecha_inicio_contrato');
        if (fechaInicio) {
            fechaInicio.value = new Date().toISOString().split('T')[0];
        }

        // Limpiar vistas previas
        const estadoPreview = document.getElementById('estadoPreview');
        if (estadoPreview) estadoPreview.style.display = 'none';
        
        const resumenContrato = document.getElementById('resumenContrato');
        if (resumenContrato) resumenContrato.style.display = 'none';

        // Actualizar vista previa si existe la función
        if (typeof actualizarVistaPrevia === 'function') {
            actualizarVistaPrevia();
        }

        // Scroll al inicio
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        // Mostrar estado de carga
        btnTextoNormal.classList.add('d-none');
        btnTextoCargando.classList.remove('d-none');
        btnCrear.disabled = true;

        // Opcional: Agregar timeout para restablecer botón en caso de error
        setTimeout(() => {
            btnTextoNormal.classList.remove('d-none');
            btnTextoCargando.classList.add('d-none');
            btnCrear.disabled = false;
        }, 10000); // 10 segundos timeout
    });
});
</script>