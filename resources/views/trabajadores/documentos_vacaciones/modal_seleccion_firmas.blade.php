{{-- resources/views/trabajadores/documentos_vacaciones/modal_seleccion_firmas.blade.php --}}

<div class="modal fade" id="seleccionFirmasModal" tabindex="-1" aria-labelledby="seleccionFirmasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="seleccionFirmasModalLabel">
                    <i class="bi bi-file-earmark-pdf"></i> Seleccionar Firmas para PDF
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-seleccion-firmas" novalidate>
                @csrf
                <div class="modal-body">
                    
                    <!-- Información del trabajador -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-1">
                            <i class="bi bi-person"></i> <span id="trabajador-nombre">{{ $trabajador->nombre_completo }}</span>
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Categoría:</strong> <span id="trabajador-categoria">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small>
                                    <strong>Vacaciones:</strong> <span id="total-vacaciones-pendientes">{{ $trabajador->vacacionesPendientes->count() }}</span>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small>
                                    <strong>Total días:</strong> <span id="total-dias-pendientes">{{ $trabajador->vacacionesPendientes->sum('dias_solicitados') }}</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div class="alert alert-light border">
                        <i class="bi bi-info-circle text-primary"></i>
                        <strong>Seleccione 3 gerentes diferentes</strong> que firmarán el documento junto con el trabajador.
                    </div>

                    <!-- Selección del primer gerente -->
                    <div class="mb-3">
                        <label for="gerente_1" class="form-label">
                            <i class="bi bi-person-star"></i> Primer Gerente
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="gerente_1" name="gerente_1" required>
                            <option value="">Seleccionar primer gerente...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Selección del segundo gerente -->
                    <div class="mb-3">
                        <label for="gerente_2" class="form-label">
                            <i class="bi bi-person-star"></i> Segundo Gerente
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="gerente_2" name="gerente_2" required>
                            <option value="">Seleccionar segundo gerente...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Selección del tercer gerente -->
                    <div class="mb-4">
                        <label for="gerente_3" class="form-label">
                            <i class="bi bi-person-star"></i> Tercer Gerente
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="gerente_3" name="gerente_3" required>
                            <option value="">Seleccionar tercer gerente...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Alertas del modal -->
                    <div id="alert-seleccion-firmas" class="alert" style="display: none;" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span id="alert-mensaje-firmas"></span>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-generar-pdf">
                        <span class="btn-text">
                            <i class="bi bi-download"></i> Generar y Descargar PDF
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Generando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('seleccionFirmasModal');
    const selects = ['gerente_1', 'gerente_2', 'gerente_3'];

    // Función para filtrar opciones duplicadas
    function actualizarOpciones() {
        const valoresSeleccionados = selects.map(id => document.getElementById(id).value).filter(v => v);
        
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            const valorActual = select.value;
            
            // Habilitar todas las opciones primero
            Array.from(select.options).forEach(option => {
                if (option.value) {
                    option.disabled = false;
                    option.style.display = '';
                }
            });
            
            // Deshabilitar opciones ya seleccionadas en otros selects
            valoresSeleccionados.forEach(valor => {
                if (valor && valor !== valorActual) {
                    const option = select.querySelector(`option[value="${valor}"]`);
                    if (option) {
                        option.disabled = true;
                        option.style.display = 'none';
                    }
                }
            });
        });
    }

    // Agregar eventos a los selects
    selects.forEach(selectId => {
        document.getElementById(selectId).addEventListener('change', actualizarOpciones);
    });

    // Limpiar formulario al cerrar modal
    modal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('form-seleccion-firmas').reset();
        document.getElementById('alert-seleccion-firmas').style.display = 'none';
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Reactivar todas las opciones
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            Array.from(select.options).forEach(option => {
                option.disabled = false;
                option.style.display = '';
            });
        });

        // Limpiar backdrop si queda pegado
        document.body.classList.remove('modal-open');
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(el => el.remove());
    });
});
</script>