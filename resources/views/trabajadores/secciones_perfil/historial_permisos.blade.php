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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permisos as $permiso)
                            <tr>
                                <td>{{ $permiso->tipo_permiso_texto }}</td>
                                <td>{{ $permiso->motivo }}</td>
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