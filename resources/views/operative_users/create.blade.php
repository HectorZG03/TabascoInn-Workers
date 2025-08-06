@extends('layouts.app')

@section('title', 'Crear Usuario Operativo')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>
                <i class="bi bi-person-plus"></i> Crear Usuario Operativo
            </h2>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('users.operative.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Permisos</label>
                    <div class="border p-3 rounded">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="ver" id="perm-ver">
                            <label class="form-check-label" for="perm-ver">Ver</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="editar" id="perm-editar">
                            <label class="form-check-label" for="perm-editar">Editar</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="borrar" id="perm-borrar">
                            <label class="form-check-label" for="perm-borrar">Borrar</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="actualizar" id="perm-actualizar">
                            <label class="form-check-label" for="perm-actualizar">Actualizar</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('users.operative.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection