// ========================================
// 🏢 GESTIÓN DE ÁREAS Y CATEGORÍAS
// ========================================

window.initAreasyCategorias = function() {
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    if (!areaSelect || !categoriaSelect) return;
    
    areaSelect.addEventListener('change', async function() {
        const areaId = this.value;
        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        categoriaSelect.disabled = true;
        
        try {
            if (areaId) {
                const response = await fetch(`${window.PERFIL_CONFIG.endpoints.categorias}${areaId}`);
                const categorias = await response.json();
                
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                categorias.forEach(categoria => {
                    categoriaSelect.innerHTML += `<option value="${categoria.id_categoria}">${categoria.nombre_categoria}</option>`;
                });
            } else {
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
            }
        } catch (error) {
            categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
            console.error('Error cargando categorías:', error);
        } finally {
            categoriaSelect.disabled = false;
        }
    });
    
    console.log('🏢 Áreas y Categorías inicializadas');
};