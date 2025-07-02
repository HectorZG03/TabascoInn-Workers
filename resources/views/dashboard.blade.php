@extends('layouts.app')

@section('title', 'Dashboard - Hotel')

@section('content')
<div class="dashboard-container">
    <!-- Tarjeta de bienvenida -->
    <div class="welcome-section">
        <div class="welcome-card">
            <div class="welcome-header">
                <div class="welcome-icon">
                    <i class="bi bi-house-heart"></i>
                </div>
                <h1 class="welcome-title">¡Hola {{ $user->nombre }}!</h1>
                <p class="welcome-subtitle">Sistema de Administración de Fichas Técnicas</p>
                <span class="badge {{ $user->esGerencia() ? 'badge-management' : 'badge-user' }}">
                    <i class="bi bi-{{ $user->esGerencia() ? 'person-gear' : 'people' }}"></i>
                    {{ str_replace('_', ' ', $user->tipo) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Grid de funcionalidades -->
    <div class="functions-grid">
        <!-- Alta de Empleado -->
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

        <!-- Gestión de Trabajadores -->
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

        <!-- Bajas de Empleados -->
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

        <!-- Permisos Activos -->
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

        <!-- Configuración -->
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
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection