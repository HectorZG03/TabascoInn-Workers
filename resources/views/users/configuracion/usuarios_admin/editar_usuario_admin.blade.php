@extends('layouts.app')

@section('title', 'Editar Usuario Administrativo - Hotel TABASCO INN')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-pencil-square"></i> Editar Usuario Administrativo
                        </h3>
                        <a href="{{ route('usuarios.admin.lista') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Información del Usuario Actual -->
            <div class="card border-info mb-3 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-person-circle"></i> Información Actual del Usuario
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nombre:</strong> {{ $usuario->nombre }}
                        </div>
                        <div class="col-md-4">
                            <strong>Email:</strong> {{ $usuario->email }}
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong> 
                            <span class="badge {{ $usuario->tipo == 'Gerencia' ? 'bg-primary' : 'bg-warning' }}">
                                {{ $usuario->tipo == 'Gerencia' ? 'Gerencia' : 'Recursos Humanos' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>Estado:</strong> 
                            <span class="badge {{ $usuario->activo ? 'bg-success' : 'bg-danger' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Creado:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-4">
                            <strong>Actualizado:</strong> {{ $usuario->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Edición -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Errores de Validación -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle-fill"></i> Por favor corrige los siguientes errores:
                            </h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('usuarios.admin.actualizar', $usuario->id) }}" method="POST" id="formEditarUsuario">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información del Usuario -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-person-badge"></i> Información del Usuario
                            </h5>
                            
                            <div class="row">
                                <!-- Nombre Completo -->
                                <div class="col-md-12 mb-3">
                                    <label for="nombre" class="form-label">
                                        Nombre Completo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person-fill"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="{{ old('nombre', $usuario->nombre) }}"
                                               placeholder="Ej: Juan Pérez García"
                                               required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-12 mb-3">
                                    <label for="email" class="form-label">
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope-fill"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $usuario->email) }}"
                                               placeholder="usuario@hotelTabascoinn.com"
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Este será el correo para iniciar sesión</small>
                                </div>

                                <!-- Tipo de Usuario -->
                                <div class="col-md-12 mb-3">
                                    <label for="tipo" class="form-label">
                                        Tipo de Usuario <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-shield-check"></i>
                                        </span>
                                        <select class="form-select @error('tipo') is-invalid @enderror" 
                                                id="tipo" 
                                                name="tipo" 
                                                required>
                                            <option value="Gerencia" {{ old('tipo', $usuario->tipo) == 'Gerencia' ? 'selected' : '' }}>
                                                Gerencia - Acceso Total
                                            </option>
                                            <option value="Recursos_Humanos" {{ old('tipo', $usuario->tipo) == 'Recursos_Humanos' ? 'selected' : '' }}>
                                                Recursos Humanos - Acceso Total (excepto usuarios admin)
                                            </option>
                                        </select>
                                        @error('tipo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if($usuario->tipo != old('tipo', $usuario->tipo))
                                        <div class="alert alert-warning mt-2">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            <strong>Atención:</strong> Cambiar el tipo de usuario modificará sus permisos de acceso al sistema.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Cambio de Contraseña (Opcional) -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-key-fill"></i> Cambiar Contraseña 
                                <small class="text-muted">(Opcional - dejar en blanco para mantener la actual)</small>
                            </h5>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="cambiarPassword">
                                <label class="form-check-label" for="cambiarPassword">
                                    Deseo cambiar la contraseña
                                </label>
                            </div>
                            
                            <div id="passwordFields" style="display: none;">
                                <div class="row">
                                    <!-- Nueva Contraseña -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            Nueva Contraseña
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock-fill"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password"
                                                   placeholder="Mínimo 8 caracteres"
                                                   disabled>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Confirmar Nueva Contraseña -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">
                                            Confirmar Nueva Contraseña
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock-fill"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation"
                                                   placeholder="Repite la contraseña"
                                                   disabled>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Indicador de fortaleza de contraseña -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar"></div>
                                        </div>
                                        <small class="text-muted" id="passwordStrengthText">Ingresa una contraseña</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Permisos -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-shield-fill-check"></i> Permisos del Usuario
                            </h5>
                            
                            <div class="d-none" id="permisos-gerencia">
                                <div class="alert alert-success">
                                    <h6><i class="bi bi-briefcase-fill"></i> Permisos de Gerencia</h6>
                                    <hr>
                                    <p class="mb-2">Los usuarios de <strong>Gerencia</strong> tienen acceso completo a:</p>
                                    <ul class="mb-0">
                                        <li>✓ Gestión completa de trabajadores</li>
                                        <li>✓ Gestión de contratos y plantillas</li>
                                        <li>✓ Permisos laborales y vacaciones</li>
                                        <li>✓ Despidos y bajas</li>
                                        <li>✓ Configuración del sistema</li>
                                        <li>✓ Gestión de usuarios operativos</li>
                                        <li>✓ <strong>Gestión de usuarios administrativos (solo SuperAdmin)</strong></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-none" id="permisos-rrhh">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-person-workspace"></i> Permisos de Recursos Humanos</h6>
                                    <hr>
                                    <p class="mb-2">Los usuarios de <strong>Recursos Humanos</strong> tienen acceso a:</p>
                                    <ul class="mb-0">
                                        <li>✓ Gestión completa de trabajadores</li>
                                        <li>✓ Gestión de contratos y plantillas</li>
                                        <li>✓ Permisos laborales y vacaciones</li>
                                        <li>✓ Despidos y bajas</li>
                                        <li>✓ Configuración del sistema</li>
                                        <li>✓ Gestión de usuarios operativos</li>
                                        <li>✗ <strong>NO pueden gestionar usuarios administrativos</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Registro de Cambios -->
                        <div class="mb-4">
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-clock-history"></i> Registro de Cambios</h6>
                                <small>Los cambios realizados serán registrados con tu usuario: <strong>{{ Auth::user()->email }}</strong></small>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('usuarios.admin.lista') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnActualizar">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Mostrar/ocultar campos de contraseña
    document.getElementById('cambiarPassword').addEventListener('change', function() {
        const passwordFields = document.getElementById('passwordFields');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        
        if (this.checked) {
            passwordFields.style.display = 'block';
            passwordInput.disabled = false;
            passwordConfirmInput.disabled = false;
        } else {
            passwordFields.style.display = 'none';
            passwordInput.disabled = true;
            passwordConfirmInput.disabled = true;
            passwordInput.value = '';
            passwordConfirmInput.value = '';
        }
    });

    // Toggle de visibilidad de contraseña
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
        const passwordInput = document.getElementById('password_confirmation');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    // Validación de fortaleza de contraseña
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Ingresa una contraseña';
            return;
        }
        
        // Calcular fortaleza
        if (password.length >= 8) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[!@#$%^&*]/.test(password)) strength += 20;
        
        // Actualizar barra de progreso
        strengthBar.style.width = strength + '%';
        
        if (strength <= 20) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Contraseña muy débil';
        } else if (strength <= 40) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Contraseña débil';
        } else if (strength <= 60) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Contraseña moderada';
        } else if (strength <= 80) {
            strengthBar.className = 'progress-bar bg-primary';
            strengthText.textContent = 'Contraseña fuerte';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Contraseña muy fuerte';
        }
    });

    // Mostrar permisos según el tipo de usuario
    function mostrarPermisos() {
        const tipo = document.getElementById('tipo').value;
        const permisosGerencia = document.getElementById('permisos-gerencia');
        const permisosRrhh = document.getElementById('permisos-rrhh');
        
        // Ocultar todos
        permisosGerencia.classList.add('d-none');
        permisosRrhh.classList.add('d-none');
        
        // Mostrar según selección
        if (tipo === 'Gerencia') {
            permisosGerencia.classList.remove('d-none');
        } else if (tipo === 'Recursos_Humanos') {
            permisosRrhh.classList.remove('d-none');
        }
    }

    // Mostrar permisos al cargar
    mostrarPermisos();

    // Mostrar permisos al cambiar
    document.getElementById('tipo').addEventListener('change', mostrarPermisos);
</script>


@endsection