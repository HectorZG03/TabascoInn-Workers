{{-- resources/views/trabajadores/modales/crear_contrato.blade.php --}}

<div class="modal fade" id="modalCrearContrato" tabindex="-1" aria-labelledby="modalCrearContratoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCrearContratoLabel">
                    <i class="bi bi-file-earmark-plus"></i>
                    Crear Nuevo Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formCrearContrato" action="{{ route('trabajadores.contratos.crear', $trabajador) }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Información del trabajador --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Trabajador:</strong> {{ $trabajador->nombre_completo }}<br>
                                <strong>Categoría:</strong> {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'No especificada' }}<br>
                                <strong>Área:</strong> {{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'No especificada' }}
                            </div>
                        </div>
                    </div>

                    {{-- Fechas del contrato --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio_contrato" class="form-label">
                                    <i class="bi bi-calendar-event"></i>
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha_inicio_contrato" 
                                       id="fecha_inicio_contrato"
                                       class="form-control"
                                       value="{{ now()->format('Y-m-d') }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       required>
                                <div class="form-text">El contrato iniciará en esta fecha</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin_contrato" class="form-label">
                                    <i class="bi bi-calendar-x"></i>
                                    Fecha de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha_fin_contrato" 
                                       id="fecha_fin_contrato"
                                       class="form-control"
                                       required>
                                <div class="form-text">El contrato terminará en esta fecha</div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ ACTUALIZADO: Tipo de duración AUTOMÁTICO --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock"></i>
                                    Tipo de Duración
                                </label>
                                <div class="form-control bg-light d-flex align-items-center">
                                    <span id="tipo_duracion_texto" class="text-muted">Seleccione las fechas</span>
                                </div>
                                <div class="form-text">Se determina automáticamente (> 30 días = meses)</div>
                                {{-- ✅ Campo oculto para enviar el tipo calculado --}}
                                <input type="hidden" name="tipo_duracion" id="tipo_duracion_hidden">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-calculator"></i>
                                    Duración Calculada
                                </label>
                                <div class="form-control bg-light d-flex align-items-center">
                                    <span id="duracion_calculada" class="text-muted">Seleccione las fechas</span>
                                </div>
                                <div class="form-text">Se calcula automáticamente según las fechas</div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ NUEVO: Resumen del Contrato --}}
                    <div id="resumen_contrato" class="row mb-3" style="display: none;">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-check"></i> Resumen del Contrato
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Inicio:</small>
                                            <div class="fw-bold" id="resumen_inicio">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Fin:</small>
                                            <div class="fw-bold" id="resumen_fin">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Duración:</small>
                                            <div class="fw-bold text-success" id="resumen_duracion">-</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Tipo:</small>
                                            <div class="fw-bold text-primary" id="resumen_tipo">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vista previa de información --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-eye"></i>
                                        Vista Previa del Contrato
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Sueldo Diario:</small>
                                            <div class="fw-bold text-success">
                                                ${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Horario:</small>
                                            <div class="fw-bold">
                                                {{ $trabajador->fichaTecnica->hora_entrada ?? '08:00' }} - 
                                                {{ $trabajador->fichaTecnica->hora_salida ?? '17:00' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="text-muted">Turno:</small>
                                            <div class="fw-bold">
                                                {{ ucfirst($trabajador->fichaTecnica->turno ?? 'Por definir') }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Horas Semanales:</small>
                                            <div class="fw-bold">
                                                {{ $trabajador->fichaTecnica->horas_semanales ?? 'Por calcular' }}h
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ NUEVO: Observaciones --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">
                                    <i class="bi bi-chat-text"></i>
                                    Observaciones (Opcional)
                                </label>
                                <textarea name="observaciones" 
                                          id="observaciones"
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Detalles adicionales sobre el contrato"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnCrearContrato">
                        <i class="bi bi-file-earmark-plus"></i>
                        Crear Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT ACTUALIZADO CON LÓGICA AUTOMÁTICA --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalCrearContrato = document.getElementById('modalCrearContrato');
    
    if (modalCrearContrato) {
        const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        const tipoDuracionTexto = document.getElementById('tipo_duracion_texto');
        const tipoDuracionHidden = document.getElementById('tipo_duracion_hidden');
        const duracionCalculadaSpan = document.getElementById('duracion_calculada');
        const formCrearContrato = document.getElementById('formCrearContrato');
        const btnCrearContrato = document.getElementById('btnCrearContrato');
        
        // ✅ Elementos del resumen
        const resumenContrato = document.getElementById('resumen_contrato');
        const resumenInicio = document.getElementById('resumen_inicio');
        const resumenFin = document.getElementById('resumen_fin');
        const resumenDuracion = document.getElementById('resumen_duracion');
        const resumenTipo = document.getElementById('resumen_tipo');

        // ✅ Calcular fecha fin por defecto (6 meses después del inicio)
        function calcularFechaFinPorDefecto() {
            const fechaInicio = new Date(fechaInicioInput.value);
            if (fechaInicio && !fechaFinInput.value) {
                const fechaFin = new Date(fechaInicio);
                fechaFin.setMonth(fechaFin.getMonth() + 6);
                
                const formattedDate = fechaFin.toISOString().split('T')[0];
                fechaFinInput.value = formattedDate;
            }
            
            // Actualizar fecha mínima del fin
            if (fechaInicio) {
                fechaFinInput.min = fechaInicioInput.value;
            }
            
            calcularDuracionAutomatica();
        }

        // ✅ NUEVA FUNCIÓN: Calcular duración automática (misma lógica que creación de trabajadores)
        function calcularDuracionAutomatica() {
            const fechaInicio = fechaInicioInput.value;
            const fechaFin = fechaFinInput.value;
            
            if (!fechaInicio || !fechaFin) {
                tipoDuracionTexto.textContent = 'Seleccione las fechas';
                tipoDuracionTexto.className = 'text-muted';
                duracionCalculadaSpan.textContent = 'Seleccione las fechas';
                duracionCalculadaSpan.className = 'text-muted';
                tipoDuracionHidden.value = '';
                ocultarResumen();
                return;
            }
            
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            if (fin <= inicio) {
                tipoDuracionTexto.textContent = 'Fecha fin debe ser posterior al inicio';
                tipoDuracionTexto.className = 'text-danger';
                duracionCalculadaSpan.textContent = 'Fechas inválidas';
                duracionCalculadaSpan.className = 'text-danger';
                tipoDuracionHidden.value = '';
                ocultarResumen();
                return;
            }

            // ✅ LÓGICA AUTOMÁTICA: > 30 días = meses, <= 30 días = días
            const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
            
            let tipoDuracion, duracionMostrar, tipoTexto;
            
            if (diasTotales > 30) {
                tipoDuracion = 'meses';
                tipoTexto = 'Por meses';
                
                // Calcular meses exactos
                let meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
                
                if (fin.getDate() < inicio.getDate()) {
                    meses--;
                }
                
                if (meses <= 0 && fin > inicio) {
                    meses = 1;
                }
                
                duracionMostrar = `${meses} ${meses === 1 ? 'mes' : 'meses'} (${diasTotales} días)`;
            } else {
                tipoDuracion = 'dias';
                tipoTexto = 'Por días';
                duracionMostrar = `${diasTotales} ${diasTotales === 1 ? 'día' : 'días'}`;
            }
            
            // ✅ Actualizar interfaz
            tipoDuracionTexto.textContent = tipoTexto;
            tipoDuracionTexto.className = 'text-success fw-bold';
            duracionCalculadaSpan.textContent = duracionMostrar;
            duracionCalculadaSpan.className = 'text-success fw-bold';
            tipoDuracionHidden.value = tipoDuracion;
            
            // ✅ Mostrar resumen
            mostrarResumen(fechaInicio, fechaFin, duracionMostrar, tipoTexto);
        }

        // ✅ NUEVA FUNCIÓN: Mostrar resumen
        function mostrarResumen(fechaInicio, fechaFin, duracion, tipo) {
            if (resumenContrato) {
                resumenInicio.textContent = formatearFecha(fechaInicio);
                resumenFin.textContent = formatearFecha(fechaFin);
                resumenDuracion.textContent = duracion;
                resumenTipo.textContent = tipo;
                resumenContrato.style.display = 'block';
            }
        }

        // ✅ NUEVA FUNCIÓN: Ocultar resumen
        function ocultarResumen() {
            if (resumenContrato) {
                resumenContrato.style.display = 'none';
            }
        }

        // ✅ NUEVA FUNCIÓN: Formatear fecha
        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        // ✅ Event listeners actualizados
        fechaInicioInput.addEventListener('change', calcularFechaFinPorDefecto);
        fechaFinInput.addEventListener('change', calcularDuracionAutomatica);

        // ✅ Validación del formulario actualizada
        formCrearContrato.addEventListener('submit', function(e) {
            const fechaInicio = new Date(fechaInicioInput.value);
            const fechaFin = new Date(fechaFinInput.value);
            const tipoDuracion = tipoDuracionHidden.value;
            
            if (!tipoDuracion) {
                e.preventDefault();
                alert('Por favor, seleccione fechas válidas para calcular la duración');
                return false;
            }
            
            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }

            const diferenciaDias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24);
            if (diferenciaDias < 1) {
                e.preventDefault();
                alert('El contrato debe tener al menos 1 día de duración');
                return false;
            }

            // Deshabilitar botón para evitar doble envío
            btnCrearContrato.disabled = true;
            btnCrearContrato.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando...';
            
            // Re-habilitar después de 5 segundos si no se redirige
            setTimeout(() => {
                btnCrearContrato.disabled = false;
                btnCrearContrato.innerHTML = '<i class="bi bi-file-earmark-plus"></i> Crear Contrato';
            }, 5000);
        });

        // ✅ Inicializar al abrir el modal
        modalCrearContrato.addEventListener('show.bs.modal', function() {
            // Calcular fecha fin por defecto si está vacía
            if (!fechaFinInput.value) {
                calcularFechaFinPorDefecto();
            } else {
                // Si ya hay fechas, recalcular
                calcularDuracionAutomatica();
            }
            
            // Enfocar primer campo
            setTimeout(() => {
                fechaInicioInput.focus();
            }, 500);
        });

        // ✅ Limpiar formulario al cerrar
        modalCrearContrato.addEventListener('hidden.bs.modal', function() {
            formCrearContrato.reset();
            tipoDuracionTexto.textContent = 'Seleccione las fechas';
            tipoDuracionTexto.className = 'text-muted';
            duracionCalculadaSpan.textContent = 'Seleccione las fechas';
            duracionCalculadaSpan.className = 'text-muted';
            tipoDuracionHidden.value = '';
            ocultarResumen();
            btnCrearContrato.disabled = false;
            btnCrearContrato.innerHTML = '<i class="bi bi-file-earmark-plus"></i> Crear Contrato';
            
            // Restaurar fecha de inicio a hoy
            fechaInicioInput.value = new Date().toISOString().split('T')[0];
            fechaFinInput.value = '';
        });

        console.log('✅ Modal Crear Contrato - Scripts con lógica automática inicializados');
    }
});
</script>