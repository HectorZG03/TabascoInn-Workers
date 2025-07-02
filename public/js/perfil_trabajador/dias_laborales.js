// ========================================
// 🗓️ GESTIÓN DE DÍAS LABORABLES
// ========================================

window.initDiasLaborables = function() {
    const checkboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
    const container = document.querySelector('.dias-descanso-container p');
    
    if (!checkboxes.length || !container) return;
    
    const actualizarDiasDescanso = () => {
        const seleccionados = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        const descanso = Object.keys(window.DIAS_SEMANA)
            .filter(dia => !seleccionados.includes(dia))
            .map(dia => window.DIAS_SEMANA[dia]);
        
        container.textContent = descanso.length ? descanso.join(', ') : 'No calculados';
    };
    
    checkboxes.forEach(cb => cb.addEventListener('change', actualizarDiasDescanso));
    actualizarDiasDescanso();
    
    console.log('🗓️ Días laborables inicializados');
};