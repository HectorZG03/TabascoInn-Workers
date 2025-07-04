@props(['permisoId'])

<!-- Modal para subir archivo -->
<div class="modal fade" id="modalSubirArchivo{{ $permisoId }}" tabindex="-1" aria-labelledby="modalSubirArchivoLabel{{ $permisoId }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('permisos.subirArchivo', $permisoId) }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalSubirArchivoLabel{{ $permisoId }}">Subir archivo del permiso #{{ $permisoId }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="archivo_permiso_{{ $permisoId }}" class="form-label">Archivo (PDF, JPG, PNG, máx 5MB):</label>
                    <input type="file" name="archivo_permiso" id="archivo_permiso_{{ $permisoId }}" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir archivo</button>
            </div>
        </form>
    </div>
</div>
