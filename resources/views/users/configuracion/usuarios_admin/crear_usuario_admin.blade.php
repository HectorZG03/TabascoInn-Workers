@extends('layouts.app')

@section('title', 'Crear Usuario Administrativo - Hotel TABASCO INN')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-person-plus-fill"></i> Crear Usuario Administrativo
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
            <!-- Formulario de Creación -->
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

                    <form action="{{ route('usuarios.admin.guardar') }}" method="POST" id="formCrearUsuario">
                        @csrf
                        
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
                                               value="{{ old('nombre') }}"
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
                                               value="{{ old('email') }}"
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
                                            <option value="">Seleccione un tipo...</option>
                                            <option value="Gerencia" {{ old('tipo') == 'Gerencia' ? 'selected' : '' }}>
                                                Gerencia - Acceso Total
                                            </option>
                                            <option value="Recursos_Humanos" {{ old('tipo') == 'Recursos_Humanos' ? 'selected' : '' }}>
                                                Recursos Humanos - Acceso Total (excepto usuarios admin)
                                            </option>
                                        </select>
                                        @error('tipo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-key-fill"></i> Contraseña de Acceso
                            </h5>
                            
                            <div class="row">
                                <!-- Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Contraseña <span class="text-danger">*</span>
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
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        Confirmar Contraseña <span class="text-danger">*</span>
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
                                               required>
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

                            <!-- Requisitos de contraseña -->
                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading">
                                    <i class="bi bi-info-circle"></i> Requisitos de la contraseña:
                                </h6>
                                <ul class="mb-0 small">
                                    <li id="length-check"><i class="bi bi-x text-danger"></i> Mínimo 8 caracteres</li>
                                    <li id="uppercase-check"><i class="bi bi-x text-danger"></i> Al menos una letra mayúscula</li>
                                    <li id="lowercase-check"><i class="bi bi-x text-danger"></i> Al menos una letra minúscula</li>
                                    <li id="number-check"><i class="bi bi-x text-danger"></i> Al menos un número</li>
                                    <li id="special-check"><i class="bi bi-x text-danger"></i> Al menos un carácter especial (!@#$%^&*)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Información de Permisos -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-shield-fill-check"></i> Permisos del Usuario
                            </h5>
                            
                            <div class="alert alert-warning" id="permisos-info">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Selecciona un tipo de usuario</strong> para ver los permisos asociados.
                            </div>

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

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('usuarios.admin.lista') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" id="btnCrear">
                                <i class="bi bi-check-circle"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
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
        
        // Validaciones
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[!@#$%^&*]/.test(password);
        
        // Actualizar checkmarks
        updateCheck('length-check', hasLength);
        updateCheck('uppercase-check', hasUppercase);
        updateCheck('lowercase-check', hasLowercase);
        updateCheck('number-check', hasNumber);
        updateCheck('special-check', hasSpecial);
        
        // Calcular fortaleza
        if (hasLength) strength += 20;
        if (hasUppercase) strength += 20;
        if (hasLowercase) strength += 20;
        if (hasNumber) strength += 20;
        if (hasSpecial) strength += 20;
        
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

    function updateCheck(id, isValid) {
        const element = document.getElementById(id);
        const icon = element.querySelector('i');
        
        if (isValid) {
            icon.className = 'bi bi-check text-success';
        } else {
            icon.className = 'bi bi-x text-danger';
        }
    }

    // Mostrar permisos según el tipo de usuario
    document.getElementById('tipo').addEventListener('change', function() {
        const permisosInfo = document.getElementById('permisos-info');
        const permisosGerencia = document.getElementById('permisos-gerencia');
        const permisosRrhh = document.getElementById('permisos-rrhh');
        
        // Ocultar todos
        permisosInfo.classList.add('d-none');
        permisosGerencia.classList.add('d-none');
        permisosRrhh.classList.add('d-none');
        
        // Mostrar según selección
        if (this.value === 'Gerencia') {
            permisosGerencia.classList.remove('d-none');
        } else if (this.value === 'Recursos_Humanos') {
            permisosRrhh.classList.remove('d-none');
        } else {
            permisosInfo.classList.remove('d-none');
        }
    });
</script>


@endsection