@extends('layouts.app')

@section('title', 'Gestión de Usuarios Administrativos - Hotel TABASCO INN')

@section('content')
<div class="container-fluid">
    <!-- Header Principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-shield-lock-fill"></i> Gestión de Usuarios Administrativos
                        </h3>
                        <div>
                            <span class="badge bg-white text-danger me-2">
                                <i class="bi bi-person-check-fill"></i> Super Administrador
                            </span>
                            <a href="{{ route('users.config') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-3 text-primary"></i>
                    <h3 class="mt-2">{{ $estadisticas['total_usuarios'] }}</h3>
                    <p class="text-muted mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-briefcase-fill fs-3 text-info"></i>
                    <h3 class="mt-2">{{ $estadisticas['gerencia'] }}</h3>
                    <p class="text-muted mb-0">Gerencia</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-person-workspace fs-3 text-warning"></i>
                    <h3 class="mt-2">{{ $estadisticas['recursos_humanos'] }}</h3>
                    <p class="text-muted mb-0">Recursos Humanos</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    <h3 class="mt-2">{{ $estadisticas['activos'] }}</h3>
                    <p class="text-muted mb-0">Activos</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
                    <h3 class="mt-2">{{ $estadisticas['inactivos'] }}</h3>
                    <p class="text-muted mb-0">Inactivos</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <a href="{{ route('usuarios.admin.crear') }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabla de Usuarios -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Lista de Usuarios Administrativos
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre o email...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Nombre</th>
                            <th width="25%">Email</th>
                            <th width="15%">Tipo</th>
                            <th width="10%" class="text-center">Estado</th>
                            <th width="10%" class="text-center">Creado</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                        @forelse($usuarios as $index => $usuario)
                            <tr class="usuario-row">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <span class="avatar-title rounded-circle {{ $usuario->tipo == 'Gerencia' ? 'bg-info' : 'bg-warning' }} text-white">
                                                {{ strtoupper(substr($usuario->nombre, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $usuario->nombre }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @if($usuario->tipo == 'Gerencia')
                                        <span class="badge bg-info">
                                            <i class="bi bi-briefcase-fill"></i> Gerencia
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-person-workspace"></i> Recursos Humanos
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($usuario->activo)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small>{{ $usuario->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <!-- Editar -->
                                        <a href="{{ route('usuarios.admin.editar', $usuario->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        
                                        <!-- Cambiar Estado -->
                                        <form action="{{ route('usuarios.admin.cambiar-estado', $usuario->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-{{ $usuario->activo ? 'warning' : 'success' }}"
                                                    title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}"
                                                    onclick="return confirm('¿Estás seguro de {{ $usuario->activo ? 'desactivar' : 'activar' }} este usuario?')">
                                                <i class="bi bi-{{ $usuario->activo ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Eliminar -->
                                        <form action="{{ route('usuarios.admin.eliminar', $usuario->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar"
                                                    onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay usuarios administrativos registrados</p>
                                    <a href="{{ route('usuarios.admin.crear') }}" class="btn btn-success">
                                        <i class="bi bi-person-plus-fill"></i> Crear Primer Usuario
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <h6 class="alert-heading">
                    <i class="bi bi-info-circle-fill"></i> Información Importante
                </h6>
                <hr>
                <ul class="mb-0">
                    <li><strong>Usuarios de Gerencia:</strong> Tienen acceso completo a todas las funciones del sistema.</li>
                    <li><strong>Usuarios de Recursos Humanos:</strong> Tienen acceso completo excepto a la gestión de usuarios administrativos.</li>
                    <li><strong>Estado Inactivo:</strong> Los usuarios inactivos no pueden iniciar sesión en el sistema.</li>
                    <li><strong>Eliminación:</strong> Al eliminar un usuario, se eliminarán todos sus registros asociados.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-title {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }
    
    .usuario-row {
        transition: all 0.3s ease;
    }
    
    .usuario-row:hover {
        background-color: #f8f9fa;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
</style>



<script>
    // Búsqueda en tiempo real
    document.getElementById('buscarUsuario').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('.usuario-row');
        
        rows.forEach(row => {
            const nombre = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            
            if (nombre.includes(searchValue) || email.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>


@endsection