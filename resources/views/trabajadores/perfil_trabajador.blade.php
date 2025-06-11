{{-- resources/views/trabajadores/perfil_trabajador.blade.php --}}

@extends('layouts.app')

@section('title', 'Perfil de ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header del Perfil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                                {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="mb-1">{{ $trabajador->nombre_completo }}</h2>
                            <p class="text-muted mb-1">
                                <i class="bi bi-briefcase"></i> 
                                {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                            </p>
                            <p class="text-muted mb-1">
                                <i class="bi bi-building"></i> 
                                {{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}
                            </p>
                            <div class="d-flex gap-2 mt-2">
                                @if($trabajador->es_nuevo)
                                    <span class="badge bg-info">
                                        <i class="bi bi-star"></i> Nuevo
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 text-primary mb-0">{{ $trabajador->antiguedad_texto ?? 'N/A' }}</div>
                                    <small class="text-muted">Antigüedad</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-success mb-0">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}</div>
                                    <small class="text-muted">Sueldo Diario</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-info mb-0">{{ $stats['porcentaje_documentos'] ?? 0 }}%</div>
                                    <small class="text-muted">Documentos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <nav>
                    <div class="nav nav-pills" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-datos-tab" data-bs-toggle="tab" data-bs-target="#nav-datos" type="button" role="tab">
                            <i class="bi bi-person"></i> Datos Personales
                        </button>
                        <button class="nav-link" id="nav-laborales-tab" data-bs-toggle="tab" data-bs-target="#nav-laborales" type="button" role="tab">
                            <i class="bi bi-briefcase"></i> Datos Laborales
                        </button>
                        <button class="nav-link" id="nav-documentos-tab" data-bs-toggle="tab" data-bs-target="#nav-documentos" type="button" role="tab">
                            <i class="bi bi-files"></i> Documentos
                        </button>
                    </div>
                </nav>
                <div>
                    <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ CONTENIDO DE LAS PESTAÑAS -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="nav-tabContent">
                <!-- Pestaña de Datos Personales -->
                <div class="tab-pane fade show active" id="nav-datos" role="tabpanel" aria-labelledby="nav-datos-tab">
                    @include('trabajadores.secciones_perfil.datos_personales')
                </div>

                <!-- Pestaña de Datos Laborales -->
                <div class="tab-pane fade" id="nav-laborales" role="tabpanel" aria-labelledby="nav-laborales-tab">
                    @include('trabajadores.secciones_perfil.datos_laborales')
                </div>

                <!-- Pestaña de Documentos -->
                <div class="tab-pane fade" id="nav-documentos" role="tabpanel" aria-labelledby="nav-documentos-tab">
                    @include('trabajadores.secciones_perfil.documentos')
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ALERTAS DESPUÉS DEL CONTENIDO -->
    @include('components.alertas')
</div>

{{-- ✅ MODALES SEPARADOS --}}
@include('trabajadores.modales.subir_documento', ['trabajador' => $trabajador])

{{-- ✅ JAVASCRIPT ESPECÍFICO DEL PERFIL --}}
@include('trabajadores.secciones_perfil.perfil_scripts')

@endsection