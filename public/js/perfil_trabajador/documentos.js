// ========================================
// 📄 GESTIÓN DE DOCUMENTOS
// ========================================

window.initDocumentos = function() {
    
    // Modal de subida de documentos
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modalTitle = uploadModal.querySelector('.modal-title');
            const tipoInput = uploadModal.querySelector('#tipo_documento');
            
            modalTitle.textContent = `Subir ${button.getAttribute('data-nombre')}`;
            tipoInput.value = button.getAttribute('data-tipo');
        });
    }

    // Validación de archivos
    const fileInput = document.getElementById('archivo');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            
            if (file.size > window.PERFIL_CONFIG.fileMaxSize) {
                alert('Archivo muy grande. Máximo 10MB.');
                this.value = '';
                return;
            }
            
            if (!window.PERFIL_CONFIG.allowedTypes.includes(file.type)) {
                alert('Tipo no válido. Solo PDF, JPG, PNG.');
                this.value = '';
                return;
            }
        });
    }
    
    console.log('📄 Documentos inicializados');
};