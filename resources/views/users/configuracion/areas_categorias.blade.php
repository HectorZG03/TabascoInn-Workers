@extends('layouts.app')

@section('title', 'Áreas y Categorías')

@section('content')
<div class="container">
    <h4><i class="bi bi-diagram-3"></i> Administración de Áreas y Categorías</h4>

    {{-- Alertas de éxito o error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mt-4">
        <!-- Crear Área -->
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <strong><i class="bi bi-building-add"></i> Nueva Área</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('areas.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre_area" class="form-label">Nombre del Área</label>
                            <input type="text" class="form-control" id="nombre_area" name="nombre_area" required>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Crear Área
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Crear Categoría -->
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <strong><i class="bi bi-tags"></i> Nueva Categoría</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('categorias.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="id_area" class="form-label">Área</label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <option value="">-- Selecciona un Área --</option>
                                @foreach ($todasLasAreas as $area)
                                    <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                            <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Crear Categoría
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador y Listado -->
    <div class="card mt-5">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <strong><i class="bi bi-list-ul"></i> Áreas y Categorías Registradas</strong>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('areas.categorias.index') }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="busqueda" 
                                   placeholder="Buscar área o categoría..." 
                                   value="{{ $busqueda }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($busqueda)
                                <a href="{{ route('areas.categorias.index') }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ✅ CONTROLES DE SELECCIÓN MÚLTIPLE --}}
        <div class="card-header bg-light border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label fw-bold" for="selectAll">
                        Seleccionar todas las categorías
                    </label>
                </div>
                <div>
                    <span id="selectedCount" class="badge bg-secondary me-2">0 seleccionadas</span>
                    <button type="button" class="btn btn-danger btn-sm" id="deleteSelected" style="display: none;" onclick="eliminarSeleccionadas()">
                        <i class="bi bi-trash"></i> Eliminar seleccionadas
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            {{-- ✅ FORMULARIO PARA ELIMINACIÓN MÚLTIPLE --}}
            <form id="deleteMultipleForm" method="POST" action="{{ route('categorias.multiple.destroy') }}" style="display: none;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="categorias" id="categoriasToDelete">
            </form>

            @forelse ($areas as $area)
                <h6 class="d-flex justify-content-between align-items-center mt-3">
                    <span><i class="bi bi-building"></i> {{ $area->nombre_area }}</span>
                    <span>
                        <form action="{{ route('areas.destroy', $area) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta área y TODAS sus categorías?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarAreaModal{{ $area->id_area }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </span>
                </h6>

                <!-- Modal editar área -->
                <div class="modal fade" id="editarAreaModal{{ $area->id_area }}" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('areas.update', $area) }}">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Área</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="nombre_area" class="form-control" value="{{ $area->nombre_area }}" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                  </div>
                </div>

                {{-- ✅ LISTA DE CATEGORÍAS CON CHECKBOXES --}}
                <ul class="ms-3">
                    @foreach ($area->categorias as $categoria)
                        <li class="d-flex justify-content-between align-items-center py-1">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input me-2 categoria-checkbox" 
                                       type="checkbox" 
                                       value="{{ $categoria->id_categoria }}" 
                                       id="categoria{{ $categoria->id_categoria }}">
                                <label class="form-check-label" for="categoria{{ $categoria->id_categoria }}">
                                    {{ $categoria->nombre_categoria }}
                                </label>
                            </div>
                            <span>
                                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarCategoriaModal{{ $categoria->id_categoria }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </span>
                        </li>

                        <!-- Modal editar categoría -->
                        <div class="modal fade" id="editarCategoriaModal{{ $categoria->id_categoria }}" tabindex="-1">
                          <div class="modal-dialog">
                            <form method="POST" action="{{ route('categorias.update', $categoria) }}">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Categoría</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <select name="id_area" class="form-select mb-2" required>
                                            @foreach ($todasLasAreas as $areaOption)
                                                <option value="{{ $areaOption->id_area }}" {{ $categoria->id_area == $areaOption->id_area ? 'selected' : '' }}>
                                                    {{ $areaOption->nombre_area }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="nombre_categoria" class="form-control" value="{{ $categoria->nombre_categoria }}" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                </div>
                            </form>
                          </div>
                        </div>
                    @endforeach
                </ul>
            @empty
                <div class="text-center py-4">
                    @if($busqueda)
                        <p>No se encontraron resultados para "<strong>{{ $busqueda }}</strong>"</p>
                        <a href="{{ route('areas.categorias.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Mostrar todos
                        </a>
                    @else
                        <p>No hay áreas registradas.</p>
                    @endif
                </div>
            @endforelse

            {{-- Paginación Simple --}}
            @if($areas->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $areas->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const categoriaCheckboxes = document.querySelectorAll('.categoria-checkbox');
        const selectedCountBadge = document.getElementById('selectedCount');
        const deleteSelectedButton = document.getElementById('deleteSelected');

        // Función para actualizar el contador y botón
        function updateSelectionUI() {
            const selectedCheckboxes = document.querySelectorAll('.categoria-checkbox:checked');
            const count = selectedCheckboxes.length;
            
            selectedCountBadge.textContent = count + ' seleccionadas';
            
            if (count > 0) {
                deleteSelectedButton.style.display = 'inline-block';
                selectedCountBadge.className = 'badge bg-primary me-2';
            } else {
                deleteSelectedButton.style.display = 'none';
                selectedCountBadge.className = 'badge bg-secondary me-2';
            }

            // Actualizar estado del checkbox "Seleccionar todo"
            if (count === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (count === categoriaCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Manejar "Seleccionar todo"
        selectAllCheckbox.addEventListener('change', function() {
            categoriaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionUI();
        });

        // Manejar checkboxes individuales
        categoriaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionUI);
        });

        // Inicializar UI
        updateSelectionUI();
    });

    function eliminarSeleccionadas() {
        const selectedCheckboxes = document.querySelectorAll('.categoria-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('No hay categorías seleccionadas');
            return;
        }

        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        const count = selectedIds.length;

        if (confirm(`¿Estás seguro de eliminar ${count} categoría(s) seleccionada(s)?`)) {
            document.getElementById('categoriasToDelete').value = JSON.stringify(selectedIds);
            document.getElementById('deleteMultipleForm').submit();
        }
    }
</script>
@endsection