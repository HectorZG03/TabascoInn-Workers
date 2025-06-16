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

                    {{-- Tipo de duración --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_duracion" class="form-label">
                                    <i class="bi bi-clock"></i>
                                    Tipo de Duración <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_duracion" id="tipo_duracion" class="form-select" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="dias">Días</option>
                                    <option value="meses" selected>Meses</option>
                                </select>
                                <div class="form-text">Cómo se medirá la duración del contrato</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-calculator"></i>
                                    Duración Calculada
                                </label>
                                <div class="form-control-plaintext">
                                    <span id="duracion_calculada" class="fw-bold text-primary">-</span>
                                </div>
                                <div class="form-text">Se calcula automáticamente según las fechas</div>
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

{{-- JavaScript específico del modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalCrearContrato = document.getElementById('modalCrearContrato');
    
    if (modalCrearContrato) {
        const fechaInicioInput = document.getElementById('fecha_inicio_contrato');
        const fechaFinInput = document.getElementById('fecha_fin_contrato');
        const tipoDuracionSelect = document.getElementById('tipo_duracion');
        const duracionCalculadaSpan = document.getElementById('duracion_calculada');
        const formCrearContrato = document.getElementById('formCrearContrato');
        const btnCrearContrato = document.getElementById('btnCrearContrato');

        // ✅ Calcular fecha fin por defecto (6 meses después del inicio)
        function calcularFechaFinPorDefecto() {
            const fechaInicio = new Date(fechaInicioInput.value);
            if (fechaInicio) {
                const fechaFin = new Date(fechaInicio);
                fechaFin.setMonth(fechaFin.getMonth() + 6);
                
                const formattedDate = fechaFin.toISOString().split('T')[0];
                fechaFinInput.value = formattedDate;
                fechaFinInput.min = fechaInicioInput.value;
                
                calcularDuracion();
            }
        }

        // ✅ Calcular duración basada en las fechas y tipo
        function calcularDuracion() {
            const fechaInicio = fechaInicioInput.value;
            const fechaFin = fechaFinInput.value;
            const tipo = tipoDuracionSelect.value;
            
            if (fechaInicio && fechaFin && tipo) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);
                
                if (fin <= inicio) {
                    duracionCalculadaSpan.textContent = 'Fecha fin debe ser posterior al inicio';
                    duracionCalculadaSpan.className = 'fw-bold text-danger';
                    return;
                }
                
                let duracion;
                let texto;
                
                if (tipo === 'dias') {
                    duracion = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
                    texto = duracion + ' ' + (duracion === 1 ? 'día' : 'días');
                } else { // meses
                    const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + 
                                 (fin.getMonth() - inicio.getMonth());
                    duracion = meses > 0 ? meses : 1;
                    texto = duracion + ' ' + (duracion === 1 ? 'mes' : 'meses');
                }
                
                duracionCalculadaSpan.textContent = texto;
                duracionCalculadaSpan.className = 'fw-bold text-primary';
            } else {
                duracionCalculadaSpan.textContent = '-';
                duracionCalculadaSpan.className = 'fw-bold text-muted';
            }
        }

        // ✅ Event listeners
        fechaInicioInput.addEventListener('change', function() {
            calcularFechaFinPorDefecto();
        });

        fechaFinInput.addEventListener('change', calcularDuracion);
        tipoDuracionSelect.addEventListener('change', calcularDuracion);

        // ✅ Validación del formulario
        formCrearContrato.addEventListener('submit', function(e) {
            const fechaInicio = new Date(fechaInicioInput.value);
            const fechaFin = new Date(fechaFinInput.value);
            
            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
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
            }
            
            // Enfocar primer campo
            setTimeout(() => {
                fechaInicioInput.focus();
            }, 500);
        });

        // ✅ Limpiar formulario al cerrar
        modalCrearContrato.addEventListener('hidden.bs.modal', function() {
            formCrearContrato.reset();
            duracionCalculadaSpan.textContent = '-';
            duracionCalculadaSpan.className = 'fw-bold text-muted';
            btnCrearContrato.disabled = false;
            btnCrearContrato.innerHTML = '<i class="bi bi-file-earmark-plus"></i> Crear Contrato';
        });

        console.log('✅ Modal Crear Contrato - Scripts inicializados');
    }
});
</script>