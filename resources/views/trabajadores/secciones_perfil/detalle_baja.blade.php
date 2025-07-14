@extends('layouts.app')

@section('title', 'Detalle de la Baja #' . $baja->id_baja . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-x fs-3 me-3"></i>
                            <div>
                                <h4 class="mb-0">Detalle de la Baja #{{ $baja->id_baja }}</h4>
                                <p class="mb-0 opacity-75">{{ $trabajador->nombre_completo }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('trabajadores.perfil.bajas.historial', $trabajador) }}" class="btn btn-light btn-sm">
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

    <!-- Información de la Baja -->
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
                                    <td class="fw-bold text-muted">ID de la Baja:</td>
                                    <td>#{{ $baja->id_baja }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Trabajador:</td>
                                    <td>{{ $trabajador->nombre_completo }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Baja:</td>
                                    <td>
                                        <span class="badge bg-danger fs-6">{{ $baja->fecha_baja->format('d/m/Y') }}</span>
                                        <br><small class="text-muted">{{ $baja->fecha_baja->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Condición de Salida:</td>
                                    <td><span class="badge bg-secondary fs-6">{{ $baja->condicion_salida }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Tipo de Baja:</td>
                                    <td><span class="badge bg-info fs-6">{{ $baja->tipo_baja_texto }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Estado Actual:</td>
                                    <td>
                                        <span class="badge fs-6 bg-{{ $baja->es_activo ? 'danger' : 'success' }}">
                                            {{ $baja->estado_texto }}
                                        </span>
                                    </td>
                                </tr>
                                @if($baja->fecha_reintegro)
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Reintegro:</td>
                                    <td>
                                        <span class="badge bg-warning text-dark fs-6">{{ $baja->fecha_reintegro->format('d/m/Y') }}</span>
                                        <br><small class="text-muted">
                                            @if($baja->fecha_reintegro->isPast())
                                                Venció {{ $baja->fecha_reintegro->diffForHumans() }}
                                            @else
                                                {{ $baja->fecha_reintegro->diffForHumans() }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold text-muted">Fecha de Registro:</td>
                                    <td>{{ $baja->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo de la Baja -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-quote me-2"></i>Motivo de la Baja
                    </h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $baja->motivo }}
                    </div>
                </div>
            </div>

            <!-- Observaciones (si existen) -->
            @if($baja->observaciones)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>Observaciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $baja->observaciones }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Información de Cancelación (si existe) -->
            @if($baja->fecha_cancelacion)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-arrow-clockwise me-2"></i>Información de Cancelación
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <p class="mb-1"><strong>Fecha de Cancelación:</strong> {{ $baja->fecha_cancelacion->format('d/m/Y H:i') }}</p>
                        @if($baja->usuarioCancelacion)
                            <p class="mb-1"><strong>Cancelado por:</strong> {{ $baja->usuarioCancelacion->nombre }}</p>
                        @endif
                        @if($baja->motivo_cancelacion)
                            <div class="mt-2">
                                <strong>Motivo de Cancelación:</strong>
                                <div class="bg-light p-2 rounded mt-1">
                                    {{ $baja->motivo_cancelacion }}
                                </div>
                            </div>
                        @endif
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

            <!-- Resumen de la Baja -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-x me-2"></i>Resumen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="h4 text-danger">{{ $baja->dias_desde_ejecutado }}</div>
                            <small class="text-muted">{{ $baja->dias_desde_ejecutado == 1 ? 'Día' : 'Días' }} desde la baja</small>
                        </div>
                        @if($baja->esTemporal() && $baja->fecha_reintegro)
                            <div class="col-12">
                                @if($baja->fecha_reintegro->isPast())
                                    <div class="h5 text-warning">Vencida</div>
                                    <small class="text-muted">{{ $baja->fecha_reintegro->diffForHumans() }}</small>
                                @else
                                    <div class="h5 text-info">{{ $baja->fecha_reintegro->diffInDays(now()) }}</div>
                                    <small class="text-muted">{{ $baja->fecha_reintegro->diffInDays(now()) == 1 ? 'Día' : 'Días' }} para reintegro</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            @if(Auth::user()->esGerencia() && $baja->es_activo)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('despidos.cancelar', $baja) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="mb-3">
                            <label class="form-label">Motivo de cancelación:</label>
                            <textarea name="motivo_cancelacion" class="form-control" rows="3" 
                                      placeholder="Especifica el motivo..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('¿Cancelar esta baja y reactivar al trabajador?')">
                            <i class="bi bi-arrow-clockwise"></i> Cancelar Baja
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection