

<div class="modal fade" id="modalRenovarContrato" tabindex="-1" aria-labelledby="modalRenovarContratoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white" id="modalRenovarContratoLabel">
                    <i class="bi bi-arrow-repeat"></i> Renovar Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('trabajadores.contratos.renovar', [$trabajador, $contrato]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Estás renovando el contrato que finaliza el 
                        <strong>{{ $contrato->fecha_fin_contrato->format('d/m/Y') }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Inicio</label>
                        <input type="date" 
                               name="fecha_inicio" 
                               class="form-control"
                               value="{{ now()->addDay()->format('Y-m-d') }}"
                               min="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Fin</label>
                        <input type="date" 
                               name="fecha_fin" 
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Duración</label>
                        <select name="tipo_duracion" class="form-select" required>
                            <option value="dias">Días</option>
                            <option value="meses" selected>Meses</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Renovar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calcular fecha fin por defecto (6 meses después de hoy)
    const fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    const fechaFinInput = document.querySelector('input[name="fecha_fin"]');
    
    fechaInicioInput.addEventListener('change', function() {
        const fechaInicio = new Date(this.value);
        const fechaFin = new Date(fechaInicio);
        fechaFin.setMonth(fechaFin.getMonth() + 6);
        
        // Formatear a YYYY-MM-DD
        const formattedDate = fechaFin.toISOString().split('T')[0];
        fechaFinInput.value = formattedDate;
        fechaFinInput.min = formattedDate;
    });
    
    // Inicializar con fecha por defecto
    const event = new Event('change');
    fechaInicioInput.dispatchEvent(event);
});
</script>