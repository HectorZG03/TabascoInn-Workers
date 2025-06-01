<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Fichas Técnicas - Hotel')</title>
   
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
   
    <style>
        /* Navbar personalizado con tonos arena */
        .navbar-custom {
            background: linear-gradient(135deg, #F5E6D3 0%, #E6D7C3 50%, #D4C4A8 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #D4C4A8;
        }
        
        .navbar-brand {
            font-weight: bold;
            /* CAMBIO: Texto blanco */
            color: white !important; 
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand:hover {
            /* CAMBIO: Texto blanco en hover */
            color: white !important;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
        
        .logo-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #C4B49C;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .nav-link {
            /* CAMBIO: Texto blanco */
            color: white !important; 
            font-weight: 500;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            /* CAMBIO: Texto blanco en hover */
            color: white !important;
            transform: translateY(-1px);
        }
        
        .dropdown-toggle::after {
            /* CAMBIO: Flecha blanca */
            border-top-color: white; 
        }
        
        .dropdown-menu {
            background: linear-gradient(135deg, #F5E6D3 0%, #E6D7C3 100%);
            border: 2px solid #D4C4A8;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-item {
            color: #8B4513;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, #D4C4A8 0%, #C4B49C 100%);
            color: #654321;
            transform: translateX(5px);
        }
        
        .badge {
            background: linear-gradient(135deg, #C4B49C 0%, #A0926C 100%) !important;
            color: white;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #D4C4A8 0%, #C4B49C 100%);
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #D4C4A8 0%, #C4B49C 100%);
            border: none;
            color: white;
            font-weight: 500;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background: linear-gradient(135deg, #C4B49C 0%, #A0926C 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* Efecto de hover en el navbar */
        .navbar-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(245, 230, 211, 0.1) 0%, rgba(230, 215, 195, 0.1) 50%, rgba(212, 196, 168, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .navbar-custom:hover::before {
            opacity: 1;
        }
        
        /* Animación sutil para el icono de usuario */
        .bi-person-circle {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover .bi-person-circle {
            transform: rotate(10deg) scale(1.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 0.9rem;
            }
            
            .logo-img {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body class="bg-light">
   
    @auth
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('image/estaticas/images.png') }}" alt="Hotel Logo" class="logo-img">
                <span>Hotel TABASCO INN - Sistema de Fichas Técnicas</span>
            </a>
           
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->nombre }}
                        <span class="badge ms-1">{{ Auth::user()->tipo }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    
    <!-- Contenido principal -->
    <main class="py-4">
        <div class="container">
           
            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    @yield('scripts')
</body>
</html>