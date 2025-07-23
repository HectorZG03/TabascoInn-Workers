{{-- resources/views/trabajadores/secciones_perfil/vacaciones.blade.php - SIMPLIFICADO --}}

@extends('layouts.app')

@section('title', 'Vacaciones de ' . $trabajador->nombre_completo)

@section('content')
<div class="container-fluid" data-trabajador-id="{{ $trabajador->id_trabajador }}">
    
    <!-- ✅ HEADER SIMPLIFICADO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-heart fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Gestión de Vacaciones</h4>
                                <p class="mb-0 opacity-75">{{ $trabajador->nombre_completo }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-person"></i> Ver Perfil
                            </a>
                            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Avatar y datos básicos -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                    style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem;">
                                    {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $trabajador->nombre_completo }}</h6>
                                    <p class="text-muted mb-1 small">
                                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                                    </p>
                                    <span class="badge bg-{{ $trabajador->estatus_color }} trabajador-estatus-badge">
                                        <i class="{{ $trabajador->estatus_icono }}"></i> {{ $trabajador->estatus_texto }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ESTADÍSTICAS SIMPLIFICADAS -->
                        <div class="col-md-8">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="h5 text-success mb-0" id="header-dias-correspondientes">{{ $estadisticas['dias_correspondientes_año_actual'] ?? 0 }}</div>
                                    <small class="text-muted">Días LFT</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-info mb-0" id="header-dias-restantes">{{ $estadisticas['dias_restantes_año_actual'] ?? 0 }}</div>
                                    <small class="text-muted">Disponibles</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-warning mb-0" id="header-vacaciones-activas">{{ $estadisticas['vacaciones_activas'] ?? 0 }}</div>
                                    <small class="text-muted">Activas</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-secondary mb-0" id="header-total-tomadas">{{ $estadisticas['total_dias_tomados'] ?? 0 }}</div>
                                    <small class="text-muted">Disfrutados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ACCIONES SIMPLIFICADAS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#asignarVacacionesModal">
                            <i class="bi bi-plus-lg"></i> Asignar Vacaciones
                        </button>
                    @endif
                    <a href="{{ route('trabajadores.documentos-vacaciones.index', $trabajador) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-pdf"></i> Documentos
                    </a>
                    <button class="btn btn-outline-primary" id="refresh-vacaciones">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
                
                <!-- Filtros simplificados -->
                <div class="d-flex gap-2" id="vacaciones-filtros" style="display: none;">
                    <select class="form-select form-select-sm" id="filtro-estado" style="width: auto;">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="activa">Activas</option>
                        <option value="finalizada">Finalizadas</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                    <select class="form-select form-select-sm" id="filtro-periodo" style="width: auto;">
                        <option value="">Todos los períodos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ESTADOS DE UI SIMPLIFICADOS -->
    <!-- Loading -->
    <div id="vacaciones-loading" class="text-center py-5">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
        <h5 class="text-muted">Cargando vacaciones...</h5>
    </div>

    <!-- Estadísticas detalladas -->
    <div id="vacaciones-estadisticas" class="row mb-4" style="display: none;">
        <div class="col-md-3">
            <div class="card bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-primary fs-1"></i>
                    <h3 class="text-primary mt-2 mb-1" id="stat-dias-correspondientes">0</h3>
                    <p class="text-muted mb-0">Días Correspondientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-plus text-success fs-1"></i>
                    <h3 class="text-success mt-2 mb-1" id="stat-dias-restantes">0</h3>
                    <p class="text-muted mb-0">Días Disponibles</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-info fs-1"></i>
                    <h3 class="text-info mt-2 mb-1" id="stat-total-tomados">0</h3>
                    <p class="text-muted mb-0">Días Disfrutados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-heart text-warning fs-1"></i>
                    <h3 class="text-warning mt-2 mb-1" id="stat-vacaciones-activas">0</h3>
                    <p class="text-muted mb-0">Vacaciones Activas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Vacaciones -->
    <div id="vacaciones-lista" class="row"></div>

    <!-- Estado Vacío -->
    <div id="vacaciones-vacio" class="text-center py-5" style="display: none;">
        <i class="bi bi-calendar-heart text-muted mb-3" style="font-size: 4rem;"></i>
        <h4 class="text-muted">No hay vacaciones registradas</h4>
        <p class="text-muted mb-4">Este trabajador aún no tiene vacaciones asignadas.</p>
        @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#asignarVacacionesModal">
                <i class="bi bi-plus-lg"></i> Asignar Primera Vacación
            </button>
        @endif
    </div>

    <!-- Error State -->
    <div id="vacaciones-error" class="alert alert-danger text-center" style="display: none;">
        <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
        <h5>Error al cargar las vacaciones</h5>
        <p id="error-mensaje" class="mb-3"></p>
        <button class="btn btn-outline-danger" id="retry-vacaciones">
            <i class="bi bi-arrow-clockwise"></i> Reintentar
        </button>
    </div>
</div>

<!-- ✅ TEMPLATE SIMPLIFICADO -->
<template id="template-vacacion-item">
    <div class="col-12 mb-3">
        <div class="card vacacion-item border-start border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge estado-badge me-3"></span>
                            <h5 class="mb-0 periodo-texto"></h5>
                            <small class="text-muted ms-3 creado-por"></small>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <i class="bi bi-calendar2-range text-muted me-2"></i>
                                <span class="fechas-texto"></span>
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-clock text-muted me-2"></i>
                                <span class="duracion-texto"></span>
                            </div>
                        </div>
                        
                        <div class="observaciones-texto text-muted small" style="display: none;"></div>
                    </div>

                    <div class="col-md-4">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="small text-muted">Solicitados</div>
                                <div class="h4 text-primary dias-solicitados"></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Disfrutados</div>
                                <div class="h4 text-success dias-disfrutados"></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Restantes</div>
                                <div class="h4 text-warning dias-restantes"></div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end acciones-vacacion"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@include('trabajadores.modales.asignar_vacaciones', ['trabajador' => $trabajador])

{{-- ===================================== --}}
{{-- ✅ SCRIPTS SIMPLIFICADOS --}}
{{-- ===================================== --}}

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/app-routes.js') }}"></script>

<script>
// ✅ CONFIGURACIÓN GLOBAL SIMPLIFICADA
window.APP_DEBUG = @json(config('app.debug'));
window.currentUser = @json(['id' => Auth::id(), 'nombre' => Auth::user()->nombre, 'tipo' => Auth::user()->tipo]);

// Verificación básica
if (typeof AppRoutes === 'undefined') {
    console.error('❌ AppRoutes no disponible');
}
</script>

<script src="{{ asset('js/formato-global.js') }}"></script>
<script src="{{ asset('js/vacaciones.js') }}"></script>
<script src="{{ asset('js/modales/asignar_vacacion.js') }}"></script>

<script>
// ✅ VERIFICACIÓN FINAL SIMPLIFICADA
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof AppRoutes !== 'undefined' && typeof window.vacacionesApp !== 'undefined') {
            console.log('🎉 Sistema de vacaciones inicializado correctamente');
        } else {
            console.error('❌ Error en inicialización');
        }
    }, 500);
});
</script>

<!-- ✅ ESTILOS SIMPLIFICADOS -->
<style>
.vacacion-item[data-estado="pendiente"] { border-left-color: #ffc107 !important; }
.vacacion-item[data-estado="activa"] { border-left-color: #198754 !important; }
.vacacion-item[data-estado="finalizada"] { border-left-color: #6c757d !important; }
.vacacion-item[data-estado="cancelada"] { border-left-color: #dc3545 !important; opacity: 0.8; }

.card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.card:hover { transform: translateY(-2px); box-shadow: 0 4px 25px rgba(0,0,0,0.1); }
</style>

@endsection