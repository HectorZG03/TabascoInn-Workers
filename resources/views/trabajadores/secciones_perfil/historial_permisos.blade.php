<div class="card shadow border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>Historial de Permisos
        </h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="tipo-permiso" class="form-label">Tipo</label>
                <select id="tipo-permiso" class="form-select">
                    <option value="">Todos</option>
                    @foreach($tiposPermisos as $key => $tipo)
                        <option value="{{ $key }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="estado-permiso" class="form-label">Estado</label>
                <select id="estado-permiso" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="fecha-desde" class="form-label">Desde</label>
                <input type="date" id="fecha-desde" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="fecha-hasta" class="form-label">Hasta</label>
                <input type="date" id="fecha-hasta" class="form-control">
            </div>
            <div class="col-md-12 text-end">
                <button id="filtrar-permisos" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </div>

        <!-- Resultados -->
        <div id="contenedor-permisos">
            @if($permisos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permisos as $permiso)
                            <tr>
                                <td>{{ $permiso->tipo_permiso_texto }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" 
                                         title="{{ $permiso->motivo }}">
                                        {{ $permiso->motivo }}
                                    </div>
                                </td>
                                <td>{{ $permiso->fecha_inicio->format('d/m/Y') }}</td>
                                <td>{{ $permiso->fecha_fin->format('d/m/Y') }}</td>
                                <td>{{ $permiso->dias_de_permiso }}</td>
                                <td>
                                    <span class="badge 
                                        @if($permiso->estatus_permiso == 'activo') bg-success
                                        @elseif($permiso->estatus_permiso == 'finalizado') bg-info
                                        @else bg-secondary
                                        @endif">
                                        {{ $permiso->estatus_permiso_texto }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetallePermiso"
                                            onclick="verDetallePermiso({{ $permiso->id_permiso }})"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $permisos->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i> No se encontraron registros de permisos
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ MODAL COMPLETO EN LA VISTA --}}
<div class="modal fade" id="modalDetallePermiso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detalle del Permiso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Loading spinner (se muestra inicialmente) --}}
                <div id="permiso-loading" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>

                {{-- Contenido del modal (se muestra cuando se cargan los datos) --}}
                <div id="permiso-content" style="display: none;">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID del Permiso:</td>
                                    <td><span id="permiso-id"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Trabajador:</td>
                                    <td><span id="permiso-trabajador"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tipo de Permiso:</td>
                                    <td><span id="permiso-tipo" class="badge bg-info"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Estado:</td>
                                    <td><span id="permiso-estado" class="badge"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Inicio:</td>
                                    <td><span id="permiso-fecha-inicio"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Fin:</td>
                                    <td><span id="permiso-fecha-fin"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Días de Permiso:</td>
                                    <td><span id="permiso-dias" class="badge bg-secondary"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Solicitud:</td>
                                    <td><span id="permiso-fecha-solicitud"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold">Motivo del Permiso:</h6>
                            <div id="permiso-motivo" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                    
                    <div id="permiso-observaciones-container" class="row mt-3" style="display: none;">
                        <div class="col-12">
                            <h6 class="fw-bold">Observaciones:</h6>
                            <div id="permiso-observaciones" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                </div>

                {{-- Error state --}}
                <div id="permiso-error" class="alert alert-danger" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar los detalles del permiso
                </div>
            </div>
        </div>
    </div>
</div>