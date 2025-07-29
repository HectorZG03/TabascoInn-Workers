{{-- resources/views/trabajadores/secciones_perfil/datos_laborales.blade.php --}}

<div class="row">
    <!-- ✅ DATOS LABORALES CON TURNO MANUAL -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase-fill"></i> Datos Laborales
                </h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalEditarDatosLaborales">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="card-body">
                <!-- Información Principal -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">Área</label>
                        <div class="fw-bold">{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Categoría</label>
                        <div class="fw-bold">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Sueldo Diario</label>
                        <div class="fw-bold text-success">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- ✅ HORARIOS CON TURNO MANUAL Y HORARIO DE DESCANSO -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted">Hora Entrada</label>
                        <div class="fw-bold">
                            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->hora_entrada)
                                {{ \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_entrada)->format('H:i') }}
                            @else
                                <span class="text-muted">No definida</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Hora Salida</label>
                        <div class="fw-bold">
                            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->hora_salida)
                                {{ \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_salida)->format('H:i') }}
                            @else
                                <span class="text-muted">No definida</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Horas Diarias</label>
                        <div class="fw-bold">{{ number_format($trabajador->fichaTecnica->horas_trabajo ?? 0, 1) }}h</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Turno</label>
                        <div class="fw-bold">
                            @php
                                $turno = $trabajador->fichaTecnica->turno ?? 'no_definido';
                                $badgeClass = match($turno) {
                                    'diurno' => 'warning',
                                    'nocturno' => 'dark',
                                    'mixto' => 'info',
                                    default => 'secondary'
                                };
                                $turnoTexto = match($turno) {
                                    'diurno' => 'Diurno',
                                    'nocturno' => 'Nocturno',
                                    'mixto' => 'Mixto',
                                    default => 'No definido'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ $turnoTexto }}</span>
                            <small class="text-muted d-block">Configurado manualmente</small>
                        </div>
                    </div>
                </div>

                <!-- ✅ NUEVA FILA: Horario de Descanso -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Horario de Descanso</label>
                        <div class="fw-bold">
                            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->horario_descanso)
                                <i class="bi bi-pause-circle text-info me-1"></i>
                                {{ $trabajador->fichaTecnica->horario_descanso }}
                            @else
                                <span class="text-muted">No definido</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Espacio para información adicional si es necesario -->
                    </div>
                </div>

                <!-- Días Laborables -->
                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted">Días Laborables</label>
                        <div class="fw-bold">
                            @foreach($trabajador->fichaTecnica->dias_laborables as $dia)
                                <span class="badge bg-primary me-1">{{ \App\Models\FichaTecnica::DIAS_SEMANA[$dia] ?? $dia }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Horas Semanales</label>
                        <div class="fw-bold">{{ number_format($trabajador->fichaTecnica->horas_semanales ?? 0, 1) }}h</div>
                    </div>
                </div>
                @endif

                <!-- Días de Descanso -->
                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_descanso)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted">Días de Descanso</label>
                        <div class="fw-bold">
                            @foreach($trabajador->fichaTecnica->dias_descanso as $dia)
                                <span class="badge bg-secondary me-1">{{ \App\Models\FichaTecnica::DIAS_SEMANA[$dia] ?? $dia }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Formación y Estudios -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Formación</label>
                        <div class="fw-bold">{{ $trabajador->fichaTecnica->formacion ?? 'No especificada' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Grado de Estudios</label>
                        <div class="fw-bold">{{ $trabajador->fichaTecnica->grado_estudios ?? 'No especificado' }}</div>
                    </div>
                </div>

                <!-- Beneficiario -->
                @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->beneficiario_nombre)
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label text-muted">Beneficiario Principal</label>
                        <div class="fw-bold">{{ $trabajador->fichaTecnica->beneficiario_nombre }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Parentesco</label>
                        <div class="fw-bold">{{ \App\Models\FichaTecnica::PARENTESCOS_BENEFICIARIO[$trabajador->fichaTecnica->beneficiario_parentesco] ?? $trabajador->fichaTecnica->beneficiario_parentesco }}</div>
                    </div>
                </div>
                @endif

                <!-- Información Adicional -->
                @if($trabajador->fichaTecnica)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <strong>Horario:</strong><br>
                                    <span class="text-primary">{{ $trabajador->fichaTecnica->horario_formateado }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Semanal:</strong><br>
                                    <span class="text-success">{{ number_format($trabajador->fichaTecnica->horas_semanales ?? 0, 1) }}h</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Días Laborables:</strong><br>
                                    <span class="text-info">{{ count($trabajador->fichaTecnica->dias_laborables ?? []) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Días Descanso:</strong><br>
                                    <span class="text-warning">{{ count($trabajador->fichaTecnica->dias_descanso ?? []) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Panel de Historial -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up-arrow"></i> Historial de Cambios
                    </h6>
                    @if(Route::has('trabajadores.historial-promociones'))
                        <a href="{{ route('trabajadores.historial-promociones', $trabajador) }}" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-eye"></i> Ver Todo
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-2">
                @if(isset($statsPromociones) && $statsPromociones['total_cambios'] > 0)
                    <!-- Estadísticas -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="text-success fw-bold">{{ $statsPromociones['promociones'] ?? 0 }}</div>
                            <small class="text-muted">Promociones</small>
                        </div>
                        <div class="col-4">
                            <div class="text-primary fw-bold">{{ $statsPromociones['transferencias'] ?? 0 }}</div>
                            <small class="text-muted">Transferencias</small>
                        </div>
                        <div class="col-4">
                            <div class="text-info fw-bold">{{ $statsPromociones['total_cambios'] ?? 0 }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>

                    <!-- Últimos Cambios -->
                    @if(isset($historialReciente) && $historialReciente->isNotEmpty())
                        <div class="timeline-sm">
                            @foreach($historialReciente->take(3) as $cambio)
                                <div class="timeline-item mb-2">
                                    <div class="d-flex">
                                        <div class="me-2">
                                            <span class="badge bg-{{ $cambio->color_tipo_cambio ?? 'secondary' }} rounded-pill p-1">
                                                @if($cambio->tipo_cambio == 'promocion')
                                                    <i class="bi bi-arrow-up"></i>
                                                @elseif($cambio->tipo_cambio == 'transferencia')
                                                    <i class="bi bi-arrow-left-right"></i>
                                                @elseif($cambio->tipo_cambio == 'aumento_sueldo')
                                                    <i class="bi bi-cash"></i>
                                                @else
                                                    <i class="bi bi-gear"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold">{{ $cambio->tipo_cambio_texto ?? 'Cambio' }}</div>
                                            <div class="text-muted small">
                                                {{ $cambio->categoriaNueva->nombre_categoria ?? 'Sin categoría' }}
                                            </div>
                                            <div class="text-success small">
                                                ${{ number_format($cambio->sueldo_nuevo ?? 0, 2) }}
                                                @if(isset($cambio->diferencia_sueldo) && $cambio->diferencia_sueldo != 0)
                                                    <small class="text-muted">
                                                        ({{ $cambio->diferencia_sueldo >= 0 ? '+' : '' }}${{ number_format($cambio->diferencia_sueldo, 2) }})
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                {{ $cambio->fecha_cambio ? $cambio->fecha_cambio->format('d/m/Y') : 'Fecha no disponible' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-graph-up fs-2 opacity-50"></i>
                            <p class="mb-0 small">Sin historial reciente</p>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-graph-up fs-2 opacity-50"></i>
                        <p class="mb-0 small">Sin historial de cambios</p>
                        <small class="text-muted">Los cambios aparecerán aquí cuando actualices los datos laborales</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ✅ MODAL CON SELECCIÓN MANUAL DE TURNO Y HORARIO DE DESCANSO -->
<div class="modal fade" id="modalEditarDatosLaborales" tabindex="-1" aria-labelledby="modalEditarDatosLaboralesLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarDatosLaboralesLabel">
                    <i class="bi bi-briefcase-fill"></i> Editar Datos Laborales
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('trabajadores.perfil.update-ficha', $trabajador) }}" method="POST" id="formEditarDatosLaborales">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Área y Categoría -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_area" class="form-label">Área *</label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <option value="">Seleccionar área...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}" 
                                            {{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->categoria->id_area == $area->id_area ? 'selected' : '' }}>
                                        {{ $area->nombre_area }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="id_categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccionar categoría...</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria }}" 
                                            {{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->id_categoria == $categoria->id_categoria ? 'selected' : '' }}>
                                        {{ $categoria->nombre_categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Sueldo -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="sueldo_diarios" class="form-label">Sueldo Diario *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="sueldo_diarios" 
                                       name="sueldo_diarios" 
                                       value="{{ $trabajador->fichaTecnica->sueldo_diarios ?? '' }}" 
                                       step="0.01"
                                       min="1"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ HORARIOS, TURNO MANUAL Y HORARIO DE DESCANSO -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                            <input type="text" 
                                   class="form-control formato-hora" 
                                   id="hora_entrada" 
                                   name="hora_entrada" 
                                   value="{{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->hora_entrada ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_entrada)->format('H:i') : '' }}" 
                                   placeholder="HH:MM"
                                   maxlength="5">
                            <div class="form-text">Formato: HH:MM (24 horas)</div>
                        </div>
                        <div class="col-md-3">
                            <label for="hora_salida" class="form-label">Hora de Salida</label>
                            <input type="text" 
                                   class="form-control formato-hora" 
                                   id="hora_salida" 
                                   name="hora_salida" 
                                   value="{{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->hora_salida ? \Carbon\Carbon::parse($trabajador->fichaTecnica->hora_salida)->format('H:i') : '' }}" 
                                   placeholder="HH:MM"
                                   maxlength="5">
                            <div class="form-text">Formato: HH:MM (24 horas)</div>
                        </div>
                        <div class="col-md-3">
                            <label for="turno" class="form-label">Turno *</label>
                            <select class="form-select" id="turno" name="turno" required>
                                <option value="">Seleccionar turno...</option>
                                @foreach(\App\Models\FichaTecnica::TURNOS_DISPONIBLES as $valor => $texto)
                                    <option value="{{ $valor }}" 
                                            {{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->turno == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Selecciona el turno manualmente</div>
                        </div>
                        <!-- ✅ NUEVO: Horario de Descanso -->
                        <div class="col-md-3">
                            <label for="horario_descanso" class="form-label">Horario Descanso *</label>
                            <input type="text" 
                                   class="form-control @error('horario_descanso') is-invalid @enderror" 
                                   id="horario_descanso" 
                                   name="horario_descanso" 
                                   value="{{ $trabajador->fichaTecnica->horario_descanso ?? '' }}" 
                                   placeholder="13:00 a 13:30"
                                   maxlength="100"
                                   required>
                            <div class="form-text">Ej: 13:00 a 13:30 de la tarde</div>
                            @error('horario_descanso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- ✅ SUGERENCIA AUTOMÁTICA (OPCIONAL) -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info" id="sugerenciaTurno" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    <div>
                                        <strong>Sugerencia:</strong> 
                                        <span id="sugerenciaTurnoTexto"></span>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btnAplicarSugerencia">
                                            Aplicar Sugerencia
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Días Laborables -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Días Laborables</label>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    @foreach(\App\Models\FichaTecnica::DIAS_SEMANA as $valor => $texto)
                                        <div class="col-md-3 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox"
                                                       id="dia_{{ $valor }}_perfil" 
                                                       name="dias_laborables[]" 
                                                       value="{{ $valor }}"
                                                       {{ $trabajador->fichaTecnica && in_array($valor, $trabajador->fichaTecnica->dias_laborables ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dia_{{ $valor }}_perfil">
                                                    {{ $texto }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Botones de selección rápida -->
                                <div class="mt-3 pt-2 border-top">
                                    <small class="text-muted">Selección rápida:</small>
                                    <div class="btn-group btn-group-sm ms-2" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="seleccionarDiasPerfil(['lunes', 'martes', 'miercoles', 'jueves', 'viernes'])">
                                            Lun-Vie
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="seleccionarDiasPerfil(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'])">
                                            Lun-Sáb
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="seleccionarTodosDiasPerfil()">
                                            Todos
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarDiasPerfil()">
                                            Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Beneficiario -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="beneficiario_nombre" class="form-label">Beneficiario Principal</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="beneficiario_nombre" 
                                   name="beneficiario_nombre" 
                                   value="{{ $trabajador->fichaTecnica->beneficiario_nombre ?? '' }}" 
                                   placeholder="Nombre del beneficiario"
                                   style="text-transform: uppercase">
                        </div>
                        <div class="col-md-4">
                            <label for="beneficiario_parentesco" class="form-label">Parentesco</label>
                            <select class="form-select" id="beneficiario_parentesco" name="beneficiario_parentesco">
                                <option value="">Seleccionar...</option>
                                @foreach(\App\Models\FichaTecnica::PARENTESCOS_BENEFICIARIO as $valor => $texto)
                                    <option value="{{ $valor }}" 
                                            {{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->beneficiario_parentesco == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Formación y Estudios -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="formacion" class="form-label">Formación</label>
                            <select class="form-select" id="formacion" name="formacion">
                                <option value="">Seleccionar...</option>
                                @php
                                    $opcionesFormacion = ['Sin estudios', 'Primaria', 'Secundaria', 'Preparatoria', 'Universidad', 'Posgrado'];
                                @endphp
                                @foreach($opcionesFormacion as $opcion)
                                    <option value="{{ $opcion }}" {{ $trabajador->fichaTecnica && $trabajador->fichaTecnica->formacion == $opcion ? 'selected' : '' }}>
                                        {{ $opcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="grado_estudios" class="form-label">Grado de Estudios</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="grado_estudios" 
                                   name="grado_estudios" 
                                   value="{{ $trabajador->fichaTecnica->grado_estudios ?? '' }}" 
                                   placeholder="Ej: Licenciatura en Administración">
                        </div>
                    </div>

                    <!-- Tipo de Cambio y Motivo -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo_cambio" class="form-label">Tipo de Cambio</label>
                            <select class="form-select" id="tipo_cambio" name="tipo_cambio">
                                <option value="">Seleccionar...</option>
                                <option value="promocion">Promoción</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="aumento_sueldo">Aumento de Sueldo</option>
                                <option value="reclasificacion">Reclasificación</option>
                                <option value="ajuste_salarial">Ajuste Salarial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="motivo_cambio" class="form-label">Motivo del Cambio</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="motivo_cambio" 
                                   name="motivo_cambio" 
                                   placeholder="Opcional"
                                   maxlength="255">
                        </div>
                    </div>

                    <!-- Vista Previa de Cálculos -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info" id="previewCalculos" style="display: none;">
                                <h6><i class="bi bi-calculator"></i> Vista Previa de Cálculos</h6>
                                <div class="row">
                                    <div class="col-3">
                                        <strong>Horas Diarias:</strong> <span id="preview-horas-diarias">-</span>
                                    </div>
                                    <div class="col-3">
                                        <strong>Horas Semanales:</strong> <span id="preview-horas-semanales">-</span>
                                    </div>
                                    <div class="col-3">
                                        <strong>Turno Seleccionado:</strong> <span id="preview-turno-seleccionado">-</span>
                                    </div>
                                    <div class="col-3">
                                        <strong>Días Laborables:</strong> <span id="preview-dias-count">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT PARA TURNO MANUAL -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const horaEntrada = document.getElementById('hora_entrada');
    const horaSalida = document.getElementById('hora_salida');
    const turnoSelect = document.getElementById('turno');
    const sugerenciaTurno = document.getElementById('sugerenciaTurno');
    const sugerenciaTurnoTexto = document.getElementById('sugerenciaTurnoTexto');
    const btnAplicarSugerencia = document.getElementById('btnAplicarSugerencia');
    
    // Función para calcular turno sugerido
    function calcularTurnoSugerido() {
        const entrada = horaEntrada.value;
        const salida = horaSalida.value;
        
        if (!entrada || !salida) {
            sugerenciaTurno.style.display = 'none';
            return;
        }
        
        const [horaEnt] = entrada.split(':').map(Number);
        const [horaSal] = salida.split(':').map(Number);
        
        let turnoSugerido = 'mixto';
        let descripcion = '';
        
        // Si cruza medianoche es nocturno
        if (horaSal <= horaEnt) {
            turnoSugerido = 'nocturno';
            descripcion = 'Basado en horario que cruza medianoche';
        }
        // Diurno: 06:00 - 18:00
        else if (horaEnt >= 6 && horaSal <= 18) {
            turnoSugerido = 'diurno';
            descripcion = 'Basado en horario de 06:00 a 18:00';
        }
        // Nocturno: 18:00 - 06:00
        else if (horaEnt >= 18 || horaSal <= 6) {
            turnoSugerido = 'nocturno';
            descripcion = 'Basado en horario nocturno';
        }
        // Mixto: otros horarios
        else {
            turnoSugerido = 'mixto';
            descripcion = 'Basado en horario mixto/rotativo';
        }
        
        // Mostrar sugerencia solo si es diferente al seleccionado
        if (turnoSelect.value !== turnoSugerido) {
            const textoTurno = turnoSelect.querySelector(`option[value="${turnoSugerido}"]`).textContent;
            sugerenciaTurnoTexto.textContent = `${textoTurno} - ${descripcion}`;
            sugerenciaTurno.style.display = 'block';
            
            // Aplicar sugerencia
            btnAplicarSugerencia.onclick = function() {
                turnoSelect.value = turnoSugerido;
                sugerenciaTurno.style.display = 'none';
                actualizarPreviewCalculos();
            };
        } else {
            sugerenciaTurno.style.display = 'none';
        }
    }
    
    // Funciones para días laborables
    window.seleccionarDiasPerfil = function(dias) {
        limpiarDiasPerfil();
        dias.forEach(dia => {
            const checkbox = document.getElementById(`dia_${dia}_perfil`);
            if (checkbox) checkbox.checked = true;
        });
        actualizarPreviewCalculos();
    };

    window.seleccionarTodosDiasPerfil = function() {
        const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = true);
        actualizarPreviewCalculos();
    };

    window.limpiarDiasPerfil = function() {
        const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        actualizarPreviewCalculos();
    };

    // Función para actualizar preview
    function actualizarPreviewCalculos() {
        const entrada = horaEntrada.value;
        const salida = horaSalida.value;
        const turnoSeleccionado = turnoSelect.value;
        const diasSeleccionados = Array.from(document.querySelectorAll('input[name="dias_laborables[]"]:checked')).length;
        
        let horasDiarias = 0;
        
        if (entrada && salida && window.FormatoGlobal) {
            if (window.FormatoGlobal.validarFormatoHora(entrada) && window.FormatoGlobal.validarFormatoHora(salida)) {
                horasDiarias = window.FormatoGlobal.calcularHoras(entrada, salida);
            }
        }
        
        const horasSemanales = horasDiarias * diasSeleccionados;
        
        // Actualizar preview
        document.getElementById('preview-horas-diarias').textContent = horasDiarias > 0 ? horasDiarias.toFixed(1) + 'h' : '-';
        document.getElementById('preview-horas-semanales').textContent = horasSemanales > 0 ? horasSemanales.toFixed(1) + 'h' : '-';
        document.getElementById('preview-turno-seleccionado').textContent = turnoSeleccionado ? 
            turnoSelect.querySelector(`option[value="${turnoSeleccionado}"]`).textContent : '-';
        document.getElementById('preview-dias-count').textContent = diasSeleccionados || '-';
        
        // Mostrar/ocultar preview
        const previewDiv = document.getElementById('previewCalculos');
        if (entrada || salida || turnoSeleccionado || diasSeleccionados > 0) {
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    // Event listeners
    horaEntrada.addEventListener('input', function() {
        calcularTurnoSugerido();
        actualizarPreviewCalculos();
    });
    
    horaSalida.addEventListener('input', function() {
        calcularTurnoSugerido();
        actualizarPreviewCalculos();
    });
    
    turnoSelect.addEventListener('change', function() {
        sugerenciaTurno.style.display = 'none';
        actualizarPreviewCalculos();
    });
    
    document.querySelectorAll('input[name="dias_laborables[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', actualizarPreviewCalculos);
    });

    // Limpiar modal al cerrar
    const modal = document.getElementById('modalEditarDatosLaborales');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            sugerenciaTurno.style.display = 'none';
            const previewDiv = document.getElementById('previewCalculos');
            if (previewDiv) previewDiv.style.display = 'none';
        });
    }
});
</script>