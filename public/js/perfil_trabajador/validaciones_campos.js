// ========================================
// 📝 VALIDACIONES DE CAMPOS
// ========================================

window.initValidacionesCampos = function() {
    
    // Configuración de campos
    const fieldConfigs = [
        { id: 'curp', maxLength: 18, transform: 'upper' },
        { id: 'rfc', maxLength: 13, transform: 'upper' },
        { id: 'telefono', maxLength: 10, transform: 'numeric' },
        { id: 'no_nss', maxLength: 11, transform: 'numeric' }
    ];

    // Aplicar validaciones
    fieldConfigs.forEach(config => {
        const input = document.getElementById(config.id);
        if (!input) return;
        
        input.addEventListener('input', function() {
            let value = this.value;
            
            switch (config.transform) {
                case 'upper':
                    value = value.toUpperCase();
                    break;
                case 'numeric':
                    value = value.replace(/\D/g, '');
                    break;
            }
            
            this.value = value.substring(0, config.maxLength);
        });
    });

    // Validación general de formularios (loading en botones)
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.dataset.noLoading) {
                window.PerfilUtils.showLoading(submitBtn);
                setTimeout(() => window.PerfilUtils.hideLoading(submitBtn), 3000);
            }
        });
    });
    
    console.log('📝 Validaciones de campos inicializadas');
};