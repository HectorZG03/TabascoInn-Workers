<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Sistema de Fichas Técnicas - Hotel')</title>

    <!-- Google Fonts (Montserrat) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<style>
            /* Navbar encapsulado para no afectar otras vistas */
        .navbar-custom {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #F5E6D3 0%, #E6D7C3 50%, #D4C4A8 100%);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.12);
            border-bottom: 4px solid #BBA973;
            padding-top: 1rem;
            padding-bottom: 1rem;
            position: relative;
            z-index: 1030;
        }

        /* Navbar Brand */
        .navbar-custom .navbar-brand {
            font-weight: 600;
            color: #fff !important;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
            letter-spacing: 0.05em;
            transition: transform 0.3s ease;
            user-select: none;
        }

        .navbar-custom .navbar-brand:hover {
            color: #fff !important;
            transform: scale(1.05);
            text-decoration: none;
        }

        .navbar-custom .logo-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid #BBA973;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
            transition: box-shadow 0.3s ease;
            object-fit: cover;
        }

        .navbar-custom .logo-img:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
        }

        /* Nav links */
        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
            font-size: 1rem;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
            margin-left: 0.8rem;
            padding: 0.3rem 0.6rem;
            border-radius: 0.35rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff !important;
            transform: translateY(-3px);
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            outline: none;
        }

        /* Icono usuario animado */
        .navbar-custom .bi-person-circle {
            font-size: 1.3rem;
            margin-right: 6px;
            transition: transform 0.3s ease;
            vertical-align: middle;
        }

        .navbar-custom .nav-link:hover .bi-person-circle {
            transform: rotate(15deg) scale(1.2);
        }

        /* Dropdown toggle arrow blanca */
        .navbar-custom .dropdown-toggle::after {
            border-top-color: #fff !important;
        }

        /* Dropdown menu */
        .navbar-custom .dropdown-menu {
            background: linear-gradient(135deg, #F5E6D3 0%, #E6D7C3 100%);
            border: 2px solid #BBA973;
            border-radius: 0.5rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            min-width: 210px;
            padding: 0.5rem 0;
            font-weight: 500;
        }

        .navbar-custom .dropdown-menu .dropdown-item {
            color: #6B4F23;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.25s ease;
            padding: 0.5rem 1.5rem;
            border-radius: 0.25rem;
        }

        .navbar-custom .dropdown-menu .dropdown-item:hover,
        .navbar-custom .dropdown-menu .dropdown-item:focus {
            background: linear-gradient(135deg, #BBA973 0%, #A1925E 100%);
            color: #422E0B;
            transform: translateX(6px);
            outline: none;
        }

        /* Badge */
        .navbar-custom .badge {
            background: linear-gradient(135deg, #BBA973 0%, #9C8C54 100%) !important;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.35em 0.6em;
            border-radius: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.22);
            user-select: none;
        }

        /* Responsive: hamburguesa */
        @media (max-width: 991px) {
            .navbar-custom .navbar-brand {
                font-size: 1.1rem;
            }
            .navbar-custom .logo-img {
                width: 38px;
                height: 38px;
            }
            .navbar-custom .nav-link {
                font-size: 1rem;
                margin-left: 0;
                padding: 0.5rem 1rem;
            }
        }

        /* Ajuste para el botón hamburguesa */
        .navbar-custom .navbar-toggler {
            border-color: #BBA973;
        }
        .navbar-custom .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%23BBA973' stroke-width='3' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
</style>
<body class="bg-light">

    @auth
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('image/estaticas/images.png') }}" alt="Hotel Logo" class="logo-img" />
                <span>Hotel TABASCO INN - Sistema de Fichas Técnicas</span>
            </a>

            <!-- Botón hamburguesa para móviles -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú colapsable -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle d-flex align-items-center"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->nombre }}</span>
                            <span class="badge ms-2">{{ Auth::user()->tipo }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
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


    {{-- ✅ CARGAR FORMATO GLOBAL ANTES DE LOS MODALES --}}
    <script src="{{ asset('js/formato-global.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
