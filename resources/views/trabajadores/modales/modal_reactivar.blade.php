<!-- Modal: Reactivar Trabajador -->
<div class="modal fade" id="modalReactivar{{ $despido->id_baja }}" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bi bi-arrow-clockwise"></i> Reactivar Trabajador
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('despidos.cancelar', $despido->id_baja) }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="modal-body">
          <p class="fw-semibold mb-3">
            Estás a punto de reactivar al trabajador 
            <span class="text-success">{{ $despido->trabajador->nombre_completo }}</span>.
          </p>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item">
              <strong>Fecha de Baja:</strong> {{ \Carbon\Carbon::parse($despido->fecha_baja)->format('d/m/Y') }}
            </li>
            <li class="list-group-item">
              <strong>Tipo de Baja:</strong> {{ ucfirst($despido->tipo_baja) }}
            </li>
            <li class="list-group-item">
              <strong>Motivo:</strong> {{ $despido->motivo }}
            </li>

            @if($despido->esTemporal() && $despido->fecha_reintegro)
              <li class="list-group-item">
                <strong>Fecha de Reintegro:</strong> {{ $despido->fecha_reintegro->format('d/m/Y') }}
              </li>
            @endif
          </ul>

          @if($despido->esTemporal() && $despido->fecha_reintegro && \Carbon\Carbon::now()->lt($despido->fecha_reintegro))
            <div class="alert alert-warning mt-3">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Nota:</strong> Estás reactivando al trabajador antes de la fecha de reintegro programada 
              (<strong>{{ $despido->fecha_reintegro->format('d/m/Y') }}</strong>).
            </div>
          @endif

          <div class="mb-3 mt-4">
            <label for="motivo_cancelacion{{ $despido->id_baja }}" class="form-label">
              Motivo de Reactivación (opcional)
            </label>
            <textarea class="form-control" name="motivo_cancelacion" id="motivo_cancelacion{{ $despido->id_baja }}" rows="2" maxlength="255"></textarea>
            <small class="form-text text-muted">Máximo 255 caracteres.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Confirmar Reactivación
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
