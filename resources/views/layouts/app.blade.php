<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Sistema de Fichas Técnicas - Hotel')</title>
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <!-- Google Fonts (Montserrat) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        /* ✅ ESTILOS MEJORADOS PARA NOTIFICACIONES */
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(25%, -25%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: translate(25%, -25%) scale(1); }
            50% { transform: translate(25%, -25%) scale(1.1); }
            100% { transform: translate(25%, -25%) scale(1); }
        }
        
        .notification-item {
            white-space: normal;
            max-width: 400px;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 4px 8px;
        }
        
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .notification-scroll {
            max-height: 600px;
            overflow-y: auto;
        }
        
        /* Dropdown de notificaciones más ancho */
        #notificationDropdown + .dropdown-menu {
            width: 450px;
            max-height: 650px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        /* Estilos para diferentes niveles de urgencia */
        .urgencia-critica {
            border-left: 4px solid #dc3545 !important;
            background: linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(220,53,69,0.05) 100%);
        }
        
        .urgencia-alta {
            border-left: 4px solid #ffc107 !important;
            background: linear-gradient(90deg, rgba(255,193,7,0.1) 0%, rgba(255,193,7,0.05) 100%);
        }
        
        .urgencia-media {
            border-left: 4px solid #0dcaf0 !important;
            background: linear-gradient(90deg, rgba(13,202,240,0.1) 0%, rgba(13,202,240,0.05) 100%);
        }
        
        /* Efectos para botones de notificación */
        .notification-item .btn {
            transition: all 0.2s ease;
        }
        
        .notification-item .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Badge pulsante para notificaciones críticas */
        .badge.bg-danger {
            animation: pulse-badge 1.5s infinite;
        }
        
        @keyframes pulse-badge {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        /* Fondo body más cálido */
        body.bg-light {
            background-color: #fdfaf4 !important;
        }
        
        /* Contador de notificaciones según urgencia */
        .notification-badge.badge-critica {
            background-color: #dc3545 !important;
            animation: pulse-critical 1s infinite;
        }
        
        @keyframes pulse-critical {
            0% { background-color: #dc3545; }
            50% { background-color: #bb2d3b; }
            100% { background-color: #dc3545; }
        }
        
        /* Estilos para el dropdown header */
        .dropdown-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            border-radius: 0.375rem 0.375rem 0 0;
            margin: -0.5rem -0.5rem 0.5rem -0.5rem;
            padding: 1rem;
        }
        
        .dropdown-header .btn-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .dropdown-header .btn-link:hover {
            color: white;
        }
    </style>
</head>

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
                    <!-- ✅ ICONO DE NOTIFICACIONES MEJORADO -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-4"></i>
                            <span id="notificationCounter" class="notification-badge badge bg-danger rounded-pill">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationDropdown">
                            <li>
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-bell me-2"></i>Notificaciones del Sistema</span>
                                    <button id="markAsRead" class="btn btn-sm btn-link p-0" title="Marcar todas como vistas">
                                        <i class="bi bi-check2-all"></i>
                                    </button>
                                </div>
                            </li>
                            <div id="notificationList" class="notification-scroll">
                                <li class="px-3 py-3 text-center text-muted">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <span>Cargando notificaciones...</span>
                                </li>
                            </div>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div class="px-3 py-2 text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Las notificaciones se actualizan automáticamente
                                    </small>
                                </div>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Menú de usuario -->
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

    <script src="{{ asset('js/formato-global.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- ✅ SCRIPT DE NOTIFICACIONES MEJORADO -->
    <!-- En el archivo app.blade.php, reemplaza la sección de JavaScript de notificaciones -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationCounter = document.getElementById('notificationCounter');
        const notificationList = document.getElementById('notificationList');
        const markAsReadBtn = document.getElementById('markAsRead');
        
        // ✅ GESTIÓN DE NOTIFICACIONES PERSISTENTES
        let notificacionesProcesadas = JSON.parse(localStorage.getItem('notificaciones_procesadas') || '[]');
        let ultimaActualizacion = localStorage.getItem('ultima_actualizacion_notificaciones') || Date.now();
        
        // Limpiar notificaciones procesadas más antiguas (24 horas)
        const hace24Horas = Date.now() - (24 * 60 * 60 * 1000);
        notificacionesProcesadas = notificacionesProcesadas.filter(item => item.timestamp > hace24Horas);
        localStorage.setItem('notificaciones_procesadas', JSON.stringify(notificacionesProcesadas));
        
        function obtenerIconoYColor(tipo) {
            const configuracion = {
                'vacacion_inicio': { icon: 'bi-sun', color: 'text-success' },
                'vacacion_fin': { icon: 'bi-sunset', color: 'text-warning' },
                'vacacion_vencida': { icon: 'bi-exclamation-triangle', color: 'text-danger' },
                'contrato_expiracion': { icon: 'bi-file-earmark-text', color: 'text-warning' },
                'contrato_vencido': { icon: 'bi-file-earmark-x', color: 'text-danger' },
                'permiso_fin': { icon: 'bi-clock-history', color: 'text-info' },
                'despido_reintegro': { icon: 'bi-person-check', color: 'text-primary' }
            };
            
            return configuracion[tipo] || { icon: 'bi-info-circle', color: 'text-secondary' };
        }
        
        function obtenerClaseUrgencia(urgencia) {
            switch (urgencia) {
                case 'critica': return 'border-danger bg-danger bg-opacity-10';
                case 'alta': return 'border-warning bg-warning bg-opacity-10';
                case 'media': return 'border-info bg-info bg-opacity-10';
                default: return 'border-secondary';
            }
        }
        
        function estaNotificacionProcesada(notificationId) {
            return notificacionesProcesadas.some(item => item.id === notificationId);
        }
        
        function marcarNotificacionComoProcesada(notificationId) {
            if (!estaNotificacionProcesada(notificationId)) {
                notificacionesProcesadas.push({
                    id: notificationId,
                    timestamp: Date.now()
                });
                localStorage.setItem('notificaciones_procesadas', JSON.stringify(notificacionesProcesadas));
            }
        }
        
        function fetchNotifications() {
            fetch('{{ route("notificaciones.eventos") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                // Filtrar notificaciones ya procesadas
                const notificacionesPendientes = data.filter(notification => 
                    !estaNotificacionProcesada(notification.id)
                );
                
                // Actualizar contador con clase según urgencia
                notificationCounter.textContent = notificacionesPendientes.length;
                notificationCounter.className = 'notification-badge badge rounded-pill';
                
                if (notificacionesPendientes.length > 0) {
                    const hayUrgente = notificacionesPendientes.some(n => n.urgencia === 'critica');
                    notificationCounter.classList.add(hayUrgente ? 'bg-danger' : 'bg-warning');
                } else {
                    notificationCounter.classList.add('bg-secondary');
                }
                
                if (notificacionesPendientes.length === 0) {
                    notificationList.innerHTML = `
                        <li class="px-3 py-3 text-center text-muted">
                            <i class="bi bi-check-circle text-success me-2 fs-4"></i>
                            <div>No hay notificaciones pendientes</div>
                            <small class="text-muted">Todas las tareas están al día</small>
                        </li>
                    `;
                } else {
                    let notificationsHTML = '';
                    
                    // Ordenar por urgencia (crítica > alta > media)
                    const ordenUrgencia = { 'critica': 3, 'alta': 2, 'media': 1 };
                    notificacionesPendientes.sort((a, b) => 
                        (ordenUrgencia[b.urgencia] || 0) - (ordenUrgencia[a.urgencia] || 0)
                    );
                    
                    notificacionesPendientes.forEach(notification => {
                        const { icon, color } = obtenerIconoYColor(notification.tipo);
                        const claseUrgencia = obtenerClaseUrgencia(notification.urgencia);
                        
                        let badgeUrgencia = '';
                        if (notification.urgencia === 'critica') {
                            badgeUrgencia = '<span class="badge bg-danger ms-2"><i class="bi bi-exclamation-triangle"></i> URGENTE</span>';
                        } else if (notification.urgencia === 'alta') {
                            badgeUrgencia = '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-clock"></i> PRIORITARIO</span>';
                        }
                        
                        // ✅ ELIMINADO EL BOTÓN DE "RESOLVER" - SOLO QUEDA EL BOTÓN DE MARCAR COMO VISTA
                        notificationsHTML += `
                            <li class="border-bottom">
                                <div class="notification-item p-3 ${claseUrgencia}" data-notification-id="${notification.id}">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3 ${color}">
                                            <i class="bi ${icon} fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold d-flex align-items-center flex-wrap">
                                                ${notification.trabajador}
                                                ${badgeUrgencia}
                                            </div>
                                            <div class="small text-muted mb-2">${notification.mensaje}</div>
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-sm btn-outline-secondary marcar-procesada" 
                                                        data-notification-id="${notification.id}"
                                                        title="Marcar como vista">
                                                    <i class="bi bi-eye"></i> Marcar como vista
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    
                    notificationList.innerHTML = notificationsHTML;
                    
                    // Agregar event listeners para botones "marcar como procesada"
                    document.querySelectorAll('.marcar-procesada').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const notificationId = this.getAttribute('data-notification-id');
                            marcarNotificacionComoProcesada(notificationId);
                            
                            // Mostrar feedback temporal
                            const originalHTML = this.innerHTML;
                            this.innerHTML = '<i class="bi bi-check-circle text-success"></i> Vista';
                            this.disabled = true;
                            
                            setTimeout(() => {
                                fetchNotifications();
                            }, 500);
                        });
                    });
                }
                
                // Actualizar timestamp
                ultimaActualizacion = Date.now();
                localStorage.setItem('ultima_actualizacion_notificaciones', ultimaActualizacion);
                
            })
            .catch(error => {
                console.error('Error al obtener notificaciones:', error);
                notificationList.innerHTML = `
                    <li class="px-3 py-3 text-center text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <div>Error al cargar notificaciones</div>
                        <small>Reintentando automáticamente...</small>
                    </li>
                `;
            });
        }
        
        // Marcar todas como procesadas
        markAsReadBtn.addEventListener('click', function() {
            fetch('{{ route("notificaciones.eventos") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                data.forEach(notification => {
                    marcarNotificacionComoProcesada(notification.id);
                });
                
                fetchNotifications();
                
                // Feedback visual
                this.innerHTML = '<i class="bi bi-check-all text-success"></i>';
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-check2-all"></i>';
                }, 1000);
            });
        });
        
        // Cargar notificaciones inicialmente
        fetchNotifications();
        
        // Actualizar cada 30 segundos
        setInterval(fetchNotifications, 30000);
        
        // Refrescar notificaciones cuando se regresa a la página
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                fetchNotifications();
            }
        });
    });
    </script>
    
    @yield('scripts')
</body>
</html>