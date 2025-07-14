// ========================================
// 🏢 GESTIÓN DE ÁREAS Y CATEGORÍAS - CON RUTAS DINÁMICAS
// ========================================

window.initAreasyCategorias = function() {
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    if (!areaSelect || !categoriaSelect) {
        console.warn('⚠️ Elementos de área o categoría no encontrados');
        return;
    }

    // ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
    if (typeof AppRoutes === 'undefined') {
        console.error('❌ AppRoutes no está disponible para cargar categorías');
        return;
    }
    
    areaSelect.addEventListener('change', async function() {
        const areaId = this.value;
        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        categoriaSelect.disabled = true;
        
        try {
            if (areaId) {
                // ✅ USAR RUTAS DINÁMICAS EN LUGAR DE RUTAS ABSOLUTAS
                // ❌ ANTES: const response = await fetch(`${window.PERFIL_CONFIG.endpoints.categorias}${areaId}`);
                // ✅ AHORA: Usar AppRoutes para construir la URL correcta
                const url = AppRoutes.api(`categorias/${areaId}`);
                
                console.log('🔄 Cargando categorías desde:', url);
                
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const categorias = await response.json();
                
                // Limpiar y llenar el select de categorías
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                
                if (Array.isArray(categorias) && categorias.length > 0) {
                    categorias.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.id_categoria;
                        option.textContent = categoria.nombre_categoria;
                        categoriaSelect.appendChild(option);
                    });
                    console.log(`✅ ${categorias.length} categorías cargadas`);
                } else {
                    categoriaSelect.innerHTML += '<option value="" disabled>No hay categorías disponibles</option>';
                    console.warn('⚠️ No se encontraron categorías para el área seleccionada');
                }
            } else {
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
            }
        } catch (error) {
            console.error('❌ Error cargando categorías:', error);
            categoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
            
            // ✅ MOSTRAR ERROR AL USUARIO DE FORMA AMIGABLE
            const errorMsg = error.message.includes('Failed to fetch') 
                ? 'Error de conexión. Verifica tu conexión a internet.'
                : `Error: ${error.message}`;
                
            // Si hay un contenedor de alertas, mostrar el error ahí
            const alertContainer = document.querySelector('.alert-container') || document.querySelector('#alerts');
            if (alertContainer) {
                const alertHTML = `
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Error al cargar categorías:</strong> ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                alertContainer.innerHTML = alertHTML;
            }
        } finally {
            categoriaSelect.disabled = false;
        }
    });

    // ✅ NUEVO: FUNCIÓN PARA PRECARGAR CATEGORÍAS SI YA HAY UN ÁREA SELECCIONADA
    const precargarCategorias = () => {
        if (areaSelect.value) {
            console.log('🔄 Precargando categorías para área:', areaSelect.value);
            areaSelect.dispatchEvent(new Event('change'));
        }
    };

    // ✅ PRECARGAR CATEGORÍAS AL INICIALIZAR (útil en edición)
    setTimeout(precargarCategorias, 100);
    
    console.log('🏢 Áreas y Categorías inicializadas con rutas dinámicas');
    
    // ✅ DEBUG: Mostrar URL que se usará
    if (typeof window.APP_DEBUG !== 'undefined' && window.APP_DEBUG) {
        console.log('🔍 URL base para categorías:', AppRoutes.api('categorias/'));
    }
};