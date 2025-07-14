// ========================================
// 🎯 MODAL DE PERMISOS - CON RUTAS DINÁMICAS
// ========================================

function toggleTipoPersonalizado() {
    const select = document.getElementById('tipo_permiso');
    const container = document.getElementById('tipoPersonalizadoContainer');
    const input = document.getElementById('tipo_personalizado');
    
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

document.addEventListener('DOMContentLoaded', function () {
    // ✅ VERIFICAR QUE LAS DEPENDENCIAS ESTÉN DISPONIBLES
    if (!window.FormatoGlobal) {
        console.error('❌ FormatoGlobal no está disponible. Asegúrese de cargar formato-global.js');
        return;
    }

    if (!window.AppRoutes) {
        console.error('❌ AppRoutes no está disponible. Asegúrese de cargar app-routes.js');
        return;
    }

    const modalPermisos = document.getElementById('modalPermisos');
    const formPermisos = document.getElementById('formPermisos');
    const nombreTrabajadorPermiso = document.getElementById('nombreTrabajadorPermiso');
    const tipoPermiso = document.getElementById('tipo_permiso');
    const tipoPersonalizado = document.getElementById('tipo_personalizado');
    const motivo = document.getElementById('motivo');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const btnConfirmarPermiso = document.getElementById('btnConfirmarPermiso');
    const duracionPermiso = document.getElementById('duracionPermiso');

    const esPorHoras = document.getElementById('es_por_horas');
    const camposHoras = document.getElementById('camposHoras');
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');

    if (!modalPermisos || !formPermisos) {
        console.warn('⚠️ Elementos del modal de permisos no encontrados');
        return;
    }

    // ✅ CONFIGURAR ACCIÓN DEL FORMULARIO AL ABRIR MODAL CON RUTAS DINÁMICAS
    document.querySelectorAll('.btn-permisos').forEach(btn => {
        btn.addEventListener('click', function () {
            const trabajadorId = this.dataset.id;
            const trabajadorNombre = this.dataset.nombre;

            if (!trabajadorId) {
                console.error('❌ No se encontró el ID del trabajador');
                return;
            }

            // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE HARDCODED
            const actionUrl = AppRoutes.url(`trabajadores/${trabajadorId}/permisos`);
            formPermisos.action = actionUrl;

            console.log('🔄 Configurando modal de permisos para trabajador:', trabajadorId);
            console.log('🔗 URL del formulario:', actionUrl);

            nombreTrabajadorPermiso.textContent = trabajadorNombre;

            resetForm();
            configurarFechasPorDefecto();
            new bootstrap.Modal(modalPermisos).show();
        });
    });

    // ✅ EVENT LISTENERS PARA FECHAS USANDO FORMATO PERSONALIZADO
    fechaInicio?.addEventListener('input', () => {
        setTimeout(calcularDuracion, 100); // Esperar a que el formato se aplique
    });

    fechaInicio?.addEventListener('blur', () => {
        setTimeout(calcularDuracion, 100);
    });

    fechaFin?.addEventListener('input', () => {
        setTimeout(calcularDuracion, 100);
    });

    fechaFin?.addEventListener('blur', () => {
        setTimeout(calcularDuracion, 100);
    });

    // ✅ MANEJO DE PERMISOS POR HORAS
    esPorHoras?.addEventListener('change', () => {
        if (esPorHoras.checked) {
            camposHoras.classList.remove('d-none');
            horaInicio.required = true;
            horaFin.required = true;
        } else {
            camposHoras.classList.add('d-none');
            horaInicio.required = false;
            horaFin.required = false;
            horaInicio.value = '';
            horaFin.value = '';
            // Limpiar validaciones de hora
            if (window.FormatoGlobal) {
                FormatoGlobal.limpiarValidacion(horaInicio);
                FormatoGlobal.limpiarValidacion(horaFin);
            }
        }
    });

    // ✅ VALIDACIÓN DE RANGO DE HORAS EN TIEMPO REAL
    [horaInicio, horaFin].forEach(campo => {
        if (campo) {
            campo.addEventListener('blur', () => {
                if (horaInicio.value && horaFin.value && window.FormatoGlobal) {
                    FormatoGlobal.validarRangoHorario(horaInicio, horaFin);
                }
            });
        }
    });

    // ✅ FUNCIÓN PARA CALCULAR DURACIÓN CON FORMATO DD/MM/YYYY
    function calcularDuracion() {
        if (!fechaInicio.value || !fechaFin.value || !window.FormatoGlobal) {
            duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
            return;
        }

        const inicio = fechaInicio.value.trim();
        const fin = fechaFin.value.trim();

        // Verificar formato válido
        if (!FormatoGlobal.validarFormatoFecha(inicio) || !FormatoGlobal.validarFormatoFecha(fin)) {
            duracionPermiso.innerHTML = '<span class="fw-bold text-warning">Formato inválido</span>';
            return;
        }

        // Convertir fechas
        const fechaInicioObj = FormatoGlobal.convertirFechaADate(inicio);
        const fechaFinObj = FormatoGlobal.convertirFechaADate(fin);

        if (!fechaInicioObj || !fechaFinObj) {
            duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fechas inválidas</span>';
            return;
        }

        // Validar que fecha fin sea >= fecha inicio
        if (fechaFinObj < fechaInicioObj) {
            duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fecha fin debe ser posterior</span>';
            FormatoGlobal.mostrarError(fechaFin, 'La fecha de fin debe ser igual o posterior a la de inicio');
            return;
        }

        // Validar que fecha inicio no sea pasada
        const hoy = new Date();
        const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        
        if (fechaInicioObj < fechaHoy) {
            duracionPermiso.innerHTML = '<span class="fw-bold text-danger">Fecha de inicio no puede ser pasada</span>';
            FormatoGlobal.mostrarError(fechaInicio, 'La fecha de inicio no puede ser anterior a hoy');
            return;
        }

        // Calcular días
        const diffTime = fechaFinObj.getTime() - fechaInicioObj.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        duracionPermiso.innerHTML = `<span class="fw-bold text-success">${diffDays} día${diffDays > 1 ? 's' : ''}</span>`;
        
        // Limpiar errores si todo está bien
        FormatoGlobal.limpiarValidacion(fechaInicio);
        FormatoGlobal.limpiarValidacion(fechaFin);
    }

    // ✅ VALIDACIÓN DEL FORMULARIO CON FORMATO PERSONALIZADO
    formPermisos.addEventListener('submit', function (e) {
        e.preventDefault();

        // ✅ VERIFICAR QUE LA URL DEL FORMULARIO ESTÉ CONFIGURADA
        if (!formPermisos.action || formPermisos.action.includes('undefined')) {
            console.error('❌ URL del formulario no configurada correctamente:', formPermisos.action);
            alert('Error: No se pudo configurar la URL del formulario. Recargue la página e intente nuevamente.');
            return;
        }

        console.log('🔄 Enviando formulario a:', formPermisos.action);

        const formData = new FormData(formPermisos);
        const valores = {
            tipoPermiso: formData.get('tipo_permiso'),
            tipoPersonalizado: formData.get('tipo_personalizado'),
            motivo: formData.get('motivo'),
            fechaInicio: formData.get('fecha_inicio'),
            fechaFin: formData.get('fecha_fin'),
            esPorHoras: formData.get('es_por_horas') === '1',
            horaInicio: formData.get('hora_inicio'),
            horaFin: formData.get('hora_fin'),
            observaciones: formData.get('observaciones'),
        };

        let isValid = true;

        // Limpiar validaciones previas
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // ✅ VALIDAR TIPO DE PERMISO
        if (!valores.tipoPermiso) {
            showFieldError(tipoPermiso, 'Debe seleccionar un tipo de permiso');
            isValid = false;
        } else if (valores.tipoPermiso === 'OTRO') {
            if (!valores.tipoPersonalizado || valores.tipoPersonalizado.trim().length < 3) {
                showFieldError(tipoPersonalizado, 'Debe especificar el tipo de permiso (mín. 3 caracteres)');
                isValid = false;
            }
        }

        // ✅ VALIDAR MOTIVO
        if (!valores.motivo || valores.motivo.trim().length < 3) {
            showFieldError(motivo, 'El motivo es obligatorio (mín. 3 caracteres)');
            isValid = false;
        }

        // ✅ VALIDAR FECHAS CON FORMATO PERSONALIZADO
        if (!valores.fechaInicio) {
            showFieldError(fechaInicio, 'La fecha de inicio es obligatoria');
            isValid = false;
        } else if (!FormatoGlobal.validarFormatoFecha(valores.fechaInicio)) {
            showFieldError(fechaInicio, 'Formato de fecha inválido (DD/MM/YYYY)');
            isValid = false;
        } else {
            const fechaInicioObj = FormatoGlobal.convertirFechaADate(valores.fechaInicio);
            const hoy = new Date();
            const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
            
            if (fechaInicioObj < fechaHoy) {
                showFieldError(fechaInicio, 'La fecha de inicio no puede ser anterior a hoy');
                isValid = false;
            }
        }

        if (!valores.fechaFin) {
            showFieldError(fechaFin, 'La fecha de fin es obligatoria');
            isValid = false;
        } else if (!FormatoGlobal.validarFormatoFecha(valores.fechaFin)) {
            showFieldError(fechaFin, 'Formato de fecha inválido (DD/MM/YYYY)');
            isValid = false;
        } else if (valores.fechaInicio && FormatoGlobal.validarFormatoFecha(valores.fechaInicio)) {
            const fechaInicioObj = FormatoGlobal.convertirFechaADate(valores.fechaInicio);
            const fechaFinObj = FormatoGlobal.convertirFechaADate(valores.fechaFin);
            
            if (fechaFinObj < fechaInicioObj) {
                showFieldError(fechaFin, 'La fecha de fin debe ser igual o posterior a la fecha de inicio');
                isValid = false;
            }
        }

        // ✅ VALIDAR HORAS SI ES NECESARIO
        if (valores.esPorHoras) {
            if (!valores.horaInicio) {
                showFieldError(horaInicio, 'La hora de inicio es obligatoria');
                isValid = false;
            } else if (!FormatoGlobal.validarFormatoHora(valores.horaInicio)) {
                showFieldError(horaInicio, 'Formato de hora inválido (HH:MM)');
                isValid = false;
            }

            if (!valores.horaFin) {
                showFieldError(horaFin, 'La hora de fin es obligatoria');
                isValid = false;
            } else if (!FormatoGlobal.validarFormatoHora(valores.horaFin)) {
                showFieldError(horaFin, 'Formato de hora inválido (HH:MM)');
                isValid = false;
            }

            // Validar rango de horas
            if (valores.horaInicio && valores.horaFin && 
                FormatoGlobal.validarFormatoHora(valores.horaInicio) && 
                FormatoGlobal.validarFormatoHora(valores.horaFin)) {
                
                const horas = FormatoGlobal.calcularHoras(valores.horaInicio, valores.horaFin);
                if (horas <= 0) {
                    showFieldError(horaFin, 'La hora de fin debe ser posterior a la hora de inicio');
                    isValid = false;
                }
            }
        }

        if (isValid) {
            btnConfirmarPermiso.disabled = true;
            btnConfirmarPermiso.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
            
            // ✅ AGREGAR LOGS PARA DEBUG
            console.log('✅ Formulario válido, enviando...');
            console.log('📝 Datos del formulario:', valores);
            
            formPermisos.submit();
        } else {
            console.warn('⚠️ Formulario inválido, corrigiendo errores...');
            // Scroll al primer error
            const primerError = formPermisos.querySelector('.is-invalid');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                primerError.focus();
            }
        }
    });

    // ✅ FUNCIONES AUXILIARES
    function showFieldError(field, message) {
        if (field) {
            field.classList.add('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = message;
        }
    }

    function configurarFechasPorDefecto() {
        // Configurar fecha de inicio con la fecha actual
        const hoy = new Date();
        const fechaHoy = formatearFechaParaInput(hoy);
        
        if (!fechaInicio.value) {
            fechaInicio.value = fechaHoy;
        }
        
        // Configurar fecha de fin (misma fecha por defecto)
        if (!fechaFin.value) {
            fechaFin.value = fechaHoy;
        }
        
        // Calcular duración inicial
        setTimeout(calcularDuracion, 100);
    }

    function formatearFechaParaInput(fecha) {
        const dia = String(fecha.getDate()).padStart(2, '0');
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const año = fecha.getFullYear();
        return `${dia}/${mes}/${año}`;
    }

    function resetForm() {
        formPermisos.reset();
        duracionPermiso.innerHTML = '<span class="fw-bold text-primary">0 días</span>';
        btnConfirmarPermiso.disabled = false;
        btnConfirmarPermiso.innerHTML = '<i class="bi bi-check-circle"></i> Asignar Permiso';
        
        if (camposHoras) camposHoras.classList.add('d-none');
        if (horaInicio) horaInicio.required = false;
        if (horaFin) horaFin.required = false;
        
        toggleTipoPersonalizado();
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Limpiar todas las validaciones de formato
        if (window.FormatoGlobal) {
            [fechaInicio, fechaFin, horaInicio, horaFin].forEach(campo => {
                if (campo) FormatoGlobal.limpiarValidacion(campo);
            });
        }
    }

    // ✅ RESETEAR FORMULARIO AL CERRAR MODAL
    modalPermisos.addEventListener('hidden.bs.modal', resetForm);

    console.log('✅ Modal de permisos con rutas dinámicas inicializado correctamente');
});