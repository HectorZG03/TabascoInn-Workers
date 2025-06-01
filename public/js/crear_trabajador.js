document.addEventListener('DOMContentLoaded', function() {
    // ✅ DETECTAR MENSAJE DE ÉXITO Y LIMPIAR FORMULARIO
    const successAlert = document.getElementById('success-alert');
    const form = document.getElementById('formTrabajador');
    
    if (successAlert) {
        // Limpiar formulario después de éxito
        limpiarFormulario();
        
        // Auto-ocultar alerta después de 5 segundos
        setTimeout(() => {
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
        }, 5000);
        
        // Scroll hacia arriba para ver el mensaje
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Cascada Área -> Categoría
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        
        // Limpiar categorías
        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        categoriaSelect.disabled = true;
        
        if (areaId) {
            fetch(`/api/categorias/${areaId}`)
                .then(response => response.json())
                .then(data => {
                    categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    data.forEach(categoria => {
                        categoriaSelect.innerHTML += `<option value="${categoria.id_categoria}">${categoria.nombre_categoria}</option>`;
                    });
                    categoriaSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                });
        } else {
            categoriaSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
            categoriaSelect.disabled = true;
        }
        
        // Actualizar vista previa cuando cambie área
        actualizarVistaPrevia();
    });

    // Vista previa en tiempo real
    function actualizarVistaPrevia() {
        const nombre = document.getElementById('nombre_trabajador').value;
        const apePaterno = document.getElementById('ape_pat').value;
        const apeMaterno = document.getElementById('ape_mat').value;
        const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
        const sueldo = document.getElementById('sueldo_diarios').value;
        const categoriaText = categoriaSelect.options[categoriaSelect.selectedIndex]?.text || 'Sin categoría';
        const areaText = areaSelect.options[areaSelect.selectedIndex]?.text || 'Sin área';
        
        // Actualizar nombre
        const nombreCompleto = `${nombre} ${apePaterno} ${apeMaterno}`.trim() || 'Nombre del Trabajador';
        document.getElementById('preview-nombre').textContent = nombreCompleto;
        
        // Actualizar categoría y área
        document.getElementById('preview-categoria').textContent = 
            (categoriaText !== 'Seleccionar categoría...' && categoriaText !== 'Sin categoría') ? 
            `${categoriaText} - ${areaText}` : 'Categoría - Área';
        
        // Actualizar sueldo
        document.getElementById('preview-sueldo').textContent = sueldo ? `$${parseFloat(sueldo).toFixed(2)}` : '$0.00';
        
        // Calcular edad
        if (fechaNacimiento) {
            const hoy = new Date();
            const nacimiento = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mesActual = hoy.getMonth();
            const mesNacimiento = nacimiento.getMonth();
            
            if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            
            document.getElementById('preview-edad').textContent = `${edad} años`;
        } else {
            document.getElementById('preview-edad').textContent = '-- años';
        }
    }

    // Event listeners para vista previa
    ['nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', actualizarVistaPrevia);
        }
    });
    
    categoriaSelect.addEventListener('change', actualizarVistaPrevia);

    // Validación CURP y RFC en tiempo real
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
        
        // Limpiar todos los inputs de texto, email, tel, date, number
        const textInputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="number"]');
        textInputs.forEach(input => {
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
        
        // Limpiar archivos
        const fileInputs = form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.value = '';
            input.classList.remove('is-invalid', 'is-valid');
        });
        
        // Resetear fecha de ingreso a hoy
        const fechaIngreso = document.getElementById('fecha_ingreso');
        if (fechaIngreso) {
            fechaIngreso.value = new Date().toISOString().split('T')[0];
        }
        
        // Actualizar vista previa
        actualizarVistaPrevia();
        
        console.log('✅ Formulario limpiado exitosamente');
    }

    // ✅ MEJORAR EXPERIENCIA DE ENVÍO
    const btnGuardar = document.getElementById('btnGuardar');
    
    if (form && btnGuardar) {
        form.addEventListener('submit', function(e) {
            // Deshabilitar botón para evitar doble envío
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            
            // Re-habilitar si hay error (el navegador no redirige)
            setTimeout(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Trabajador';
            }, 3000);
        });
    }

    // ✅ BOTÓN MANUAL PARA LIMPIAR FORMULARIO (opcional)
    const btnLimpiar = document.createElement('button');
    btnLimpiar.type = 'button';
    btnLimpiar.className = 'btn btn-outline-warning me-2';
    btnLimpiar.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Limpiar Formulario';
    btnLimpiar.onclick = limpiarFormulario;
    
    // Insertar botón antes del botón cancelar
    const btnCancelar = document.querySelector('a[href*="trabajadores.crear_trabajador"]');
    if (btnCancelar) {
        btnCancelar.parentNode.insertBefore(btnLimpiar, btnCancelar);
    }
});