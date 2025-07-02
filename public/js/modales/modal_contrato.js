
/**
 * ✅ SCRIPT COMPLETAMENTE LIMPIO - SIN función de preview
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principales
    const form = document.getElementById('formTrabajador');
    const modal = new bootstrap.Modal(document.getElementById('modalContrato'));
    const estatusSelect = document.getElementById('estatus');
    const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
    const fechaFinInput = document.getElementById('fecha_fin_contrato');
    const btnCrear = document.getElementById('btnCrearTrabajador');
    
    let tipoCalculado = null;
    
    console.log('✅ Modal de contrato inicializado (SIN PREVIEW)');

    // ========================================
    // 🔵 INTERCEPTAR ENVÍO DEL FORMULARIO
    // ========================================
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                mostrarToast('Por favor completa todos los campos obligatorios', 'warning');
                return;
            }

            // Abrir modal directamente
            modal.show();
        });
    }

    // ========================================
    // 🔵 MANEJAR CAMBIO DE ESTADO
    // ========================================
    if (estatusSelect) {
        estatusSelect.addEventListener('change', function() {
            mostrarVistaEstado();
        });
    }

    function mostrarVistaEstado() {
        const estadoSeleccionado = estatusSelect.value;
        const estadoPreview = document.getElementById('estadoPreview');
        const previewAlert = document.getElementById('estadoPreviewAlert');
        const previewIcon = document.getElementById('estadoPreviewIcon');
        const previewTexto = document.getElementById('estadoPreviewTexto');
        const previewDescripcion = document.getElementById('estadoPreviewDescripcion');
        const errorEstatus = document.getElementById('errorEstatus');
        
        // Limpiar errores
        if (errorEstatus) errorEstatus.style.display = 'none';
        estatusSelect.classList.remove('is-invalid');
        
        if (!estadoSeleccionado) {
            if (estadoPreview) estadoPreview.style.display = 'none';
            return;
        }
        
        let alertClass, iconClass, textoEstado, descripcionEstado;
        
        switch (estadoSeleccionado) {
            case 'activo':
                alertClass = 'alert-success';
                iconClass = 'bi-check-circle-fill text-success';
                textoEstado = 'Trabajador Activo';
                descripcionEstado = 'Operará normalmente desde el primer día con todos los derechos.';
                break;
            case 'prueba':
                alertClass = 'alert-warning';
                iconClass = 'bi-hourglass-split text-warning';
                textoEstado = 'En Período de Prueba';
                descripcionEstado = 'Estará en evaluación durante el período establecido.';
                break;
            default:
                if (estadoPreview) estadoPreview.style.display = 'none';
                return;
        }
        
        // Actualizar vista previa
        if (previewAlert) previewAlert.className = `alert ${alertClass} mb-0`;
        if (previewIcon) previewIcon.className = `${iconClass} me-2 fs-5`;
        if (previewTexto) previewTexto.textContent = textoEstado;
        if (previewDescripcion) previewDescripcion.textContent = descripcionEstado;
        if (estadoPreview) estadoPreview.style.display = 'block';
        
        console.log('✅ Estado seleccionado:', estadoSeleccionado);
    }

    // ========================================
    // 🔵 MANEJAR CAMBIOS DE FECHAS
    // ========================================
    if (fechaInicioInput) {
        fechaInicioInput.addEventListener('change', calcularDuracion);
    }
    
    if (fechaFinInput) {
        fechaFinInput.addEventListener('change', calcularDuracion);
    }

    function calcularDuracion() {
        const fechaInicio = fechaInicioInput?.value;
        const fechaFin = fechaFinInput?.value;
        
        ocultarError();
        
        if (!fechaInicio) {
            ocultarDuracion();
            return;
        }

        // Configurar fecha mínima para fecha fin
        if (fechaFinInput && fechaInicio) {
            const minDate = new Date(fechaInicio);
            minDate.setDate(minDate.getDate() + 1);
            fechaFinInput.min = minDate.toISOString().split('T')[0];
        }

        if (!fechaFin) {
            ocultarDuracion();
            return;
        }

        // Validar fechas
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            mostrarError('La fecha de finalización debe ser posterior a la fecha de inicio');
            ocultarDuracion();
            return;
        }

        // Calcular duración
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        
        let tipoDuracion, duracionTexto;
        
        if (diasTotales > 30) {
            tipoDuracion = 'meses';
            let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
            
            if (fin.getDate() < inicio.getDate()) {
                meses--;
            }
            
            if (meses <= 0 && fin > inicio) {
                meses = 1;
            }
            
            duracionTexto = `${meses} ${meses === 1 ? 'mes' : 'meses'}`;
        } else {
            tipoDuracion = 'dias';
            duracionTexto = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
        }
        
        tipoCalculado = tipoDuracion;
        
        // Mostrar vista previa
        const duracionEl = document.getElementById('duracionTexto');
        const fechaInicioEl = document.getElementById('fechaInicioTexto');
        const fechaFinEl = document.getElementById('fechaFinTexto');
        
        if (duracionEl) duracionEl.textContent = duracionTexto;
        if (fechaInicioEl) fechaInicioEl.textContent = inicio.toLocaleDateString('es-MX');
        if (fechaFinEl) fechaFinEl.textContent = fin.toLocaleDateString('es-MX');
        
        mostrarDuracion();
        
        console.log('✅ Duración calculada:', { diasTotales, tipoDuracion, duracionTexto });
    }

    // ========================================
    // 🔵 CREAR TRABAJADOR (BOTÓN PRINCIPAL)
    // ========================================
    if (btnCrear) {
        btnCrear.addEventListener('click', function() {
            console.log('🚀 Iniciando creación de trabajador...');
            
            const estatus = estatusSelect?.value;
            const fechaInicio = fechaInicioInput?.value;
            const fechaFin = fechaFinInput?.value;
            
            // Validaciones
            if (!validarEstado(estatus)) return;
            if (!validarFechas(fechaInicio, fechaFin)) return;
            if (!tipoCalculado) {
                mostrarError('Error en el cálculo de la duración del contrato');
                return;
            }
            
            // Estado de carga
            mostrarCargando();
            
            try {
                // Agregar campos al formulario
                agregarCampoOculto('estatus', estatus);
                agregarCampoOculto('fecha_inicio_contrato', fechaInicio);
                agregarCampoOculto('fecha_fin_contrato', fechaFin);
                agregarCampoOculto('tipo_duracion', tipoCalculado);
                
                console.log('✅ Campos agregados, enviando formulario:', {
                    estatus,
                    fechaInicio,
                    fechaFin,
                    tipo: tipoCalculado
                });
                
                // Enviar formulario después de un pequeño delay
                setTimeout(() => {
                    form.submit();
                }, 100);
                
            } catch (error) {
                console.error('❌ Error al crear trabajador:', error);
                mostrarError('Error al procesar los datos. Inténtalo de nuevo.');
                ocultarCargando();
            }
        });
    }

    // ========================================
    // 🔧 FUNCIONES AUXILIARES
    // ========================================
    
    function validarEstado(estatus) {
        const errorEstatus = document.getElementById('errorEstatus');
        
        if (!estatus) {
            if (errorEstatus) {
                errorEstatus.textContent = 'Por favor selecciona el estado inicial';
                errorEstatus.style.display = 'block';
            }
            estatusSelect.classList.add('is-invalid');
            estatusSelect.focus();
            return false;
        }
        
        if (!['activo', 'prueba'].includes(estatus)) {
            if (errorEstatus) {
                errorEstatus.textContent = 'Estado no válido';
                errorEstatus.style.display = 'block';
            }
            estatusSelect.classList.add('is-invalid');
            return false;
        }
        
        if (errorEstatus) errorEstatus.style.display = 'none';
        estatusSelect.classList.remove('is-invalid');
        return true;
    }
    
    function validarFechas(fechaInicio, fechaFin) {
        if (!fechaInicio || !fechaFin) {
            mostrarError('Por favor completa ambas fechas del contrato');
            return false;
        }
        
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (fin <= inicio) {
            mostrarError('La fecha de fin debe ser posterior a la de inicio');
            return false;
        }
        
        ocultarError();
        return true;
    }
    
    function agregarCampoOculto(nombre, valor) {
        // Remover campo existente
        const existente = form.querySelector(`input[name="${nombre}"]`);
        if (existente) {
            existente.value = valor;
            return;
        }
        
        // Crear nuevo campo
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
        
        console.log(`✅ Campo agregado: ${nombre} = ${valor}`);
    }
    
    function mostrarCargando() {
        const textoNormal = document.getElementById('btnTextoNormal');
        const textoCargando = document.getElementById('btnTextoCargando');
        
        if (textoNormal) textoNormal.style.display = 'none';
        if (textoCargando) textoCargando.style.display = 'inline-flex';
        btnCrear.disabled = true;
    }
    
    function ocultarCargando() {
        const textoNormal = document.getElementById('btnTextoNormal');
        const textoCargando = document.getElementById('btnTextoCargando');
        
        if (textoNormal) textoNormal.style.display = 'inline-flex';
        if (textoCargando) textoCargando.style.display = 'none';
        btnCrear.disabled = false;
    }
    
    function mostrarDuracion() {
        const preview = document.getElementById('duracionPreview');
        if (preview) preview.style.display = 'block';
    }
    
    function ocultarDuracion() {
        const preview = document.getElementById('duracionPreview');
        if (preview) preview.style.display = 'none';
    }
    
    function mostrarError(mensaje) {
        const errorDiv = document.getElementById('errorFechas');
        const errorTexto = document.getElementById('errorFechasTexto');
        
        if (errorDiv && errorTexto) {
            errorTexto.textContent = mensaje;
            errorDiv.style.display = 'block';
        }
    }
    
    function ocultarError() {
        const errorDiv = document.getElementById('errorFechas');
        if (errorDiv) errorDiv.style.display = 'none';
    }
    
    function mostrarToast(mensaje, tipo) {
        const alertaExistente = document.querySelector('.toast-alert');
        if (alertaExistente) alertaExistente.remove();

        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show toast-alert position-fixed`;
        alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) alerta.remove();
        }, 5000);
    }

    

});