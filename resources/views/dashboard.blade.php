@extends('layouts.app')

@section('title', 'Dashboard - Hotel')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Saludo personalizado -->
        <div class="card shadow mb-4 border-0" style="background: linear-gradient(to right, #F5F5DC, #FFFFFF);">
            <!-- ✅ CORREGIDO: Agregado !important para forzar el color verde esmeralda -->
            <div class="card-header py-3 header-verde">
                <h3 class="mb-0 text-white">
                    <i class="bi bi-house-heart"></i> Bienvenido al Sistema
                </h3>
            </div>
            <div class="card-body text-center py-5" style="background-color: #E6F2ED;"> <!-- Fondo verde claro -->
                <h1 class="display-4 mb-3" style="color: #007A4D;"> <!-- Verde esmeralda -->
                    ¡Hola {{ $user->nombre }}!
                </h1>
                <p class="lead mb-4" style="color: #5D3A1A;">
                    Bienvenido al Sistema de Administración de Fichas Técnicas del Hotel TABASCO INN 
                </p>
                <div class="badge text-white fs-6 px-3 py-2" style="background-color: {{ $user->esGerencia() ? '#007A4D' : '#D2B48C' }};">
                    <i class="bi bi-{{ $user->esGerencia() ? 'person-gear' : 'people' }}"></i>
                    Acceso: {{ str_replace('_', ' ', $user->tipo) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gestión de Empleados -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow h-100 card-hover" style="border-top: 3px solid #007A4D;">
            <div class="card-body text-center" style="background-color: #FFFFFF;">
                <i class="bi bi-person-plus-fill fs-1 mb-3" style="color: #007A4D;"></i>
                <h5 class="card-title" style="color: #2F2F2F;">Creacion Empleados</h5>
                <p class="card-text" style="color: #5D3A1A;">Creación de Nuevos Empleados en el sistema</p>
                <a href="{{ route('trabajadores.create') }}" class="btn text-white" style="background-color: #007A4D;">
                    <i class="bi bi-arrow-right"></i> Acceder
                </a>
            </div>
        </div>
    </div>

    <!-- Reportes del Sistema -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow h-100 card-hover" style="border-top: 3px solid #D2B48C;">
            <div class="card-body text-center" style="background-color: #FFFFFF;">
                <i class="bi bi-file-earmark-text fs-1 mb-3" style="color: #12e416;"></i>
                <h5 class="card-title" style="color: #2F2F2F;">Gestion de Trabajadores Activos</h5>
                <p class="card-text" style="color: #5D3A1A;">Lista de trabajadores en el sistema, Asignacion de permisos y bajas </p>
                    <a href="{{ route('trabajadores.index') }}" class="btn text-white" style="background-color: #12e416;">
                        <i class="bi bi-arrow-right"></i> Ver lista
                    </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Ejecutivo -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow h-100 card-hover" style="border-top: 3px solid #007A4D;">
            <div class="card-body text-center" style="background-color: #FFFFFF;">
                <i class="bi bi-file-earmark-text fs-1 mb-3" style="color: #be0b0b;"></i>
                <h5 class="card-title" style="color: #2F2F2F;">Lista de Trabajzdores Inactivos</h5>
                <p class="card-text" style="color: #5D3A1A;">Lista y administracion de los Trabajadores dados de baja en el sistema</p>
                <a href="{{ route('despidos.index') }}" class="btn text-white" style="background-color: #be0b0b;">
                    <i class="bi bi-arrow-right"></i> Ver lista
                </a>
            </div>
        </div>
    </div>

    <!-- Supervisión y Monitoreo -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow h-100 card-hover" style="border-top: 3px solid #D2B48C;">
            <div class="card-body text-center" style="background-color: #FFFFFF;">
                <i class="bi bi-people fs-1 mb-3" style="color: #1f86d4;"></i>
                <h5 class="card-title" style="color: #2F2F2F;">Consulta de permisos Activos</h5>
                <p class="card-text" style="color: #5D3A1A;">Listado de trabajadores con permisos activos</p>
                <a href="{{ route('permisos.index') }}" 
                class="btn text-white" 
                style="background-color: #1f86d4; text-decoration: none;">
                    <i class="bi bi-arrow-right"></i> Supervisar
                </a>
            </div>
        </div>
    </div>


    <!-- Configuración del Sistema -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow h-100 card-hover" style="border-top: 3px solid #D2B48C;">
            <div class="card-body text-center" style="background-color: #FFFFFF;">
                <i class="bi bi-gear fs-1 mb-3" style="color: #D2B48C;"></i>
                <h5 class="card-title" style="color: #2F2F2F;">Configuración</h5>
                <p class="card-text" style="color: #5D3A1A;">Ajustes del sistema y perfil de usuario</p>
            <a href="{{ route('users.config') }}" class="btn" style="border-color: #D2B48C; color: #5D3A1A;">
                <i class="bi bi-arrow-right"></i> Configurar
            </a>
            </div>
        </div>
    </div>
</div>


<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

@endsection