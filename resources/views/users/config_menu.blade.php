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
            </div>
        </div>
    </div>

    <!-- Menú de Configuración -->
    <div class="row">
        <!-- boton para vista de creacion de areas y categorias-->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #007A4D;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-folder-fill fs-1 mb-3" style="color: #007A4D;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Áreas y Categorías</h5>
                    <p class="card-text" style="color: #5D3A1A;">Administración de Áreas y Categorías</p>
                    <a href="{{ route('areas.categorias.index') }}" class="btn text-white" style="background-color: #007A4D;">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>
                </div>
            </div>
        </div>

        <!-- Botón para vista de gerentes -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #0d6efd;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-person-badge-fill fs-1 mb-3" style="color: #0d6efd;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Gerentes</h5>
                    <p class="card-text" style="color: #5D3A1A;">Administración del personal gerencial</p>
                    <a href="{{ route('gerentes.index') }}" class="btn text-white" style="background-color: #0d6efd;">
                        <i class="bi bi-eye-fill"></i> Ver Gerentes
                    </a>
                </div>
            </div>
        </div>

        <!-- Botón para Plantillas de Contrato -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100 card-hover" style="border-top: 3px solid #6f42c1;">
                <div class="card-body text-center" style="background-color: #FFFFFF;">
                    <i class="bi bi-file-earmark-text-fill fs-1 mb-3" style="color: #6f42c1;"></i>
                    <h5 class="card-title" style="color: #2F2F2F;">Plantillas de Contrato</h5>
                    <p class="card-text" style="color: #5D3A1A;">Editor de plantillas de contratos laborales</p>
                    <a href="{{ route('configuracion.plantillas.index') }}" class="btn text-white" style="background-color: #6f42c1;">
                        <i class="bi bi-file-earmark-edit"></i> Administrar
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