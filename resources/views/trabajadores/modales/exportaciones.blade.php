{{-- ✅ MODAL DE EXPORTACIÓN DE LISTAS --}}
<div class="modal fade" id="modalExportaciones" tabindex="-1" aria-labelledby="modalExportacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalExportacionesLabel">
                    <i class="bi bi-download me-1"></i> Exportar Listas de Trabajadores
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form method="GET" id="formExportaciones" action="{{ route('exportaciones.exportar', ['tipo' => 'cumpleaneros']) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="tipoExportacion" class="form-label">Tipo de lista a exportar</label>
                        <select name="tipo" id="tipoExportacion" class="form-select" required>
                            <option value="" disabled selected>Seleccione una opción</option>
                            <option value="cumpleaneros">🎂 Cumpleañeros por mes</option>
                            <option value="con-permisos">🟡 Con permisos activos</option>
                            <option value="bajas-temporales">💤 Bajas temporales</option>
                            <option value="con-horas-extra">⏱️ Con horas extra</option>
                            <option value="activos">✅ Todos los activos</option>
                        </select>
                    </div>

                    <div id="mesCumple" class="mb-3 d-none">
                        <label for="mes" class="form-label">Mes</label>
                        <select name="mes" class="form-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->monthName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="formato" class="form-label">Formato</label>
                        <select name="formato" id="formato" class="form-select" required>
                            <option value="pdf">📄 PDF</option>
                            <option value="excel">📊 Excel</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" id="btnExportar">
                    <i class="bi bi-check2-circle"></i> Exportar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Script rápido para mostrar campo "mes" solo cuando es cumpleaños --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipoExportacion');
    const mesField = document.getElementById('mesCumple');
    const form = document.getElementById('formExportaciones');
    const btnExportar = document.getElementById('btnExportar');

    tipoSelect.addEventListener('change', function () {
        const tipo = this.value;
        mesField.classList.toggle('d-none', tipo !== 'cumpleaneros');
        form.setAttribute('action', `/exportar/lista/${tipo}`);
    });

    btnExportar.addEventListener('click', () => {
        form.submit();
    });
});
</script>
@endpush
