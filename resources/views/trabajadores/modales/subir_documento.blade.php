{{-- resources/views/trabajadores/modales/subir-documento.blade.php --}}

<!-- Modal para Subir Documentos -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('trabajadores.perfil.upload-document', $trabajador) }}" method="POST" enctype="multipart/form-data" id="form-subir-documento">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="bi bi-cloud-upload"></i> Subir Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tipo_documento" id="modal_tipo_documento">
                    
                    <div class="alert alert-info" id="alert-info-documento">
                        <i class="bi bi-info-circle"></i>
                        Documento: <strong id="modal_nombre_documento"></strong>
                    </div>

                    <div class="mb-3">
                        <label for="documento" class="form-label">Seleccionar Archivo</label>
                        <input type="file" 
                               class="form-control" 
                               id="documento" 
                               name="documento" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               required>
                        <div class="form-text">
                            Formatos permitidos: PDF, JPG, JPEG, PNG. Tamaño máximo: 2MB
                        </div>
                        <div class="invalid-feedback" id="error-archivo"></div>
                    </div>

                    <!-- Vista previa del archivo (opcional) -->
                    <div id="preview-container" class="mt-3" style="display: none;">
                        <h6>Vista previa:</h6>
                        <div id="preview-content"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-subir-documento">
                        <i class="bi bi-cloud-upload"></i> Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript específico del modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadModal = document.getElementById('uploadModal');
    const modalTipoDocumento = document.getElementById('modal_tipo_documento');
    const modalNombreDocumento = document.getElementById('modal_nombre_documento');
    const archivoInput = document.getElementById('documento');
    const formSubir = document.getElementById('form-subir-documento');
    const btnSubir = document.getElementById('btn-subir-documento');
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');
    const errorArchivo = document.getElementById('error-archivo');

    /**
     * Configurar modal cuando se abre
     */
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const tipo = button?.getAttribute('data-tipo');
            const nombre = button?.getAttribute('data-nombre');
            
            // Limpiar formulario
            resetearFormulario();
            
            // Establecer valores
            if (modalTipoDocumento && tipo) {
                modalTipoDocumento.value = tipo;
            }
            
            if (modalNombreDocumento && nombre) {
                modalNombreDocumento.textContent = nombre;
            }
        });

        // Limpiar al cerrar
        uploadModal.addEventListener('hidden.bs.modal', function () {
            resetearFormulario();
        });
    }

    /**
     * Validar archivo seleccionado
     */
    if (archivoInput) {
        archivoInput.addEventListener('change', function() {
            validarArchivo(this.files[0]);
        });
    }

    /**
     * Manejar envío del formulario
     */
    if (formSubir) {
        formSubir.addEventListener('submit', function(e) {
            const archivo = archivoInput.files[0];
            
            if (!validarArchivo(archivo)) {
                e.preventDefault();
                return false;
            }

            // Mostrar loading en el botón
            mostrarCargando(true);
        });
    }

    /**
     * Validar archivo seleccionado
     */
    function validarArchivo(archivo) {
        // Limpiar errores previos
        limpiarErrores();

        if (!archivo) {
            mostrarError('Debe seleccionar un archivo');
            return false;
        }

        // Validar tipo de archivo
        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!tiposPermitidos.includes(archivo.type)) {
            mostrarError('Tipo de archivo no permitido. Solo se permiten PDF, JPG, JPEG, PNG');
            return false;
        }

        // Validar tamaño (2MB = 2 * 1024 * 1024 bytes)
        const tamañoMaximo = 2 * 1024 * 1024;
        if (archivo.size > tamañoMaximo) {
            mostrarError('El archivo es demasiado grande. Máximo permitido: 2MB');
            return false;
        }

        // Mostrar vista previa si es una imagen
        if (archivo.type.startsWith('image/')) {
            mostrarVistaPrevia(archivo);
        }

        return true;
    }

    /**
     * Mostrar vista previa para imágenes
     */
    function mostrarVistaPrevia(archivo) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewContent.innerHTML = `
                <img src="${e.target.result}" 
                     class="img-thumbnail" 
                     style="max-width: 200px; max-height: 200px;"
                     alt="Vista previa">
                <p class="small text-muted mt-2">
                    <strong>Archivo:</strong> ${archivo.name}<br>
                    <strong>Tamaño:</strong> ${formatearTamaño(archivo.size)}
                </p>
            `;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(archivo);
    }

    /**
     * Mostrar error de validación
     */
    function mostrarError(mensaje) {
        if (errorArchivo) {
            errorArchivo.textContent = mensaje;
            errorArchivo.style.display = 'block';
        }
        
        if (archivoInput) {
            archivoInput.classList.add('is-invalid');
        }
        
        if (btnSubir) {
            btnSubir.disabled = true;
        }
    }

    /**
     * Limpiar errores
     */
    function limpiarErrores() {
        if (errorArchivo) {
            errorArchivo.textContent = '';
            errorArchivo.style.display = 'none';
        }
        
        if (archivoInput) {
            archivoInput.classList.remove('is-invalid');
        }
        
        if (btnSubir) {
            btnSubir.disabled = false;
        }
    }

    /**
     * Mostrar estado de carga
     */
    function mostrarCargando(cargando) {
        if (!btnSubir) return;

        if (cargando) {
            btnSubir.disabled = true;
            btnSubir.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Subiendo...
            `;
        } else {
            btnSubir.disabled = false;
            btnSubir.innerHTML = `
                <i class="bi bi-cloud-upload"></i> Subir Documento
            `;
        }
    }

    /**
     * Resetear formulario
     */
    function resetearFormulario() {
        if (formSubir) {
            formSubir.reset();
        }
        
        limpiarErrores();
        mostrarCargando(false);
        
        if (previewContainer) {
            previewContainer.style.display = 'none';
            previewContent.innerHTML = '';
        }
    }

    /**
     * Formatear tamaño de archivo
     */
    function formatearTamaño(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const tamaños = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + tamaños[i];
    }

    // Mensaje de debug
    console.log('✅ Modal de Subir Documento inicializado correctamente');
});
</script>