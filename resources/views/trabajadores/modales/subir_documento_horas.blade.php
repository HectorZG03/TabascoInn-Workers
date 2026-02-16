<!-- Modal Subir Documento Horas Extra -->
<div class="modal fade" id="modalDocumento{{ $registro->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload text-info"></i>
                    Subir Documento - Horas Compensadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('trabajadores.horas-extra.subir-documento', [$trabajador, $registro]) }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf
                
                <div class="modal-body">
                    <!-- Información del registro -->
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-6">
                                <strong>Fecha:</strong> {{ $registro->fecha_formateada }}
                            </div>
                            <div class="col-6">
                                <strong>Horas:</strong> {{ $registro->horas_formateadas }}
                            </div>
                        </div>
                        @if($registro->descripcion)
                            <div class="mt-2">
                                <strong>Descripción:</strong> {{ $registro->descripcion }}
                            </div>
                        @endif
                    </div>

                    <!-- Campo para subir archivo -->
                    <div class="mb-3">
                        <label for="documento{{ $registro->id }}" class="form-label">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            Seleccionar Documento
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="documento{{ $registro->id }}" 
                               name="documento"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>
                        <div class="form-text">
                            Formatos permitidos: PDF, JPG, PNG. Tamaño máximo: 10MB
                        </div>
                    </div>

                    <!-- Preview del archivo -->
                    <div id="preview{{ $registro->id }}" class="d-none">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark me-2"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" id="fileName{{ $registro->id }}"></div>
                                    <small class="text-muted" id="fileSize{{ $registro->id }}"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info" id="btnSubir{{ $registro->id }}">
                        <i class="bi bi-upload"></i> Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('documento{{ $registro->id }}');
    const preview = document.getElementById('preview{{ $registro->id }}');
    const fileName = document.getElementById('fileName{{ $registro->id }}');
    const fileSize = document.getElementById('fileSize{{ $registro->id }}');
    const btnSubir = document.getElementById('btnSubir{{ $registro->id }}');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Mostrar preview
                preview.classList.remove('d-none');
                fileName.textContent = file.name;
                
                // Formatear tamaño
                const size = file.size;
                let sizeText;
                if (size >= 1048576) {
                    sizeText = (size / 1048576).toFixed(2) + ' MB';
                } else if (size >= 1024) {
                    sizeText = (size / 1024).toFixed(2) + ' KB';
                } else {
                    sizeText = size + ' B';
                }
                fileSize.textContent = sizeText;
                
                // Validar tamaño
                if (size > 10485760) { // 10MB
                    fileInput.classList.add('is-invalid');
                    preview.querySelector('.alert').className = 'alert alert-danger';
                    fileSize.textContent += ' - Archivo demasiado grande';
                    btnSubir.disabled = true;
                    
                    // Mostrar feedback de error
                    let errorFeedback = fileInput.parentNode.querySelector('.invalid-feedback');
                    if (!errorFeedback) {
                        errorFeedback = document.createElement('div');
                        errorFeedback.className = 'invalid-feedback';
                        fileInput.parentNode.appendChild(errorFeedback);
                    }
                    errorFeedback.textContent = 'El archivo es demasiado grande. Máximo 10MB.';
                } else {
                    fileInput.classList.remove('is-invalid');
                    fileInput.classList.add('is-valid');
                    preview.querySelector('.alert').className = 'alert alert-info';
                    btnSubir.disabled = false;
                    
                    // Remover feedback de error
                    const errorFeedback = fileInput.parentNode.querySelector('.invalid-feedback');
                    if (errorFeedback) {
                        errorFeedback.remove();
                    }
                }
            } else {
                preview.classList.add('d-none');
                fileInput.classList.remove('is-valid', 'is-invalid');
                btnSubir.disabled = false;
            }
        });
    }
    
    // Manejar envío del formulario
    const form = document.querySelector('#modalDocumento{{ $registro->id }} form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Subiendo...';
            }
        });
    }
});
</script>