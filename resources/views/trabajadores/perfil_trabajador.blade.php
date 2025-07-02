@extends('layouts.app')

@section('title', 'Perfil de ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<style>
    /* Pestañas nav-pills con texto e iconos negros */
    .nav-pills .nav-link {
        color: black; /* texto negro */
        font-weight: 600;
        border-radius: 0.5rem;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #e7f1ff;
        color: black;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd; /* azul bootstrap */
        color: black; /* texto negro */
        box-shadow: 0 0 8px rgb(13 110 253 / 0.5);
    }
    /* Iconos en pestañas (negros) */
    .nav-pills .nav-link i {
        margin-right: 6px;
        font-size: 1.1rem;
        vertical-align: middle;
        color: inherit; /* hereda color del texto */
    }
</style>


<div class="container-fluid" data-trabajador-id="{{ $trabajador->id_trabajador }}">
    <!-- Header del Perfil -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge"></i> Información del Trabajador
                        </h5>
                        {{-- Selector de estatus mejorado --}}
                        <form action="{{ route('trabajadores.perfil.update-estatus', $trabajador) }}" method="POST" class="d-flex gap-2 align-items-center" style="max-width: 300px;">
                            @csrf
                            @method('PUT')
                            <select class="form-select form-select-sm" name="estatus" id="estatus-select">
                                @foreach(App\Models\Trabajador::TODOS_ESTADOS as $key => $estado)
                                    <option value="{{ $key }}" {{ $trabajador->estatus == $key ? 'selected' : '' }}>
                                        {{ $estado }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                    </div>

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
                                    <div class="col-3">
                                        <div class="h4 text-primary mb-0">{{ $trabajador->antiguedad_texto ?? 'N/A' }}</div>
                                        <small class="text-muted">Antigüedad</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-success mb-0">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}</div>
                                        <small class="text-muted">Sueldo Diario</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-info mb-0">{{ $stats['porcentaje_documentos'] ?? 0 }}%</div>
                                        <small class="text-muted">Documentos</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-warning mb-0">{{ $trabajador->saldo_horas_extra }}</div>
                                        <small class="text-muted">Horas Extra</small>
                                    </div>
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
                        <button class="nav-link" id="nav-horas-tab" data-bs-toggle="tab" data-bs-target="#nav-horas" type="button" role="tab">
                            <i class="bi bi-clock"></i> Horas Extra 
                            @if($trabajador->saldo_horas_extra > 0)
                                <span class="badge bg-warning text-dark ms-1">{{ $trabajador->saldo_horas_extra }}</span>
                            @endif
                        </button>
                        <button class="nav-link" id="nav-contratos-tab" data-bs-toggle="tab" data-bs-target="#nav-contratos" type="button" role="tab">
                            <i class="bi bi-file-earmark-text"></i> Contratos
                        </button>
                        <button class="nav-link" id="nav-permisos-tab" data-bs-toggle="tab" data-bs-target="#nav-permisos" type="button" role="tab">
                            <i class="bi bi-calendar-check"></i> Permisos
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

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-datos" role="tabpanel" aria-labelledby="nav-datos-tab">
                    @include('trabajadores.secciones_perfil.datos_personales')
                </div>

                <div class="tab-pane fade" id="nav-laborales" role="tabpanel" aria-labelledby="nav-laborales-tab">
                    @include('trabajadores.secciones_perfil.datos_laborales')
                </div>

                <div class="tab-pane fade" id="nav-documentos" role="tabpanel" aria-labelledby="nav-documentos-tab">
                    @include('trabajadores.secciones_perfil.documentos')
                </div>

                <div class="tab-pane fade" id="nav-horas" role="tabpanel" aria-labelledby="nav-horas-tab">
                    @include('trabajadores.secciones_perfil.horas_extra')
                </div>

                <div class="tab-pane fade" id="nav-contratos" role="tabpanel" aria-labelledby="nav-contratos-tab">
                    <div id="contratos-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando contratos...</span>
                            </div>
                            <p class="mt-3 text-muted">Cargando información de contratos...</p>
                        </div>
                    </div>
                </div>

                {{-- En el contenido de pestañas --}}
                <div class="tab-pane fade" id="nav-permisos" role="tabpanel" aria-labelledby="nav-permisos-tab">
                    <div id="permisos-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando permisos...</span>
                            </div>
                            <p class="mt-3 text-muted">Cargando historial de permisos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alertas')
</div>

@include('trabajadores.modales.subir_documento', ['trabajador' => $trabajador])

@if($trabajador->fichaTecnica)
    @include('trabajadores.modales.crear_contrato', ['trabajador' => $trabajador])
@endif

@include('trabajadores.modales.asignar_horas_extras', ['trabajador' => $trabajador])
@include('trabajadores.modales.restar_horas_extras', [
    'trabajador' => $trabajador,
    'saldoActual' => $trabajador->saldo_horas_extra
])

{{-- Al final del archivo perfil_trabajador.blade.php, antes de @endsection --}}

{{-- Scripts del perfil trabajador en orden de dependencias --}}
<script src="{{ asset('js/perfil_trabajador/perfil_scripts.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/areas_categorias.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/documentos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/contratos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/dias_laborables.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/validaciones_campos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/navegacion.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/notificaciones.js') }}"></script>

{{-- Script adicional si existe (mantener compatibilidad) --}}
@if(file_exists(public_path('js/perfil_trabajador/historiales_perfil.js')))
<script src="{{ asset('js/perfil_trabajador/historiales_perfil.js') }}"></script>
@endif

@endsection
