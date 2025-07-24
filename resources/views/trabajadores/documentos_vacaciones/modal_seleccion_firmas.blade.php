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

                    <!-- Selección de Primera Firma -->
                    <div class="mb-4">
                        <label for="firma1_id" class="form-label">
                            <i class="bi bi-person-badge"></i> Primera Firma
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="firma1_id" name="firma1_id" required>
                            <option value="">Seleccionar gerente para primera firma...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Seleccione quien firmará en la primera posición (superior derecha)
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

                    <!-- Selección de Segunda Firma -->
                    <div class="mb-4">
                        <label for="firma2_id" class="form-label">
                            <i class="bi bi-person-star"></i> Segunda Firma
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="firma2_id" name="firma2_id" required>
                            <option value="">Seleccionar gerente para segunda firma...</option>
                            @foreach($gerentes as $gerente)
                                <option value="{{ $gerente['id'] }}" data-cargo="{{ $gerente['cargo'] }}">
                                    {{ $gerente['nombre_completo'] }} - {{ $gerente['cargo'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Seleccione quien firmará en la segunda posición (inferior derecha)
                        </div>
                    </div>

                    <!-- Vista previa de firmas -->
                    <div class="card bg-light" id="preview-firmas" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-eye"></i> Vista Previa de Firmas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center border-end">
                                        <strong>Trabajador</strong>
                                        <hr style="border: 1px solid #000; width: 80%; margin: 10px auto;">
                                        <div>Firma del trabajador</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <strong>Primera Firma</strong>
                                        <hr style="border: 1px solid #000; width: 80%; margin: 10px auto;">
                                        <div id="preview-firma1-nombre">-</div>
                                        <small id="preview-firma1-cargo" class="text-muted">-</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center border-end">
                                        <strong>Recursos Humanos</strong>
                                        <hr style="border: 1px solid #000; width: 80%; margin: 10px auto;">
                                        <div>{{ Auth::user()->nombre }}</div>
                                        <small class="text-muted">Recursos Humanos</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <strong>Segunda Firma</strong>
                                        <hr style="border: 1px solid #000; width: 80%; margin: 10px auto;">
                                        <div id="preview-firma2-nombre">-</div>
                                        <small id="preview-firma2-cargo" class="text-muted">-</small>
                                    </div>
                                </div>
                            </div>
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
// Script para el modal de selección de firmas
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('seleccionFirmasModal');
    const firma1Select = document.getElementById('firma1_id');
    const firma2Select = document.getElementById('firma2_id');
    const previewFirmas = document.getElementById('preview-firmas');
    
    // Actualizar vista previa cuando cambian las selecciones
    function updatePreview() {
        const firma1Option = firma1Select.selectedOptions[0];
        const firma2Option = firma2Select.selectedOptions[0];
        
        if (firma1Option && firma1Option.value) {
            document.getElementById('preview-firma1-nombre').textContent = firma1Option.textContent.split(' - ')[0];
            document.getElementById('preview-firma1-cargo').textContent = firma1Option.dataset.cargo || 'Sin cargo';
        } else {
            document.getElementById('preview-firma1-nombre').textContent = '-';
            document.getElementById('preview-firma1-cargo').textContent = '-';
        }
        
        if (firma2Option && firma2Option.value) {
            document.getElementById('preview-firma2-nombre').textContent = firma2Option.textContent.split(' - ')[0];
            document.getElementById('preview-firma2-cargo').textContent = firma2Option.dataset.cargo || 'Sin cargo';
        } else {
            document.getElementById('preview-firma2-nombre').textContent = '-';
            document.getElementById('preview-firma2-cargo').textContent = '-';
        }
        
        // Mostrar/ocultar preview
        if ((firma1Option && firma1Option.value) || (firma2Option && firma2Option.value)) {
            previewFirmas.style.display = 'block';
        } else {
            previewFirmas.style.display = 'none';
        }
    }
    
    firma1Select.addEventListener('change', updatePreview);
    firma2Select.addEventListener('change', updatePreview);
    
    // Resetear modal al cerrar
    modal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('form-seleccion-firmas').reset();
        previewFirmas.style.display = 'none';
        document.getElementById('alert-seleccion-firmas').style.display = 'none';
        
        // Limpiar validaciones
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
});
</script>