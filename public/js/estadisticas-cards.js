/**
 * ✅ GESTIÓN DE TARJETAS ESTADÍSTICAS
 * Maneja la actualización automática y interacciones de las tarjetas de estadísticas
 */

class EstadisticasCards {
    constructor() {
        this.intervalos = new Map();
        this.configuracion = {
            intervaloActualizacion: 30000, // 30 segundos
            animacionDuracion: 600,
            habilitarActualizacionAutomatica: true
        };
        
        this.init();
    }

    /**
     * Inicializar el sistema de estadísticas
     */
    init() {
        console.log('🔄 Inicializando sistema de estadísticas...');
        
        // Detectar tipos de estadísticas presentes en la página
        const tiposPresentes = this.detectarTiposEstadisticas();
        
        if (tiposPresentes.length === 0) {
            console.log('ℹ️ No se encontraron tarjetas de estadísticas en esta página');
            return;
        }

        // Configurar actualización automática para cada tipo
        tiposPresentes.forEach(tipo => {
            if (this.configuracion.habilitarActualizacionAutomatica) {
                this.configurarActualizacionAutomatica(tipo);
            }
        });

        // Configurar eventos de interacción
        this.configurarEventosInteraccion();
        
        console.log(`✅ Sistema de estadísticas inicializado para: ${tiposPresentes.join(', ')}`);
    }

    /**
     * Detectar qué tipos de estadísticas están presentes en la página
     */
    detectarTiposEstadisticas() {
        const elementos = document.querySelectorAll('[id^="estadisticas-"]');
        const tipos = [];
        
        elementos.forEach(elemento => {
            const tipo = elemento.id.replace('estadisticas-', '');
            tipos.push(tipo);
        });
        
        return tipos;
    }

    /**
     * Configurar actualización automática para un tipo específico
     */
    configurarActualizacionAutomatica(tipo) {
        // Limpiar intervalo existente si existe
        if (this.intervalos.has(tipo)) {
            clearInterval(this.intervalos.get(tipo));
        }

        // Configurar nuevo intervalo
        const intervalo = setInterval(() => {
            this.actualizarEstadisticas(tipo);
        }, this.configuracion.intervaloActualizacion);

        this.intervalos.set(tipo, intervalo);
        
        console.log(`🔄 Actualización automática configurada para ${tipo} cada ${this.configuracion.intervaloActualizacion/1000}s`);
    }

    /**
     * Actualizar estadísticas de un tipo específico
     */
    async actualizarEstadisticas(tipo) {
        try {
            console.log(`🔄 Actualizando estadísticas de ${tipo}...`);
            
            // Mostrar indicadores de carga
            this.mostrarIndicadoresCarga(tipo, true);
            
            // Realizar petición al servidor
            const response = await fetch(`/api/estadisticas?tipo=${tipo}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            const nuevasEstadisticas = await response.json();

            // Verificar si hay errores en la respuesta
            if (nuevasEstadisticas.error) {
                throw new Error(nuevasEstadisticas.error);
            }

            // Actualizar valores en las tarjetas
            this.actualizarValoresTarjetas(tipo, nuevasEstadisticas);
            
            console.log(`✅ Estadísticas de ${tipo} actualizadas correctamente`);

        } catch (error) {
            console.error(`❌ Error actualizando estadísticas de ${tipo}:`, error);
            
            // Mostrar notificación de error (opcional)
            this.mostrarNotificacionError(tipo, error.message);
            
        } finally {
            // Ocultar indicadores de carga
            this.mostrarIndicadoresCarga(tipo, false);
        }
    }

    /**
     * Actualizar valores en las tarjetas
     */
    actualizarValoresTarjetas(tipo, nuevasEstadisticas) {
        const contenedorEstadisticas = document.getElementById(`estadisticas-${tipo}`);
        
        if (!contenedorEstadisticas) {
            console.warn(`⚠️ No se encontró contenedor de estadísticas para ${tipo}`);
            return;
        }

        const tarjetas = contenedorEstadisticas.querySelectorAll('.estadistica-card');
        let cambiosDetectados = 0;

        tarjetas.forEach(tarjeta => {
            const clave = tarjeta.dataset.clave;
            const valorActual = parseInt(tarjeta.dataset.valor);
            const nuevoValor = this.obtenerValorAnidado(nuevasEstadisticas, clave);

            if (valorActual !== nuevoValor) {
                this.actualizarTarjeta(tarjeta, nuevoValor);
                cambiosDetectados++;
            }
        });

        if (cambiosDetectados > 0) {
            console.log(`🔄 ${cambiosDetectados} tarjeta(s) de ${tipo} actualizadas`);
        }
    }

    /**
     * Actualizar una tarjeta individual
     */
    actualizarTarjeta(tarjeta, nuevoValor) {
        const elementoValor = tarjeta.querySelector('.estadistica-valor');
        
        if (!elementoValor) return;

        // Actualizar valor mostrado
        elementoValor.textContent = nuevoValor;
        
        // Actualizar atributo data
        tarjeta.dataset.valor = nuevoValor;
        
        // Aplicar animación de actualización
        tarjeta.classList.add('estadistica-actualizada');
        setTimeout(() => {
            tarjeta.classList.remove('estadistica-actualizada');
        }, this.configuracion.animacionDuracion);
    }

    /**
     * Obtener valor anidado de un objeto (ej: "por_estado.inactivo")
     */
    obtenerValorAnidado(objeto, clave) {
        const partes = clave.split('.');
        let valor = objeto;
        
        for (const parte of partes) {
            if (valor && typeof valor === 'object' && parte in valor) {
                valor = valor[parte];
            } else {
                return 0;
            }
        }
        
        return typeof valor === 'number' ? valor : 0;
    }

    /**
     * Mostrar/ocultar indicadores de carga
     */
    mostrarIndicadoresCarga(tipo, mostrar = true) {
        const contenedor = document.getElementById(`estadisticas-${tipo}`);
        
        if (!contenedor) return;

        const indicadores = contenedor.querySelectorAll('.estadistica-loading');
        
        indicadores.forEach(indicador => {
            if (mostrar) {
                indicador.classList.remove('d-none');
            } else {
                indicador.classList.add('d-none');
            }
        });
    }

    /**
     * Mostrar notificación de error
     */
    mostrarNotificacionError(tipo, mensaje) {
        // Implementación básica con console, se puede expandir con toasts/alertas
        console.error(`❌ Error en estadísticas de ${tipo}: ${mensaje}`);
        
        // Opcional: Mostrar un pequeño indicador de error en las tarjetas
        const contenedor = document.getElementById(`estadisticas-${tipo}`);
        if (contenedor) {
            contenedor.style.opacity = '0.7';
            setTimeout(() => {
                contenedor.style.opacity = '1';
            }, 2000);
        }
    }

    /**
     * Configurar eventos de interacción con las tarjetas
     */
    configurarEventosInteraccion() {
        // Click manual para actualizar
        document.addEventListener('click', (e) => {
            const tarjeta = e.target.closest('.estadistica-card');
            
            if (tarjeta && e.shiftKey) { // Shift + Click para actualizar manualmente
                const tipo = tarjeta.dataset.tipo;
                if (tipo) {
                    this.actualizarEstadisticas(tipo);
                }
            }
        });

        // Pausar actualización automática cuando la página no está visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pausarActualizacionAutomatica();
            } else {
                this.reanudarActualizacionAutomatica();
            }
        });
    }

    /**
     * Pausar actualización automática
     */
    pausarActualizacionAutomatica() {
        this.intervalos.forEach((intervalo, tipo) => {
            clearInterval(intervalo);
            console.log(`⏸️ Actualización automática pausada para ${tipo}`);
        });
    }

    /**
     * Reanudar actualización automática
     */
    reanudarActualizacionAutomatica() {
        const tiposPresentes = this.detectarTiposEstadisticas();
        
        tiposPresentes.forEach(tipo => {
            if (this.configuracion.habilitarActualizacionAutomatica) {
                this.configurarActualizacionAutomatica(tipo);
            }
        });
        
        console.log(`▶️ Actualización automática reanudada`);
    }

    /**
     * Destruir instancia y limpiar recursos
     */
    destruir() {
        this.intervalos.forEach((intervalo) => {
            clearInterval(intervalo);
        });
        
        this.intervalos.clear();
        console.log('🗑️ Sistema de estadísticas destruido');
    }
}

// ✅ INICIALIZACIÓN AUTOMÁTICA
document.addEventListener('DOMContentLoaded', function() {
    // Crear instancia global del sistema de estadísticas
    window.estadisticasCards = new EstadisticasCards();
    
    console.log('🎯 EstadisticasCards inicializado correctamente');
});

// ✅ LIMPIAR RECURSOS AL SALIR DE LA PÁGINA
window.addEventListener('beforeunload', function() {
    if (window.estadisticasCards) {
        window.estadisticasCards.destruir();
    }
});