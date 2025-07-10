{{-- ✅ MODAL DE DESPIDO CON FORMATO GLOBAL DD/MM/YYYY --}}
<div class="modal fade" id="modalDespido" tabindex="-1" aria-labelledby="modalDespidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-danger shadow-sm">
      
      <!-- Header -->
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalDespidoLabel">
          <i class="bi bi-exclamation-triangle-fill fs-4"></i> Dar de Baja a Trabajador
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <!-- Form -->
      <form id="formDespido" method="POST" novalidate>
        @csrf
        <div class="modal-body">
          
          <!-- Info trabajador -->
          <div class="alert alert-warning py-3 d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-3"></i>
            <div>
              <p class="mb-1 fw-semibold">Está a punto de dar de baja al trabajador:</p>
              <p class="mb-0 fs-5 text-truncate"><strong id="nombreTrabajador"></strong></p>
              <small class="text-muted fst-italic">Esta acción cambiará su estado a <span class="fw-bold">"Inactivo"</span> y creará un registro permanente.</small>
            </div>
          </div>
          
          <div class="row g-3">
            <!-- ✅ FECHA DE BAJA CON FORMATO PERSONALIZADO -->
            <div class="col-md-6">
              <label for="fecha_baja" class="form-label fw-semibold">
                <i class="bi bi-calendar-x-fill me-1"></i> Fecha de Baja <span class="text-danger">*</span>
              </label>
              @php
                  $fechaBaja = old('fecha_baja') 
                      ? \Carbon\Carbon::parse(old('fecha_baja'))->format('d/m/Y') 
                      : '';
              @endphp

              <input type="text" 
                    class="form-control formato-fecha" 
                    id="fecha_baja" 
                    name="fecha_baja" 
                    placeholder="DD/MM/YYYY"
                    maxlength="10"
                    value="{{ $fechaBaja }}"
                    required>
              <div class="form-text">Formato: DD/MM/YYYY (no puede ser posterior a hoy)</div>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Tipo de Baja -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Tipo de Baja <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo_baja" id="tipo_definitiva" value="definitiva" checked onchange="toggleReintegroField(false)">
                  <label class="form-check-label" for="tipo_definitiva">Definitiva</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo_baja" id="tipo_temporal" value="temporal" onchange="toggleReintegroField(true)">
                  <label class="form-check-label" for="tipo_temporal">Temporal</label>
                </div>
              </div>
            </div>
            
            <!-- ✅ FECHA DE REINTEGRO CON FORMATO PERSONALIZADO -->
            <div class="col-md-6" id="fechaReintegroContainer" style="display: none;">
              <label for="fecha_reintegro" class="form-label fw-semibold">
                <i class="bi bi-calendar-check-fill me-1"></i> Fecha de Reintegro <span class="text-danger">*</span>
              </label>
              <input type="text" 
                     class="form-control formato-fecha" 
                     id="fecha_reintegro" 
                     name="fecha_reintegro" 
                     placeholder="DD/MM/YYYY"
                     maxlength="10">
              <div class="form-text">Formato: DD/MM/YYYY (debe ser posterior a la fecha de baja)</div>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- ✅ CONDICIÓN DE SALIDA MEJORADA -->
            <div class="col-md-6">
              <label for="condicion_salida" class="form-label fw-semibold">
                <i class="bi bi-list-check me-1"></i> Condición de Salida <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="condicion_salida" name="condicion_salida" required onchange="toggleCondicionPersonalizada()">
                <option value="" disabled selected>Seleccionar condición...</option>
                <option value="Voluntaria">Voluntaria (Renuncia)</option>
                <option value="Despido con Causa">Baja con Causa</option>
                <option value="Despido sin Causa">Baja sin Causa</option>
                <option value="Castigo">Castigo</option>
                <option value="Mutuo Acuerdo">Mutuo Acuerdo</option>
                <option value="Abandono de Trabajo">Abandono de Trabajo</option>
                <option value="Fin de Contrato">Fin de Contrato</option>
                <option value="Incapacidad Permanente">Incapacidad Permanente</option>
                <option value="Jubilación">Jubilación</option>
                <option value="Defunción">Defunción</option>
                <!-- ✅ OPCIÓN PARA ESCRIBIR PERSONALIZADA -->
                <option value="OTRO">✏️ Otro (especificar)</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- ✅ CAMPO PERSONALIZADO PARA CONDICIÓN -->
            <div class="col-12" id="condicionPersonalizadaContainer" style="display: none;">
              <label for="condicion_personalizada" class="form-label fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Especificar Condición de Salida <span class="text-danger">*</span>
              </label>
              <input type="text" 
                     class="form-control" 
                     id="condicion_personalizada" 
                     name="condicion_personalizada" 
                     placeholder="Escriba la condición de salida específica..."
                     maxlength="100"
                     style="text-transform: uppercase">
              <small class="form-text text-muted">
                <i class="bi bi-lightbulb-fill text-warning"></i> 
                Escriba la condición exacta cuando ninguna de las opciones anteriores sea apropiada.
              </small>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Motivo -->
            <div class="col-12">
              <label for="motivo" class="form-label fw-semibold">
                <i class="bi bi-chat-text me-1"></i> Motivo de la Baja <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" 
                        id="motivo" 
                        name="motivo" 
                        rows="3" 
                        minlength="10" 
                        maxlength="500" 
                        placeholder="Descripción detallada del motivo de la baja..."
                        style="text-transform: uppercase"
                        required></textarea>
              <div class="d-flex justify-content-between align-items-center">
                <small class="form-text text-muted">Mínimo 10 caracteres, máximo 500.</small>
                <small id="contadorMotivo" class="text-muted">0/500</small>
              </div>
              <div class="invalid-feedback"></div>
            </div>
            
            <!-- Observaciones -->
            <div class="col-12">
              <label for="observaciones" class="form-label fw-semibold">
                <i class="bi bi-clipboard-data me-1"></i> Observaciones Adicionales
              </label>
              <textarea class="form-control" 
                        id="observaciones" 
                        name="observaciones" 
                        rows="3" 
                        maxlength="1000" 
                        placeholder="Observaciones adicionales, recomendaciones o notas relevantes..."
                        style="text-transform: uppercase"></textarea>
              <div class="d-flex justify-content-between align-items-center">
                <small class="form-text text-muted">Opcional. Máximo 1000 caracteres.</small>
                <small id="contadorObservaciones" class="text-muted">0/1000</small>
              </div>
              <div class="invalid-feedback"></div>
            </div>

            <!-- ✅ INFORMACIÓN DE DURACIÓN (solo para temporales) -->
            <div class="col-12" id="duracionBajaContainer" style="display: none;">
              <div class="card bg-light border-info">
                <div class="card-body py-2">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-week text-info"></i>
                    <strong class="text-info">Duración de la baja temporal:</strong>
                    <span id="duracionBaja" class="badge bg-info">0 días</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        
        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-danger" id="btnConfirmarDespido">
            <i class="bi bi-person-x me-1"></i> Confirmar Baja
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('js/modales/despidos_modal.js') }}"></script>