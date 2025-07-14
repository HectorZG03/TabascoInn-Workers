/**
 * ✅ SCRIPT ESPECÍFICO PARA HORAS EXTRA
 * Integra con formato-global.js para validaciones específicas de fechas
 * horas_extra.js
 */

window.HorasExtraJS = {
    
    // =================================
    // 🎯 INICIALIZACIÓN
    // =================================
    
    init() {
        console.log('🚀 Inicializando validaciones específicas de horas extra');
        this.initValidacionesFechas();
        this.initCalculadoras();
        this.initContadores();
        console.log('✅ Horas extra JS inicializado');
    },

    // =================================
    // 📅 VALIDACIONES ESPECÍFICAS DE FECHAS
    // =================================

    initValidacionesFechas() {
        // Validaciones para asignar horas (30 días atrás máximo)
        const camposAsignar = document.querySelectorAll('[id*="fecha_asignar"]');
        camposAsignar.forEach(campo => {
            this.configurarValidacionAsignar(campo);
        });

        // Validaciones para compensar horas (7 días atrás máximo)
        const camposCompensar = document.querySelectorAll('[id*="fecha_restar"]');
        camposCompensar.forEach(campo => {
            this.configurarValidacionCompensar(campo);
        });
    },

    configurarValidacionAsignar(campo) {
        campo.addEventListener('blur', (e) => {
            this.validarFechaAsignacion(e.target);
        });

        // También validar en tiempo real después de completar la fecha
        campo.addEventListener('input', (e) => {
            const valor = e.target.value;
            if (valor.length === 10 && valor.includes('/')) {
                setTimeout(() => this.validarFechaAsignacion(e.target), 100);
            }
        });
    },

    configurarValidacionCompensar(campo) {
        campo.addEventListener('blur', (e) => {
            this.validarFechaCompensacion(e.target);
        });

        // También validar en tiempo real después de completar la fecha
        campo.addEventListener('input', (e) => {
            const valor = e.target.value;
            if (valor.length === 10 && valor.includes('/')) {
                setTimeout(() => this.validarFechaCompensacion(e.target), 100);
            }
        });
    },

    validarFechaAsignacion(campo) {
        const fecha = campo.value.trim();
        
        if (!fecha) {
            this.limpiarValidacion(campo);
            return true;
        }

        // Validar formato usando el sistema global
        if (!window.FormatoGlobal || !window.FormatoGlobal.validarFormatoFecha(fecha)) {
            this.mostrarError(campo, 'Formato inválido. Use DD/MM/YYYY');
            return false;
        }

        const fechaObj = window.FormatoGlobal.convertirFechaADate(fecha);
        if (!fechaObj) {
            this.mostrarError(campo, 'Fecha inválida');
            return false;
        }

        const hoy = new Date();
        const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        const hace30Dias = new Date(fechaHoy.getTime() - (30 * 24 * 60 * 60 * 1000));

        // Validar que no sea futura
        if (fechaObj > fechaHoy) {
            this.mostrarError(campo, 'La fecha no puede ser futura');
            return false;
        }

        // Validar que no sea más de 30 días atrás
        if (fechaObj < hace30Dias) {
            this.mostrarError(campo, 'La fecha no puede ser anterior a 30 días');
            return false;
        }

        this.mostrarExito(campo);
        return true;
    },

    validarFechaCompensacion(campo) {
        const fecha = campo.value.trim();
        
        if (!fecha) {
            this.limpiarValidacion(campo);
            return true;
        }

        // Validar formato usando el sistema global
        if (!window.FormatoGlobal || !window.FormatoGlobal.validarFormatoFecha(fecha)) {
            this.mostrarError(campo, 'Formato inválido. Use DD/MM/YYYY');
            return false;
        }

        const fechaObj = window.FormatoGlobal.convertirFechaADate(fecha);
        if (!fechaObj) {
            this.mostrarError(campo, 'Fecha inválida');
            return false;
        }

        const hoy = new Date();
        const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        const hace7Dias = new Date(fechaHoy.getTime() - (7 * 24 * 60 * 60 * 1000));

        // Validar que no sea futura
        if (fechaObj > fechaHoy) {
            this.mostrarError(campo, 'La fecha no puede ser futura');
            return false;
        }

        // Validar que no sea más de 7 días atrás
        if (fechaObj < hace7Dias) {
            this.mostrarError(campo, 'La fecha no puede ser anterior a 7 días');
            return false;
        }

        this.mostrarExito(campo);
        return true;
    },

    // =================================
    // 🧮 CALCULADORAS DE SALDO
    // =================================

    initCalculadoras() {
        // Calculadoras para asignar horas
        const inputsAsignar = document.querySelectorAll('[id*="horas_asignar"]');
        inputsAsignar.forEach(input => {
            const trabajadorId = this.extraerTrabajadorId(input.id);
            if (trabajadorId) {
                this.configurarCalculadoraAsignar(input, trabajadorId);
            }
        });

        // Calculadoras para compensar horas
        const inputsCompensar = document.querySelectorAll('[id*="horas_restar"]');
        inputsCompensar.forEach(input => {
            const trabajadorId = this.extraerTrabajadorId(input.id);
            if (trabajadorId) {
                this.configurarCalculadoraCompensar(input, trabajadorId);
            }
        });
    },

    configurarCalculadoraAsignar(input, trabajadorId) {
        const spanHorasAAsignar = document.getElementById(`horasAAsignar${trabajadorId}`);
        const spanSaldoFinal = document.getElementById(`saldoFinalAsignar${trabajadorId}`);
        
        if (!spanHorasAAsignar || !spanSaldoFinal) return;

        // Obtener saldo actual del DOM
        const saldoElement = document.querySelector(`[id*="saldoActual"]`);
        let saldoActual = 0;
        
        if (saldoElement) {
            const saldoText = saldoElement.textContent.match(/\d+/);
            saldoActual = saldoText ? parseInt(saldoText[0]) : 0;
        }

        input.addEventListener('input', function() {
            const horasAAsignar = parseInt(this.value) || 0;
            const saldoFinal = saldoActual + horasAAsignar;
            
            spanHorasAAsignar.textContent = horasAAsignar;
            spanSaldoFinal.textContent = saldoFinal;
            
            // Cambiar color según validez
            if (horasAAsignar < 1 || horasAAsignar > 24) {
                spanSaldoFinal.className = 'text-warning';
            } else {
                spanSaldoFinal.className = 'text-primary';
            }
        });
    },

    configurarCalculadoraCompensar(input, trabajadorId) {
        const spanHorasACompensar = document.getElementById(`horasACompensar${trabajadorId}`);
        const spanSaldoResultante = document.getElementById(`saldoResultante${trabajadorId}`);
        
        if (!spanHorasACompensar || !spanSaldoResultante) return;

        // Obtener saldo actual del input max
        const saldoActual = parseInt(input.getAttribute('max')) || 0;

        input.addEventListener('input', function() {
            const horasACompensar = parseInt(this.value) || 0;
            const saldoResultante = Math.max(0, saldoActual - horasACompensar);
            
            spanHorasACompensar.textContent = horasACompensar;
            spanSaldoResultante.textContent = saldoResultante;
            
            // Cambiar color según el resultado
            if (horasACompensar > saldoActual) {
                spanSaldoResultante.className = 'text-danger';
                this.classList.add('is-invalid');
            } else {
                spanSaldoResultante.className = 'text-primary';
                this.classList.remove('is-invalid');
            }
        });

        // Inicializar con valor por defecto
        const valorInicial = parseInt(input.value) || 0;
        if (valorInicial > 0) {
            input.dispatchEvent(new Event('input'));
        }
    },

    // =================================
    // 📝 CONTADORES DE CARACTERES
    // =================================

    initContadores() {
        // Contadores para descripción de asignar
        const textareasAsignar = document.querySelectorAll('[id*="descripcion_asignar"]');
        textareasAsignar.forEach(textarea => {
            const trabajadorId = this.extraerTrabajadorId(textarea.id);
            if (trabajadorId) {
                const contador = document.getElementById(`contadorAsignar${trabajadorId}`);
                if (contador) {
                    this.configurarContador(textarea, contador);
                }
            }
        });

        // Contadores para descripción de compensar
        const textareasCompensar = document.querySelectorAll('[id*="descripcion_restar"]');
        textareasCompensar.forEach(textarea => {
            const trabajadorId = this.extraerTrabajadorId(textarea.id);
            if (trabajadorId) {
                const contador = document.getElementById(`contadorRestar${trabajadorId}`);
                if (contador) {
                    this.configurarContador(textarea, contador);
                }
            }
        });
    },

    configurarContador(textarea, contador) {
        textarea.addEventListener('input', function() {
            const longitud = this.value.length;
            contador.textContent = longitud;
            
            // Cambiar color según proximidad al límite
            if (longitud > 180) {
                contador.className = 'text-danger fw-bold';
            } else if (longitud > 150) {
                contador.className = 'text-warning fw-bold';
            } else {
                contador.className = 'text-muted';
            }
        });
        
        // Inicializar contador
        contador.textContent = textarea.value.length;
    },

    // =================================
    // 🛠️ FUNCIONES DE UTILIDAD
    // =================================

    extraerTrabajadorId(elementId) {
        const match = elementId.match(/\d+/);
        return match ? match[0] : null;
    },

    // =================================
    // 🎨 FUNCIONES DE FEEDBACK VISUAL
    // =================================

    mostrarError(campo, mensaje) {
        // Usar sistema global si está disponible
        if (window.FormatoGlobal && window.FormatoGlobal.mostrarError) {
            window.FormatoGlobal.mostrarError(campo, mensaje);
            return;
        }

        // Fallback manual
        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
        
        let feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            campo.parentNode.appendChild(feedback);
        }
        feedback.textContent = mensaje;
    },

    mostrarExito(campo) {
        // Usar sistema global si está disponible
        if (window.FormatoGlobal && window.FormatoGlobal.mostrarExito) {
            window.FormatoGlobal.mostrarExito(campo);
            return;
        }

        // Fallback manual
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    },

    limpiarValidacion(campo) {
        // Usar sistema global si está disponible
        if (window.FormatoGlobal && window.FormatoGlobal.limpiarValidacion) {
            window.FormatoGlobal.limpiarValidacion(campo);
            return;
        }

        // Fallback manual
        campo.classList.remove('is-valid', 'is-invalid');
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    },

    // =================================
    // 📊 FUNCIONES PÚBLICAS ADICIONALES
    // =================================

    // Validar todo el formulario antes del envío
    validarFormularioAsignar(trabajadorId) {
        const campoFecha = document.getElementById(`fecha_asignar${trabajadorId}`);
        const campoHoras = document.getElementById(`horas_asignar${trabajadorId}`);
        
        let esValido = true;
        
        if (campoFecha && !this.validarFechaAsignacion(campoFecha)) {
            esValido = false;
        }
        
        if (campoHoras) {
            const horas = parseInt(campoHoras.value);
            if (!horas || horas < 1 || horas > 24) {
                this.mostrarError(campoHoras, 'Las horas deben estar entre 1 y 24');
                esValido = false;
            } else {
                this.mostrarExito(campoHoras);
            }
        }
        
        return esValido;
    },

    validarFormularioCompensar(trabajadorId) {
        const campoFecha = document.getElementById(`fecha_restar${trabajadorId}`);
        const campoHoras = document.getElementById(`horas_restar${trabajadorId}`);
        
        let esValido = true;
        
        if (campoFecha && !this.validarFechaCompensacion(campoFecha)) {
            esValido = false;
        }
        
        if (campoHoras) {
            const horas = parseInt(campoHoras.value);
            const maxHoras = parseInt(campoHoras.getAttribute('max'));
            
            if (!horas || horas < 1 || horas > maxHoras) {
                this.mostrarError(campoHoras, `Las horas deben estar entre 1 y ${maxHoras}`);
                esValido = false;
            } else {
                this.mostrarExito(campoHoras);
            }
        }
        
        return esValido;
    }
};

// =================================
// 🚀 AUTO-INICIALIZACIÓN
// =================================

// Inicializar cuando el DOM esté listo y después de que FormatoGlobal esté disponible
function initHorasExtra() {
    if (typeof window.FormatoGlobal !== 'undefined') {
        window.HorasExtraJS.init();
    } else {
        // Reintentar después de un momento
        setTimeout(initHorasExtra, 100);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHorasExtra);
} else {
    initHorasExtra();
}

// Exponer función de validación para uso en formularios
window.validarHorasExtra = {
    asignar: (trabajadorId) => window.HorasExtraJS.validarFormularioAsignar(trabajadorId),
    compensar: (trabajadorId) => window.HorasExtraJS.validarFormularioCompensar(trabajadorId)
};