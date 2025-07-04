@extends('layouts.app')

@section('title', 'Nuevo Trabajador - Hotel')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-fluid">
    <!-- ✅ HEADER -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-person-plus-fill text-primary"></i> Nuevo Trabajador
                    </h2>
                    <p class="text-muted mb-0">Complete todos los datos para crear el trabajador y su contrato</p>
                </div>
                <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Ver Lista de Trabajadores
                </a>
            </div>
        </div>
    </div>

    <!-- ALERTAS -->
    @include('components.alertas')

    <!-- ✅ FORMULARIO PRINCIPAL UNIFICADO -->
    <form action="{{ route('trabajadores.store') }}" method="POST" enctype="multipart/form-data" id="formTrabajador">
        @csrf
        
        <div class="row">
            <!-- ✅ COLUMNA PRINCIPAL: Formularios -->
            <div class="col-lg-8">
                <!-- SECCIÓN 1: Datos Personales -->
                @include('trabajadores.secciones_crear.datos_personales')
                
                <!-- SECCIÓN 2: Datos Laborales -->
                @include('trabajadores.secciones_crear.datos_laborales', ['areas' => $areas])
                
                <!-- ✅ SECCIÓN 3: Estado y Contrato (NUEVA) -->
                @include('trabajadores.secciones_crear.estado_y_contrato')
                
                <!-- SECCIÓN 4: Contacto de Emergencia -->
                @include('trabajadores.secciones_crear.contacto_emergencia')

                <!-- SECCIÓN 5: Botones de Acción -->
                @include('trabajadores.secciones_crear.botones_accion')
            </div>

            <!-- ✅ COLUMNA LATERAL: Vista Previa -->
            <div class="col-lg-4">
                @include('trabajadores.secciones_crear.vista_previa')
                @include('trabajadores.secciones_crear.panel_ayuda')
            </div>
        </div>
    </form>
</div>

<!-- ✅ SCRIPTS: PRIMERO EL GLOBAL, LUEGO EL ESPECÍFICO -->
<script src="{{ asset('js/formato-global.js') }}"></script>
<script src="{{ asset('js/crear_trabajador.js') }}"></script>

@endsection