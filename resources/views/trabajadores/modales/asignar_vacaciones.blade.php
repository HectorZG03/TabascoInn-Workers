{{-- resources/views/trabajadores/modales/asignar_vacaciones.blade.php --}}
{{-- Modal con FORMATO GLOBAL - Fechas DD/MM/YYYY automáticas --}}

<div class="modal fade" id="asignarVacacionesModal" tabindex="-1" aria-labelledby="asignarVacacionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="asignarVacacionesModalLabel">
                    <i class="bi bi-calendar-heart"></i> Asignar Vacaciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-asignar-vacaciones" novalidate>
                @csrf
                <div class="modal-body">
                    
                    <!-- ===================================== -->
                    <!-- INFORMACIÓN DEL TRABAJADOR -->
                    <!-- ===================================== -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="alert-heading mb-1">
                                    <i class="bi bi-person"></i> {{ $trabajador->nombre_completo }}
                                </h6>
                                <small>
                                    Antigüedad: <span id="trabajador-antiguedad">{{ $trabajador->antiguedad ?? 0 }}</span> años |
                                    Estado: <span class="badge bg-{{ $trabajador->estatus_color }}">{{ $trabajador->estatus_texto }}</span>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="h5 mb-0 text-primary" id="dias-disponibles-display">
                                    <i class="bi bi-calendar-check"></i> <span id="dias-disponibles">0</span> días
                                </div>
                                <small class="text-muted">Disponibles este año</small>
                            </div>
                        </div>
                    </div>

                    <!-- ===================================== -->
                    <!-- FORMULARIO PRINCIPAL -->
                    <!-- ===================================== -->
                    <div class="row">
                        
                        <!-- Días Solicitados -->
                        <div class="col-md-6 mb-3">
                            <label for="dias_solicitados" class="form-label">
                                <i class="bi bi-calendar2-date"></i> Días Solicitados
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="dias_solicitados" 
                                       name="dias_solicitados" 
                                       min="1" 
                                       max="30"
                                       required
                                       autocomplete="off">
                                <span class="input-group-text">días</span>
                            </div>
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                Máximo: <span id="max-dias-texto">0</span> días disponibles
                            </div>
                        </div>

                        <!-- Año Correspondiente -->
                        <div class="col-md-6 mb-3">
                            <label for="año_correspondiente" class="form-label">
                                <i class="bi bi-calendar"></i> Año Correspondiente
                            </label>
                            <select class="form-select" id="año_correspondiente" name="año_correspondiente">
                                <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                                <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- ✅ FECHA DE INICIO - CON FORMATO GLOBAL DD/MM/YYYY -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="bi bi-calendar-event"></i> Fecha de Inicio
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control formato-fecha" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio"
                                   placeholder="DD/MM/YYYY"
                                   maxlength="10"
                                   required
                                   autocomplete="off">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Formato: DD/MM/YYYY - No puede ser fecha pasada
                            </div>
                        </div>

                        <!-- ✅ FECHA DE FIN - CON FORMATO GLOBAL DD/MM/YYYY -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="bi bi-calendar-x"></i> Fecha de Fin
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control formato-fecha" 
                                   id="fecha_fin" 
                                   name="fecha_fin"
                                   placeholder="DD/MM/YYYY"
                                   maxlength="10"
                                   required
                                   readonly
                                   autocomplete="off">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Se calcula automáticamente según los días solicitados
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12 mb-3">
                            <label for="observaciones" class="form-label">
                                <i class="bi bi-chat-text"></i> Observaciones
                            </label>
                            <textarea class="form-control" 
                                      id="observaciones" 
                                      name="observaciones" 
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Comentarios adicionales sobre estas vacaciones..."
                                      autocomplete="off"></textarea>
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                <span id="observaciones-count">0</span>/500 caracteres
                            </div>
                        </div>
                    </div>

                    <!-- ===================================== -->
                    <!-- RESUMEN DE VACACIONES -->
                    <!-- ===================================== -->
                    <div class="card bg-light mt-3" id="resumen-vacacion" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-info-circle"></i> Resumen de Vacaciones
                            </h6>
                            <div class="row text-sm">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Duración:</strong> <span id="resumen-duracion">0 días</span></li>
                                        <li><strong>Período:</strong> <span id="resumen-fechas">-</span></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Estado inicial:</strong> <span class="badge bg-warning">Pendiente</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===================================== -->
                    <!-- ALERTAS DEL MODAL -->
                    <!-- ===================================== -->
                    <div id="alert-vacaciones" class="alert" style="display: none;" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span id="alert-mensaje"></span>
                    </div>

                </div>

                <!-- ===================================== -->
                <!-- FOOTER CON BOTONES -->
                <!-- ===================================== -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-asignar-vacaciones">
                        <span class="btn-text">
                            <i class="bi bi-check-lg"></i> Asignar Vacaciones
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Asignando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 
====================================================================
🎯 CAMBIOS IMPLEMENTADOS:
====================================================================

1. ✅ Inputs type="text" con clase "formato-fecha"
2. ✅ Placeholder DD/MM/YYYY para guía visual
3. ✅ maxlength="10" para limitar caracteres
4. ✅ El formato global se aplica automáticamente
5. ✅ readonly en fecha_fin (se calcula automáticamente)
6. ✅ Textos de ayuda actualizados

====================================================================
--}}