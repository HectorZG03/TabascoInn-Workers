@extends('layouts.app')

@section('title', 'Detalle del Permiso #' . $permiso->id_permiso . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Detalle del Permiso #{{ $permiso->id_permiso }}</h4>
                                <p class="mb-0 opacity-75">{{ $trabajador->nombre_completo }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('trabajadores.perfil.permisos.historial', $trabajador) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver al Historial
                            </a>
                            <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-person"></i> Ver Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Permiso -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">ID del Permiso:</td>
                                    <td>#{{ $permiso->id_permiso }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Trabajador:</td>
                                    <td>{{ $trabajador->nombre_completo }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Tipo de Permiso:</td>
                                    <td><span class="badge bg-info fs-6">{{ $permiso->tipo_permiso_texto }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Estado:</td>
                                    <td>
                                        <span class="badge fs-6
                                            @if($permiso->estatus_permiso == 'activo') bg-success
                                            @elseif($permiso->estatus_permiso == 'finalizado') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ $permiso->estatus_permiso_texto }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Inicio:</td>
                                    <td>{{ $permiso->fecha_inicio->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Fin:</td>
                                    <td>{{ $permiso->fecha_fin->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Días de Permiso:</td>
                                    <td><span class="badge bg-secondary fs-6">{{ $permiso->dias_de_permiso }} días</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Solicitud:</td>
                                    <td>{{ $permiso->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo del Permiso -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-quote me-2"></i>Motivo del Permiso
                    </h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $permiso->motivo }}
                    </div>
                </div>
            </div>

            <!-- Observaciones (si existen) -->
            @if($permiso->observaciones)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>Observaciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $permiso->observaciones }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Información del Trabajador -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-person me-2"></i>Información del Trabajador
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-lg bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem;">
                        {{ substr($trabajador->nombre_trabajador, 0, 1) }}{{ substr($trabajador->ape_pat, 0, 1) }}
                    </div>
                    <h6>{{ $trabajador->nombre_completo }}</h6>
                    <p class="text-muted mb-2">
                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}
                    </p>
                    <span class="badge bg-{{ $trabajador->estatus_color }}">
                        <i class="{{ $trabajador->estatus_icono }}"></i> {{ $trabajador->estatus_texto }}
                    </span>
                </div>
            </div>

            <!-- Resumen del Permiso -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-range me-2"></i>Resumen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="h4 text-primary">{{ $permiso->dias_de_permiso }}</div>
                            <small class="text-muted">{{ $permiso->dias_de_permiso == 1 ? 'Día' : 'Días' }} de Permiso</small>
                        </div>
                        @if($permiso->estatus_permiso == 'activo')
                            <div class="col-12">
                                <div class="h5 text-warning">{{ $permiso->dias_restantes }}</div>
                                <small class="text-muted">{{ $permiso->dias_restantes == 1 ? 'Día Restante' : 'Días Restantes' }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($permiso->tiene_pdf)
                            <a href="{{ $permiso->url_pdf }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bi bi-file-pdf"></i> Ver PDF
                            </a>
                        @endif
                        
                        @if(Auth::user()->esGerencia() && $permiso->estatus_permiso == 'activo')
                            <form action="{{ route('permisos.finalizar', $permiso) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                        onclick="return confirm('¿Finalizar este permiso?')">
                                    <i class="bi bi-check-circle"></i> Finalizar Permiso
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection