<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sistema de Fichas Técnicas - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body style="background-image: url('{{ asset('image/estaticas/slide-1.jpg') }}'); 
             background-size: cover; 
             background-position: center; 
             background-repeat: no-repeat;">

    <div class="login-wrapper">
        <div class="login-container">
            <div class="card login-card">
                <!-- Header compacto -->
                <div class="card-header">
                    <div class="brand-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <h1 class="brand-title">Sistema de Fichas Técnicas</h1>
                    <p class="brand-subtitle">
                        <i class="bi bi-star-fill text-success"></i> Hotel - Administración <i class="bi bi-star-fill text-success"></i>
                    </p>
                </div>
                
                <!-- Contenido principal -->
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Formulario de login -->
                        <div class="col-lg-7">
                            <div class="form-section">
                                <h3 class="form-title">
                                    <i class="bi bi-key me-2"></i> Iniciar Sesión
                                </h3>

                                <form method="POST" action="{{ url('/login') }}">
                                    @csrf
                                    
                                    <!-- Campo Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope-fill"></i> Correo Institucional
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-at"></i>
                                            </span>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}" 
                                                   placeholder="usuario@hotel.com"
                                                   required 
                                                   autocomplete="email" 
                                                   autofocus>
                                            @error('email')
                                                <div class="invalid-feedback">
                                                    <i class="bi bi-exclamation-triangle"></i> {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Campo Contraseña -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="bi bi-lock-fill"></i> Contraseña
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-shield-lock"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Ingresa tu contraseña"
                                                   required 
                                                   autocomplete="current-password">
                                            @error('password')
                                                <div class="invalid-feedback">
                                                    <i class="bi bi-exclamation-triangle"></i> {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Botón de Login -->
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn login-btn">
                                            <i class="bi bi-box-arrow-in-right me-2"></i> Acceder al Sistema
                                        </button>
                                    </div>
                                </form>

                                <!-- Divider -->
                                <div class="divider">
                                    <div class="divider-line"></div>
                                    <div class="divider-text">
                                        <i class="bi bi-info-circle me-1"></i> Usuarios de Prueba
                                    </div>
                                    <div class="divider-line"></div>
                                </div>
                                
                                <!-- Usuarios de prueba compactos -->
                                <div class="test-users">
                                    <div class="row g-2">
                                        <div class="col-12 col-sm-6">
                                            <div class="user-card">
                                                <div class="user-icon">
                                                    <i class="bi bi-person-badge"></i>
                                                </div>
                                                <div class="user-title">Recursos Humanos</div>
                                                <div class="user-details">
                                                    <div>rh@hotel.com</div>
                                                    <div><strong>password123</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <div class="user-card">
                                                <div class="user-icon">
                                                    <i class="bi bi-person-gear"></i>
                                                </div>
                                                <div class="user-title">Gerencia</div>
                                                <div class="user-details">
                                                    <div>gerencia@hotel.com</div>
                                                    <div><strong>password123</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel de información -->
                        <div class="col-lg-5">
                            <div class="info-section">
                                <div class="info-content">
                                    <div class="info-icon">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <h3 class="info-title">Sistema Seguro</h3>
                                    <p class="info-text">
                                        Admnistracion de Fichas Tecnicas de los Trabajadores TABASCO INN.
                                    </p>
                                </div>
                                
                                <div class="features-row">
                                    <div class="row text-center g-3">
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <i class="bi bi-shield-shaded"></i>
                                                </div>
                                                <p class="feature-title">Seguro</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <i class="bi bi-speedometer2"></i>
                                                </div>
                                                <p class="feature-title">Rápido</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <i class="bi bi-patch-check"></i>
                                                </div>
                                                <p class="feature-title">Confiable</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer compacto -->
                <div class="card-footer">
                    <i class="bi bi-shield-check text-success me-1"></i> Sistema Seguro | 
                    <i class="bi bi-clock text-success me-1"></i> Laravel 12 | 
                    <i class="bi bi-heart-fill text-success me-1"></i> Hotel Administration
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>