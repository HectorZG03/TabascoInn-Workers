{{-- ✅ MODAL PARA COMPENSAR HORAS EXTRA CON DECIMALES Y SIN RESTRICCIONES DE FECHA --}}
<div class="modal fade" id="modalRestarHoras{{ $trabajador->id_trabajador }}" tabindex="-1" aria-labelledby="modalRestarHorasLabel{{ $trabajador->id_trabajador }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('trabajadores.horas-extra.restar', $trabajador) }}">
                @csrf
                
                {{-- ✅ Header del modal --}}
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalRestarHorasLabel{{ $trabajador->id_trabajador }}">
                        <i class="bi bi-dash-circle"></i> Compensar Horas Extra
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                {{-- ✅ Body del modal --}}
                <div class="modal-body">
                    {{-- Información del trabajador --}}
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="bi bi-person-fill"></i> {{ $trabajador->nombre_completo }}
                                </h6>
                                <small class="text-muted">
                                    {{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A' }} - 
                                    {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge {{ $saldoActual > 0 ? 'bg-success' : 'bg-secondary' }} fs-6">
                                    <i class="bi bi-clock"></i> 
                                    <span id="saldoActualRestar{{ $trabajador->id_trabajador }}">
                                        {{ $saldoActual == floor($saldoActual) ? number_format($saldoActual, 0) : number_format($saldoActual, 1) }} 
                                        {{ $saldoActual == 1 ? 'hora' : 'horas' }}
                                    </span>
                                </div>
                                <div class="small text-muted mt-1">Disponibles para compensar</div>
                            </div>
                        </div>
                    </div>

                    {{-- Validación de saldo disponible --}}
                    @if($saldoActual <= 0)
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> No hay horas disponibles</h6>
                            <p class="mb-0">Este trabajador no tiene horas extra acumuladas para compensar.</p>
                        </div>
                    @else
                        {{-- Formulario --}}
                        <div class="row g-3">
                            {{-- ✅ HORAS CON DECIMALES --}}
                            <div class="col-md-6">
                                <label for="horas_restar{{ $trabajador->id_trabajador }}" class="form-label">
                                    <i class="bi bi-clock-fill text-warning"></i> Horas a Compensar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('horas') is-invalid @enderror" 
                                           id="horas_restar{{ $trabajador->id_trabajador }}" 
                                           name="horas" 
                                           value="{{ old('horas', $saldoActual) }}"
                                           min="0.1" 
                                           max="{{ $saldoActual }}" 
                                           step="0.1" 
                                           placeholder="{{ $saldoActual }}"
                                           required>
                                    <span class="input-group-text">horas</span>
                                    @error('horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Mínimo: 0.1 horas (6 min) | Máximo disponible: {{ $saldoActual == floor($saldoActual) ? number_format($saldoActual, 0) : number_format($saldoActual, 1) }} horas
                                </div>
                            </div>

                            {{-- ✅ FECHA SIN RESTRICCIONES --}}
                            <div class="col-md-6">
                                <label for="fecha_restar{{ $trabajador->id_trabajador }}" class="form-label">
                                    <i class="bi bi-calendar3"></i> Fecha de Compensación <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control formato-fecha @error('fecha') is-invalid @enderror" 
                                       id="fecha_restar{{ $trabajador->id_trabajador }}" 
                                       name="fecha" 
                                       value="{{ old('fecha', now()->format('d/m/Y')) }}"
                                       placeholder="DD/MM/YYYY"
                                       maxlength="10"
                                       autocomplete="off"
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Formato: DD/MM/YYYY - <strong>Cualquier fecha válida</strong>
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12">
                                <label for="descripcion_restar{{ $trabajador->id_trabajador }}" class="form-label">
                                    <i class="bi bi-chat-left-text"></i> Descripción de la Compensación
                                </label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion_restar{{ $trabajador->id_trabajador }}" 
                                          name="descripcion" 
                                          rows="3" 
                                          placeholder="Ej: Salida temprana, día libre compensatorio, reducción de horario, etc..."
                                          maxlength="200">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <span id="contadorRestar{{ $trabajador->id_trabajador }}">0</span>/200 caracteres
                                </div>
                            </div>

                            {{-- ✅ CALCULADORA ACTUALIZADA PARA DECIMALES --}}
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-body bg-light">
                                        <h6 class="card-title">
                                            <i class="bi bi-calculator"></i> Resumen de Compensación
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="h5 text-success mb-1">
                                                    {{ $saldoActual == floor($saldoActual) ? number_format($saldoActual, 0) : number_format($saldoActual, 1) }}
                                                </div>
                                                <small class="text-muted">{{ $saldoActual == 1 ? 'Hora Disponible' : 'Horas Disponibles' }}</small>
                                            </div>
                                            <div class="col-1 align-self-center">
                                                <i class="bi bi-dash text-warning"></i>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 text-warning mb-1">
                                                    <span id="horasACompensar{{ $trabajador->id_trabajador }}">
                                                        {{ $saldoActual == floor($saldoActual) ? number_format($saldoActual, 0) : number_format($saldoActual, 1) }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">A Compensar</small>
                                            </div>
                                            <div class="col-1 align-self-center">
                                                <i class="bi bi-equals text-primary"></i>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 text-primary mb-1">
                                                    <span id="saldoResultante{{ $trabajador->id_trabajador }}">0</span>
                                                </div>
                                                <small class="text-muted">Saldo Final</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ INFORMACIÓN ACTUALIZADA --}}
                        <div class="mt-4">
                            <div class="alert alert-light border">
                                <h6 class="mb-2">
                                    <i class="bi bi-lightbulb text-warning"></i> Información Importante
                                </h6>
                                <ul class="mb-0 small">
                                    <li>Las horas compensadas se restarán del saldo acumulado</li>
                                    <li><strong>Acepta decimales:</strong> puede compensar 0.5, 1.25, 2.75 horas, etc.</li>
                                    <li>Esta acción <strong>no se puede deshacer</strong></li>
                                    <li>El registro quedará en el historial laboral</li>
                                    <li><strong>Sin restricciones de fecha:</strong> puede registrar cualquier fecha válida</li>
                                    <li><strong>Formato de fecha:</strong> DD/MM/YYYY (se formatea automáticamente)</li>
                                    <li>Mínimo: 0.1 horas (6 minutos) | Máximo: horas disponibles</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ✅ Footer del modal --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    @if($saldoActual > 0)
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-dash-circle"></i> Compensar Horas
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ Script actualizado para decimales --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('descripcion_restar{{ $trabajador->id_trabajador }}');
    const contador = document.getElementById('contadorRestar{{ $trabajador->id_trabajador }}');
    const inputHoras = document.getElementById('horas_restar{{ $trabajador->id_trabajador }}');
    const spanHorasACompensar = document.getElementById('horasACompensar{{ $trabajador->id_trabajador }}');
    const spanSaldoResultante = document.getElementById('saldoResultante{{ $trabajador->id_trabajador }}');
    const saldoActual = {{ $saldoActual }};
    
    // Contador de caracteres
    if (textarea && contador) {
        textarea.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });
        
        // Inicializar contador
        contador.textContent = textarea.value.length;
    }
    
    // ✅ CALCULADORA ACTUALIZADA PARA DECIMALES
    if (inputHoras && spanHorasACompensar && spanSaldoResultante) {
        inputHoras.addEventListener('input', function() {
            const horasACompensar = parseFloat(this.value) || 0; // ✅ parseFloat en lugar de parseInt
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
            } else if (horasACompensar < 0.1) { // ✅ Validar mínimo
                spanSaldoResultante.className = 'text-warning';
                this.classList.add('is-invalid');
            } else {
                spanSaldoResultante.className = 'text-primary';
                this.classList.remove('is-invalid');
            }
        });
        
        // Inicializar la calculadora con el valor por defecto
        const valorInicial = parseFloat(inputHoras.value) || 0; // ✅ parseFloat
        if (valorInicial > 0) {
            const saldoResultanteInicial = Math.max(0, saldoActual - valorInicial);
            spanHorasACompensar.textContent = valorInicial === Math.floor(valorInicial) ? 
                valorInicial.toString() : valorInicial.toFixed(1);
            spanSaldoResultante.textContent = saldoResultanteInicial === Math.floor(saldoResultanteInicial) ? 
                saldoResultanteInicial.toString() : saldoResultanteInicial.toFixed(1);
        }
    }
    
    // ✅ VALIDACIÓN SIMPLIFICADA DE FECHAS - SOLO FORMATO
    const campoFecha = document.getElementById('fecha_restar{{ $trabajador->id_trabajador }}');
    if (campoFecha) {
        campoFecha.addEventListener('blur', function() {
            const fecha = this.value.trim();
            
            if (!fecha) return;
            
            // Solo validar formato usando el sistema global
            if (window.FormatoGlobal && window.FormatoGlobal.validarFormatoFecha(fecha)) {
                const fechaObj = window.FormatoGlobal.convertirFechaADate(fecha);
                if (fechaObj) {
                    window.FormatoGlobal.mostrarExito(this);
                } else {
                    window.FormatoGlobal.mostrarError(this, 'Fecha inválida');
                }
            } else {
                if (window.FormatoGlobal) {
                    window.FormatoGlobal.mostrarError(this, 'Formato inválido. Use DD/MM/YYYY');
                }
            }
        });
        
        console.log('✅ Validaciones básicas de fecha asignadas (sin restricciones de período)');
    }
});
</script>