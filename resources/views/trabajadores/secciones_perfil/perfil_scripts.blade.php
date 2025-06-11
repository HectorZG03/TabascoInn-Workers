{{-- resources/views/trabajadores/components/perfil_scripts.blade.php --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ✅ CARGAR CATEGORÍAS CUANDO CAMBIA EL ÁREA
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    if (areaSelect && categoriaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            
            // Limpiar categorías
            categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';
            categoriaSelect.disabled = true;
            
            if (areaId) {
                // ✅ Usar la ruta API general
                fetch(`/api/categorias/${areaId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(categorias => {
                        // Limpiar y agregar opción por defecto
                        categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                        
                        // Agregar categorías
                        categorias.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.id_categoria;
                            option.textContent = categoria.nombre_categoria;
                            categoriaSelect.appendChild(option);
                        });
                        
                        categoriaSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error al cargar categorías:', error);
                        categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                        categoriaSelect.disabled = false;
                        
                        // Mostrar alerta al usuario
                        alert('Error al cargar las categorías. Por favor, recarga la página.');
                    });
            } else {
                // Si no hay área seleccionada, limpiar categorías
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                categoriaSelect.disabled = false;
            }
        });
    }

    // ❌ ELIMINADO: AUTO-HIDE ALERTS (ya manejado por components.alertas)
    
    // ✅ VALIDACIÓN DE FORMULARIOS
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                // Deshabilitar botón para evitar doble envío
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                
                // Re-habilitar después de 3 segundos si no se redirige
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        });
    });

    // ✅ MODAL DE DOCUMENTOS
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const tipo = button.getAttribute('data-tipo');
            const nombre = button.getAttribute('data-nombre');
            
            const modalTitle = uploadModal.querySelector('.modal-title');
            const tipoInput = uploadModal.querySelector('#tipo_documento');
            
            modalTitle.textContent = `Subir ${nombre}`;
            tipoInput.value = tipo;
        });
    }

    // ✅ VALIDACIÓN DE ARCHIVOS
    const fileInput = document.getElementById('archivo');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validar tamaño (máximo 10MB)
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('El archivo es demasiado grande. Máximo 10MB.');
                    this.value = '';
                    return;
                }
                
                // Validar tipo de archivo
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipo de archivo no válido. Solo PDF, JPG, PNG permitidos.');
                    this.value = '';
                    return;
                }
                
                // Mostrar nombre del archivo
                const fileName = uploadModal.querySelector('#file-name');
                if (fileName) {
                    fileName.textContent = file.name;
                    fileName.style.display = 'block';
                }
            }
        });
    }

    // ✅ TOOLTIPS
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ✅ NAVEGACIÓN ENTRE PESTAÑAS CON URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        const tabElement = document.querySelector(`[data-bs-target="#nav-${activeTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    // ✅ ACTUALIZAR URL AL CAMBIAR DE PESTAÑA
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const targetTab = event.target.getAttribute('data-bs-target').replace('#nav-', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState(null, '', url);
        });
    });

    // Mensaje de debug
    console.log('✅ Perfil Trabajador - Scripts inicializados correctamente');
});
</script>