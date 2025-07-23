{{-- resources/views/trabajadores/modales/asignar_vacaciones.blade.php --}}
{{-- Modal con FORMATO GLOBAL - Fechas DD/MM/YYYY automáticas + Días Laborables --}}

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
                    <!-- ✅ NUEVA: INFORMACIÓN DE HORARIO LABORAL -->
                    <!-- ===================================== -->
                    @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                        <div class="alert alert-success">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="alert-heading mb-1">
                                        <i class="bi bi-calendar-week"></i> Horario Laboral Definido
                                    </h6>
                                    <small>
                                        <strong>Días laborables:</strong> {{ $trabajador->fichaTecnica->dias_laborables_texto }}<br>
                                        <strong>Horas semanales:</strong> {{ $trabajador->fichaTecnica->horas_semanales ?? 'No definido' }}h |
                                        <strong>Turno:</strong> {{ $trabajador->fichaTecnica->turno_calculado ?? 'No definido' }}
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="badge bg-success fs-6 mb-1">
                                        <i class="bi bi-check-circle"></i> Cálculo inteligente
                                    </div>
                                    <div class="small text-muted">Solo días laborables</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="alert-heading mb-1">
                                        <i class="bi bi-exclamation-triangle"></i> Sin horario laboral definido
                                    </h6>
                                    <small>
                                        Se usará cálculo tradicional de días calendario consecutivos.<br>
                                        <strong>Recomendación:</strong> Configure el horario laboral en la ficha técnica.
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="badge bg-warning fs-6 mb-1">
                                        <i class="bi bi-calendar"></i> Días calendario
                                    </div>
                                    <div class="small text-muted">Cálculo tradicional</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ===================================== -->
                    <!-- FORMULARIO PRINCIPAL -->
                    <!-- ===================================== -->
                    <div class="row">
                        
                        <!-- ✅ ACTUALIZADO: Días Solicitados con contexto -->
                        <div class="col-md-6 mb-3">
                            <label for="dias_solicitados" class="form-label">
                                <i class="bi bi-calendar2-date"></i> 
                                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                                    Días Laborables Solicitados
                                @else
                                    Días Solicitados
                                @endif
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
                                <span class="input-group-text">
                                    @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                                        días lab.
                                    @else
                                        días
                                    @endif
                                </span>
                            </div>
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                                    <i class="bi bi-info-circle text-success"></i> 
                                    Se calculará considerando solo <strong>{{ $trabajador->fichaTecnica->dias_laborables_texto }}</strong>.
                                    Máximo: <span id="max-dias-texto">0</span> días disponibles
                                @else
                                    <i class="bi bi-info-circle text-warning"></i> 
                                    Cálculo tradicional (días calendario consecutivos).
                                    Máximo: <span id="max-dias-texto">0</span> días disponibles
                                @endif
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
                                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                                    <br><i class="bi bi-lightbulb text-success"></i> 
                                    <small>Si no es día laborable, se ajustará al siguiente día hábil</small>
                                @endif
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
                                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                                    <br><i class="bi bi-gear text-success"></i> 
                                    <small>Calculado usando horario laboral definido</small>
                                @endif
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
                    <!-- ✅ ACTUALIZADO: RESUMEN DE VACACIONES CON DÍAS LABORABLES -->
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
                                        <li id="resumen-tipo-calculo" style="display: none;">
                                            <strong>Tipo de cálculo:</strong> <span id="tipo-calculo-badge" class="badge">-</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- ✅ NUEVA: Información adicional dinámica -->
                            <div id="resumen-info-adicional" class="mt-2" style="display: none;">
                                <!-- Se llena dinámicamente desde JS -->
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