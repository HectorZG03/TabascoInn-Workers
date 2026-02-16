/**
 * ✅ CREAR TRABAJADOR CON CONTRATOS DETERMINADO/INDETERMINADO - VERSIÓN CORREGIDA
 * Script actualizado para manejar tipos de contrato sin conflictos
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando sistema de creación de trabajadores con contratos determinado/indeterminado');

    // Elementos principales
    const form = document.getElementById('formTrabajador');
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    const horaEntradaInput = document.getElementById('hora_entrada');
    const horaSalidaInput = document.getElementById('hora_salida');
    
    // ✅ Elementos de contrato
    const tipoContratoSelect = document.getElementById('tipo_contrato');
    const fechaInicioContrato = document.getElementById('fecha_inicio_contrato');
    const fechaFinContrato = document.getElementById('fecha_fin_contrato');

    // Helpers
    const get = id => document.getElementById(id);

    // ✅ FUNCIÓN SEGURA PARA ACTUALIZAR VISTA PREVIA
    function actualizarVistaPreviaSafe() {
        try {
            if (typeof window.actualizarVistaPrevia === 'function') {
                window.actualizarVistaPrevia();
            }
        } catch (error) {
            console.warn('⚠️ Error al actualizar vista previa:', error);
        }
    }

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
            if (categoriaSelect) {
                categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
                categoriaSelect.disabled = true;
            }

            if (!areaId) {
                if (categoriaSelect) {
                    categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
                }
                actualizarVistaPreviaSafe();
                return;
            }

            // ✅ USAR RUTA DINÁMICA
            fetch(apiUrl(`categorias/${areaId}`))
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (categoriaSelect) {
                        categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                        data.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.id_categoria;
                            option.textContent = categoria.nombre_categoria;
                            categoriaSelect.appendChild(option);
                        });
                        categoriaSelect.disabled = false;
                    }
                    console.log(`✅ Categorías cargadas para área ${areaId}:`, data.length);
                })
                .catch(error => {
                    console.error('❌ Error cargando categorías:', error);
                    if (categoriaSelect) {
                        categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                    }
                    mostrarAlerta('Error al cargar categorías. Recarga la página.', 'danger');
                });

            actualizarVistaPreviaSafe();
        });
    }

    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', actualizarVistaPreviaSafe);
    }

    // =================================
    // 🕒 VALIDACIÓN DE HORARIOS
    // =================================
    
    [horaEntradaInput, horaSalidaInput].forEach(input => {
        if (input) {
            input.addEventListener('input', validarHorarios);
            input.addEventListener('change', validarHorarios);
        }
    });

    function validarHorarios() {
        // Usar función global para validar rango de horario
        if (window.FormatoGlobal && horaEntradaInput && horaSalidaInput) {
            window.FormatoGlobal.validarRangoHorario(horaEntradaInput, horaSalidaInput);
        }
        actualizarVistaPreviaSafe();
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
                actualizarVistaPreviaSafe();
            });
        }
        
        if (btnLimpiarDias) {
            btnLimpiarDias.addEventListener('click', () => {
                const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
                checkboxes.forEach(cb => cb.checked = false);
                actualizarVistaPreviaSafe();
            });
        }

        // Event listeners para cambios en días laborables
        document.querySelectorAll('input[name="dias_laborables[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', actualizarVistaPreviaSafe);
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
        
        actualizarVistaPreviaSafe();
    }

    // =================================
    // ⚡ VALIDACIÓN EN TIEMPO REAL ACTUALIZADA
    // =================================
    
    // ✅ Validar tipo de contrato y fechas
    if (tipoContratoSelect) {
        tipoContratoSelect.addEventListener('change', () => {
            validarContratosSegunTipo();
            actualizarVistaPreviaSafe();
        });
    }

    if (fechaInicioContrato && fechaFinContrato) {
        [fechaInicioContrato, fechaFinContrato].forEach(campo => {
            campo.addEventListener('input', () => {
                setTimeout(() => {
                    validarContratosSegunTipo();
                    actualizarVistaPreviaSafe();
                }, 300);
            });
        });
    }

    // ✅ Función para validar contratos según tipo
    function validarContratosSegunTipo() {
        const tipoContrato = tipoContratoSelect?.value;
        
        if (!tipoContrato) return;

        if (tipoContrato === 'indeterminado') {
            // Para contratos indeterminados, solo validar fecha inicio
            if (window.FormatoGlobal && fechaInicioContrato?.value) {
                window.FormatoGlobal.validarFormatoFecha(fechaInicioContrato);
            }
        } else if (tipoContrato === 'determinado') {
            // Para contratos determinados, validar ambas fechas
            if (window.FormatoGlobal && fechaInicioContrato && fechaFinContrato) {
                window.FormatoGlobal.validarRangoFechas(fechaInicioContrato, fechaFinContrato);
            }
        }
    }

    // =================================
    // 📝 VALIDACIÓN DEL FORMULARIO ACTUALIZADA
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
        
        // ✅ Campos requeridos básicos sin fecha_fin_contrato
        const camposBasicos = [
            'nombre_trabajador', 'ape_pat', 'fecha_nacimiento', 'curp', 'rfc', 
            'telefono', 'fecha_ingreso', 'id_area', 'id_categoria', 'sueldo_diarios',
            'hora_entrada', 'hora_salida', 'estatus', 'tipo_contrato', 'fecha_inicio_contrato'
        ];

        // Validar campos básicos
        camposBasicos.forEach(campo => {
            const elemento = get(campo);
            if (!elemento?.value?.trim()) {
                elemento?.classList.add('is-invalid');
                esValido = false;
                console.warn(`❌ Campo requerido faltante: ${campo}`);
            } else {
                elemento?.classList.remove('is-invalid');
                
                // Validar formato específico usando funciones globales
                if (campo.includes('fecha') && window.validarFormatoFecha) {
                    if (!window.validarFormatoFecha(elemento.value)) {
                        elemento.classList.add('is-invalid');
                        esValido = false;
                    }
                } else if (campo.includes('hora') && window.validarFormatoHora) {
                    if (!window.validarFormatoHora(elemento.value)) {
                        elemento.classList.add('is-invalid');
                        esValido = false;
                    }
                }
            }
        });

        // ✅ Validación condicional de fecha_fin_contrato
        const tipoContrato = get('tipo_contrato')?.value;
        const fechaFin = get('fecha_fin_contrato');
        
        if (tipoContrato === 'determinado') {
            // Para contratos determinados, fecha fin ES requerida
            if (!fechaFin?.value?.trim()) {
                fechaFin?.classList.add('is-invalid');
                esValido = false;
                console.warn('❌ Fecha fin requerida para contrato determinado');
            } else {
                fechaFin?.classList.remove('is-invalid');
                
                // Validar formato de fecha fin
                if (window.validarFormatoFecha && !window.validarFormatoFecha(fechaFin.value)) {
                    fechaFin.classList.add('is-invalid');
                    esValido = false;
                }
            }
        } else if (tipoContrato === 'indeterminado') {
            // Para contratos indeterminados, fecha fin NO debe estar presente
            if (fechaFin?.value?.trim()) {
                fechaFin.classList.add('is-invalid');
                esValido = false;
                console.warn('❌ Fecha fin no debe especificarse para contrato indeterminado');
                mostrarAlerta('Los contratos indeterminados no deben tener fecha de fin', 'warning');
            } else {
                fechaFin?.classList.remove('is-invalid');
            }
        }

        // Validar días laborables
        const diasSeleccionados = document.querySelectorAll('input[name="dias_laborables[]"]:checked');
        if (diasSeleccionados.length === 0) {
            mostrarAlerta('Debe seleccionar al menos un día laborable', 'warning');
            esValido = false;
        }

        // Validar rango de horario usando función global
        if (window.FormatoGlobal && horaEntradaInput && horaSalidaInput) {
            if (!window.FormatoGlobal.validarRangoHorario(horaEntradaInput, horaSalidaInput)) {
                esValido = false;
            }
        }

        // ✅ Validación de fechas según tipo de contrato
        if (tipoContrato === 'determinado' && window.FormatoGlobal && fechaInicioContrato && fechaFinContrato) {
            if (!window.FormatoGlobal.validarRangoFechas(fechaInicioContrato, fechaFinContrato)) {
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
    // 🎨 EVENT LISTENERS PARA VISTA PREVIA
    // =================================
    
    // ✅ Event listeners para actualizar vista previa (incluye tipo_contrato)
    const camposConVistaPrevia = [
        'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios',
        'ciudad_actual', 'estado_actual', 'hora_entrada', 'hora_salida', 'estatus',
        'tipo_contrato', 'fecha_inicio_contrato', 'fecha_fin_contrato'
    ];

    camposConVistaPrevia.forEach(campo => {
        const elemento = get(campo);
        if (elemento) {
            elemento.addEventListener('input', actualizarVistaPreviaSafe);
            elemento.addEventListener('change', actualizarVistaPreviaSafe);
        }
    });

    // =================================
    // 🛠️ FUNCIONES AUXILIARES
    // =================================

    function configurarFechasPorDefecto() {
        const fechaIngreso = get('fecha_ingreso');
        const fechaInicioContrato = get('fecha_inicio_contrato');
        // Solo sugerir fecha actual como placeholder visual, pero sin forzar
        if (fechaIngreso && !fechaIngreso.value && fechaIngreso.placeholder === 'DD/MM/YYYY') {
            // No establecer valor automático
        }
        
        if (fechaInicioContrato && !fechaInicioContrato.value && fechaInicioContrato.placeholder === 'DD/MM/YYYY') {
            // No establecer valor automático
        }
    }

    function configurarCamposMayusculas() {
        const camposMayusculas = [
            'nombre_trabajador', 'ape_pat', 'ape_mat', 'ciudad_actual', 
            'estado_actual', 'curp', 'rfc', 'lugar_nacimiento', 'direccion',
            'beneficiario_nombre', 'contacto_nombre_completo', 'contacto_direccion'
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
    
    // ✅ Ejecutar vista previa inicial con delay para asegurar que todo esté cargado
    setTimeout(() => {
        actualizarVistaPreviaSafe();
    }, 200);

    console.log('✅ Sistema de creación de trabajadores inicializado correctamente con soporte para contratos determinado/indeterminado');
});