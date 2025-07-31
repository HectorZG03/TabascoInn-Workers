{{-- ✅ MODAL PARA ASIGNAR HORAS EXTRA CON DECIMALES Y SIN RESTRICCIONES DE FECHA --}}
<div class="modal fade" id="modalAsignarHoras{{ $trabajador->id_trabajador }}" tabindex="-1" aria-labelledby="modalAsignarHorasLabel{{ $trabajador->id_trabajador }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <form method="POST" action="{{ route('trabajadores.horas-extra.asignar', $trabajador->id_trabajador) }}">
            @csrf

                
                {{-- ✅ Header del modal --}}
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAsignarHorasLabel{{ $trabajador->id_trabajador }}">
                        <i class="bi bi-plus-circle"></i> Asignar Horas Extra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                @php
                                    $saldoActualAsignar = \App\Models\HorasExtra::calcularSaldo($trabajador->id_trabajador);
                                @endphp
                                <div class="badge bg-primary fs-6">
                                    <i class="bi bi-clock"></i> 
                                    {{ $saldoActualAsignar == floor($saldoActualAsignar) ? number_format($saldoActualAsignar, 0) : number_format($saldoActualAsignar, 1) }} 
                                    {{ $saldoActualAsignar == 1 ? 'hora' : 'horas' }}
                                </div>
                                <div class="small text-muted mt-1">Saldo actual</div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario --}}
                    <div class="row g-3">
                        {{-- ✅ HORAS CON DECIMALES --}}
                        <div class="col-md-6">
                            <label for="horas_asignar{{ $trabajador->id_trabajador }}" class="form-label">
                                <i class="bi bi-clock-fill text-success"></i> Horas Extra Trabajadas <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('horas') is-invalid @enderror" 
                                       id="horas_asignar{{ $trabajador->id_trabajador }}" 
                                       name="horas" 
                                       value="{{ old('horas') }}"
                                       min="0.1" 
                                       max="24" 
                                       step="0.1" 
                                       placeholder="0.0"
                                       required>
                                <span class="input-group-text">horas</span>
                                @error('horas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                Mínimo: 0.1 horas (6 min) | Máximo: 24 horas | <strong>Acepta decimales</strong> (ej: 1.5, 2.25)
                            </div>
                        </div>

                        {{-- ✅ FECHA SIN RESTRICCIONES --}}
                        <div class="col-md-6">
                            <label for="fecha_asignar{{ $trabajador->id_trabajador }}" class="form-label">
                                <i class="bi bi-calendar3"></i> Fecha del Trabajo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control formato-fecha @error('fecha') is-invalid @enderror" 
                                   id="fecha_asignar{{ $trabajador->id_trabajador }}" 
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
                            <label for="descripcion_asignar{{ $trabajador->id_trabajador }}" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Descripción o Motivo
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion_asignar{{ $trabajador->id_trabajador }}" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Ej: Trabajo extra por evento especial, horario extendido, etc..."
                                      maxlength="200">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span id="contadorAsignar{{ $trabajador->id_trabajador }}">0</span>/200 caracteres
                            </div>
                        </div>

                        {{-- ✅ VISTA PREVIA ACTUALIZADA PARA DECIMALES --}}
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-body bg-light">
                                    <h6 class="card-title text-success">
                                        <i class="bi bi-calculator"></i> Resumen de Asignación
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="h5 text-primary mb-1">
                                                {{ $saldoActualAsignar == floor($saldoActualAsignar) ? number_format($saldoActualAsignar, 0) : number_format($saldoActualAsignar, 1) }}
                                            </div>
                                            <small class="text-muted">Saldo Actual</small>
                                        </div>
                                        <div class="col-1 align-self-center">
                                            <i class="bi bi-plus text-success"></i>
                                        </div>
                                        <div class="col-3">
                                            <div class="h5 text-success mb-1">
                                                <span id="horasAAsignar{{ $trabajador->id_trabajador }}">0</span>
                                            </div>
                                            <small class="text-muted">A Asignar</small>
                                        </div>
                                        <div class="col-1 align-self-center">
                                            <i class="bi bi-equals text-primary"></i>
                                        </div>
                                        <div class="col-3">
                                            <div class="h5 text-primary mb-1">
                                                <span id="saldoFinalAsignar{{ $trabajador->id_trabajador }}">
                                                    {{ $saldoActualAsignar == floor($saldoActualAsignar) ? number_format($saldoActualAsignar, 0) : number_format($saldoActualAsignar, 1) }}
                                                </span>
                                            </div>
                                            <small class="text-muted">Horas Acumuladas</small>
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
                                <li>Las horas extra se acumularán al saldo del trabajador</li>
                                <li><strong>Acepta decimales:</strong> puede registrar 0.5, 1.25, 2.75 horas, etc.</li>
                                <li>Estas horas podrán ser compensadas posteriormente</li>
                                <li>El registro quedará en el historial laboral</li>
                                <li><strong>Sin restricciones de fecha:</strong> puede registrar cualquier fecha válida</li>
                                <li><strong>Formato de fecha:</strong> DD/MM/YYYY (se formatea automáticamente)</li>
                                <li>Mínimo: 0.1 horas (6 minutos) | Máximo: 24 horas por registro</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- ✅ Footer del modal --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Asignar Horas Extra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ Script actualizado para decimales --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('descripcion_asignar{{ $trabajador->id_trabajador }}');
    const contador = document.getElementById('contadorAsignar{{ $trabajador->id_trabajador }}');
    const inputHoras = document.getElementById('horas_asignar{{ $trabajador->id_trabajador }}');
    const spanHorasAAsignar = document.getElementById('horasAAsignar{{ $trabajador->id_trabajador }}');
    const spanSaldoFinal = document.getElementById('saldoFinalAsignar{{ $trabajador->id_trabajador }}');
    const saldoActual = {{ $saldoActualAsignar }};
    
    // Contador de caracteres
    if (textarea && contador) {
        textarea.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });
        
        // Inicializar contador
        contador.textContent = textarea.value.length;
    }
    
    // ✅ CALCULADORA ACTUALIZADA PARA DECIMALES
    if (inputHoras && spanHorasAAsignar && spanSaldoFinal) {
        inputHoras.addEventListener('input', function() {
            const horasAAsignar = parseFloat(this.value) || 0; // ✅ parseFloat en lugar de parseInt
            const saldoFinal = saldoActual + horasAAsignar;
            
            // ✅ FORMATEAR DECIMALES CORRECTAMENTE
            spanHorasAAsignar.textContent = horasAAsignar === Math.floor(horasAAsignar) ? 
                horasAAsignar.toString() : horasAAsignar.toFixed(1);
            spanSaldoFinal.textContent = saldoFinal === Math.floor(saldoFinal) ? 
                saldoFinal.toString() : saldoFinal.toFixed(1);
                
            // Cambiar color según validez
            if (horasAAsignar < 0.1 || horasAAsignar > 24) { // ✅ min 0.1
                spanSaldoFinal.className = 'text-warning';
                inputHoras.classList.add('is-invalid');
            } else {
                spanSaldoFinal.className = 'text-primary';
                inputHoras.classList.remove('is-invalid');
            }
        });
    }
    
    // ✅ VALIDACIÓN SIMPLIFICADA DE FECHAS - SOLO FORMATO
    const campoFecha = document.getElementById('fecha_asignar{{ $trabajador->id_trabajador }}');
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