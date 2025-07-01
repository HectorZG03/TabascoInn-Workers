document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Iniciando crear_trabajador.js v3.1 - Optimizado');

    const form = document.getElementById('formTrabajador');
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    const horaEntradaInput = document.getElementById('hora_entrada');
    const horaSalidaInput = document.getElementById('hora_salida');
    const camposVistaPrevia = [
        'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento',
        'sueldo_diarios', 'ciudad_actual', 'estado_actual'
    ];

    const get = id => document.getElementById(id);
    const setText = (id, text) => { const el = get(id); if (el) el.textContent = text; };

    // 💡 Ocultar alerta de éxito
    const successAlert = get('success-alert');
    if (successAlert) {
        limpiarFormulario();
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // 🎯 Cargar categorías al seleccionar área
    areaSelect?.addEventListener('change', () => {
        const areaId = areaSelect.value;
        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        categoriaSelect.disabled = true;

        if (!areaId) {
            categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
            return;
        }

        fetch(`/api/categorias/${areaId}`)
            .then(res => res.ok ? res.json() : Promise.reject(res.status))
            .then(data => {
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>' +
                    data.map(cat => `<option value="${cat.id_categoria}">${cat.nombre_categoria}</option>`).join('');
                categoriaSelect.disabled = false;
                console.log(`✅ Categorías cargadas para área ${areaId}:`, data.length);
            })
            .catch(err => {
                console.error('❌ Error cargando categorías:', err);
                categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show mt-2';
                alert.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Error al cargar categorías. 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                categoriaSelect.parentNode.appendChild(alert);
            });

        actualizarVistaPrevia();
    });

    // 🕒 Horarios
    [horaEntradaInput, horaSalidaInput].forEach(input => {
        input?.addEventListener('input', () => {
            actualizarVistaPrevia();
            validarHorarios();
        });
        input?.addEventListener('change', () => {
            actualizarVistaPrevia();
            validarHorarios();
        });
    });

    // 👁️ Vista previa en tiempo real
    function actualizarVistaPrevia() {
        const nombre = `${get('nombre_trabajador')?.value || ''} ${get('ape_pat')?.value || ''} ${get('ape_mat')?.value || ''}`.trim() || 'Nombre del Trabajador';
        setText('preview-nombre', nombre);

        const areaText = areaSelect?.selectedOptions[0]?.text || 'Sin área';
        const categoriaText = categoriaSelect?.selectedOptions[0]?.text || 'Sin categoría';
        setText('preview-categoria', (categoriaText !== 'Seleccionar categoría...') ? `${categoriaText} - ${areaText}` : 'Categoría - Área');

        const sueldo = get('sueldo_diarios')?.value;
        setText('preview-sueldo', sueldo ? `$${parseFloat(sueldo).toFixed(2)}` : '$0.00');

        const fechaNacimiento = get('fecha_nacimiento')?.value;
        setText('preview-edad', fechaNacimiento ? `${calcularEdad(fechaNacimiento)} años` : '-- años');

        const ciudad = get('ciudad_actual')?.value;
        const estado = get('estado_actual')?.value;
        setText('preview-ubicacion', ciudad && estado ? `${ciudad}, ${estado}` : ciudad || estado || 'No especificada');

        actualizarVistaHorario();

        setText('preview-estado', 'Se configurará en el siguiente paso');
    }

    function actualizarVistaHorario() {
        const entrada = horaEntradaInput?.value;
        const salida = horaSalidaInput?.value;

        if (entrada && salida) {
            const horas = calcularHoras(entrada, salida);
            const turno = calcularTurno(entrada, salida);
            setText('preview-horas', `${horas} hrs`);
            setText('preview-turno', turno.charAt(0).toUpperCase() + turno.slice(1));
        } else {
            setText('preview-horas', '-- hrs');
            setText('preview-turno', '--');
        }
    }

    const calcularEdad = fecha => {
        const hoy = new Date(), fn = new Date(fecha);
        let edad = hoy.getFullYear() - fn.getFullYear();
        if (hoy.getMonth() < fn.getMonth() || (hoy.getMonth() === fn.getMonth() && hoy.getDate() < fn.getDate())) edad--;
        return edad;
    };

    const calcularHoras = (entrada, salida) => {
        const base = '2024-01-01';
        let e = new Date(`${base}T${entrada}`), s = new Date(`${base}T${salida}`);
        if (s <= e) s.setDate(s.getDate() + 1);
        return Math.round((s - e) / 3600000 * 100) / 100;
    };

    const calcularTurno = (entrada, salida) => {
        const toMin = h => h.split(':').map(Number).reduce((h, m) => h * 60 + m);
        const e = toMin(entrada), s = toMin(salida);
        if (s <= e) return 'nocturno';
        if (e >= 360 && s <= 1080) return 'diurno';
        if (e >= 1080 || s <= 360) return 'nocturno';
        return 'mixto';
    };

    function validarHorarios() {
        const e = horaEntradaInput?.value;
        const s = horaSalidaInput?.value;
        const formato24h = /^([01]\d|2[0-3]):[0-5]\d$/;

        horaEntradaInput.classList.remove('is-invalid', 'is-valid');
        horaSalidaInput.classList.remove('is-invalid', 'is-valid');
        eliminarMensajeValidacion(horaEntradaInput);
        eliminarMensajeValidacion(horaSalidaInput);

        if (e && !formato24h.test(e)) {
            horaEntradaInput.classList.add('is-invalid');
            mostrarMensajeValidacion(horaEntradaInput, 'Formato inválido. Usa el formato 24h (HH:mm)');
        }

        if (s && !formato24h.test(s)) {
            horaSalidaInput.classList.add('is-invalid');
            mostrarMensajeValidacion(horaSalidaInput, 'Formato inválido. Usa el formato 24h (HH:mm)');
        }

        if (e && s && formato24h.test(e) && formato24h.test(s)) {
            const horas = calcularHoras(e, s);
            if (horas < 1 || horas > 16) {
                horaSalidaInput.classList.add('is-invalid');
                mostrarMensajeValidacion(horaSalidaInput, `Horario inválido: ${horas} horas. Debe estar entre 1 y 16.`);
            } else {
                horaSalidaInput.classList.add('is-valid');
                eliminarMensajeValidacion(horaSalidaInput);
            }
        }
    }


    const mostrarMensajeValidacion = (el, msg) => {
        let feedback = el.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            el.parentNode.appendChild(feedback);
        }
        feedback.textContent = msg;
    };

    const eliminarMensajeValidacion = el => {
        const f = el.parentNode.querySelector('.invalid-feedback');
        if (f && f.textContent.includes('Horario inválido')) f.remove();
    };

    // 💡 Eventos para campos de vista previa
    camposVistaPrevia.forEach(id => {
        const el = get(id);
        el?.addEventListener('input', actualizarVistaPrevia);
        el?.addEventListener('change', actualizarVistaPrevia);
    });
    categoriaSelect?.addEventListener('change', actualizarVistaPrevia);

    // 💡 Forzar mayúsculas en CURP/RFC
    ['curp', 'rfc'].forEach(id => {
        const input = get(id);
        input?.addEventListener('input', () => input.value = input.value.toUpperCase());
    });

    // 🔄 Función para limpiar formulario
    function limpiarFormulario() {
        if (!form) return;
        form.querySelectorAll('input, select').forEach(el => {
            if (el.type !== 'hidden') el.value = '';
            el.classList.remove('is-valid', 'is-invalid');
        });
        categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
        categoriaSelect.disabled = true;
        get('fecha_ingreso').value = new Date().toISOString().split('T')[0];
        actualizarVistaPrevia();
    }

    // 🔘 Botón de limpiar
    const btnLimpiar = document.createElement('button');
    btnLimpiar.type = 'button';
    btnLimpiar.className = 'btn btn-outline-warning me-2';
    btnLimpiar.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Limpiar Formulario';
    btnLimpiar.onclick = limpiarFormulario;
    const btnCancelar = document.querySelector('a[href*="trabajadores.index"]');
    btnCancelar?.parentNode.insertBefore(btnLimpiar, btnCancelar);

    // 📆 Validación de días laborables
    const validarDiasLaborables = () => {
        const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
        const seleccionados = Array.from(checkboxes).some(cb => cb.checked);
        const container = document.querySelector('.form-label:has-text("Días Laborables")')?.parentElement;
        const advertencia = container?.querySelector('.dias-laborables-warning');

        if (!seleccionados) {
            if (!advertencia) {
                const div = document.createElement('div');
                div.className = 'alert alert-warning alert-sm mt-2 dias-laborables-warning';
                div.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Debes seleccionar al menos un día laborable';
                container?.appendChild(div);
            }
            return false;
        } else {
            advertencia?.remove();
            return true;
        }
    };

    document.querySelectorAll('input[name="dias_laborables[]"]').forEach(cb =>
        cb.addEventListener('change', validarDiasLaborables)
    );

    // Inicializar
    actualizarVistaPrevia();
    console.log('✅ crear_trabajador.js v3.1 cargado - Optimizado');
});
