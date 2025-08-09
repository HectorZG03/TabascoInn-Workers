@extends('layouts.app')

@section('title', 'Dashboard - Hotel')

@section('content')

<div class="dashboard-container">

    <!-- 🕒 Reloj animado -->
    <div class="clock-widget">
        <div id="clock-time">00:00:00</div>
        <div id="clock-date">Cargando fecha...</div>
    </div>

    <!-- Grid de funcionalidades -->
    <div class="functions-grid">
        @if (Auth::user()->tienePermiso('trabajadores', 'crear'))
            <div class="function-card" data-category="empleados">
                <div class="card-accent accent-primary"></div>
                <div class="card-content">
                    <div class="card-icon icon-primary">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="card-title">Alta de Empleado</h3>
                    <p class="card-description">Registro de nuevos empleados en el sistema</p>
                    <a href="{{ route('trabajadores.create') }}" class="btn-primary">
                        <span>Registrar</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        @if (Auth::user()->tienePermiso('trabajadores', 'ver'))
            <div class="function-card" data-category="trabajadores">
                <div class="card-accent accent-secondary"></div>
                <div class="card-content">
                    <div class="card-icon icon-secondary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="card-title">Trabajadores Activos</h3>
                    <p class="card-description">Consulta y gestión de empleados activos</p>
                    <a href="{{ route('trabajadores.index') }}" class="btn-secondary">
                        <span>Ver lista</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        @if (Auth::user()->tienePermiso('despidos', 'ver'))
            <div class="function-card" data-category="bajas">
                <div class="card-accent accent-warning"></div>
                <div class="card-content">
                    <div class="card-icon icon-warning">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <h3 class="card-title">Bajas de Empleados</h3>
                    <p class="card-description">Gestión de trabajadores dados de baja</p>
                    <a href="{{ route('despidos.index') }}" class="btn-warning">
                        <span>Gestionar</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        @if (Auth::user()->tienePermiso('permisos_laborales', 'ver'))
            <div class="function-card" data-category="permisos">
                <div class="card-accent accent-info"></div>
                <div class="card-content">
                    <div class="card-icon icon-info">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <h3 class="card-title">Permisos Activos</h3>
                    <p class="card-description">Supervisión de permisos laborales</p>
                    <a href="{{ route('permisos.index') }}" class="btn-info">
                        <span>Supervisar</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        @if (Auth::user()->esAdministrador())
            <div class="function-card" data-category="config">
                <div class="card-accent accent-neutral"></div>
                <div class="card-content">
                    <div class="card-icon icon-neutral">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h3 class="card-title">Configuración</h3>
                    <p class="card-description">Ajustes generales del sistema</p>
                    <a href="{{ route('users.config') }}" class="btn-neutral">
                        <span>Configurar</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<script>
    // Reloj digital animado
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('es-MX', { hour12: false });
        const date = now.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        document.getElementById('clock-time').textContent = time;
        document.getElementById('clock-date').textContent = date.charAt(0).toUpperCase() + date.slice(1);
    }

    setInterval(updateClock, 1000);
    updateClock();
</script>

@endsection
