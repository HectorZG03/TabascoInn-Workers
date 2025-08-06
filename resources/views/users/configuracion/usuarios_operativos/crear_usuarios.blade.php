@extends('layouts.app')

@section('title', 'Crear Usuario Operativo')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header py-3" style="background-color: #007A4D;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-person-plus-fill"></i> Crear Usuario Operativo
                        </h3>
                        <a href="{{ route('usuarios.operativos.lista') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('usuarios.operativos.guardar') }}" method="POST">
                        @csrf
                        
                        <!-- Datos básicos -->
                        <h5 class="mb-3">Datos del Usuario</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                                       value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                       required minlength="6">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Permisos -->
                        <h5 class="mb-3">Permisos por Módulo</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr style="background-color: #f8f9fa;">
                                        <th>Módulo</th>
                                        <th class="text-center" width="100">Ver</th>
                                        <th class="text-center" width="100">Crear</th>
                                        <th class="text-center" width="100">Editar</th>
                                        <th class="text-center" width="100">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modulos as $key => $nombre)
                                        <tr>
                                            <td>{{ $nombre }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" name="permisos[{{ $key }}][ver]" 
                                                       class="form-check-input check-ver" data-modulo="{{ $key }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="permisos[{{ $key }}][crear]" 
                                                       class="form-check-input check-accion" data-modulo="{{ $key }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="permisos[{{ $key }}][editar]" 
                                                       class="form-check-input check-accion" data-modulo="{{ $key }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="permisos[{{ $key }}][eliminar]" 
                                                       class="form-check-input check-accion" data-modulo="{{ $key }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn text-white" style="background-color: #007A4D;">
                                <i class="bi bi-save"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Si se marca crear, editar o eliminar, automáticamente marcar ver
document.querySelectorAll('.check-accion').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            const modulo = this.dataset.modulo;
            document.querySelector(`.check-ver[data-modulo="${modulo}"]`).checked = true;
        }
    });
});
</script>
@endsection