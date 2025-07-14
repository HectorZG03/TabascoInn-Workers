// ========================================
// 🎯 MODAL DE DESPIDOS - CON RUTAS DINÁMICAS
// ========================================

// Funciones globales para el modal
function toggleCondicionPersonalizada() {
  const select = document.getElementById('condicion_salida');
  const container = document.getElementById('condicionPersonalizadaContainer');
  const input = document.getElementById('condicion_personalizada');
  
  if (select && container && input) {
    if (select.value === 'OTRO') {
      container.style.display = 'block';
      input.required = true;
      input.focus();
    } else {
      container.style.display = 'none';
      input.required = false;
      input.value = '';
      input.classList.remove('is-invalid');
    }
  }
}

function toggleReintegroField(show) {
  const container = document.getElementById('fechaReintegroContainer');
  const duracionContainer = document.getElementById('duracionBajaContainer');
  const input = document.getElementById('fecha_reintegro');
  
  if (container && duracionContainer && input) {
    container.style.display = show ? 'block' : 'none';
    duracionContainer.style.display = show ? 'block' : 'none';
    input.required = show;
    
    if (!show) {
      input.value = '';
      const duracionBadge = document.getElementById('duracionBaja');
      if (duracionBadge) {
        duracionBadge.textContent = '0 días';
      }
      input.classList.remove('is-invalid', 'is-valid');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // ✅ VERIFICAR QUE LAS DEPENDENCIAS ESTÉN DISPONIBLES
  if (!window.FormatoGlobal) {
    console.error('❌ FormatoGlobal no disponible para modal de despidos');
    return;
  }

  if (!window.AppRoutes) {
    console.error('❌ AppRoutes no disponible para modal de despidos');
    return;
  }

  // Elementos del DOM
  const modal = document.getElementById('modalDespido');
  const form = document.getElementById('formDespido');
  const fechaBaja = document.getElementById('fecha_baja');
  const fechaReintegro = document.getElementById('fecha_reintegro');
  const motivo = document.getElementById('motivo');
  const observaciones = document.getElementById('observaciones');
  const btnSubmit = document.getElementById('btnConfirmarDespido');
  
  if (!modal || !form) {
    console.warn('⚠️ Elementos del modal de despidos no encontrados');
    return;
  }

  let fechaIngresoTrabajador = null;

  // ✅ APLICAR FORMATO A CAMPOS DE FECHA
  if (fechaBaja) {
    FormatoGlobal.configurarCampoFecha(fechaBaja);
  }
  if (fechaReintegro) {
    FormatoGlobal.configurarCampoFecha(fechaReintegro);
  }

  // ✅ SI HAY FECHA POR DEFECTO, CONVERTIRLA A FORMATO DD/MM/YYYY
  if (fechaBaja && fechaBaja.value && fechaBaja.value.includes('-')) {
    const fecha = new Date(fechaBaja.value);
    if (!isNaN(fecha.getTime())) {
      const dia = String(fecha.getDate()).padStart(2, '0');
      const mes = String(fecha.getMonth() + 1).padStart(2, '0');
      const año = fecha.getFullYear();
      fechaBaja.value = `${dia}/${mes}/${año}`;
    }
  }

  // ✅ CALCULAR DURACIÓN DE BAJA TEMPORAL
  function calcularDuracion() {
    const duracionBadge = document.getElementById('duracionBaja');
    if (!duracionBadge) return;
    
    if (!fechaBaja || !fechaBaja.value || !fechaReintegro || !fechaReintegro.value) {
      duracionBadge.textContent = '0 días';
      duracionBadge.className = 'badge bg-info';
      return;
    }

    const fechaInicio = FormatoGlobal.convertirFechaADate(fechaBaja.value);
    const fechaFin = FormatoGlobal.convertirFechaADate(fechaReintegro.value);
    
    if (fechaInicio && fechaFin && fechaFin > fechaInicio) {
      const dias = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24));
      duracionBadge.textContent = `${dias} día${dias > 1 ? 's' : ''}`;
      duracionBadge.className = dias <= 7 ? 'badge bg-success' : 
                                dias <= 30 ? 'badge bg-warning' : 'badge bg-danger';
    } else {
      duracionBadge.textContent = 'Fechas inválidas';
      duracionBadge.className = 'badge bg-danger';
    }
  }

  // ✅ EVENTOS PARA CALCULAR DURACIÓN
  [fechaBaja, fechaReintegro].forEach(campo => {
    if (campo) {
      campo.addEventListener('blur', () => setTimeout(calcularDuracion, 100));
    }
  });

  // ✅ CONTADORES DE CARACTERES
  if (motivo) {
    motivo.addEventListener('input', () => {
      const len = motivo.value.length;
      const contador = document.getElementById('contadorMotivo');
      if (contador) {
        contador.textContent = `${len}/500`;
      }
    });
  }

  if (observaciones) {
    observaciones.addEventListener('input', () => {
      const len = observaciones.value.length;
      const contador = document.getElementById('contadorObservaciones');
      if (contador) {
        contador.textContent = `${len}/1000`;
      }
    });
  }

  // ✅ ABRIR MODAL CON RUTAS DINÁMICAS
  document.querySelectorAll('.btn-despedir').forEach(btn => {
    btn.addEventListener('click', () => {
      // Resetear formulario
      form.reset();
      limpiarErrores();

      // ✅ LIMPIAR FECHA DE BAJA (evitar que aparezca precargada)
      if (fechaBaja) fechaBaja.value = '';
      if (fechaReintegro) fechaReintegro.value = '';
      
      const duracionBadge = document.getElementById('duracionBaja');
      if (duracionBadge) {
        duracionBadge.textContent = '0 días';
      }

      // ✅ Volver a aplicar formato a los campos limpios
      if (fechaBaja) {
        FormatoGlobal.configurarCampoFecha(fechaBaja);
      }
      if (fechaReintegro) {
        FormatoGlobal.configurarCampoFecha(fechaReintegro);
      }

      // Configurar datos del trabajador
      const trabajadorId = btn.dataset.id;
      const trabajadorNombre = btn.dataset.nombre;
      
      if (!trabajadorId) {
        console.error('❌ No se encontró el ID del trabajador');
        return;
      }

      // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
      const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/despedir`);
      form.action = actionUrl;

      console.log('🔄 Configurando modal de despidos para trabajador:', trabajadorId);
      console.log('🔗 URL del formulario:', actionUrl);

      const nombreElement = document.getElementById('nombreTrabajador');
      if (nombreElement) {
        nombreElement.textContent = trabajadorNombre;
      }
      
      fechaIngresoTrabajador = btn.dataset.fechaIngreso;

      // Estado inicial
      const tipoDefinitiva = document.getElementById('tipo_definitiva');
      if (tipoDefinitiva) {
        tipoDefinitiva.checked = true;
      }
      
      toggleReintegroField(false);
      toggleCondicionPersonalizada();

      // Resetear contadores
      const contadorMotivo = document.getElementById('contadorMotivo');
      const contadorObservaciones = document.getElementById('contadorObservaciones');
      
      if (contadorMotivo) contadorMotivo.textContent = '0/500';
      if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';

      new bootstrap.Modal(modal).show();
    });
  });

  // ✅ VALIDACIÓN PERSONALIZADA
  function validarFecha(campo, valor) {
    if (!FormatoGlobal.validarFormatoFecha(valor)) {
      return 'Formato inválido. Use DD/MM/YYYY';
    }

    const fecha = FormatoGlobal.convertirFechaADate(valor);
    if (!fecha) return 'Fecha inválida';

    if (campo.id === 'fecha_baja') {
      // ✅ ELIMINADA la validación "no puede ser posterior a hoy"
      // Ahora se permiten fechas futuras para la baja
      
      // Solo validar que no sea anterior a la fecha de ingreso
      if (fechaIngresoTrabajador) {
        const [año, mes, dia] = fechaIngresoTrabajador.split('-').map(Number);
        const fechaIngreso = new Date(año, mes - 1, dia);
        if (fecha < fechaIngreso) {
          return `No puede ser anterior a la fecha de ingreso (${dia.toString().padStart(2, '0')}/${mes.toString().padStart(2, '0')}/${año})`;
        }
      }
    } else if (campo.id === 'fecha_reintegro') {
      // Para fecha de reintegro sí debe ser posterior a hoy
      const hoy = new Date();
      hoy.setHours(23, 59, 59, 999);
      
      if (fecha <= hoy) return 'Debe ser posterior a hoy';
      
      // Debe ser posterior a la fecha de baja
      if (fechaBaja && fechaBaja.value) {
        const fechaBajaObj = FormatoGlobal.convertirFechaADate(fechaBaja.value);
        if (fechaBajaObj && fecha <= fechaBajaObj) {
          return 'Debe ser posterior a la fecha de baja';
        }
      }
    }

    return null;
  }

  // ✅ VALIDACIÓN AL ENVIAR
  form.addEventListener('submit', e => {
    e.preventDefault();
    
    // ✅ VERIFICAR QUE LA URL DEL FORMULARIO ESTÉ CONFIGURADA
    if (!form.action || form.action.includes('undefined')) {
      console.error('❌ URL del formulario no configurada correctamente:', form.action);
      alert('Error: No se pudo configurar la URL del formulario. Recargue la página e intente nuevamente.');
      return;
    }

    console.log('🔄 Enviando formulario de despido a:', form.action);
    
    limpiarErrores();
    let esValido = true;

    // Validar fecha de baja
    if (!fechaBaja || !fechaBaja.value.trim()) {
      if (fechaBaja) mostrarError(fechaBaja, 'La fecha de baja es obligatoria');
      esValido = false;
    } else {
      const error = validarFecha(fechaBaja, fechaBaja.value.trim());
      if (error) {
        mostrarError(fechaBaja, error);
        esValido = false;
      }
    }

    // Validar condición de salida
    const condicion = document.getElementById('condicion_salida');
    const condicionPersonalizada = document.getElementById('condicion_personalizada');
    
    if (!condicion || !condicion.value) {
      if (condicion) mostrarError(condicion, 'Debe seleccionar una condición');
      esValido = false;
    } else if (condicion.value === 'OTRO' && (!condicionPersonalizada || !condicionPersonalizada.value.trim())) {
      if (condicionPersonalizada) mostrarError(condicionPersonalizada, 'Debe especificar la condición');
      esValido = false;
    }

    // Validar motivo
    if (!motivo || !motivo.value.trim() || motivo.value.trim().length < 10) {
      if (motivo) mostrarError(motivo, 'El motivo debe tener al menos 10 caracteres');
      esValido = false;
    }

    // Validar fecha de reintegro si es temporal
    const tipoTemporal = document.getElementById('tipo_temporal');
    if (tipoTemporal && tipoTemporal.checked) {
      if (!fechaReintegro || !fechaReintegro.value.trim()) {
        if (fechaReintegro) mostrarError(fechaReintegro, 'La fecha de reintegro es obligatoria');
        esValido = false;
      } else {
        const error = validarFecha(fechaReintegro, fechaReintegro.value.trim());
        if (error) {
          mostrarError(fechaReintegro, error);
          esValido = false;
        }
      }
    }

    if (esValido) {
      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
      }
      
      console.log('✅ Formulario de despido válido, enviando...');
      form.submit();
    } else {
      console.warn('⚠️ Formulario de despido inválido, corrigiendo errores...');
      // Enfocar primer error
      const primerError = form.querySelector('.is-invalid');
      if (primerError) {
        primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        primerError.focus();
      }
    }
  });

  // ✅ FUNCIONES AUXILIARES
  function mostrarError(campo, mensaje) {
    if (campo) {
      campo.classList.add('is-invalid');
      let feedback = campo.parentNode.querySelector('.invalid-feedback');
      if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        campo.parentNode.appendChild(feedback);
      }
      feedback.textContent = mensaje;
    }
  }

  function limpiarErrores() {
    document.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
      el.classList.remove('is-invalid', 'is-valid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
  }

  // ✅ RESET AL CERRAR MODAL
  modal.addEventListener('hidden.bs.modal', () => {
    form.reset();
    limpiarErrores();
    
    if (btnSubmit) {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = '<i class="bi bi-person-x me-1"></i> Confirmar Baja';
    }
    
    fechaIngresoTrabajador = null;
    toggleReintegroField(false);
    toggleCondicionPersonalizada();
    
    // ✅ RESETEAR CONTADORES
    const contadorMotivo = document.getElementById('contadorMotivo');
    const contadorObservaciones = document.getElementById('contadorObservaciones');
    
    if (contadorMotivo) contadorMotivo.textContent = '0/500';
    if (contadorObservaciones) contadorObservaciones.textContent = '0/1000';
  });

  console.log('✅ Modal de despidos con rutas dinámicas inicializado correctamente');
});