document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando crear_trabajador.js v2.0');

    // ✅ DETECTAR MENSAJE DE ÉXITO Y LIMPIAR FORMULARIO
    const successAlert = document.getElementById('success-alert');
    const form = document.getElementById('formTrabajador');
    
    if (successAlert) {
        limpiarFormulario();
        setTimeout(() => {
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
        }, 5000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ✅ ELEMENTOS DEL FORMULARIO
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    const horaEntradaInput = document.getElementById('hora_entrada');
    const horaSalidaInput = document.getElementById('hora_salida');
    
    // ✅ VALIDAR QUE LOS ELEMENTOS EXISTEN
    if (!areaSelect || !categoriaSelect) {
        console.error('❌ No se encontraron los elementos de área o categoría');
        return;
    }

    // ✅ CASCADA ÁREA -> CATEGORÍA
    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        categoriaSelect.disabled = true;
        
        if (areaId) {
            fetch(`/api/categorias/${areaId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    data.forEach(categoria => {
                        categoriaSelect.innerHTML += `<option value="${categoria.id_categoria}">${categoria.nombre_categoria}</option>`;
                    });
                    categoriaSelect.disabled = false;
                    console.log(`✅ Categorías cargadas para área ${areaId}:`, data.length);
                })
                .catch(error => {
                    console.error('❌ Error cargando categorías:', error);
                    categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                    
                    // Mostrar mensaje de error al usuario
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                    alertDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle"></i>
                        Error al cargar categorías. Verifique su conexión.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    categoriaSelect.parentNode.appendChild(alertDiv);
                });
        } else {
            categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
            categoriaSelect.disabled = true;
        }
        
        actualizarVistaPrevia();
    });

    // ✅ EVENT LISTENERS PARA HORARIOS
    if (horaEntradaInput && horaSalidaInput) {
        horaEntradaInput.addEventListener('change', actualizarVistaPrevia);
        horaSalidaInput.addEventListener('change', actualizarVistaPrevia);
        horaEntradaInput.addEventListener('input', actualizarVistaPrevia);
        horaSalidaInput.addEventListener('input', actualizarVistaPrevia);
        
        // ✅ VALIDACIÓN EN TIEMPO REAL
        horaEntradaInput.addEventListener('change', validarHorarios);
        horaSalidaInput.addEventListener('change', validarHorarios);
        
        console.log('✅ Event listeners de horarios configurados');
    } else {
        console.warn('⚠️ No se encontraron los inputs de horarios');
    }

    // ✅ FUNCIÓN PRINCIPAL: Vista previa en tiempo real
    function actualizarVistaPrevia() {
        try {
            // Obtener valores del formulario
            const nombre = document.getElementById('nombre_trabajador')?.value || '';
            const apePaterno = document.getElementById('ape_pat')?.value || '';
            const apeMaterno = document.getElementById('ape_mat')?.value || '';
            const fechaNacimiento = document.getElementById('fecha_nacimiento')?.value || '';
            const sueldo = document.getElementById('sueldo_diarios')?.value || '';
            
            // Información de área y categoría
            const categoriaText = categoriaSelect.options[categoriaSelect.selectedIndex]?.text || 'Sin categoría';
            const areaText = areaSelect.options[areaSelect.selectedIndex]?.text || 'Sin área';
            
            // ✅ ACTUALIZAR NOMBRE
            const nombreCompleto = `${nombre} ${apePaterno} ${apeMaterno}`.trim() || 'Nombre del Trabajador';
            const previewNombre = document.getElementById('preview-nombre');
            if (previewNombre) {
                previewNombre.textContent = nombreCompleto;
            }
            
            // ✅ ACTUALIZAR CATEGORÍA Y ÁREA
            const previewCategoria = document.getElementById('preview-categoria');
            if (previewCategoria) {
                previewCategoria.textContent = 
                    (categoriaText !== 'Seleccionar categoría...' && categoriaText !== 'Sin categoría') ? 
                    `${categoriaText} - ${areaText}` : 'Categoría - Área';
            }
            
            // ✅ ACTUALIZAR SUELDO
            const previewSueldo = document.getElementById('preview-sueldo');
            if (previewSueldo) {
                previewSueldo.textContent = sueldo ? `$${parseFloat(sueldo).toFixed(2)}` : '$0.00';
            }
            
            // ✅ CALCULAR Y MOSTRAR EDAD
            const previewEdad = document.getElementById('preview-edad');
            if (previewEdad) {
                if (fechaNacimiento) {
                    const edad = calcularEdad(fechaNacimiento);
                    previewEdad.textContent = `${edad} años`;
                } else {
                    previewEdad.textContent = '-- años';
                }
            }

            // ✅ ACTUALIZAR UBICACIÓN Y HORARIO
            actualizarVistaUbicacion();
            actualizarVistaHorario();
            
        } catch (error) {
            console.error('❌ Error en actualizarVistaPrevia:', error);
        }
    }

    // ✅ FUNCIÓN: Calcular edad
    function calcularEdad(fechaNacimiento) {
        try {
            const hoy = new Date();
            const nacimiento = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mesActual = hoy.getMonth();
            const mesNacimiento = nacimiento.getMonth();
            
            if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            
            return edad;
        } catch (error) {
            console.error('❌ Error calculando edad:', error);
            return 0;
        }
    }

    // ✅ FUNCIÓN: Actualizar vista previa de ubicación
    function actualizarVistaUbicacion() {
        try {
            const ciudad = document.getElementById('ciudad_actual')?.value || '';
            const estado = document.getElementById('estado_actual')?.value || '';
            const previewUbicacion = document.getElementById('preview-ubicacion');
            
            if (previewUbicacion) {
                if (ciudad && estado) {
                    previewUbicacion.textContent = `${ciudad}, ${estado}`;
                } else if (ciudad || estado) {
                    previewUbicacion.textContent = ciudad || estado;
                } else {
                    previewUbicacion.textContent = 'No especificada';
                }
            }
        } catch (error) {
            console.error('❌ Error actualizando ubicación:', error);
        }
    }
    
    // ✅ FUNCIÓN: Vista previa de horario con cálculo de horas y turno
    function actualizarVistaHorario() {
        try {
            const entrada = horaEntradaInput?.value || '';
            const salida = horaSalidaInput?.value || '';
            
            const previewHoras = document.getElementById('preview-horas');
            const previewTurno = document.getElementById('preview-turno');
            
            if (entrada && salida) {
                // ✅ CALCULAR HORAS Y TURNO
                const horasCalculadas = calcularHorasTrabajadas(entrada, salida);
                const turnoCalculado = calcularTurno(entrada, salida);
                
                // Actualizar vista previa
                if (previewHoras) {
                    previewHoras.textContent = `${horasCalculadas} hrs`;
                }
                
                if (previewTurno) {
                    previewTurno.textContent = turnoCalculado.charAt(0).toUpperCase() + turnoCalculado.slice(1);
                }
                
                console.log(`✅ Horario: ${entrada} - ${salida} = ${horasCalculadas} hrs (${turnoCalculado})`);
            } else {
                // Sin horarios completos
                if (previewHoras) previewHoras.textContent = '-- hrs';
                if (previewTurno) previewTurno.textContent = '--';
            }
        } catch (error) {
            console.error('❌ Error actualizando horario:', error);
        }
    }

    // ✅ FUNCIÓN: Calcular horas trabajadas
    function calcularHorasTrabajadas(entrada, salida) {
        try {
            const fechaBase = '2024-01-01';
            const horaEntrada = new Date(`${fechaBase} ${entrada}`);
            let horaSalida = new Date(`${fechaBase} ${salida}`);
            
            // Si la salida es menor que la entrada, asumir que cruza medianoche
            if (horaSalida <= horaEntrada) {
                horaSalida.setDate(horaSalida.getDate() + 1);
            }
            
            const diferenciaMilisegundos = horaSalida - horaEntrada;
            const horas = diferenciaMilisegundos / (1000 * 60 * 60);
            
            return Math.round(horas * 100) / 100; // Redondear a 2 decimales
        } catch (error) {
            console.error('❌ Error calculando horas:', error);
            return 0;
        }
    }

    // ✅ FUNCIÓN: Calcular turno automáticamente
    function calcularTurno(entrada, salida) {
        try {
            const HORARIO_DIURNO_INICIO = '06:00';
            const HORARIO_DIURNO_FIN = '18:00';
            const HORARIO_NOCTURNO_INICIO = '18:00';
            
            const entradaMinutos = convertirHoraAMinutos(entrada);
            const salidaMinutos = convertirHoraAMinutos(salida);
            const diurnoInicioMinutos = convertirHoraAMinutos(HORARIO_DIURNO_INICIO);
            const diurnoFinMinutos = convertirHoraAMinutos(HORARIO_DIURNO_FIN);
            const nocturnoInicioMinutos = convertirHoraAMinutos(HORARIO_NOCTURNO_INICIO);
            
            // Si cruza medianoche, es nocturno
            if (salidaMinutos <= entradaMinutos) {
                return 'nocturno';
            }
            
            // Clasificar según horarios
            if (entradaMinutos >= diurnoInicioMinutos && salidaMinutos <= diurnoFinMinutos) {
                return 'diurno';
            } else if (entradaMinutos >= nocturnoInicioMinutos || salidaMinutos <= diurnoInicioMinutos) {
                return 'nocturno';
            } else {
                return 'mixto';
            }
        } catch (error) {
            console.error('❌ Error calculando turno:', error);
            return 'mixto';
        }
    }

    // ✅ FUNCIÓN: Convertir hora a minutos
    function convertirHoraAMinutos(hora) {
        const [horas, minutos] = hora.split(':').map(Number);
        return horas * 60 + minutos;
    }

    // ✅ FUNCIÓN: Validación en tiempo real de horarios
    function validarHorarios() {
        try {
            const entrada = horaEntradaInput?.value;
            const salida = horaSalidaInput?.value;
            
            if (entrada && salida) {
                const horas = calcularHorasTrabajadas(entrada, salida);
                
                // Limpiar estilos anteriores
                horaEntradaInput.classList.remove('is-invalid');
                horaSalidaInput.classList.remove('is-invalid');
                
                // Validar rango (1-16 horas)
                if (horas < 1 || horas > 16) {
                    horaSalidaInput.classList.add('is-invalid');
                    mostrarMensajeValidacion(horaSalidaInput, `Horario inválido: ${horas} horas. Debe estar entre 1 y 16 horas.`);
                } else {
                    horaSalidaInput.classList.add('is-valid');
                    eliminarMensajeValidacion(horaSalidaInput);
                }
            }
        } catch (error) {
            console.error('❌ Error validando horarios:', error);
        }
    }

    // ✅ FUNCIÓN: Mostrar mensaje de validación
    function mostrarMensajeValidacion(elemento, mensaje) {
        let feedback = elemento.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            elemento.parentNode.appendChild(feedback);
        }
        feedback.textContent = mensaje;
    }

    // ✅ FUNCIÓN: Eliminar mensaje de validación
    function eliminarMensajeValidacion(elemento) {
        const feedback = elemento.parentNode.querySelector('.invalid-feedback');
        if (feedback && feedback.textContent.includes('Horario inválido')) {
            feedback.remove();
        }
    }

    // ✅ EVENT LISTENERS PARA VISTA PREVIA
    const camposParaVistaPrevia = ['nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios', 'ciudad_actual', 'estado_actual'];
    
    camposParaVistaPrevia.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', actualizarVistaPrevia);
            element.addEventListener('change', actualizarVistaPrevia);
        }
    });
    
    categoriaSelect.addEventListener('change', actualizarVistaPrevia);

    // ✅ VALIDACIÓN CURP Y RFC EN TIEMPO REAL
    const curpInput = document.getElementById('curp');
    const rfcInput = document.getElementById('rfc');
    
    if (curpInput) {
        curpInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    if (rfcInput) {
        rfcInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // ✅ FUNCIÓN PARA LIMPIAR EL FORMULARIO
    function limpiarFormulario() {
        if (!form) return;
        
        try {
            // Limpiar todos los inputs
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="number"], input[type="time"]');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            // Limpiar selects
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0;
                select.classList.remove('is-invalid', 'is-valid');
            });
            
            // Resetear categoría
            categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
            categoriaSelect.disabled = true;
            
            // Resetear fecha de ingreso a hoy
            const fechaIngreso = document.getElementById('fecha_ingreso');
            if (fechaIngreso) {
                fechaIngreso.value = new Date().toISOString().split('T')[0];
            }
            
            actualizarVistaPrevia();
            console.log('✅ Formulario limpiado exitosamente');
        } catch (error) {
            console.error('❌ Error limpiando formulario:', error);
        }
    }

    // ✅ BOTÓN MANUAL PARA LIMPIAR FORMULARIO
    const btnLimpiar = document.createElement('button');
    btnLimpiar.type = 'button';
    btnLimpiar.className = 'btn btn-outline-warning me-2';
    btnLimpiar.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Limpiar Formulario';
    btnLimpiar.onclick = limpiarFormulario;
    
    const btnCancelar = document.querySelector('a[href*="trabajadores.index"]');
    if (btnCancelar) {
        btnCancelar.parentNode.insertBefore(btnLimpiar, btnCancelar);
    }

    // ✅ INICIALIZAR VISTA PREVIA
    actualizarVistaPrevia();
    
    console.log('✅ crear_trabajador.js FINAL cargado correctamente');
});