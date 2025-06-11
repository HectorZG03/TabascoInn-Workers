<!-- ✅ SECCIÓN: BOTONES DE ACCIÓN -->
<div class="card shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <div>
                <button type="submit" class="btn btn-success btn-lg" id="btnGuardar">
                    <i class="bi bi-file-earmark-text me-1"></i> Continuar con Contrato
                </button>
            </div>
        </div>
    </div>
</div>