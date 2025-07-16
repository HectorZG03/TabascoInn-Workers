{{-- resources/views/trabajadores/documentos_vacaciones/modal_subir_documento.blade.php --}}

<div class="modal fade" id="subirDocumentoModal" tabindex="-1" aria-labelledby="subirDocumentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="subirDocumentoModalLabel">
                    <i class="bi bi-upload"></i> Subir Documento Firmado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-subir-documento" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">
                    
                    <!-- Información del trabajador -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-1">
                            <i class="bi bi-person"></i> {{ $trabajador->nombre_completo }}
                        </h6>
                        <small>
                            {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }} |
                            {{ $trabajador->antiguedad }} años de antigüedad
                        </small>
                    </div>

                    <!-- Selector de archivo -->
                    <div class="mb-4">
                        <label for="documento" class="form-label">
                            <i class="bi bi-file-earmark-pdf"></i> Documento PDF Firmado
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="documento" 
                               name="documento" 
                               accept=".pdf"
                               required>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Solo archivos PDF, máximo 2MB
                        </div>
                    </div>

                    <!-- Selección de vacaciones -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-calendar-check"></i> Vacaciones a Asociar
                            <span class="text-danger">*</span>
                        </label>
                        
                        @if($vacacionesPendientesSinDocumento->count() > 0)
                            <div class="border rounded p-3 bg-light">
                                <div class="form-text mb-3">
                                    Selecciona las vacaciones que están cubiertas por este documento:
                                </div>
                                
                                @foreach($vacacionesPendientesSinDocumento as $vacacion)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               value="{{ $vacacion->id_vacacion }}" 
                                               id="vacacion_{{ $vacacion->id_vacacion }}"
                                               name="vacaciones_ids[]">
                                        <label class="form-check-label" for="vacacion_{{ $vacacion->id_vacacion }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $vacacion->periodo_vacacional }}</strong>
                                                    <span class="badge bg-warning ms-2">{{ $vacacion->dias_solicitados }} días</span>
                                                </div>
                                                <div class="small text-muted">
                                                    {{ $vacacion->fecha_inicio->format('d/m/Y') }} - 
                                                    {{ $vacacion->fecha_fin->format('d/m/Y') }}
                                                </div>
                                            </div>
                                            @if($vacacion->observaciones)
                                                <div class="small text-muted mt-1">
                                                    <i class="bi bi-chat-text"></i> {{ $vacacion->observaciones }}
                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="invalid-feedback" id="vacaciones-error"></div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                No hay vacaciones pendientes para asociar con este documento.
                            </div>
                        @endif
                    </div>

                    <!-- Resumen de selección -->
                    <div class="card bg-light" id="resumen-seleccion" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-info-circle"></i> Resumen de Selección
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Vacaciones seleccionadas:</strong> 
                                    <span id="total-vacaciones-seleccionadas">0</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Total días:</strong> 
                                    <span id="total-dias-seleccionados">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas del modal -->
                    <div id="alert-subir-documento" class="alert" style="display: none;" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span id="alert-mensaje-subir"></span>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btn-subir-documento">
                        <span class="btn-text">
                            <i class="bi bi-upload"></i> Subir Documento
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Subiendo...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script básico para el modal (se mejorará en la fase frontend)
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('subirDocumentoModal');
    const checkboxes = document.querySelectorAll('input[name="vacaciones_ids[]"]');
    const resumenSeleccion = document.getElementById('resumen-seleccion');
    const totalVacaciones = document.getElementById('total-vacaciones-seleccionadas');
    const totalDias = document.getElementById('total-dias-seleccionados');

    // Actualizar resumen cuando cambian las selecciones
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const seleccionadas = document.querySelectorAll('input[name="vacaciones_ids[]"]:checked');
            const totalSeleccionadas = seleccionadas.length;
            
            if (totalSeleccionadas > 0) {
                // Calcular total de días (esto se puede mejorar obteniendo los días del DOM)
                let diasTotal = 0;
                seleccionadas.forEach(sel => {
                    const label = sel.nextElementSibling;
                    const badge = label.querySelector('.badge');
                    if (badge) {
                        const dias = parseInt(badge.textContent.match(/\d+/)[0]);
                        diasTotal += dias;
                    }
                });
                
                totalVacaciones.textContent = totalSeleccionadas;
                totalDias.textContent = diasTotal;
                resumenSeleccion.style.display = 'block';
            } else {
                resumenSeleccion.style.display = 'none';
            }
        });
    });

    // Resetear modal al cerrar
    modal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('form-subir-documento').reset();
        resumenSeleccion.style.display = 'none';
        document.getElementById('alert-subir-documento').style.display = 'none';
        
        // Limpiar validaciones
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
});
</script>