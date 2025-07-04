// ✅ NUEVA FUNCIÓN: Mostrar u ocultar campo de condición personalizada
function toggleCondicionPersonalizada() {
  const select = document.getElementById('condicion_salida');
  const container = document.getElementById('condicionPersonalizadaContainer');
  const input = document.getElementById('condicion_personalizada');
  
  if (select.value === 'OTRO') {
    container.style.display = 'block';
    input.required = true;
    input.focus(); // Enfocar automáticamente el campo
  } else {
    container.style.display = 'none';
    input.required = false;
    input.value = ''; // Limpiar el valor
    input.classList.remove('is-invalid');
  }
}

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
    input.value = '';
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
      toggleCondicionPersonalizada(); // ✅ Resetear condición personalizada

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

  // ✅ VALIDACIÓN FRONTEND ACTUALIZADA
  formDespido.addEventListener('submit', e => {
    e.preventDefault();
    let isValid = true;

    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    // Validar fecha de baja
    if (!fechaBaja.value) {
      showFieldError(fechaBaja, 'La fecha de baja es obligatoria');
      isValid = false;
    }

    // ✅ VALIDAR CONDICIÓN DE SALIDA (select + campo personalizado)
    const condicionSalida = document.getElementById('condicion_salida');
    const condicionPersonalizada = document.getElementById('condicion_personalizada');
    
    if (!condicionSalida.value) {
      showFieldError(condicionSalida, 'Debe seleccionar una condición de salida');
      isValid = false;
    } else if (condicionSalida.value === 'OTRO') {
      if (!condicionPersonalizada.value.trim()) {
        showFieldError(condicionPersonalizada, 'Debe especificar la condición de salida');
        isValid = false;
      } else if (condicionPersonalizada.value.trim().length < 3) {
        showFieldError(condicionPersonalizada, 'La condición debe tener al menos 3 caracteres');
        isValid = false;
      }
    }

    // Validar motivo
    if (!motivo.value.trim()) {
      showFieldError(motivo, 'El motivo es obligatorio');
      isValid = false;
    } else if (motivo.value.trim().length < 10) {
      showFieldError(motivo, 'El motivo debe tener al menos 10 caracteres');
      isValid = false;
    }

    // Validar fecha de reintegro si es temporal
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

  // ✅ RESET MODAL ACTUALIZADO
  modalDespido.addEventListener('hidden.bs.modal', () => {
    formDespido.reset();
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    btnConfirmarDespido.innerHTML = '<i class="bi bi-person-x me-1"></i> Confirmar Baja';
    btnConfirmarDespido.disabled = false;
    if (contadorMotivo) contadorMotivo.textContent = '0/500';
    if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';
    toggleReintegroField(false);
    toggleCondicionPersonalizada(); // ✅ Resetear condición personalizada
  });
});