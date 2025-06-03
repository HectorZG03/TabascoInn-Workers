@extends('layouts.app')

@section('title', 'Configuración - Hotel TABASCO INN')

@section('content')
<div class="container-fluid">
    <!-- Header de Configuración -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0" style="background: linear-gradient(to right, #F5F5DC, #FFFFFF);">
                <div class="card-header py-3 header-verde">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-gear-fill"></i> Configuración del Sistema
                        </h3>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body text-center py-4" style="background-color: #E6F2ED;">
                    <h4 class="mb-3" style="color: #007A4D;">
                        <i class="bi bi-person-circle"></i> {{ $user->nombre }}
                    </h4>
                    <p class="lead mb-2" style="color: #5D3A1A;">
                        Gestiona tu perfil y las configuraciones del sistema
                    </p>
                    <div class="badge text-white fs-6 px-3 py-2" style="background-color: {{ $user->esGerencia() ? '#007A4D' : '#D2B48C' }};">
                        <i class="bi bi-{{ $user->esGerencia() ? 'person-gear' : 'people' }}"></i>
                        {{ $user->tipo ?? 'Usuario' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú de Configuración -->
    <div class="row">
        <!-- Perfil Personal -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #007A4D;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-person-fill fs-1 mb-3" style="color: #007A4D;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Mi Perfil</h5>
                    <p class="card-text" style="color: #5D3A1A;">Actualizar información personal y datos de contacto</p>
                    <a href="{{ route('users.profile') }}" class="btn text-white" style="background-color: #007A4D;">
                        <i class="bi bi-pencil-square"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #D2B48C;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-shield-lock-fill fs-1 mb-3" style="color: #D2B48C;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Seguridad</h5>
                    <p class="card-text" style="color: #5D3A1A;">Cambiar contraseña y configurar seguridad de la cuenta</p>
                    <a href="{{ route('users.change-password') }}" class="btn text-white" style="background-color: #D2B48C;">
                        <i class="bi bi-key-fill"></i> Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>

        <!-- Preferencias del Sistema -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #007A4D;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-sliders fs-1 mb-3" style="color: #007A4D;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Preferencias</h5>
                    <p class="card-text" style="color: #5D3A1A;">Configurar idioma, tema y notificaciones del sistema</p>
                    <a href="{{ route('users.preferences') }}" class="btn" style="border-color: #007A4D; color: #007A4D;">
                        <i class="bi bi-gear"></i> Configurar
                    </a>
                </div>
            </div>
        </div>

        @if($user->esGerencia())
        <!-- Gestión de Usuarios (Solo para Gerencia) -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #28a745;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-people-fill fs-1 mb-3" style="color: #28a745;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Gestión de Usuarios</h5>
                    <p class="card-text" style="color: #5D3A1A;">Administrar usuarios del sistema y sus permisos</p>
                    <a href="{{ route('users.manage') }}" class="btn text-white" style="background-color: #28a745;">
                        <i class="bi bi-person-gear"></i> Administrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Configuración del Sistema (Solo para Gerencia) -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #17a2b8;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-tools fs-1 mb-3" style="color: #17a2b8;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Config. Sistema</h5>
                    <p class="card-text" style="color: #5D3A1A;">Configuraciones generales y parámetros del sistema</p>
                    <a href="{{ route('system.config') }}" class="btn text-white" style="background-color: #17a2b8;">
                        <i class="bi bi-wrench"></i> Configurar
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Actividad Reciente -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #ffc107;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-clock-history fs-1 mb-3" style="color: #ffc107;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Actividad Reciente</h5>
                    <p class="card-text" style="color: #5D3A1A;">Ver tu historial de actividades en el sistema</p>
                    <a href="{{ route('users.activity') }}" class="btn" style="border-color: #ffc107; color: #856404;">
                        <i class="bi bi-list-ul"></i> Ver Historial
                    </a>
                </div>
            </div>
        </div>

        <!-- Ayuda y Soporte -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #6f42c1;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-question-circle-fill fs-1 mb-3" style="color: #6f42c1;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Ayuda</h5>
                    <p class="card-text" style="color: #5D3A1A;">Manual de usuario y soporte técnico del sistema</p>
                    <a href="{{ route('help.index') }}" class="btn text-white" style="background-color: #6f42c1;">
                        <i class="bi bi-book"></i> Ver Ayuda
                    </a>
                </div>
            </div>
        </div>

        <!-- Cerrar Sesión -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #dc3545;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-box-arrow-right fs-1 mb-3" style="color: #dc3545;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Cerrar Sesión</h5>
                    <p class="card-text" style="color: #5D3A1A;">Salir de forma segura del sistema</p>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn text-white" style="background-color: #dc3545;" 
                                onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                            <i class="bi bi-power"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 header-verde">
                    <h5 class="mb-0 text-center text-white">
                        <i class="bi bi-info-circle"></i> Información del Sistema
                    </h5>
                </div>
                <div class="card-body py-4" style="background-color: #E6F2ED;">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-item">
                                <i class="bi bi-calendar-event fs-2" style="color: #007A4D;"></i>
                                <h6 class="mt-2 mb-1" style="color: #007A4D;">Último Acceso</h6>
                                <small style="color: #2F2F2F;">{{ $user->last_login_at ?? 'Primera vez' }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-item">
                                <i class="bi bi-person-badge fs-2" style="color: #007A4D;"></i>
                                <h6 class="mt-2 mb-1" style="color: #007A4D;">Usuario Desde</h6>
                                <small style="color: #2F2F2F;">{{ $user->created_at->format('d/m/Y') ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-item">
                                <i class="bi bi-shield-check fs-2" style="color: #007A4D;"></i>
                                <h6 class="mt-2 mb-1" style="color: #007A4D;">Estado Cuenta</h6>
                                <small style="color: #2F2F2F;">Activa</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-item">
                                <i class="bi bi-laptop fs-2" style="color: #007A4D;"></i>
                                <h6 class="mt-2 mb-1" style="color: #007A4D;">Versión Sistema</h6>
                                <small style="color: #2F2F2F;">v1.0.0</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS para efectos de hover -->
<style>
.header-verde {
    background-color: #007A4D !important;
}

.card-hover {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 122, 77, 0.15) !important;
}

.stat-item {
    padding: 1rem;
    border-radius: 8px;
    background-color: rgba(255, 255, 255, 0.8);
    margin: 0.5rem 0;
}
</style>

@endsection