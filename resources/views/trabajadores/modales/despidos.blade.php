{{-- ✅ MODAL DE DESPIDO REDISEÑADO --}}
<div class="modal fade" id="modalDespido" tabindex="-1" aria-labelledby="modalDespidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-danger shadow-sm">
      
      <!-- Header -->
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalDespidoLabel">
          <i class="bi bi-exclamation-triangle-fill fs-4"></i> Dar de Baja a Trabajador
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <!-- Form -->
      <form id="formDespido" method="POST" novalidate>
        @csrf
        <div class="modal-body">
          
          <!-- Info trabajador -->
          <div class="alert alert-warning py-3 d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-3"></i>
            <div>
              <p class="mb-1 fw-semibold">Está a punto de dar de baja al trabajador:</p>
              <p class="mb-0 fs-5 text-truncate"><strong id="nombreTrabajador"></strong></p>
              <small class="text-muted fst-italic">Esta acción cambiará su estado a <span class="fw-bold">"Inactivo"</span> y creará un registro permanente.</small>
            </div>
          </div>
          
          <div class="row g-3">
            <!-- Fecha de Baja -->
            <div class="col-md-6">
              <label for="fecha_baja" class="form-label fw-semibold">
                <i class="bi bi-calendar-x-fill me-1"></i> Fecha de Baja <span class="text-danger">*</span>
              </label>
              <input type="date" 
                     class="form-control" 
                     id="fecha_baja" 
                     name="fecha_baja" 
                     value="{{ date('Y-m-d') }}"
                     max="{{ date('Y-m-d') }}"
                     required>
              <small class="form-text text-muted">No puede ser posterior a hoy.</small>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Tipo de Baja -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Tipo de Baja <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo_baja" id="tipo_definitiva" value="definitiva" checked onchange="toggleReintegroField(false)">
                  <label class="form-check-label" for="tipo_definitiva">Definitiva</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo_baja" id="tipo_temporal" value="temporal" onchange="toggleReintegroField(true)">
                  <label class="form-check-label" for="tipo_temporal">Temporal</label>
                </div>
              </div>
            </div>
            
            <!-- Fecha de Reintegro -->
            <div class="col-md-6" id="fechaReintegroContainer" style="display: none;">
              <label for="fecha_reintegro" class="form-label fw-semibold">
                <i class="bi bi-calendar-check-fill me-1"></i> Fecha de Reintegro <span class="text-danger">*</span>
              </label>
              <input type="date" 
                     class="form-control" 
                     id="fecha_reintegro" 
                     name="fecha_reintegro" 
                     min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                     value="">
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Condición de Salida -->
            <div class="col-md-6">
              <label for="condicion_salida" class="form-label fw-semibold">
                <i class="bi bi-list-check me-1"></i> Condición de Salida <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="condicion_salida" name="condicion_salida" required>
                <option value="" disabled selected>Seleccionar condición...</option>
                <option value="Voluntaria">Voluntaria (Renuncia)</option>
                <option value="Despido con Causa">Baja con Causa</option>
                <option value="Despido sin Causa">Baja sin Causa</option>
                <option value="Mutuo Acuerdo">Mutuo Acuerdo</option>
                <option value="Abandono de Trabajo">Abandono de Trabajo</option>
                <option value="Fin de Contrato">Fin de Contrato</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Motivo -->
            <div class="col-12">
              <label for="motivo" class="form-label fw-semibold">
                <i class="bi bi-chat-text me-1"></i> Motivo de la Baja <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" 
                        id="motivo" 
                        name="motivo" 
                        rows="3" 
                        minlength="10" 
                        maxlength="500" 
                        placeholder="Descripción detallada del motivo de la baja..."
                        required></textarea>
              <div class="d-flex justify-content-between align-items-center">
                <small class="form-text text-muted">Mínimo 10 caracteres, máximo 500.</small>
                <small id="contadorMotivo" class="text-muted">0/500</small>
              </div>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Observaciones -->
            <div class="col-12">
              <label for="observaciones" class="form-label fw-semibold">
                <i class="bi bi-clipboard-data me-1"></i> Observaciones Adicionales
              </label>
              <textarea class="form-control" 
                        id="observaciones" 
                        name="observaciones" 
                        rows="3" 
                        maxlength="1000" 
                        placeholder="Observaciones adicionales, recomendaciones o notas relevantes..."></textarea>
              <div class="d-flex justify-content-between align-items-center">
                <small class="form-text text-muted">Opcional. Máximo 1000 caracteres.</small>
                <small id="contadorObservaciones" class="text-muted">0/1000</small>
              </div>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          
        </div>
        
        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-danger" id="btnConfirmarDespido">
            <i class="bi bi-person-x me-1"></i> Confirmar Baja
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Mostrar u ocultar campo Fecha Reintegro según tipo de baja
function toggleReintegroField(show) {
  const container = document.getElementById('fechaReintegroContainer');
  const input = document.getElementById('fecha_reintegro');
  if (show) {
    container.style.display = 'block';
    input.required = true;
  } else {
    container.style.display = 'none';
    input.required = false;
    input.value = ''; // Limpia el valor para evitar errores en backend
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const modalDespido = document.getElementById('modalDespido');
  const formDespido = document.getElementById('formDespido');
  const nombreTrabajador = document.getElementById('nombreTrabajador');
  const fechaBaja = document.getElementById('fecha_baja');
  const motivo = document.getElementById('motivo');
  const observaciones = document.getElementById('observaciones');
  const btnConfirmarDespido = document.getElementById('btnConfirmarDespido');
  const contadorMotivo = document.getElementById('contadorMotivo');
  const contadorObservaciones = document.getElementById('contadorObservaciones');

  if (!modalDespido || !formDespido) return;

  // Abrir modal y setear datos
  document.querySelectorAll('.btn-despedir').forEach(btn => {
    btn.addEventListener('click', () => {
      const trabajadorId = btn.dataset.id;
      const trabajadorNombre = btn.dataset.nombre;
      const fechaIngreso = btn.dataset.fechaIngreso;

      formDespido.reset();
      nombreTrabajador.textContent = trabajadorNombre;
      formDespido.action = `/trabajadores/${trabajadorId}/despedir`;
      fechaBaja.min = fechaIngreso;
      fechaBaja.value = new Date().toISOString().split('T')[0];

      // Por defecto baja definitiva
      document.getElementById('tipo_definitiva').checked = true;
      toggleReintegroField(false);

      // Limpiar errores y contadores
      document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      if (contadorMotivo) contadorMotivo.textContent = '0/500';
      if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';

      new bootstrap.Modal(modalDespido).show();
    });
  });

  // Contadores de caracteres
  motivo?.addEventListener('input', () => {
    const len = motivo.value.length;
    contadorMotivo.textContent = `${len}/500`;
    contadorMotivo.className = len > 450 ? 'text-warning' : 'text-muted';
  });

  observaciones?.addEventListener('input', () => {
    const len = observaciones.value.length;
    contadorObservaciones.textContent = `${len}/1000`;
    contadorObservaciones.className = len > 900 ? 'text-warning' : 'text-muted';
  });

  // Validación frontend
  formDespido.addEventListener('submit', e => {
    e.preventDefault();
    let isValid = true;

    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (!fechaBaja.value) {
      showFieldError(fechaBaja, 'La fecha de baja es obligatoria');
      isValid = false;
    }

    const condicionSalida = document.getElementById('condicion_salida');
    if (!condicionSalida.value) {
      showFieldError(condicionSalida, 'Debe seleccionar una condición de salida');
      isValid = false;
    }

    if (!motivo.value.trim()) {
      showFieldError(motivo, 'El motivo es obligatorio');
      isValid = false;
    } else if (motivo.value.trim().length < 10) {
      showFieldError(motivo, 'El motivo debe tener al menos 10 caracteres');
      isValid = false;
    }

    if (document.getElementById('tipo_temporal').checked) {
      const fechaReintegro = document.getElementById('fecha_reintegro');
      if (!fechaReintegro.value) {
        showFieldError(fechaReintegro, 'La fecha de reintegro es obligatoria');
        isValid = false;
      }
    }

    if (isValid) {
      btnConfirmarDespido.disabled = true;
      btnConfirmarDespido.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
      formDespido.submit();
    }
  });

  // Mostrar errores de validación
  function showFieldError(field, message) {
    field.classList.add('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = message;
  }

  // Reset modal al cerrar
  modalDespido.addEventListener('hidden.bs.modal', () => {
    formDespido.reset();
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    btnConfirmarDespido.innerHTML = '<i class="bi bi-person-x me-1"></i> Confirmar Baja';
    btnConfirmarDespido.disabled = false;
    if (contadorMotivo) contadorMotivo.textContent = '0/500';
    if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';
    toggleReintegroField(false);
  });
});
</script>
