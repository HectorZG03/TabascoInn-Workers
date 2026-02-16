@extends('layouts.app')

@section('title', 'Gestión de Usuarios Operativos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header py-3" style="background-color: #007A4D;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-people-fill"></i> Usuarios Operativos
                        </h3>
                        <div>
                            <a href="{{ route('usuarios.operativos.crear') }}" class="btn btn-light">
                                <i class="bi bi-plus-circle"></i> Nuevo Usuario
                            </a>
                            <a href="{{ route('users.config') }}" class="btn btn-outline-light btn-sm ms-2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Lista de usuarios -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Módulos con Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->nombre }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }}">
                                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    @if($usuario->permisos->count() > 0)
                                        <small>
                                            @foreach($usuario->permisos as $permiso)
                                                @if($permiso->ver)
                                                    <span class="badge bg-secondary me-1">
                                                        {{ ucfirst(str_replace('_', ' ', $permiso->modulo)) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </small>
                                    @else
                                        <span class="text-muted">Sin permisos asignados</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('usuarios.operativos.editar', $usuario->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <form action="{{ route('usuarios.operativos.cambiar-estado', $usuario->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-{{ $usuario->activo ? 'warning' : 'success' }}">
                                            <i class="bi bi-{{ $usuario->activo ? 'toggle-on' : 'toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('usuarios.operativos.eliminar', $usuario->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    No hay usuarios operativos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection