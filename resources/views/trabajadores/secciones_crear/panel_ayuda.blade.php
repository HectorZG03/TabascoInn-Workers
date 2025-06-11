<!-- ✅ SECCIÓN: PANEL DE AYUDA -->
<div class="card shadow mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-question-circle"></i> Ayuda
        </h6>
    </div>
    <div class="card-body">
        <div class="accordion accordion-flush" id="accordionHelp">
            <!-- Horarios -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHorarios">
                        <i class="bi bi-clock me-2"></i> Horarios de Trabajo
                    </button>
                </h2>
                <div id="collapseHorarios" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        <ul class="list-unstyled">
                            <li><strong>Diurno:</strong> 06:00 - 18:00</li>
                            <li><strong>Nocturno:</strong> 18:00 - 06:00</li>
                            <li><strong>Mixto:</strong> Otros horarios</li>
                        </ul>
                        <small class="text-muted">Las horas válidas son entre 1 y 16 por día.</small>
                    </div>
                </div>
            </div>
            
            <!-- Documentos -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentos">
                        <i class="bi bi-card-text me-2"></i> Documentos
                    </button>
                </h2>
                <div id="collapseDocumentos" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        <ul class="list-unstyled">
                            <li><strong>CURP:</strong> 18 caracteres</li>
                            <li><strong>RFC:</strong> 13 caracteres</li>
                            <li><strong>NSS:</strong> 11 dígitos (opcional)</li>
                        </ul>
                        <small class="text-muted">Todos los documentos deben ser únicos en el sistema.</small>
                    </div>
                </div>
            </div>

            <!-- Contrato -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContrato">
                        <i class="bi bi-file-earmark-text me-2"></i> Contrato
                    </button>
                </h2>
                <div id="collapseContrato" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        <p class="mb-2">Al guardar se generará automáticamente:</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success"></i> Contrato de trabajo</li>
                            <li><i class="bi bi-check-circle text-success"></i> Datos en la base</li>
                            <li><i class="bi bi-check-circle text-success"></i> Ficha técnica</li>
                        </ul>
                        <small class="text-muted">El proceso es automático y seguro.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>