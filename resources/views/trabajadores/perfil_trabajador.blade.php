@extends('layouts.app')

@section('title', 'Perfil de ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')

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
                            <option value="prueba" {{ $trabajador->estatus == 'prueba' ? 'selected' : '' }}>
                                Período de Prueba
                            </option>
                            <option value="activo" {{ $trabajador->estatus == 'activo' ? 'selected' : '' }}>
                                Activo
                            </option>
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
                                <span class="badge bg-{{ $trabajador->estatus_color }} trabajador-estatus-badge">
                                    <i class="{{ $trabajador->estatus_icono }}"></i> {{ $trabajador->estatus_texto }}
                                </span>
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

    {{-- ✅ NUEVA SECCIÓN: Accesos Rápidos --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning"></i> Acceso Vacaciones
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex gap-3 flex-wrap">
                        {{-- Botón de Vacaciones --}}
                        <a href="{{ route('trabajadores.vacaciones.show', $trabajador) }}" 
                           class="btn btn-outline-primary d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-heart"></i>
                            <div class="text-start">
                                <div class="fw-bold">Vacaciones</div>
                                <small class="text-muted">
                                    @if($trabajador->tieneVacacionesActivas())
                                        {{ $trabajador->vacacionesActivas()->count() }} activa(s)
                                    @elseif($trabajador->tieneVacacionesPendientes())
                                        {{ $trabajador->vacacionesPendientes()->count() }} pendiente(s)
                                    @else
                                        Gestionar vacaciones
                                    @endif
                                </small>
                            </div>
                            @if($trabajador->tieneVacacionesActivas())
                                <span class="badge bg-success">{{ $trabajador->vacacionesActivas()->count() }}</span>
                            @elseif($trabajador->tieneVacacionesPendientes())
                                <span class="badge bg-warning">{{ $trabajador->vacacionesPendientes()->count() }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación (SIN pestaña de vacaciones) -->
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
                        
                        {{-- ✅ NUEVA PESTAÑA DE HISTORIAL --}}
                        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab">
                            <i class="bi bi-clock-history"></i> Historial
                            @if(isset($historialCompleto) && $historialCompleto->count() > 0)
                                <span class="badge bg-info text-dark ms-1">{{ $historialCompleto->count() }}</span>
                            @endif
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
                        
                        {{-- ENLACES DIRECTOS (sin cambios) --}}
                        <a href="{{ route('trabajadores.perfil.permisos.historial', $trabajador) }}" class="nav-link">
                            <i class="bi bi-calendar-check"></i>Permisos
                        </a>
                        
                        <a href="{{ route('trabajadores.perfil.bajas.historial', $trabajador) }}" class="nav-link">
                            <i class="bi bi-person-x"></i> Historial de Bajas
                            @if($trabajador->despidosActivos() > 0)
                                <span class="badge bg-danger ms-1">{{ $trabajador->despidosActivos() }}</span>
                            @endif
                        </a>
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

    <!-- CONTENIDO DE LAS PESTAÑAS (SIN vacaciones) -->
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

            {{-- ✅ NUEVA PESTAÑA DE HISTORIAL --}}
            <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab">
                @include('trabajadores.secciones_perfil.historial_cambios')
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

{{-- Sección al final del archivo perfil_trabajador.blade.php --}}
{{-- ✅ SCRIPTS ACTUALIZADOS CON HORAS EXTRA --}}

{{-- 1. PRIMERO: Script de rutas dinámicas globales --}}
<script src="{{ asset('js/app-routes.js') }}"></script>

{{-- 2. SEGUNDO: Variables globales de configuración --}}
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
    console.error('❌ CRÍTICO: app-routes.js no se cargó correctamente');
} else {
    console.log('✅ AppRoutes disponible, base URL:', AppRoutes.getBaseUrl());
}
</script>

{{-- 3. TERCERO: Scripts del perfil trabajador en orden de dependencias --}}
<script src="{{ asset('js/formato-global.js')}}"></script>
<script src="{{ asset('js/horas_extra.js')}}"></script>
<script src="{{ asset('js/perfil_trabajador/perfil_scripts.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/areas_categorias.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/documentos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/contratos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/dias_laborales.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/validaciones_campos.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/navegacion.js') }}"></script>
<script src="{{ asset('js/perfil_trabajador/notificaciones.js') }}"></script>

{{-- ✅ 4. CUARTO: Script de inicialización final --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICACIÓN FINAL DE CARGA
    setTimeout(() => {
        if (typeof AppRoutes !== 'undefined' && typeof window.PERFIL_CONFIG !== 'undefined') {
            console.log('🎉 Perfil del trabajador completamente inicializado');
            
            // ✅ VERIFICAR QUE LOS SISTEMAS DE FORMATO ESTÉN FUNCIONANDO
            if (typeof window.FormatoGlobal !== 'undefined') {
                console.log('✅ Sistema global de formato activo');
            }
            
            if (typeof window.HorasExtraJS !== 'undefined') {
                console.log('✅ Sistema de horas extra activo');
            }
            
            // ✅ DEBUG EN DESARROLLO
            if (window.APP_DEBUG && typeof window.debugRutas === 'function') {
                window.debugRutas();
            }
        } else {
            console.error('❌ Error en la inicialización del perfil');
        }
    }, 500);
    
    // ✅ CONFIGURAR VALIDACIÓN ANTES DEL ENVÍO DE FORMULARIOS
    document.querySelectorAll('form[action*="horas-extra"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const isAsignar = this.action.includes('asignar');
            const isRestar = this.action.includes('restar');
            
            if (typeof window.validarHorasExtra !== 'undefined') {
                const trabajadorId = window.PerfilUtils ? window.PerfilUtils.getTrabajadorId() : null;
                
                if (trabajadorId) {
                    let esValido = true;
                    
                    if (isAsignar) {
                        esValido = window.validarHorasExtra.asignar(trabajadorId);
                    } else if (isRestar) {
                        esValido = window.validarHorasExtra.compensar(trabajadorId);
                    }
                    
                    if (!esValido) {
                        e.preventDefault();
                        console.log('❌ Formulario no válido, envío cancelado');
                        
                        // Mostrar mensaje de error
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                        alertDiv.innerHTML = `
                            <strong>Error:</strong> Por favor, corrija los errores en el formulario antes de continuar.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        
                        const modalBody = this.closest('.modal-body');
                        if (modalBody) {
                            modalBody.insertBefore(alertDiv, modalBody.firstChild);
                        }
                        
                        return false;
                    }
                }
            }
        });
    });
    
    // ✅ CONFIGURAR EVENTOS PARA ABRIR MODALES
    document.querySelectorAll('[data-bs-target*="modalAsignarHoras"], [data-bs-target*="modalRestarHoras"]').forEach(button => {
        button.addEventListener('click', function() {
            // Limpiar validaciones previas cuando se abre el modal
            setTimeout(() => {
                const modal = document.querySelector(this.getAttribute('data-bs-target'));
                if (modal) {
                    const campos = modal.querySelectorAll('.is-valid, .is-invalid');
                    campos.forEach(campo => {
                        campo.classList.remove('is-valid', 'is-invalid');
                    });
                    
                    const feedbacks = modal.querySelectorAll('.invalid-feedback');
                    feedbacks.forEach(feedback => feedback.remove());
                }
            }, 100);
        });
    });
});
</script>


@endsection