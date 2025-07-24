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

                    <!-- Selección de un solo gerente -->
                    <div class="mb-4">
                        <label for="firma_gerente_id" class="form-label">
                            <i class="bi bi-person-star"></i> Seleccionar Gerente
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="firma_gerente_id" name="firma_gerente_id" required>
                            <option value="">Seleccionar gerente...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Seleccione un gerente para firmar junto al Gerente General
                        </div>
                    </div>

                    <!-- Mostrar Gerente General fijo -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-person-badge"></i> Gerente General (Fijo)
                        </label>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $gerenteGeneral->nombre_completo }}</strong>
                                        <div class="small text-muted">Gerente General</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recursos Humanos (Usuario actual - Solo informativo) -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-people"></i> Recursos Humanos
                        </label>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <strong>{{ Auth::user()->nombre }}</strong>
                                        <div class="small text-muted">Usuario actual - Recursos Humanos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Se usará automáticamente tu nombre como usuario de Recursos Humanos
                        </div>
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

    modal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('form-seleccion-firmas').reset();
        document.getElementById('alert-seleccion-firmas').style.display = 'none';
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Limpia backdrop si queda pegado (previene pantalla gris)
        document.body.classList.remove('modal-open');
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(el => el.remove());
    });
});
</script>
