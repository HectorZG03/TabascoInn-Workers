/**
 * ✅ CREAR TRABAJADOR SIMPLIFICADO
 * Script unificado y simplificado para la creación de trabajadores
 * Sin modal, sin complicaciones, flujo directo
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando sistema simplificado de creación de trabajadores');

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
    
    // Configurar fechas por defecto
    configurarFechasPorDefecto();
    
    // Configurar campos con mayúsculas
    configurarCamposMayusculas();
    
    // Configurar alertas de éxito
    configurarAlertasExito();

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
    
    [horaEntradaInput, horaSalidaInput].forEach(input => {
        if (input) {
            input.addEventListener('input', validarHorarios);
            input.addEventListener('change', validarHorarios);
        }
    });

    function validarHorarios() {
        const entrada = horaEntradaInput?.value;
        const salida = horaSalidaInput?.value;
        
        // Limpiar validaciones anteriores
        [horaEntradaInput, horaSalidaInput].forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });

        if (!entrada || !salida) {
            actualizarVistaPrevia();
            return;
        }

        // Validar formato
        const formato24h = /^([01]\d|2[0-3]):[0-5]\d$/;
        
        if (!formato24h.test(entrada)) {
            horaEntradaInput.classList.add('is-invalid');
            mostrarMensajeValidacion(horaEntradaInput, 'Formato inválido. Use HH:MM (24h)');
            return;
        }

        if (!formato24h.test(salida)) {
            horaSalidaInput.classList.add('is-invalid');
            mostrarMensajeValidacion(horaSalidaInput, 'Formato inválido. Use HH:MM (24h)');
            return;
        }

        // Calcular horas
        const horas = calcularHoras(entrada, salida);
        
        if (horas < 1 || horas > 16) {
            horaSalidaInput.classList.add('is-invalid');
            mostrarMensajeValidacion(horaSalidaInput, 
                `Horario inválido: ${horas}h. Debe estar entre 1 y 16 horas.`);
        } else {
            [horaEntradaInput, horaSalidaInput].forEach(input => {
                input.classList.add('is-valid');
            });
        }

        actualizarVistaPrevia();
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
                elemento?.classList.remove('is-invalid');
            }
        });

        // Validar días laborables
        const diasSeleccionados = document.querySelectorAll('input[name="dias_laborables[]"]:checked');
        if (diasSeleccionados.length === 0) {
            mostrarAlerta('Debe seleccionar al menos un día laborable', 'warning');
            esValido = false;
        }

        // Validar fechas del contrato
        const fechaInicio = get('fecha_inicio_contrato')?.value;
        const fechaFin = get('fecha_fin_contrato')?.value;
        
        if (fechaInicio && fechaFin) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            if (fin <= inicio) {
                get('fecha_fin_contrato')?.classList.add('is-invalid');
                mostrarAlerta('La fecha de fin del contrato debe ser posterior a la de inicio', 'warning');
                esValido = false;
            }
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
    
    // La función actualizarVistaPrevia se define en vista_previa_simplificada.blade.php
    // Aquí agregamos los event listeners
    const camposConVistaPrevia = [
        'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios',
        'ciudad_actual', 'estado_actual'
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

    function configurarFechasPorDefecto() {
        const fechaIngreso = get('fecha_ingreso');
        const fechaInicioContrato = get('fecha_inicio_contrato');
        const hoy = new Date().toISOString().split('T')[0];
        
        if (fechaIngreso && !fechaIngreso.value) {
            fechaIngreso.value = hoy;
        }
        
        if (fechaInicioContrato && !fechaInicioContrato.value) {
            fechaInicioContrato.value = hoy;
        }
    }

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
        let e = new Date(`${base}T${entrada}`);
        let s = new Date(`${base}T${salida}`);
        if (s <= e) s.setDate(s.getDate() + 1);
        return Math.round((s - e) / 3600000 * 100) / 100;
    }

    function mostrarMensajeValidacion(elemento, mensaje) {
        let feedback = elemento.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            elemento.parentNode.appendChild(feedback);
        }
        feedback.textContent = mensaje;
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

    console.log('✅ Sistema de creación de trabajadores inicializado correctamente');
});