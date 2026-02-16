<div class="modal fade" id="modalEditarHoras{{ $registro->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('trabajadores.horas-extra.actualizar', [$trabajador, $registro]) }}">
                @csrf
                @method('PUT')
                
                <div class="modal-header bg-{{ $registro->color_tipo }} text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i> Editar Horas Extra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $trabajador->nombre_completo }}</h6>
                                <small class="text-muted">
                                    {{ $registro->tipo_texto }} registrado el {{ $registro->fecha_formateada }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-{{ $registro->color_tipo }} fs-6">
                                    {{ $registro->horas_formateadas }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Horas</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="horas"
                                   value="{{ $registro->horas }}"
                                   min="0.1" 
                                   max="24" 
                                   step="0.1"
                                   required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="text" 
                                   class="form-control formato-fecha" 
                                   name="fecha"
                                   value="{{ $registro->fecha->format('d/m/Y') }}"
                                   placeholder="DD/MM/YYYY"
                                   required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" 
                                      name="descripcion" 
                                      rows="3"
                                      maxlength="200">{{ $registro->descripcion }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Al actualizar este registro se recalculará el saldo de horas extra del trabajador.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>