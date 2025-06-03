{{-- ✅ MODAL DE IMPORTACIÓN MASIVA DE TRABAJADORES --}}
{{-- Archivo: resources/views/trabajadores/modales/importacion.blade.php --}}

<div class="modal fade" id="modalImportacion" tabindex="-1" aria-labelledby="modalImportacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalImportacionLabel">
                    <i class="bi bi-cloud-upload"></i> Importación Masiva de Trabajadores
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Pasos del proceso -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="step-item text-center">
                                <div class="step-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px; border-radius: 50%;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div class="step-text small">Descargar<br>Plantilla</div>
                            </div>
                            <div class="flex-fill mx-3" style="height: 2px; background-color: #dee2e6; margin-top: -20px;"></div>
                            <div class="step-item text-center">
                                <div class="step-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px; border-radius: 50%;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div class="step-text small">Completar<br>Datos</div>
                            </div>
                            <div class="flex-fill mx-3" style="height: 2px; background-color: #dee2e6; margin-top: -20px;"></div>
                            <div class="step-item text-center">
                                <div class="step-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px; border-radius: 50%;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div class="step-text small">Subir<br>Archivo</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones importantes -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle"></i> Instrucciones Importantes
                    </h6>
                    <ul class="mb-0 small">
                        <li>Primero <strong>descarga la plantilla</strong> Excel con el formato correcto</li>
                        <li>Completa <strong>todos los campos obligatorios</strong> marcados con asterisco (*)</li>
                        <li>Respeta el <strong>formato de fechas</strong>: YYYY-MM-DD (ej: 2024-01-15)</li>
                        <li>Los nombres de <strong>área y categoría</strong> deben existir en el sistema</li>
                        <li>No modifiques el <strong>orden de las columnas</strong></li>
                        <li>El archivo debe ser de tipo <strong>Excel (.xlsx)</strong></li>
                    </ul>
                </div>

                <!-- Sección 1: Descargar Plantilla -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-download"></i> Paso 1: Descargar Plantilla Excel
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Descarga la plantilla con el formato correcto y ejemplos de datos.
                        </p>
                        <a href="{{ route('import.plantilla') }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Descargar Plantilla Excel
                        </a>
                        <small class="text-muted ms-2">
                            <i class="bi bi-info-circle"></i> Se abrirá en una nueva ventana
                        </small>
                    </div>
                </div>

                <!-- Sección 2: Subir Archivo -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-cloud-upload"></i> Paso 2: Subir Archivo Completado
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formImportacion" action="{{ route('import.procesar') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Campo de archivo -->
                            <div class="mb-3">
                                <label for="archivo_excel" class="form-label">Archivo Excel *</label>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control" 
                                           id="archivo_excel" 
                                           name="archivo_excel" 
                                           accept=".xlsx,.xls"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" id="btnLimpiarArchivo">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Formato permitido: Excel (.xlsx, .xls) - Máximo 10MB
                                </div>
                            </div>

                            <!-- Vista previa del archivo -->
                            <div id="vistaArchivo" class="d-none">
                                <div class="alert alert-success">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-excel fs-4 me-3 text-success"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium" id="nombreArchivo"></div>
                                            <div class="small text-muted" id="infoArchivo"></div>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">Listo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones avanzadas -->
                            <div class="mb-3">
                                <h6 class="mb-2">Opciones de Importación</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="validarDuplicados" name="validar_duplicados" checked>
                                    <label class="form-check-label" for="validarDuplicados">
                                        Omitir trabajadores duplicados (CURP o RFC existente)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enviarNotificacion" name="enviar_notificacion">
                                    <label class="form-check-label" for="enviarNotificacion">
                                        Enviar notificación por correo al completar la importación
                                    </label>
                                </div>
                            </div>

                            <!-- Advertencias -->
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle"></i> Antes de continuar
                                </h6>
                                <ul class="mb-0 small">
                                    <li>Verifica que todos los datos estén completos y correctos</li>
                                    <li>Este proceso no se puede deshacer fácilmente</li>
                                    <li>Los trabajadores se crearán con estado "activo" por defecto</li>
                                    <li>Podrás editar los datos individualmente después de la importación</li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Progreso de importación -->
                <div id="progresoImportacion" class="d-none mt-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Procesando...</span>
                            </div>
                            <h6>Procesando importación...</h6>
                            <p class="text-muted mb-0">Por favor espera mientras se procesan los datos del Excel</p>
                            <div class="progress mt-3" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" 
                                     style="width: 100%"></div>
                            </div>
                            <div class="small text-muted mt-2">
                                <i class="bi bi-clock"></i> Este proceso puede tardar varios minutos dependiendo de la cantidad de trabajadores
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Cancelar
                </button>
                <button type="submit" form="formImportacion" class="btn btn-primary" id="btnProcesar" disabled>
                    <i class="bi bi-cloud-upload"></i> Procesar Importación
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript para el modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const archivoInput = document.getElementById('archivo_excel');
    const btnLimpiar = document.getElementById('btnLimpiarArchivo');
    const btnProcesar = document.getElementById('btnProcesar');
    const vistaArchivo = document.getElementById('vistaArchivo');
    const nombreArchivo = document.getElementById('nombreArchivo');
    const infoArchivo = document.getElementById('infoArchivo');
    const formImportacion = document.getElementById('formImportacion');
    const progresoImportacion = document.getElementById('progresoImportacion');
    const modalElement = document.getElementById('modalImportacion');

    // Manejar selección de archivo
    if (archivoInput) {
        archivoInput.addEventListener('change', function() {
            const archivo = this.files[0];
            
            if (archivo) {
                // Validar tipo de archivo
                const extension = archivo.name.split('.').pop().toLowerCase();
                const tiposPermitidos = ['xlsx', 'xls'];
                
                if (!tiposPermitidos.includes(extension)) {
                    alert('Por favor selecciona un archivo Excel válido (.xlsx o .xls)');
                    this.value = '';
                    return;
                }
                
                // Validar tamaño (10MB máximo)
                if (archivo.size > 10 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. Máximo 10MB permitido.');
                    this.value = '';
                    return;
                }
                
                // Mostrar información del archivo
                nombreArchivo.textContent = archivo.name;
                infoArchivo.textContent = `Tamaño: ${(archivo.size / 1024 / 1024).toFixed(2)} MB | Última modificación: ${new Date(archivo.lastModified).toLocaleDateString()}`;
                
                vistaArchivo.classList.remove('d-none');
                btnProcesar.disabled = false;
                
                // Actualizar pasos visuales
                const stepCircles = document.querySelectorAll('.step-circle');
                if (stepCircles[2]) {
                    stepCircles[2].classList.remove('bg-secondary');
                    stepCircles[2].classList.add('bg-success');
                }
            } else {
                ocultarVistaArchivo();
            }
        });
    }

    // Limpiar archivo
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            archivoInput.value = '';
            ocultarVistaArchivo();
        });
    }

    function ocultarVistaArchivo() {
        if (vistaArchivo) {
            vistaArchivo.classList.add('d-none');
        }
        if (btnProcesar) {
            btnProcesar.disabled = true;
        }
        
        // Resetear pasos visuales
        const stepCircles = document.querySelectorAll('.step-circle');
        if (stepCircles[2]) {
            stepCircles[2].classList.remove('bg-success');
            stepCircles[2].classList.add('bg-secondary');
        }
    }

    // Manejar envío del formulario
    if (formImportacion) {
        formImportacion.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!archivoInput.files[0]) {
                alert('Por favor selecciona un archivo Excel');
                return;
            }
            
            // Confirmación
            const numFilas = 'múltiples'; // No podemos saber sin leer el archivo
            if (!confirm(`¿Estás seguro de que deseas procesar la importación?\n\nEsta acción creará nuevos trabajadores en el sistema y no se puede deshacer fácilmente.`)) {
                return;
            }
            
            // Mostrar progreso
            const modalBody = document.querySelector('#modalImportacion .modal-body');
            const modalFooter = document.querySelector('#modalImportacion .modal-footer');
            
            if (modalBody) modalBody.style.display = 'none';
            if (modalFooter) modalFooter.style.display = 'none';
            if (progresoImportacion) progresoImportacion.classList.remove('d-none');
            
            // Deshabilitar cierre del modal durante el proceso
            if (modalElement) {
                modalElement.setAttribute('data-bs-backdrop', 'static');
                modalElement.setAttribute('data-bs-keyboard', 'false');
            }
            
            // Enviar formulario
            this.submit();
        });
    }

    // Resetear modal al cerrarse
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            if (formImportacion) {
                formImportacion.reset();
            }
            ocultarVistaArchivo();
            
            // Resetear vista
            const modalBody = document.querySelector('#modalImportacion .modal-body');
            const modalFooter = document.querySelector('#modalImportacion .modal-footer');
            
            if (modalBody) modalBody.style.display = 'block';
            if (modalFooter) modalFooter.style.display = 'flex';
            if (progresoImportacion) progresoImportacion.classList.add('d-none');
            
            // Resetear configuración del modal
            modalElement.removeAttribute('data-bs-backdrop');
            modalElement.removeAttribute('data-bs-keyboard');
            
            // Resetear pasos
            const stepCircles = document.querySelectorAll('.step-circle');
            stepCircles.forEach((circle, index) => {
                circle.classList.remove('bg-primary', 'bg-secondary', 'bg-success');
                if (index === 0) {
                    circle.classList.add('bg-primary');
                } else {
                    circle.classList.add('bg-secondary');
                }
            });
        });
    }
    
    console.log('✅ Modal de importación masiva inicializado');
});
</script>