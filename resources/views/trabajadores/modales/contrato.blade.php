<!-- ✅ MODAL COMPLETAMENTE LIMPIO - SIN botón de preview -->
<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalContratoLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Finalizar Registro del Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Mensaje introductorio -->
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-3 fs-5"></i>
                        <div>
                            <h6 class="mb-1">¡Ya casi terminamos!</h6>
                            <p class="mb-0">Solo falta configurar el estado inicial y las fechas del contrato.</p>
                        </div>
                    </div>
                </div>

                <!-- Estado del Trabajador -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>
                            Estado Inicial del Trabajador
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="estatus" class="form-label fw-semibold">
                                    <i class="bi bi-gear me-1"></i>
                                    Seleccionar Estado Inicial *
                                </label>
                                <select class="form-select form-select-lg" 
                                        id="estatus" 
                                        name="estatus" 
                                        required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="activo">
                                        ✅ Activo - Trabajador operativo completo
                                    </option>
                                    <option value="prueba">
                                        🟡 En Prueba - Período de evaluación inicial
                                    </option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <strong>Activo:</strong> Opera normalmente desde el primer día.<br>
                                        <strong>En Prueba:</strong> Período de evaluación (30-90 días típicamente).
                                    </small>
                                </div>
                                <div id="errorEstatus" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Vista previa del estado -->
                        <div id="estadoPreview" class="mt-3" style="display: none;">
                            <div class="alert mb-0" id="estadoPreviewAlert">
                                <div class="d-flex align-items-center">
                                    <i id="estadoPreviewIcon" class="me-2 fs-5"></i>
                                    <div>
                                        <div class="fw-bold" id="estadoPreviewTexto">Estado</div>
                                        <small id="estadoPreviewDescripcion" class="text-muted">Descripción</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fechas del contrato -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-dark">
                            <i class="bi bi-calendar-range me-2"></i>
                            Período del Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fecha de inicio -->
                            <div class="col-md-6">
                                <label for="fecha_inicio_contrato" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-plus text-success me-1"></i>
                                    Fecha de Inicio *
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_inicio_contrato" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ date('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Cuándo inicia a trabajar</small>
                            </div>

                            <!-- Fecha de fin -->
                            <div class="col-md-6">
                                <label for="fecha_fin_contrato" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x text-warning me-1"></i>
                                    Fecha de Finalización *
                                </label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       id="fecha_fin_contrato"
                                       required>
                                <small class="text-muted">Cuándo termina el contrato</small>
                            </div>
                        </div>

                        <!-- Vista previa de duración -->
                        <div id="duracionPreview" class="mt-4" style="display: none;">
                            <div class="bg-light rounded p-3">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="text-primary">
                                            <i class="bi bi-hourglass-split fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="duracionTexto">-</div>
                                                <small class="text-muted">Duración Total</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-success">
                                            <i class="bi bi-calendar-check fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="fechaInicioTexto">-</div>
                                                <small class="text-muted">Inicia</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-warning">
                                            <i class="bi bi-calendar-x fs-4"></i>
                                            <div class="mt-1">
                                                <div class="fw-bold" id="fechaFinTexto">-</div>
                                                <small class="text-muted">Termina</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Errores de fechas -->
                        <div id="errorFechas" class="alert alert-warning mt-3" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="errorFechasTexto">Por favor verifica las fechas</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen de lo que se creará -->
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            Se creará automáticamente:
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-plus text-primary me-2 fs-5"></i>
                                    <span>Perfil completo del trabajador</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-briefcase text-success me-2 fs-5"></i>
                                    <span>Ficha técnica laboral</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf text-danger me-2 fs-5"></i>
                                    <span>Contrato en PDF</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-check text-info me-2 fs-5"></i>
                                    <span>Estado inicial configurado</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nota importante -->
                        <div class="mt-3">
                            <div class="alert alert-success mb-0">
                                <small>
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>¿Necesitas el contrato?</strong> 
                                    Después de crear el trabajador, podrás descargar el contrato desde su perfil.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer - SOLO DOS BOTONES -->
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                
                <button type="button" class="btn btn-success btn-lg px-4" id="btnCrearTrabajador">
                    <span id="btnTextoNormal">
                        <i class="bi bi-plus-circle me-2"></i>
                        Crear Trabajador Completo
                    </span>
                    <span id="btnTextoCargando" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Creando trabajador...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>


<script src="{{asset('js/modales/modal_contrato.js')}}"></script>