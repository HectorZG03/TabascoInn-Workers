// ========================================
// 📝 VALIDACIONES DE CAMPOS - ACTUALIZADO CON CONTACTO DE EMERGENCIA
// ========================================

window.initValidacionesCampos = function() {
    
    // Configuración de campos
    const fieldConfigs = [
        { id: 'curp', maxLength: 18, transform: 'upper' },
        { id: 'rfc', maxLength: 13, transform: 'upper' },
        { id: 'telefono', maxLength: 10, transform: 'numeric' },
        { id: 'no_nss', maxLength: 11, transform: 'numeric' },
        // ✅ NUEVOS CAMPOS DE CONTACTO
        { id: 'contacto_telefono_principal', maxLength: 10, transform: 'numeric' },
        { id: 'contacto_telefono_secundario', maxLength: 10, transform: 'numeric' }
    ];

    // Aplicar validaciones
    fieldConfigs.forEach(config => {
        const input = document.getElementById(config.id);
        if (!input) return;
        
        input.addEventListener('input', function() {
            let value = this.value;
            
            switch (config.transform) {
                case 'upper':
                    value = value.toUpperCase();
                    break;
                case 'numeric':
                    value = value.replace(/\D/g, '');
                    break;
            }
            
            this.value = value.substring(0, config.maxLength);
        });
    });

    // ✅ VALIDACIÓN ESPECIAL PARA CONTACTO DE EMERGENCIA
    initContactoEmergenciaValidation();

    // Validación general de formularios (loading en botones)
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.dataset.noLoading) {
                window.PerfilUtils.showLoading(submitBtn);
                setTimeout(() => window.PerfilUtils.hideLoading(submitBtn), 3000);
            }
        });
    });
    
    console.log('📝 Validaciones de campos inicializadas (incluye contacto emergencia)');
};

// ✅ NUEVA FUNCIÓN: Validación inteligente del contacto de emergencia
function initContactoEmergenciaValidation() {
    const nombreContacto = document.getElementById('contacto_nombre_completo');
    const parentesco = document.getElementById('contacto_parentesco');
    const telefonoPrincipal = document.getElementById('contacto_telefono_principal');
    const telefonoSecundario = document.getElementById('contacto_telefono_secundario');
    const direccionContacto = document.getElementById('contacto_direccion');
    
    if (!nombreContacto) return;
    
    // Función para verificar si algún campo tiene datos
    const tieneAlgunDato = () => {
        return nombreContacto.value.trim() || 
               parentesco.value.trim() || 
               telefonoPrincipal.value.trim() ||
               telefonoSecundario.value.trim() ||
               direccionContacto.value.trim();
    };
    
    // Función para actualizar el estado de los campos requeridos
    const actualizarRequeridos = () => {
        const hayDatos = tieneAlgunDato();
        
        if (hayDatos) {
            // Si hay algún dato, nombre, parentesco y teléfono principal son obligatorios
            nombreContacto.setAttribute('required', 'required');
            parentesco.setAttribute('required', 'required');
            telefonoPrincipal.setAttribute('required', 'required');
            
            // Agregar indicador visual
            const labels = [
                document.querySelector('label[for="contacto_nombre_completo"]'),
                document.querySelector('label[for="contacto_parentesco"]'),
                document.querySelector('label[for="contacto_telefono_principal"]')
            ];
            
            labels.forEach(label => {
                if (label && !label.innerHTML.includes('*')) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }
            });
        } else {
            // Si no hay datos, quitar los requeridos
            nombreContacto.removeAttribute('required');
            parentesco.removeAttribute('required');
            telefonoPrincipal.removeAttribute('required');
            
            // Quitar indicadores visuales
            const labels = [
                document.querySelector('label[for="contacto_nombre_completo"]'),
                document.querySelector('label[for="contacto_parentesco"]'),
                document.querySelector('label[for="contacto_telefono_principal"]')
            ];
            
            labels.forEach(label => {
                if (label) {
                    label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }
            });
        }
    };
    
    // Escuchar cambios en todos los campos del contacto
    [nombreContacto, parentesco, telefonoPrincipal, telefonoSecundario, direccionContacto].forEach(campo => {
        if (campo) {
            campo.addEventListener('input', actualizarRequeridos);
            campo.addEventListener('blur', actualizarRequeridos);
        }
    });
    
    // Validación específica para teléfonos
    [telefonoPrincipal, telefonoSecundario].forEach(telefono => {
        if (telefono) {
            telefono.addEventListener('input', function() {
                // Solo permitir números
                this.value = this.value.replace(/\D/g, '');
                
                // Validar longitud
                if (this.value.length === 10) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value.length > 0) {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                    
                    // Agregar mensaje de error si no existe
                    let feedback = this.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'El teléfono debe tener exactamente 10 dígitos';
                        this.parentNode.appendChild(feedback);
                    }
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
        }
    });
    
    // Validación del formulario antes de enviar
    const formContacto = document.querySelector('form[action*="contacto-emergencia"]');
    if (formContacto) {
        formContacto.addEventListener('submit', function(e) {
            const hayDatos = tieneAlgunDato();
            
            if (hayDatos) {
                // Validar que los campos requeridos estén completos
                if (!nombreContacto.value.trim()) {
                    e.preventDefault();
                    nombreContacto.classList.add('is-invalid');
                    mostrarError(nombreContacto, 'El nombre del contacto es obligatorio');
                    return false;
                }
                
                if (!parentesco.value.trim()) {
                    e.preventDefault();
                    parentesco.classList.add('is-invalid');
                    mostrarError(parentesco, 'El parentesco es obligatorio');
                    return false;
                }
                
                if (!telefonoPrincipal.value.trim()) {
                    e.preventDefault();
                    telefonoPrincipal.classList.add('is-invalid');
                    mostrarError(telefonoPrincipal, 'El teléfono principal es obligatorio');
                    return false;
                }
                
                if (telefonoPrincipal.value.length !== 10) {
                    e.preventDefault();
                    telefonoPrincipal.classList.add('is-invalid');
                    mostrarError(telefonoPrincipal, 'El teléfono debe tener exactamente 10 dígitos');
                    return false;
                }
                
                if (telefonoSecundario.value && telefonoSecundario.value.length !== 10) {
                    e.preventDefault();
                    telefonoSecundario.classList.add('is-invalid');
                    mostrarError(telefonoSecundario, 'El teléfono debe tener exactamente 10 dígitos');
                    return false;
                }
            }
        });
    }
    
    // Función auxiliar para mostrar errores
    function mostrarError(campo, mensaje) {
        let feedback = campo.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            campo.parentNode.appendChild(feedback);
        }
        feedback.textContent = mensaje;
        campo.focus();
    }
    
    // Ejecutar al cargar para establecer el estado inicial
    actualizarRequeridos();
    
    console.log('✅ Validación de contacto de emergencia inicializada');
}