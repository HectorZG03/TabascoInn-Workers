{{-- resources/views/trabajadores/documentos_vacaciones/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Documentos de Vacaciones - ' . $trabajador->nombre_completo)

@section('content')
<div class="container-fluid" data-trabajador-id="{{ $trabajador->id_trabajador }}">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Documentos de Amortización de Vacaciones</h4>
                                <p class="mb-0 opacity-75">{{ $trabajador->nombre_completo }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('trabajadores.vacaciones.show', $trabajador) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-calendar-heart"></i> Ver Vacaciones
                            </a>
                            <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-person"></i> Perfil
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; border-radius: 50%;">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $trabajador->nombre_completo }}</h6>
                                    <p class="text-muted mb-0">
                                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }} |
                                        {{ $trabajador->antiguedad }} años de antigüedad
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h6 text-primary mb-0">{{ $trabajador->vacacionesPendientes->count() }}</div>
                                    <small class="text-muted">Vacaciones Pendientes</small>
                                </div>
                                <div class="col-6">
                                    <div class="h6 text-success mb-0">{{ $trabajador->documentosVacaciones->count() }}</div>
                                    <small class="text-muted">Documentos</small>
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
                    @if($trabajador->vacacionesPendientes->count() > 0)
                        <button id="btn-download-pdf" 
                                class="btn btn-primary"
                                data-download-pdf
                                data-trabajador-id="{{ $trabajador->id_trabajador }}">
                            <i class="bi bi-download"></i> Descargar PDF para Firmar
                        </button>
                    @endif
                    
                    @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subirDocumentoModal">
                            <i class="bi bi-upload"></i> Subir Documento Firmado
                        </button>
                    @endif
                    
                    <button class="btn btn-outline-primary" id="refresh-documentos">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert para vacaciones pendientes -->
    @if($trabajador->vacacionesPendientes->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Vacaciones Pendientes:</strong> 
                    Este trabajador tiene {{ $trabajador->vacacionesPendientes->count() }} vacaciones pendientes 
                    que pueden ser incluidas en un documento de amortización.
                </div>
            </div>
        </div>
    @endif

    <!-- Loading State -->
    <div id="documentos-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando documentos...</span>
        </div>
        <h5 class="mt-3 text-muted">Cargando documentos...</h5>
    </div>

    <!-- Lista de Documentos -->
    <div id="documentos-lista" class="row" style="display: none;">
        <!-- Se llena dinámicamente -->
    </div>

    <!-- Estado Vacío -->
    <div id="documentos-vacio" class="text-center py-5" style="display: none;">
        <div class="mb-4">
            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 4rem;"></i>
        </div>
        <h4 class="text-muted">No hay documentos de amortización</h4>
        <p class="text-muted mb-4">Aún no se han subido documentos para este trabajador.</p>
        
        @if($trabajador->vacacionesPendientes->count() > 0)
            <div class="d-flex gap-2 justify-content-center">
                <button id="btn-download-pdf-empty" 
                        class="btn btn-primary"
                        data-download-pdf
                        data-trabajador-id="{{ $trabajador->id_trabajador }}">
                    <i class="bi bi-download"></i> Descargar PDF
                </button>
                @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subirDocumentoModal">
                        <i class="bi bi-upload"></i> Subir Documento
                    </button>
                @endif
            </div>
        @else
            <p class="text-muted">No hay vacaciones pendientes para generar documentos.</p>
        @endif
    </div>

    <!-- Error State -->
    <div id="documentos-error" class="alert alert-danger text-center" style="display: none;">
        <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
        <h5>Error al cargar documentos</h5>
        <p id="error-mensaje" class="mb-3">Ha ocurrido un error al cargar la información.</p>
        <button class="btn btn-outline-danger" id="retry-documentos">
            <i class="bi bi-arrow-clockwise"></i> Reintentar
        </button>
    </div>
</div>

<!-- Template para item de documento -->
<template id="template-documento-item">
    <div class="col-12 mb-3">
        <div class="card documento-item border-start border-4 border-success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-file-earmark-pdf text-danger fs-4 me-3"></i>
                            <div>
                                <h5 class="mb-0 nombre-documento"></h5>
                                <div class="text-muted small">
                                    <span class="fecha-subida"></span> | 
                                    <span class="tamaño-archivo"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vacaciones-asociadas">
                            <strong>Vacaciones asociadas:</strong>
                            <div class="vacaciones-badges mt-2">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-outline-primary btn-sm btn-ver-documento" 
                                    onclick="viewDocument(this.dataset.url)"
                                    data-url="">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                            <button class="btn btn-primary btn-sm btn-descargar-documento" 
                                    onclick="downloadDocument(this.dataset.url, this.dataset.name)"
                                    data-url=""
                                    data-name="">
                                <i class="bi bi-download"></i> Descargar
                            </button>
                            @if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
                                <button class="btn btn-outline-danger btn-sm btn-eliminar-documento">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Modal para subir documento -->
@if(Auth::user()->esGerencia() || Auth::user()->esRecursosHumanos())
    @include('trabajadores.documentos_vacaciones.modal_subir_documento', ['trabajador' => $trabajador])
@endif

<!-- ✅ NUEVO: Modal para selección de firmas -->
@include('trabajadores.documentos_vacaciones.modal_seleccion_firmas', ['trabajador' => $trabajador, 'gerentes' => $gerentes])

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
    console.error('❌ CRÍTICO: app-routes.js no se cargó correctamente para documentos de vacaciones');
} else {
    console.log('✅ AppRoutes disponible para documentos de vacaciones, base URL:', AppRoutes.getBaseUrl());
}
</script>

{{-- ✅ 3. TERCERO: HELPER DE DESCARGA PDF --}}
<script src="{{ asset('js/helpers/helper_pdf_download.js') }}"></script>

{{-- ✅ 3.5. NUEVO: HELPER DE PDF CON FIRMAS --}}
<script src="{{ asset('js/helpers/helper_pdf_firmas.js') }}"></script>

{{-- ✅ 4. CUARTO: DOCUMENTOS DE VACACIONES (Principal) --}}
<script src="{{ asset('js/documentos_vacaciones.js') }}"></script>

{{-- ✅ 5. QUINTO: MODAL DE SUBIR DOCUMENTOS --}}
<script src="{{ asset('js/modales/modal_subir_documento.js') }}"></script>

{{-- ✅ 6. SEXTO: Script de verificación final --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ VERIFICACIÓN FINAL DE CARGA
    setTimeout(() => {
        if (typeof AppRoutes !== 'undefined' && 
            typeof window.documentosVacacionesApp !== 'undefined' &&
            typeof window.pdfDownloadHelper !== 'undefined' &&
            typeof window.pdfConFirmasHelper !== 'undefined') {
            console.log('🎉 Sistema de documentos de vacaciones completamente inicializado');
            
            // ✅ DEBUG EN DESARROLLO
            if (window.APP_DEBUG) {
                console.log('🎯 Sistema de documentos de vacaciones:');
                console.log('   📋 Lista: documentos_vacaciones.js');
                console.log('   📤 Modal: modales/modal_subir_documento.js');
                console.log('   📄 Helper PDF: helpers/helper_pdf_download.js');
                console.log('   🖋️ Helper PDF Firmas: helpers/helper_pdf_firmas.js');
                console.log('   👤 Usuario:', window.currentUser);
                console.log('   🔧 Base URL:', AppRoutes.getBaseUrl());
                console.log('   🔗 Rutas de ejemplo:');
                console.log('       API documentos:', AppRoutes.trabajadores('1/documentos-vacaciones/api/documentos'));
                console.log('       Subir documento:', AppRoutes.trabajadores('1/documentos-vacaciones/subir'));
                console.log('       Selección firmas:', AppRoutes.trabajadores('1/documentos-vacaciones/seleccion-firmas'));
                console.log('       Descargar PDF:', AppRoutes.trabajadores('1/documentos-vacaciones/descargar-pdf'));
                console.log('       Eliminar documento:', AppRoutes.trabajadores('1/documentos-vacaciones/1/eliminar'));
            }
        } else {
            console.error('❌ Error en la inicialización del sistema de documentos de vacaciones');
            
            if (typeof AppRoutes === 'undefined') {
                console.error('   - AppRoutes no disponible');
            }
            if (typeof window.documentosVacacionesApp === 'undefined') {
                console.error('   - documentosVacacionesApp no inicializada');
            }
            if (typeof window.pdfDownloadHelper === 'undefined') {
                console.error('   - pdfDownloadHelper no inicializado');
            }
            if (typeof window.pdfConFirmasHelper === 'undefined') {
                console.error('   - pdfConFirmasHelper no inicializado');
            }
        }
    }, 500);
});
</script>

<!-- Estilos -->
<style>
.documento-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.documento-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}

.avatar-lg {
    box-shadow: 0 0 0 3px rgba(255,255,255,0.2);
}

.file-info {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.toast-container {
    z-index: 1055;
}

.badge {
    font-size: 0.75em;
}

.vacaciones-badges .badge {
    cursor: help;
}

.btn-eliminar-documento:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}
</style>

@endsection