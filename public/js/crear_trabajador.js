/**
 * ✅ CREAR TRABAJADOR CON FORMATO CONTROLADO
 * Sistema unificado con formato estricto para fechas (DD/MM/YYYY) y horas (HH:MM)
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando sistema de creación de trabajadores con formato controlado');

    // Elementos principales
    const form = document.getElementById('formTrabajador');
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    const horaEntradaInput = document.getElementById('hora_entrada');
    const horaSalidaInput = document.getElementById('hora_salida');

    // Helpers
    const get = id => document.getElementById(id);

    // =================================
    // 🎯 INICIALIZACIÓN
    // =================================
    
    // Configurar formateo de campos
    configurarFormateoCampos();
    
    // Configurar campos con mayúsculas
    configurarCamposMayusculas();
    
    // Configurar alertas de éxito
    configurarAlertasExito();

    // =================================
    // 📅 SISTEMA DE FORMATEO DE FECHAS Y HORAS
    // =================================

    function configurarFormateoCampos() {
        // Configurar campos de fecha
        const camposFecha = document.querySelectorAll('.formato-fecha');
        camposFecha.forEach(campo => {
            configurarCampoFecha(campo);
        });

        // Configurar campos de hora
        const camposHora = document.querySelectorAll('.formato-hora');
        camposHora.forEach(campo => {
            configurarCampoHora(campo);
        });

        // Configurar fechas por defecto
        configurarFechasPorDefecto();
    }

    function configurarCampoFecha(campo) {
        // Formatear mientras se escribe
        campo.addEventListener('input', function(e) {
            const valor = e.target.value.replace(/\D/g, ''); // Solo números
            let fechaFormateada = '';
            
            if (valor.length >= 1) {
                fechaFormateada = valor.substring(0, 2);
            }
            if (valor.length >= 3) {
                fechaFormateada += '/' + valor.substring(2, 4);
            }
            if (valor.length >= 5) {
                fechaFormateada += '/' + valor.substring(4, 8);
            }
            
            e.target.value = fechaFormateada;
        });

        // Validar al perder foco
        campo.addEventListener('blur', function(e) {
            validarCampoFecha(e.target);
        });

        // Permitir solo números y barras
        campo.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[\d/]/.test(char)) {
                e.preventDefault();
            }
        });
    }

    function configurarCampoHora(campo) {
        // Formatear mientras se escribe
        campo.addEventListener('input', function(e) {
            const valor = e.target.value.replace(/\D/g, ''); // Solo números
            let horaFormateada = '';
            
            if (valor.length >= 1) {
                horaFormateada = valor.substring(0, 2);
            }
            if (valor.length >= 3) {
                horaFormateada += ':' + valor.substring(2, 4);
            }
            
            e.target.value = horaFormateada;
        });

        // Validar al perder foco
        campo.addEventListener('blur', function(e) {
            validarCampoHora(e.target);
        });

        // Permitir solo números y dos puntos
        campo.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[\d:]/.test(char)) {
                e.preventDefault();
            }
        });
    }

    function validarCampoFecha(campo) {
        const valor = campo.value.trim();
        
        if (!valor) {
            limpiarValidacion(campo);
            return true;
        }

        // Validar formato DD/MM/YYYY
        const formatoFecha = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!formatoFecha.test(valor)) {
            mostrarErrorValidacion(campo, 'Formato inválido. Use DD/MM/YYYY');
            return false;
        }

        const [dia, mes, año] = valor.split('/').map(Number);
        
        // Validar rangos básicos
        if (dia < 1 || dia > 31) {
            mostrarErrorValidacion(campo, 'Día inválido (1-31)');
            return false;
        }
        
        if (mes < 1 || mes > 12) {
            mostrarErrorValidacion(campo, 'Mes inválido (1-12)');
            return false;
        }
        
        if (año < 1900 || año > 2100) {
            mostrarErrorValidacion(campo, 'Año inválido (1900-2100)');
            return false;
        }

        // Crear fecha para validación
        const fecha = new Date(año, mes - 1, dia);
        if (fecha.getDate() !== dia || fecha.getMonth() !== mes - 1 || fecha.getFullYear() !== año) {
            mostrarErrorValidacion(campo, 'Fecha inválida');
            return false;
        }

        // Validaciones específicas por campo
        const hoy = new Date();
        const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        
        if (campo.id === 'fecha_nacimiento') {
            const hace18años = new Date();
            hace18años.setFullYear(hace18años.getFullYear() - 18);
            
            if (fecha > hace18años) {
                mostrarErrorValidacion(campo, 'Debe ser mayor de 18 años');
                return false;
            }
        }
        
        if (campo.id === 'fecha_ingreso') {
            if (fecha > fechaHoy) {
                mostrarErrorValidacion(campo, 'No puede ser fecha futura');
                return false;
            }
        }
        
        if (campo.id === 'fecha_inicio_contrato') {
            if (fecha < fechaHoy) {
                mostrarErrorValidacion(campo, 'No puede ser fecha pasada');
                return false;
            }
        }
        
        if (campo.id === 'fecha_fin_contrato') {
            const fechaInicio = get('fecha_inicio_contrato')?.value;
            if (fechaInicio && validarFormatoFecha(fechaInicio)) {
                const [diaIni, mesIni, añoIni] = fechaInicio.split('/').map(Number);
                const fechaInicioObj = new Date(añoIni, mesIni - 1, diaIni);
                
                if (fecha <= fechaInicioObj) {
                    mostrarErrorValidacion(campo, 'Debe ser posterior a la fecha de inicio');
                    return false;
                }
            }
        }

        mostrarValidacionCorrecta(campo);
        return true;
    }

    function validarCampoHora(campo) {
        const valor = campo.value.trim();
        
        if (!valor) {
            limpiarValidacion(campo);
            return true;
        }

        // Validar formato HH:MM
        const formatoHora = /^([01]\d|2[0-3]):([0-5]\d)$/;
        if (!formatoHora.test(valor)) {
            mostrarErrorValidacion(campo, 'Formato inválido. Use HH:MM (24h)');
            return false;
        }

        const [horas, minutos] = valor.split(':').map(Number);
        
        if (horas < 0 || horas > 23) {
            mostrarErrorValidacion(campo, 'Hora inválida (0-23)');
            return false;
        }
        
        if (minutos < 0 || minutos > 59) {
            mostrarErrorValidacion(campo, 'Minutos inválidos (0-59)');
            return false;
        }

        mostrarValidacionCorrecta(campo);
        
        // Validar horarios después de ambos campos
        if (campo.id === 'hora_entrada' || campo.id === 'hora_salida') {
            setTimeout(() => validarHorarios(), 100);
        }
        
        return true;
    }

    function validarFormatoFecha(fecha) {
        const formatoFecha = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        return formatoFecha.test(fecha);
    }

    function mostrarErrorValidacion(campo, mensaje) {
        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
        
        let feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            campo.parentNode.appendChild(feedback);
        }
        feedback.textContent = mensaje;
    }

    function mostrarValidacionCorrecta(campo) {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    function limpiarValidacion(campo) {
        campo.classList.remove('is-valid', 'is-invalid');
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    function configurarFechasPorDefecto() {
        const fechaIngreso = get('fecha_ingreso');
        const fechaInicioContrato = get('fecha_inicio_contrato');
        const hoy = new Date();
        const fechaHoy = formatearFecha(hoy);
        
        if (fechaIngreso && !fechaIngreso.value) {
            fechaIngreso.value = fechaHoy;
        }
        
        if (fechaInicioContrato && !fechaInicioContrato.value) {
            fechaInicioContrato.value = fechaHoy;
        }
    }

    function formatearFecha(fecha) {
        const dia = String(fecha.getDate()).padStart(2, '0');
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const año = fecha.getFullYear();
        return `${dia}/${mes}/${año}`;
    }

    // =================================
    // 🏢 GESTIÓN DE ÁREAS Y CATEGORÍAS
    // =================================
    
    if (areaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            
            // Limpiar y deshabilitar categorías
            categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
            categoriaSelect.disabled = true;

            if (!areaId) {
                categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
                actualizarVistaPrevia();
                return;
            }

            // Cargar categorías
            fetch(`/api/categorias/${areaId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    data.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.id_categoria;
                        option.textContent = categoria.nombre_categoria;
                        categoriaSelect.appendChild(option);
                    });
                    categoriaSelect.disabled = false;
                    console.log(`✅ Categorías cargadas para área ${areaId}:`, data.length);
                })
                .catch(error => {
                    console.error('❌ Error cargando categorías:', error);
                    categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                    mostrarAlerta('Error al cargar categorías. Recarga la página.', 'danger');
                });

            actualizarVistaPrevia();
        });
    }

    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', actualizarVistaPrevia);
    }

    // =================================
    // 🕒 GESTIÓN DE HORARIOS
    // =================================
    
    function validarHorarios() {
        const entrada = horaEntradaInput?.value;
        const salida = horaSalidaInput?.value;
        
        if (!entrada || !salida) {
            actualizarVistaPrevia();
            return;
        }

        if (!validarFormatoHora(entrada) || !validarFormatoHora(salida)) {
            return;
        }

        // Calcular horas
        const horas = calcularHoras(entrada, salida);
        
        if (horas < 1 || horas > 16) {
            mostrarErrorValidacion(horaSalidaInput, 
                `Horario inválido: ${horas}h. Debe estar entre 1 y 16 horas.`);
        } else {
            mostrarValidacionCorrecta(horaEntradaInput);
            mostrarValidacionCorrecta(horaSalidaInput);
        }

        actualizarVistaPrevia();
    }

    function validarFormatoHora(hora) {
        const formatoHora = /^([01]\d|2[0-3]):([0-5]\d)$/;
        return formatoHora.test(hora);
    }

    // =================================
    // 📅 GESTIÓN DE DÍAS LABORABLES
    // =================================
    
    configurarDiasLaborables();

    function configurarDiasLaborables() {
        // Botones de selección rápida
        const btnLunesViernes = get('btn-lunes-viernes');
        const btnLunesSabado = get('btn-lunes-sabado');
        const btnTodosDias = get('btn-todos-dias');
        const btnLimpiarDias = get('btn-limpiar-dias');
        
        if (btnLunesViernes) {
            btnLunesViernes.addEventListener('click', () => {
                seleccionarDias(['lunes', 'martes', 'miercoles', 'jueves', 'viernes']);
            });
        }
        
        if (btnLunesSabado) {
            btnLunesSabado.addEventListener('click', () => {
                seleccionarDias(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']);
            });
        }
        
        if (btnTodosDias) {
            btnTodosDias.addEventListener('click', () => {
                const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
                checkboxes.forEach(cb => cb.checked = true);
                actualizarVistaPrevia();
            });
        }
        
        if (btnLimpiarDias) {
            btnLimpiarDias.addEventListener('click', () => {
                const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
                checkboxes.forEach(cb => cb.checked = false);
                actualizarVistaPrevia();
            });
        }

        // Event listeners para cambios en días laborables
        document.querySelectorAll('input[name="dias_laborables[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', actualizarVistaPrevia);
        });
    }

    function seleccionarDias(dias) {
        // Limpiar todos
        document.querySelectorAll('input[name="dias_laborables[]"]').forEach(cb => cb.checked = false);
        
        // Seleccionar especificados
        dias.forEach(dia => {
            const checkbox = get(`dia_${dia}`);
            if (checkbox) checkbox.checked = true;
        });
        
        actualizarVistaPrevia();
    }

    // =================================
    // 📝 VALIDACIÓN DEL FORMULARIO
    // =================================
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('🔄 Enviando formulario...');
            
            // Validación básica
            if (!validarFormulario()) {
                e.preventDefault();
                return false;
            }

            // Mostrar estado de carga
            const btnCrear = get('btnCrearTrabajador');
            const textoNormal = get('btnTextoNormal');
            const textoCargando = get('btnTextoCargando');
            
            if (btnCrear && textoNormal && textoCargando) {
                textoNormal.classList.add('d-none');
                textoCargando.classList.remove('d-none');
                btnCrear.disabled = true;
            }

            console.log('✅ Formulario válido, enviando...');
        });
    }

    function validarFormulario() {
        let esValido = true;
        
        // Validar campos requeridos básicos
        const camposRequeridos = [
            'nombre_trabajador', 'ape_pat', 'fecha_nacimiento', 'curp', 'rfc', 
            'telefono', 'fecha_ingreso', 'id_area', 'id_categoria', 'sueldo_diarios',
            'hora_entrada', 'hora_salida', 'estatus', 'fecha_inicio_contrato', 'fecha_fin_contrato'
        ];

        camposRequeridos.forEach(campo => {
            const elemento = get(campo);
            if (!elemento?.value?.trim()) {
                elemento?.classList.add('is-invalid');
                esValido = false;
            } else {
                // Validar formato específico
                if (campo === 'fecha_nacimiento' || campo === 'fecha_ingreso' || 
                    campo === 'fecha_inicio_contrato' || campo === 'fecha_fin_contrato') {
                    if (!validarCampoFecha(elemento)) {
                        esValido = false;
                    }
                } else if (campo === 'hora_entrada' || campo === 'hora_salida') {
                    if (!validarCampoHora(elemento)) {
                        esValido = false;
                    }
                }
            }
        });

        // Validar días laborables
        const diasSeleccionados = document.querySelectorAll('input[name="dias_laborables[]"]:checked');
        if (diasSeleccionados.length === 0) {
            mostrarAlerta('Debe seleccionar al menos un día laborable', 'warning');
            esValido = false;
        }

        if (!esValido) {
            mostrarAlerta('Complete todos los campos requeridos correctamente', 'warning');
            form.classList.add('was-validated');
            
            // Scroll al primer campo con error
            const primerError = form.querySelector('.is-invalid');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                primerError.focus();
            }
        }

        return esValido;
    }

    // =================================
    // 🎨 FUNCIONES DE VISTA PREVIA
    // =================================
    
    // Event listeners para actualizar vista previa
    const camposConVistaPrevia = [
        'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios',
        'ciudad_actual', 'estado_actual', 'hora_entrada', 'hora_salida'
    ];

    camposConVistaPrevia.forEach(campo => {
        const elemento = get(campo);
        if (elemento) {
            elemento.addEventListener('input', () => {
                if (typeof actualizarVistaPrevia === 'function') {
                    actualizarVistaPrevia();
                }
            });
        }
    });

    // =================================
    // 🛠️ FUNCIONES AUXILIARES
    // =================================

    function configurarCamposMayusculas() {
        const camposMayusculas = [
            'nombre_trabajador', 'ape_pat', 'ape_mat', 'ciudad_actual', 
            'estado_actual', 'curp', 'rfc', 'lugar_nacimiento', 'direccion'
        ];

        camposMayusculas.forEach(campo => {
            const elemento = get(campo);
            if (elemento) {
                elemento.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }
        });
    }

    function configurarAlertasExito() {
        const successAlert = get('success-alert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function calcularHoras(entrada, salida) {
        const base = '2024-01-01';
        let e = new Date(`${base}T${entrada}:00`);
        let s = new Date(`${base}T${salida}:00`);
        if (s <= e) s.setDate(s.getDate() + 1);
        return Math.round((s - e) / 3600000 * 100) / 100;
    }

    function mostrarAlerta(mensaje, tipo = 'info') {
        // Remover alertas existentes
        const alertaExistente = document.querySelector('.alerta-temporal');
        if (alertaExistente) alertaExistente.remove();

        // Crear nueva alerta
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show alerta-temporal position-fixed`;
        alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'x-circle' : 'info-circle'} me-2"></i>
                <div>${mensaje}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alerta);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alerta.parentNode) alerta.remove();
        }, 5000);
    }

    // =================================
    // 🎯 INICIALIZACIÓN FINAL
    // =================================
    
    // Ejecutar vista previa inicial
    if (typeof actualizarVistaPrevia === 'function') {
        actualizarVistaPrevia();
    }

    console.log('✅ Sistema de creación de trabajadores con formato controlado inicializado');
});