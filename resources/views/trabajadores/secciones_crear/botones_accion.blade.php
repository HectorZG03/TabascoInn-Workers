<!-- ✅ SECCIÓN: BOTONES DE ACCIÓN ACTUALIZADOS -->
<div class="card shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <div class="text-end">
                <button type="submit" class="btn btn-success btn-lg px-4" id="btnGuardar">
                    <i class="bi bi-arrow-right-circle me-2"></i> 
                    Continuar: Estado y Contrato
                </button>
                <div class="mt-1">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Configurarás el estado inicial en el siguiente paso
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>