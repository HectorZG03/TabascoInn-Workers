{{-- ✅ MODAL PARA COMPENSAR HORAS EXTRA --}}
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
                                        {{ $saldoActual }} {{ $saldoActual == 1 ? 'hora' : 'horas' }}
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
                            {{-- Horas a compensar --}}
                            <div class="col-md-6">
                                <label for="horas_restar{{ $trabajador->id_trabajador }}" class="form-label">
                                    <i class="bi bi-clock-fill text-warning"></i> Horas a Compensar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('horas') is-invalid @enderror" 
                                           id="horas_restar{{ $trabajador->id_trabajador }}" 
                                           name="horas" 
                                           value="{{ old('horas') }}"
                                           min="1" 
                                           max="{{ $saldoActual }}" 
                                           step="1" 
                                           placeholder="1"
                                           required>
                                    <span class="input-group-text">{{ old('horas') == 1 ? 'hora' : 'horas' }}</span>
                                    @error('horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Mínimo: 1 hora | Máximo disponible: {{ $saldoActual }} {{ $saldoActual == 1 ? 'hora' : 'horas' }}
                                </div>
                            </div>

                            {{-- Fecha de compensación --}}
                            <div class="col-md-6">
                                <label for="fecha_restar{{ $trabajador->id_trabajador }}" class="form-label">
                                    <i class="bi bi-calendar3"></i> Fecha de Compensación <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha_restar{{ $trabajador->id_trabajador }}" 
                                       name="fecha" 
                                       value="{{ old('fecha', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}"
                                       min="{{ now()->subDays(7)->format('Y-m-d') }}"
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    Máximo 7 días atrás
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

                            {{-- Calculadora de saldo resultante --}}
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-body bg-light">
                                        <h6 class="card-title">
                                            <i class="bi bi-calculator"></i> Resumen de Compensación
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="h5 text-success mb-1">{{ $saldoActual }}</div>
                                                <small class="text-muted">{{ $saldoActual == 1 ? 'Hora Disponible' : 'Horas Disponibles' }}</small>
                                            </div>
                                            <div class="col-1 align-self-center">
                                                <i class="bi bi-dash text-warning"></i>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 text-warning mb-1">
                                                    <span id="horasACompensar{{ $trabajador->id_trabajador }}">0</span>
                                                </div>
                                                <small class="text-muted">A Compensar</small>
                                            </div>
                                            <div class="col-1 align-self-center">
                                                <i class="bi bi-equals text-primary"></i>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 text-primary mb-1">
                                                    <span id="saldoResultante{{ $trabajador->id_trabajador }}">{{ $saldoActual }}</span>
                                                </div>
                                                <small class="text-muted">Saldo Final</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información adicional --}}
                        <div class="mt-4">
                            <div class="alert alert-light border">
                                <h6 class="mb-2">
                                    <i class="bi bi-lightbulb text-warning"></i> Información Importante
                                </h6>
                                <ul class="mb-0 small">
                                    <li>Las horas compensadas se restarán del saldo acumulado</li>
                                    <li>Solo se compensan <strong>horas completas</strong> (sin fracciones)</li>
                                    <li>Esta acción <strong>no se puede deshacer</strong></li>
                                    <li>El registro quedará en el historial laboral</li>
                                    <li>Solo se pueden compensar horas de los últimos 7 días</li>
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

{{-- ✅ Script para contador de caracteres y calculadora --}}
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
    
    // Calculadora de saldo resultante
    if (inputHoras && spanHorasACompensar && spanSaldoResultante) {
        inputHoras.addEventListener('input', function() {
            const horasACompensar = parseInt(this.value) || 0;
            const saldoResultante = Math.max(0, saldoActual - horasACompensar);
            
            spanHorasACompensar.textContent = horasACompensar;
            spanSaldoResultante.textContent = saldoResultante;
            
            // Cambiar color según el resultado
            if (horasACompensar > saldoActual) {
                spanSaldoResultante.className = 'text-danger';
            } else {
                spanSaldoResultante.className = 'text-primary';
            }
        });
    }
});
</script>