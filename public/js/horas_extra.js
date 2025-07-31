/**
 * ✅ SCRIPT ESPECÍFICO PARA HORAS EXTRA - ACTUALIZADO
 * Integra con formato-global.js para validaciones específicas de fechas
 * Soporte para decimales y sin restricciones de fecha
 * horas_extra.js
 */

window.HorasExtraJS = {
    
    // =================================
    // 🎯 INICIALIZACIÓN
    // =================================
    
    init() {
        console.log('🚀 Inicializando validaciones específicas de horas extra (con decimales)');
        this.initValidacionesFechas();
        this.initCalculadoras();
        this.initContadores();
        console.log('✅ Horas extra JS inicializado');
    },

    // =================================
    // 📅 VALIDACIONES BÁSICAS DE FECHAS (SIN RESTRICCIONES DE PERÍODO)
    // =================================

    initValidacionesFechas() {
        // Validaciones básicas para asignar horas (solo formato)
        const camposAsignar = document.querySelectorAll('[id*="fecha_asignar"]');
        camposAsignar.forEach(campo => {
            this.configurarValidacionBasica(campo);
        });

        // Validaciones básicas para compensar horas (solo formato)
        const camposCompensar = document.querySelectorAll('[id*="fecha_restar"]');
        camposCompensar.forEach(campo => {
            this.configurarValidacionBasica(campo);
        });
    },

    configurarValidacionBasica(campo) {
        campo.addEventListener('blur', (e) => {
            this.validarFormatoFecha(e.target);
        });

        // También validar en tiempo real después de completar la fecha
        campo.addEventListener('input', (e) => {
            const valor = e.target.value;
            if (valor.length === 10 && valor.includes('/')) {
                setTimeout(() => this.validarFormatoFecha(e.target), 100);
            }
        });
    },

    // ✅ VALIDACIÓN SIMPLIFICADA: Solo formato, sin restricciones de período
    validarFormatoFecha(campo) {
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

        // ✅ SIN RESTRICCIONES DE PERÍODO - Solo validamos que sea una fecha válida
        this.mostrarExito(campo);
        return true;
    },

    // =================================
    // 🧮 CALCULADORAS DE SALDO ACTUALIZADAS PARA DECIMALES
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
            const saldoText = saldoElement.textContent.match(/[\d.]+/);
            saldoActual = saldoText ? parseFloat(saldoText[0]) : 0;
        }

        input.addEventListener('input', function() {
            const horasAAsignar = parseFloat(this.value) || 0; // ✅ CAMBIO: parseFloat en lugar de parseInt
            const saldoFinal = saldoActual + horasAAsignar;
            
            // ✅ FORMATEAR DECIMALES CORRECTAMENTE
            spanHorasAAsignar.textContent = horasAAsignar === Math.floor(horasAAsignar) ? 
                horasAAsignar.toString() : horasAAsignar.toFixed(1);
            spanSaldoFinal.textContent = saldoFinal === Math.floor(saldoFinal) ? 
                saldoFinal.toString() : saldoFinal.toFixed(1);
            
            // Cambiar color según validez
            if (horasAAsignar < 0.1 || horasAAsignar > 24) {
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
        const saldoActual = parseFloat(input.getAttribute('max')) || 0; // ✅ CAMBIO: parseFloat

        input.addEventListener('input', function() {
            const horasACompensar = parseFloat(this.value) || 0; // ✅ CAMBIO: parseFloat
            const saldoResultante = Math.max(0, saldoActual - horasACompensar);
            
            // ✅ FORMATEAR DECIMALES CORRECTAMENTE
            spanHorasACompensar.textContent = horasACompensar === Math.floor(horasACompensar) ? 
                horasACompensar.toString() : horasACompensar.toFixed(1);
            spanSaldoResultante.textContent = saldoResultante === Math.floor(saldoResultante) ? 
                saldoResultante.toString() : saldoResultante.toFixed(1);
            
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
        const valorInicial = parseFloat(input.value) || 0; // ✅ CAMBIO: parseFloat
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

    // ✅ NUEVA FUNCIÓN: Formatear horas para mostrar
    formatearHoras(horas) {
        const numHoras = parseFloat(horas);
        if (isNaN(numHoras)) return '0';
        
        return numHoras === Math.floor(numHoras) ? 
            numHoras.toString() : 
            numHoras.toFixed(1);
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
    // 📊 FUNCIONES PÚBLICAS ACTUALIZADAS PARA DECIMALES
    // =================================

    // Validar todo el formulario antes del envío
    validarFormularioAsignar(trabajadorId) {
        const campoFecha = document.getElementById(`fecha_asignar${trabajadorId}`);
        const campoHoras = document.getElementById(`horas_asignar${trabajadorId}`);
        
        let esValido = true;
        
        if (campoFecha && !this.validarFormatoFecha(campoFecha)) {
            esValido = false;
        }
        
        if (campoHoras) {
            const horas = parseFloat(campoHoras.value); // ✅ CAMBIO: parseFloat
            if (!horas || horas < 0.1 || horas > 24) { // ✅ CAMBIO: min 0.1
                this.mostrarError(campoHoras, 'Las horas deben estar entre 0.1 y 24');
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
        
        if (campoFecha && !this.validarFormatoFecha(campoFecha)) {
            esValido = false;
        }
        
        if (campoHoras) {
            const horas = parseFloat(campoHoras.value); // ✅ CAMBIO: parseFloat
            const maxHoras = parseFloat(campoHoras.getAttribute('max')); // ✅ CAMBIO: parseFloat
            
            if (!horas || horas < 0.1 || horas > maxHoras) { // ✅ CAMBIO: min 0.1
                this.mostrarError(campoHoras, `Las horas deben estar entre 0.1 y ${this.formatearHoras(maxHoras)}`);
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