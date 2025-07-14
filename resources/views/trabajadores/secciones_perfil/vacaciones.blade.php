{{-- resources/views/trabajadores/secciones_perfil/vacaciones.blade.php --}}

@extends('layouts.app')

@section('title', 'Vacaciones de ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<div class="container-fluid" data-trabajador-id="{{ $trabajador->id_trabajador }}">
    
    <!-- Header con información del trabajador -->
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
                                <i class="bi bi-arrow-left"></i> Lista de Trabajadores
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Avatar y datos básicos -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                    style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem;">
                                    {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $trabajador->nombre_completo }}</h6>
                                    <p class="text-muted mb-0 small">
                                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                                    </p>
                                    <span class="badge bg-{{ $trabajador->estatus_color }} trabajador-estatus-badge">
                                        <i class="{{ $trabajador->estatus_icono }}"></i> {{ $trabajador->estatus_texto }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información laboral -->
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-primary mb-0">{{ $trabajador->antiguedad_texto }}</div>
                                <small class="text-muted">Antigüedad</small>
                            </div>
                        </div>

                        <!-- Estadísticas rápidas -->
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="h6 text-success mb-0" id="header-dias-correspondientes">
                                        {{ $estadisticas['dias_correspondientes_año_actual'] ?? 0 }}
                                    </div>
                                    <small class="text-muted">Días LFT</small>
                                </div>
                                <div class="col-3">
                                    <div class="h6 text-info mb-0" id="header-dias-restantes">
                                        {{ $estadisticas['dias_restantes_año_actual'] ?? 0 }}
                                    </div>
                                    <small class="text-muted">Disponibles</small>
                                </div>
                                <div class="col-3">
                                    <div class="h6 text-warning mb-0" id="header-vacaciones-activas">
                                        {{ $estadisticas['vacaciones_activas'] ?? 0 }}
                                    </div>
                                    <small class="text-muted">Activas</small>
                                </div>
                                <div class="col-3">
                                    <div class="h6 text-secondary mb-0" id="header-total-tomadas">
                                        {{ $estadisticas['total_dias_tomados'] ?? 0 }}
                                    </div>
                                    <small class="text-muted">Disfrutados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones principales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#asignarVacacionesModal">
                            <i class="bi bi-plus-lg"></i> Asignar Vacaciones
                        </button>
                    @endif
                    <button class="btn btn-outline-primary" id="refresh-vacaciones">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="d-flex gap-2" id="vacaciones-filtros" style="display: none;">
                    <select class="form-select form-select-sm" id="filtro-estado" style="width: auto;">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="activa">Activas</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                    <select class="form-select form-select-sm" id="filtro-periodo" style="width: auto;">
                        <option value="">Todos los períodos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas detalladas -->
    <div id="vacaciones-estadisticas" class="row mb-4" style="display: none;">
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-primary fs-1"></i>
                    <h3 class="text-primary mt-2 mb-1" id="stat-dias-correspondientes">0</h3>
                    <p class="text-muted mb-0">Días Correspondientes</p>
                    <small class="text-muted">Según LFT México</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-plus text-success fs-1"></i>
                    <h3 class="text-success mt-2 mb-1" id="stat-dias-restantes">0</h3>
                    <p class="text-muted mb-0">Días Disponibles</p>
                    <small class="text-muted">Este año</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-info fs-1"></i>
                    <h3 class="text-info mt-2 mb-1" id="stat-total-tomados">0</h3>
                    <p class="text-muted mb-0">Días Disfrutados</p>
                    <small class="text-muted">Total histórico</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-heart text-warning fs-1"></i>
                    <h3 class="text-warning mt-2 mb-1" id="stat-vacaciones-activas">0</h3>
                    <p class="text-muted mb-0">Vacaciones Activas</p>
                    <small class="text-muted">En curso</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="vacaciones-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando vacaciones...</span>
        </div>
        <h5 class="mt-3 text-muted">Cargando información de vacaciones...</h5>
    </div>

    <!-- Lista de Vacaciones -->
    <div id="vacaciones-lista" class="row">
        <!-- Se llena dinámicamente -->
    </div>

    <!-- Estado Vacío -->
    <div id="vacaciones-vacio" class="text-center py-5" style="display: none;">
        <div class="mb-4">
            <i class="bi bi-calendar-heart text-muted" style="font-size: 4rem;"></i>
        </div>
        <h4 class="text-muted">No hay vacaciones registradas</h4>
        <p class="text-muted mb-4">Este trabajador aún no tiene vacaciones asignadas.</p>
        @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#asignarVacacionesModal">
                <i class="bi bi-plus-lg"></i> Asignar Primera Vacación
            </button>
        @endif
    </div>

    <!-- Error State -->
    <div id="vacaciones-error" class="alert alert-danger text-center" style="display: none;">
        <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
        <h5>Error al cargar las vacaciones</h5>
        <p id="error-mensaje" class="mb-3">Ha ocurrido un error al cargar la información.</p>
        <button class="btn btn-outline-danger" id="retry-vacaciones">
            <i class="bi bi-arrow-clockwise"></i> Reintentar
        </button>
    </div>
</div>

<!-- Template para item de vacación -->
<template id="template-vacacion-item">
    <div class="col-12 mb-3">
        <div class="card vacacion-item border-start border-4" data-vacacion-id="">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge estado-badge me-3 fs-6"></span>
                            <h5 class="mb-0 periodo-texto"></h5>
                            <small class="text-muted ms-3 creado-por"></small>
                        </div>
                        
                        <div class="row mb-3">
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
                        
                        <div class="d-flex gap-2 justify-content-end acciones-vacacion">
                            <!-- Botones de acción se añaden dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Modal de Asignar Vacaciones -->
@include('trabajadores.modales.asignar_vacaciones', ['trabajador' => $trabajador])

{{-- ===================================== --}}
{{-- ✅ SCRIPTS EN ORDEN CORRECTO CON RUTAS DINÁMICAS --}}
{{-- ===================================== --}}

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- ✅ 1. PRIMERO: Script de rutas dinámicas globales --}}
<script src="{{ asset('js/app-routes.js') }}"></script>

{{-- ✅ 2. SEGUNDO: Variables globales de configuración --}}
<script>
// ✅ VARIABLES GLOBALES PARA LA APLICACIÓN
window.APP_DEBUG = @json(config('app.debug'));
window.currentUser = @json([
    'id' => Auth::id(),
    'nombre' => Auth::user()->nombre,
    'tipo' => Auth::user()->tipo
]);

// ✅ VERIFICAR QUE AppRoutes ESTÉ DISPONIBLE
if (typeof AppRoutes === 'undefined') {
    console.error('❌ CRÍTICO: app-routes.js no se cargó correctamente para vacaciones');
} else {
    console.log('✅ AppRoutes disponible para vacaciones, base URL:', AppRoutes.getBaseUrl());
}
</script>

{{-- ✅ 3. TERCERO: FORMATO GLOBAL (Debe ir ANTES que otros scripts) --}}
<script src="{{ asset('js/formato-global.js') }}"></script>

{{-- ✅ 4. CUARTO: LISTA DE VACACIONES (Principal) --}}
<script src="{{ asset('js/vacaciones.js') }}"></script>

{{-- ✅ 5. QUINTO: MODAL DE ASIGNAR VACACIONES (Con formato global integrado) --}}
<script src="{{ asset('js/modales/asignar_vacacion.js') }}"></script>

{{-- ✅ 6. SEXTO: Script de verificación final --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICACIÓN FINAL DE CARGA
    setTimeout(() => {
        if (typeof AppRoutes !== 'undefined' && typeof window.vacacionesApp !== 'undefined') {
            console.log('🎉 Sistema de vacaciones completamente inicializado con rutas dinámicas');
            
            // ✅ DEBUG EN DESARROLLO
            if (window.APP_DEBUG) {
                console.log('🎯 Sistema de vacaciones con rutas dinámicas:');
                console.log('   📅 Formato Global: formato-global.js');
                console.log('   📋 Lista: vacaciones.js');
                console.log('   📝 Modal: modales/asignar_vacacion.js');
                console.log('   👤 Usuario:', window.currentUser);
                console.log('   🔧 Base URL:', AppRoutes.getBaseUrl());
                console.log('   🔗 Rutas de ejemplo:');
                console.log('       API vacaciones:', AppRoutes.trabajadores('1/vacaciones/api'));
                console.log('       Asignar vacaciones:', AppRoutes.trabajadores('1/vacaciones/asignar'));
                console.log('       Calcular días:', AppRoutes.trabajadores('1/vacaciones/calcular-dias'));
            }
        } else {
            console.error('❌ Error en la inicialización del sistema de vacaciones');
            
            if (typeof AppRoutes === 'undefined') {
                console.error('   - AppRoutes no disponible');
            }
            if (typeof window.vacacionesApp === 'undefined') {
                console.error('   - vacacionesApp no inicializada');
            }
        }
    }, 500);
});
</script>

<!-- Estilos -->
<style>
.vacacion-item[data-estado="pendiente"] { 
    border-left-color: #ffc107 !important; 
}
.vacacion-item[data-estado="activa"] { 
    border-left-color: #198754 !important; 
}
.vacacion-item[data-estado="finalizada"] { 
    border-left-color: #6c757d !important; 
}

.avatar-lg {
    box-shadow: 0 0 0 3px rgba(255,255,255,0.2);
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}

</style>

@endsection