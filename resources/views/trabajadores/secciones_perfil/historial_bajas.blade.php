<div class="card shadow border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-person-x me-2"></i>Historial de Bajas
        </h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="estado-baja" class="form-label">Estado</label>
                <select id="estado-baja" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo">Activas</option>
                    <option value="cancelado">Canceladas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="condicion-baja" class="form-label">Condición</label>
                <select id="condicion-baja" class="form-select">
                    <option value="">Todas</option>
                    @foreach($condiciones as $condicion)
                        <option value="{{ $condicion }}">{{ $condicion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tipo-baja-filtro" class="form-label">Tipo</label>
                <select id="tipo-baja-filtro" class="form-select">
                    <option value="">Todos</option>
                    @foreach($tiposBaja as $key => $tipo)
                        <option value="{{ $key }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="fecha-desde-baja" class="form-label">Desde</label>
                <input type="date" id="fecha-desde-baja" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="fecha-hasta-baja" class="form-label">Hasta</label>
                <input type="date" id="fecha-hasta-baja" class="form-control">
            </div>
            <div class="col-md-9"></div>
            <div class="col-md-12 text-end">
                <button id="filtrar-bajas" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </div>

        <!-- Resultados -->
        <div id="contenedor-bajas">
            @if($bajas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha Baja</th>
                                <th>Condición</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Fecha Reintegro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bajas as $baja)
                            <tr>
                                <td>
                                    <strong>{{ $baja->fecha_baja->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $baja->fecha_baja->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $baja->condicion_salida }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($baja->tipo_baja == 'temporal') bg-warning text-dark
                                        @else bg-info
                                        @endif">
                                        {{ $baja->tipo_baja_texto }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" 
                                         title="{{ $baja->motivo }}">
                                        {{ $baja->motivo }}
                                    </div>
                                </td>
                                <td>
                                    @if($baja->es_activo)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $baja->estado_texto }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ $baja->estado_texto }}
                                        </span>
                                        @if($baja->fecha_cancelacion)
                                            <br>
                                            <small class="text-muted">
                                                {{ $baja->fecha_cancelacion->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($baja->fecha_reintegro)
                                        <strong>{{ $baja->fecha_reintegro->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            @if($baja->fecha_reintegro->isPast())
                                                Venció {{ $baja->fecha_reintegro->diffForHumans() }}
                                            @else
                                                {{ $baja->fecha_reintegro->diffForHumans() }}
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetalleBaja"
                                            onclick="verDetalleBaja({{ $baja->id_baja }})"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $bajas->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i> 
                    Este trabajador no tiene historial de bajas registradas
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ MODAL COMPLETO EN LA VISTA --}}
<div class="modal fade" id="modalDetalleBaja" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detalle de la Baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Loading spinner (se muestra inicialmente) --}}
                <div id="baja-loading" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>

                {{-- Contenido del modal (se muestra cuando se cargan los datos) --}}
                <div id="baja-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID de la Baja:</td>
                                    <td><span id="baja-id"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Trabajador:</td>
                                    <td><span id="baja-trabajador"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Baja:</td>
                                    <td>
                                        <span id="baja-fecha" class="badge bg-danger"></span>
                                        <br><small id="baja-fecha-relativa" class="text-muted"></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Condición de Salida:</td>
                                    <td><span id="baja-condicion" class="badge bg-secondary"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tipo de Baja:</td>
                                    <td><span id="baja-tipo" class="badge bg-info"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Estado Actual:</td>
                                    <td><span id="baja-estado" class="badge"></span></td>
                                </tr>
                                <tr id="baja-reintegro-row">
                                    <td class="fw-bold">Fecha de Reintegro:</td>
                                    <td>
                                        <span id="baja-reintegro" class="badge bg-warning text-dark"></span>
                                        <br><small id="baja-reintegro-relativa" class="text-muted"></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Registro:</td>
                                    <td><span id="baja-fecha-creacion"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold">Motivo de la Baja:</h6>
                            <div id="baja-motivo" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                    
                    <div id="baja-observaciones-container" class="row mt-3" style="display: none;">
                        <div class="col-12">
                            <h6 class="fw-bold">Observaciones:</h6>
                            <div id="baja-observaciones" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                    
                    <div id="baja-cancelacion-container" class="row mt-3" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Información de Cancelación
                                </h6>
                                <p class="mb-1"><strong>Fecha:</strong> <span id="baja-fecha-cancelacion"></span></p>
                                <p id="baja-cancelado-por-row" class="mb-1" style="display: none;">
                                    <strong>Cancelado por:</strong> <span id="baja-cancelado-por"></span>
                                </p>
                                <div id="baja-motivo-cancelacion-container" style="display: none;">
                                    <p class="mb-1"><strong>Motivo de Cancelación:</strong></p>
                                    <div id="baja-motivo-cancelacion" class="bg-light p-2 rounded mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Error state --}}
                <div id="baja-error" class="alert alert-danger" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar los detalles de la baja
                </div>
            </div>
        </div>
    </div>
</div>