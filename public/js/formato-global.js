/**
 * ✅ FUNCIONES GLOBALES PARA FORMATO DE FECHAS Y HORAS
 * Sistema reutilizable y simple para campos con formato controlado
 * formato-global.js
 */

window.FormatoGlobal = {
    
    // =================================
    // 🎯 INICIALIZACIÓN
    // =================================
    
    init() {
        console.log('🚀 Inicializando sistema global de formato');
        this.initCamposFecha();
        this.initCamposHora();
        console.log('✅ Sistema global de formato inicializado');
    },

    // =================================
    // 📅 GESTIÓN DE FECHAS
    // =================================

    initCamposFecha() {
        const camposFecha = document.querySelectorAll('.formato-fecha');
        camposFecha.forEach(campo => this.configurarCampoFecha(campo));
    },

    configurarCampoFecha(campo) {
        // Formateo automático
        campo.addEventListener('input', (e) => {
            const valor = e.target.value.replace(/\D/g, '');
            e.target.value = this.formatearFecha(valor);
        });

        // Validación al perder foco
        campo.addEventListener('blur', (e) => {
            this.validarCampoFecha(e.target);
        });

        // Permitir solo números y barras
        campo.addEventListener('keypress', (e) => {
            const char = String.fromCharCode(e.which);
            if (!/[\d/]/.test(char)) {
                e.preventDefault();
            }
        });

        // Manejar paste
        campo.addEventListener('paste', (e) => {
            setTimeout(() => {
                const valor = e.target.value.replace(/\D/g, '');
                e.target.value = this.formatearFecha(valor);
                this.validarCampoFecha(e.target);
            }, 10);
        });
    },

    formatearFecha(valor) {
        let resultado = '';
        
        if (valor.length >= 1) {
            resultado = valor.substring(0, 2);
        }
        if (valor.length >= 3) {
            resultado += '/' + valor.substring(2, 4);
        }
        if (valor.length >= 5) {
            resultado += '/' + valor.substring(4, 8);
        }
        
        return resultado;
    },

    validarCampoFecha(campo) {
        const valor = campo.value.trim();
        
        // Limpiar estados previos
        this.limpiarValidacion(campo);
        
        if (!valor) return true;

        // Validar formato básico
        if (!this.validarFormatoFecha(valor)) {
            this.mostrarError(campo, 'Formato inválido. Use DD/MM/YYYY');
            return false;
        }

        // Validar fecha real
        if (!this.esFechaValida(valor)) {
            this.mostrarError(campo, 'Fecha inválida');
            return false;
        }

        // Validaciones específicas por campo
        const error = this.validarRestriccionesFecha(campo, valor);
        if (error) {
            this.mostrarError(campo, error);
            return false;
        }

        this.mostrarExito(campo);
        return true;
    },

    validarFormatoFecha(fecha) {
        return /^(\d{2})\/(\d{2})\/(\d{4})$/.test(fecha);
    },

    esFechaValida(fecha) {
        if (!this.validarFormatoFecha(fecha)) return false;
        
        const [dia, mes, año] = fecha.split('/').map(Number);
        
        // Validar rangos
        if (dia < 1 || dia > 31 || mes < 1 || mes > 12 || año < 1900 || año > 2100) {
            return false;
        }
        
        // Verificar fecha válida
        return checkdate ? checkdate(mes, dia, año) : true;
    },

    validarRestriccionesFecha(campo, fecha) {
        const fechaObj = this.convertirFechaADate(fecha);
        if (!fechaObj) return 'Fecha inválida';
        
        const hoy = new Date();
        const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        
        // Validaciones específicas por ID
        switch (campo.id) {
            case 'fecha_nacimiento':
                const hace18años = new Date();
                hace18años.setFullYear(hace18años.getFullYear() - 18);
                if (fechaObj > hace18años) {
                    return 'Debe ser mayor de 18 años';
                }
                break;
                
            case 'fecha_ingreso':
                if (fechaObj > fechaHoy) {
                    return 'No puede ser fecha futura';
                }
                break;
                
            case 'fecha_inicio_contrato':
                if (fechaObj < fechaHoy) {
                    return 'No puede ser fecha pasada';
                }
                break;
                
            case 'fecha_fin_contrato':
                const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
                if (fechaInicio && this.validarFormatoFecha(fechaInicio)) {
                    const fechaInicioObj = this.convertirFechaADate(fechaInicio);
                    if (fechaInicioObj && fechaObj <= fechaInicioObj) {
                        return 'Debe ser posterior a la fecha de inicio';
                    }
                }
                break;
        }
        
        return null;
    },

    convertirFechaADate(fecha) {
        if (!this.validarFormatoFecha(fecha)) return null;
        
        const [dia, mes, año] = fecha.split('/').map(Number);
        return new Date(año, mes - 1, dia);
    },

    // =================================
    // 🕐 GESTIÓN DE HORAS
    // =================================

    initCamposHora() {
        const camposHora = document.querySelectorAll('.formato-hora');
        camposHora.forEach(campo => this.configurarCampoHora(campo));
    },

    configurarCampoHora(campo) {
        // Formateo automático
        campo.addEventListener('input', (e) => {
            const valor = e.target.value.replace(/\D/g, '');
            e.target.value = this.formatearHora(valor);
        });

        // Validación al perder foco
        campo.addEventListener('blur', (e) => {
            this.validarCampoHora(e.target);
        });

        // Permitir solo números y dos puntos
        campo.addEventListener('keypress', (e) => {
            const char = String.fromCharCode(e.which);
            if (!/[\d:]/.test(char)) {
                e.preventDefault();
            }
        });

        // Manejar paste
        campo.addEventListener('paste', (e) => {
            setTimeout(() => {
                const valor = e.target.value.replace(/\D/g, '');
                e.target.value = this.formatearHora(valor);
                this.validarCampoHora(e.target);
            }, 10);
        });
    },

    formatearHora(valor) {
        let resultado = '';
        
        if (valor.length >= 1) {
            resultado = valor.substring(0, 2);
        }
        if (valor.length >= 3) {
            resultado += ':' + valor.substring(2, 4);
        }
        
        return resultado;
    },

    validarCampoHora(campo) {
        const valor = campo.value.trim();
        
        // Limpiar estados previos
        this.limpiarValidacion(campo);
        
        if (!valor) return true;

        // Validar formato
        if (!this.validarFormatoHora(valor)) {
            this.mostrarError(campo, 'Formato inválido. Use HH:MM (24h)');
            return false;
        }

        // Validar rangos
        const [horas, minutos] = valor.split(':').map(Number);
        
        if (horas < 0 || horas > 23) {
            this.mostrarError(campo, 'Hora inválida (0-23)');
            return false;
        }
        
        if (minutos < 0 || minutos > 59) {
            this.mostrarError(campo, 'Minutos inválidos (0-59)');
            return false;
        }

        this.mostrarExito(campo);
        return true;
    },

    validarFormatoHora(hora) {
        return /^([01]\d|2[0-3]):([0-5]\d)$/.test(hora);
    },

    // =================================
    // 🛠️ FUNCIONES DE UTILIDAD
    // =================================

    calcularHoras(entrada, salida) {
        if (!this.validarFormatoHora(entrada) || !this.validarFormatoHora(salida)) {
            return 0;
        }
        
        const base = '2024-01-01';
        let e = new Date(`${base}T${entrada}:00`);
        let s = new Date(`${base}T${salida}:00`);
        
        if (s <= e) {
            s.setDate(s.getDate() + 1);
        }
        
        return Math.round((s - e) / 3600000 * 100) / 100;
    },

    calcularEdad(fechaNacimiento) {
        if (!this.validarFormatoFecha(fechaNacimiento)) return null;
        
        const fechaObj = this.convertirFechaADate(fechaNacimiento);
        if (!fechaObj) return null;
        
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaObj.getFullYear();
        const mesActual = hoy.getMonth() - fechaObj.getMonth();
        
        if (mesActual < 0 || (mesActual === 0 && hoy.getDate() < fechaObj.getDate())) {
            edad--;
        }
        
        return edad >= 0 ? edad : null;
    },

    calcularTurno(entrada, salida) {
        if (!this.validarFormatoHora(entrada) || !this.validarFormatoHora(salida)) {
            return 'INVÁLIDO';
        }
        
        const [horaEnt, minEnt] = entrada.split(':').map(Number);
        const [horaSal, minSal] = salida.split(':').map(Number);
        
        const totalMinEnt = horaEnt * 60 + minEnt;
        const totalMinSal = horaSal * 60 + minSal;
        
        // Si cruza medianoche
        if (totalMinSal <= totalMinEnt) return 'NOCTURNO';
        
        // Diurno: 06:00 - 18:00
        if (totalMinEnt >= 360 && totalMinSal <= 1080) return 'DIURNO';
        
        // Nocturno: 18:00 - 06:00
        if (totalMinEnt >= 1080 || totalMinSal <= 360) return 'NOCTURNO';
        
        return 'MIXTO';
    },

    // =================================
    // 🎨 FUNCIONES DE FEEDBACK VISUAL
    // =================================

    mostrarError(campo, mensaje) {
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
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    },

    limpiarValidacion(campo) {
        campo.classList.remove('is-valid', 'is-invalid');
        const feedback = campo.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    },

    // =================================
    // 📦 FUNCIONES PÚBLICAS ADICIONALES
    // =================================

    // Función para validar horarios relacionados
    validarRangoHorario(horaEntrada, horaSalida) {
        if (!horaEntrada.value || !horaSalida.value) return true;
        
        const entrada = horaEntrada.value;
        const salida = horaSalida.value;
        
        if (!this.validarFormatoHora(entrada) || !this.validarFormatoHora(salida)) return false;
        
        const horas = this.calcularHoras(entrada, salida);
        
        if (horas < 1 || horas > 16) {
            this.mostrarError(horaSalida, `Jornada inválida: ${horas}h (debe ser 1-16h)`);
            return false;
        }
        
        this.mostrarExito(horaEntrada);
        this.mostrarExito(horaSalida);
        return true;
    },

    // Función para validar rango de fechas
    validarRangoFechas(fechaInicio, fechaFin) {
        if (!fechaInicio.value || !fechaFin.value) return true;
        
        const inicio = fechaInicio.value;
        const fin = fechaFin.value;
        
        if (!this.validarFormatoFecha(inicio) || !this.validarFormatoFecha(fin)) return false;
        
        const fechaInicioObj = this.convertirFechaADate(inicio);
        const fechaFinObj = this.convertirFechaADate(fin);
        
        if (!fechaInicioObj || !fechaFinObj) return false;
        
        if (fechaFinObj <= fechaInicioObj) {
            this.mostrarError(fechaFin, 'La fecha de fin debe ser posterior al inicio');
            return false;
        }
        
        this.mostrarExito(fechaInicio);
        this.mostrarExito(fechaFin);
        return true;
    }
};

// =================================
// 🚀 AUTO-INICIALIZACIÓN
// =================================

// Inicializar automáticamente cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FormatoGlobal.init());
} else {
    FormatoGlobal.init();
}

// También exponer funciones individuales en el window para fácil acceso
window.formatearFecha = (valor) => FormatoGlobal.formatearFecha(valor);
window.formatearHora = (valor) => FormatoGlobal.formatearHora(valor);
window.validarFormatoFecha = (fecha) => FormatoGlobal.validarFormatoFecha(fecha);
window.validarFormatoHora = (hora) => FormatoGlobal.validarFormatoHora(hora);
window.calcularHoras = (entrada, salida) => FormatoGlobal.calcularHoras(entrada, salida);
window.calcularEdad = (fecha) => FormatoGlobal.calcularEdad(fecha);
window.calcularTurno = (entrada, salida) => FormatoGlobal.calcularTurno(entrada, salida);