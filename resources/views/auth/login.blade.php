<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Fichas Técnicas - Login</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --olivo-oscuro: #556B2F;
      --olivo-medio: #6B8E23;
      --arena: #D2B48C;
      --marfil: #F5F5DC;
      --marfil-oscuro: #E8E4C9;
      --texto-oscuro: #333333;
      --accent-color: #8F9779;
    }

    body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, rgba(253, 252, 202, 0.9), rgba(253, 252, 202, 0.9)), url('{{ asset("image/estaticas/slide-1.jpg") }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    }


    .login-container {
      max-width: 860px;
      width: 100%;
      animation: fadeIn 0.5s ease-in-out;
    }

    .login-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      background-color: var(--marfil);
    }

    .card-header {
      background: var(--olivo-oscuro);
      color: var(--marfil);
      text-align: center;
      padding: 1rem;
      border-bottom: 3px solid var(--arena);
    }

    .brand-icon {
      font-size: 2rem;
      margin-bottom: 0.3rem;
      color: var(--arena);
    }

    .brand-title {
      font-size: 1.3rem;
      font-weight: 600;
    }

    .brand-subtitle {
      font-size: 0.8rem;
      opacity: 0.9;
    }

    .form-section {
      padding: 1.5rem;
      background-color: var(--marfil);
    }

    .form-title {
      color: var(--olivo-oscuro);
      font-weight: 600;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }

    .form-title::after {
      content: '';
      display: block;
      width: 40px;
      height: 2px;
      background: var(--olivo-medio);
      margin-top: 5px;
    }

    .form-control,
    .form-control:focus {
      border-radius: 6px;
      padding: 0.6rem 0.75rem;
      border: 1px solid var(--arena);
      background-color: rgba(255, 255, 255, 0.9);
    }

    .input-group-text {
      background: var(--marfil-oscuro);
      border-radius: 6px 0 0 6px;
      border: 1px solid var(--arena);
      color: var(--olivo-oscuro);
      padding: 0.5rem 0.75rem;
    }

    .login-btn {
      background: var(--olivo-medio);
      color: var(--marfil);
      border: none;
      padding: 0.6rem;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.9rem;
      text-transform: uppercase;
    }

    .login-btn:hover {
      background: var(--olivo-oscuro);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(85, 107, 47, 0.3);
    }

    .info-section {
      background: linear-gradient(135deg, var(--olivo-oscuro), var(--olivo-medio));
      color: var(--marfil);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .info-icon {
      font-size: 2rem;
      color: var(--arena);
      margin-bottom: 0.75rem;
    }

    .info-title {
      font-size: 1rem;
      font-weight: 600;
    }

    .info-text {
      font-size: 0.85rem;
      opacity: 0.9;
    }

    .feature-icon {
      font-size: 1.2rem;
      color: var(--arena);
    }

    .feature-title {
      font-size: 0.75rem;
      font-weight: 500;
    }

    .feature-item {
      padding: 0.6rem;
      border-radius: 6px;
      background: rgba(210, 180, 140, 0.15);
      border: 1px solid rgba(210, 180, 140, 0.2);
    }

    .divider {
      margin: 1.2rem 0;
      display: flex;
      align-items: center;
    }

    .divider-line {
      flex: 1;
      height: 1px;
      background: var(--arena);
    }

    .divider-text {
      padding: 0 0.75rem;
      font-size: 0.8rem;
      color: var(--olivo-oscuro);
    }

    .user-card {
      font-size: 0.8rem;
      padding: 0.75rem;
      border-radius: 6px;
      background: rgba(107, 142, 35, 0.1);
      border: 1px solid rgba(107, 142, 35, 0.2);
    }

    .user-icon {
      font-size: 1.2rem;
      color: var(--olivo-medio);
      margin-bottom: 0.3rem;
    }

    .card-footer {
      background: var(--olivo-oscuro);
      color: var(--marfil);
      font-size: 0.7rem;
      text-align: center;
      padding: 0.6rem;
    }

    .form-check-label {
      font-size: 0.8rem;
      color: var(--olivo-oscuro);
    }

    .form-check-input:checked {
      background-color: var(--olivo-medio);
      border-color: var(--olivo-medio);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .info-section {
        display: none;
      }

      .form-section {
        padding: 1.2rem;
      }

      .login-card {
        margin: 0.5rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="card login-card">
      <div class="card-header">
        <div class="brand-icon"><i class="bi bi-building"></i></div>
        <div class="brand-title">Sistema de Fichas Técnicas</div>
        <p class="brand-subtitle"><i class="bi bi-star-fill" style="color: var(--arena);"></i> Hotel - Administración <i class="bi bi-star-fill" style="color: var(--arena);"></i></p>
      </div>

      <div class="card-body p-0">
        <div class="row g-0">
          <!-- Login -->
          <div class="col-lg-7">
            <div class="form-section">
              <h3 class="form-title"><i class="bi bi-key me-1"></i> Iniciar Sesión</h3>
              <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-2">
                  <label for="email" class="form-label"><i class="bi bi-envelope-fill me-1"></i> Correo</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@hotel.com" required autofocus>
                  </div>
                </div>

                <div class="mb-2">
                  <label for="password" class="form-label"><i class="bi bi-lock-fill me-1"></i> Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-color: var(--arena); color: var(--olivo-oscuro);">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="form-check mb-2">
                  <input type="checkbox" class="form-check-input" id="remember" name="remember">
                  <label class="form-check-label" for="remember">Recordar sesión</label>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn login-btn">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                  </button>
                </div>
              </form>

              <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-text"><i class="bi bi-info-circle me-1"></i> Usuarios de Prueba</div>
                <div class="divider-line"></div>
              </div>

              <div class="row g-2">
                <div class="col-6">
                  <div class="user-card text-center">
                    <div class="user-icon"><i class="bi bi-person-badge"></i></div>
                    <div>rh@hotel.com<br><strong>password123</strong></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="user-card text-center">
                    <div class="user-icon"><i class="bi bi-person-gear"></i></div>
                    <div>gerencia@hotel.com<br><strong>password123</strong></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Información lateral -->
          <div class="col-lg-5 d-none d-lg-block">
            <div class="info-section">
              <div class="info-icon"><i class="bi bi-shield-lock"></i></div>
              <div class="info-title">Sistema Seguro</div>
              <p class="info-text">Administración de Fichas Técnicas de los Trabajadores TABASCO INN.</p>

              <div class="row text-center g-2 mt-3">
                <div class="col-4">
                  <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-shield-shaded"></i></div>
                    <p class="feature-title">Seguro</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-speedometer2"></i></div>
                    <p class="feature-title">Rápido</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-patch-check"></i></div>
                    <p class="feature-title">Confiable</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <i class="bi bi-shield-check me-1" style="color: var(--arena);"></i> Sistema Seguro |
        <i class="bi bi-clock me-1" style="color: var(--arena);"></i> Laravel 12 |
        <i class="bi bi-heart-fill me-1" style="color: var(--arena);"></i> Hotel Administration
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });
  </script>
</body>
</html>
