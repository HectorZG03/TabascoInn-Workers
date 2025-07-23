{{-- resources/views/trabajadores/secciones_perfil/contrato_trabajador.blade.php --}}

<div class="container-fluid">
    {{-- ✅ Header actualizado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-3">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                Contratos Laborales
                            </h4>
                            <p class="text-muted mb-0">
                                Gestión de contratos de {{ $trabajador->nombre_completo }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($contratos->count() > 0)
                                <div class="d-flex justify-content-end gap-3">
                                    <div class="text-center">
                                        <div class="h5 text-primary mb-0">{{ $estadisticas['total'] }}</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="text-center">
                                        <div class="h5 text-success mb-0">{{ $estadisticas['vigentes'] }}</div>
                                        <small class="text-muted">Vigentes</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Alertas existentes... --}}
    @if($contratos->count() > 0)
        @if($estadisticas['proximos_vencer'] > 0)
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>Atención:</strong> {{ $estadisticas['proximos_vencer'] }} contrato(s) próximo(s) a vencer (30 días o menos).
                </div>
            </div>
        @endif

        @if(!$estadisticas['tiene_contrato_vigente'])
            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <div><strong>Sin contrato vigente</strong> - Todos los contratos han terminado o se renovaron.</div>
                </div>
                <small class="text-muted">Renovar o eliminar contratos existentes</small>
            </div>
        @endif

        @if($estadisticas['renovables'] > 0)
            <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>
                    <strong>Renovaciones disponibles:</strong> {{ $estadisticas['renovables'] }} contrato(s) puede(n) renovarse.
                </div>
            </div>
        @endif
    @endif

    {{-- ✅ Contenido principal --}}
    <div class="row">
        <div class="col-12">
            @if($contratos->count() > 0)
                {{-- ✅ Lista de contratos (tabla actualizada con soporte indeterminado) --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary"></i>
                                Historial de Contratos
                            </h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Estado</th>
                                        <th>Tipo y Período</th>
                                        <th>Duración</th>
                                        <th>Información</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contratos as $contrato)
                                        <tr class="{{ $contrato->esta_vigente_bool ? 'table-success' : '' }}">
                                            {{-- Estado --}}
                                            <td>
                                                <span class="badge bg-{{ $contrato->color_estado_final }}">
                                                    @if($contrato->esta_vigente_bool)
                                                        <i class="bi bi-check-circle"></i>
                                                    @elseif($contrato->estado_final_calculado === 'renovado')
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    @else
                                                        <i class="bi bi-stop-circle"></i>
                                                    @endif
                                                    {{ $contrato->texto_estado_final }}
                                                </span>

                                                @if($contrato->esRenovacion())
                                                    <small class="d-block text-muted mt-1">
                                                        <i class="bi bi-link-45deg"></i> Renovación de #{{ $contrato->contrato_anterior_id }}
                                                    </small>
                                                @endif

                                                @if($contrato->esta_vigente_bool && $contrato->ya_expiro_bool)
                                                    <small class="d-block text-warning mt-1">
                                                        <i class="bi bi-exclamation-triangle"></i> Expirado
                                                    </small>
                                                @endif
                                            </td>

                                            {{-- ✅ ACTUALIZADO: Tipo y Período --}}
                                            <td>
                                                {{-- Tipo de contrato --}}
                                                <div class="mb-2">
                                                    @if($contrato->tipo_contrato === 'indeterminado')
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-infinity"></i> Tiempo Indeterminado
                                                        </span>
                                                    @else
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-calendar-range"></i> Tiempo Determinado
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Período --}}
                                                <div>
                                                    <strong>{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}</strong>
                                                    <small class="text-muted"> hasta </small>
                                                    <strong>
                                                        @if($contrato->tipo_contrato === 'indeterminado')
                                                            <span class="text-info">Sin fecha fin</span>
                                                        @else
                                                            {{ $contrato->fecha_fin_contrato->format('d/m/Y') }}
                                                        @endif
                                                    </strong>
                                                </div>
                                            </td>

                                            {{-- ✅ ACTUALIZADO: Duración (manejo especial para indeterminados) --}}
                                            <td>
                                                @if($contrato->tipo_contrato === 'indeterminado')
                                                    <div class="text-center">
                                                        <i class="bi bi-infinity text-info" style="font-size: 1.5rem;"></i>
                                                        <div class="small text-muted">Sin límite</div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div>
                                                            <span class="fw-bold">{{ $contrato->duracion_texto }}</span>
                                                            <div class="mt-1">
                                                                @if($contrato->esPorDias())
                                                                    <span class="badge bg-primary">
                                                                        <i class="bi bi-calendar-day"></i> Por días
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-info">
                                                                        <i class="bi bi-calendar3"></i> Por meses
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- ✅ ACTUALIZADO: Información (manejo especial para indeterminados) --}}
                                            <td>
                                                @if($contrato->tipo_contrato === 'indeterminado')
                                                    @if($contrato->esta_vigente_bool)
                                                        <span class="text-success">
                                                            <i class="bi bi-check-circle"></i>
                                                            Vigente Indefinidamente
                                                        </span>
                                                        <small class="d-block text-muted">Sin fecha de vencimiento</small>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="bi bi-dash-circle"></i> 
                                                            Terminado
                                                        </span>
                                                    @endif
                                                @else
                                                    @if($contrato->esta_vigente_bool)
                                                        @if($contrato->esta_proximo_vencer_bool)
                                                            <span class="text-warning fw-bold">
                                                                <i class="bi bi-exclamation-triangle"></i>
                                                                {{ $contrato->info_estado }}
                                                            </span>
                                                            <small class="d-block text-warning">Próximo a vencer</small>
                                                        @elseif($contrato->ya_expiro_bool)
                                                            <span class="text-danger fw-bold">
                                                                <i class="bi bi-x-circle"></i>
                                                                {{ $contrato->info_estado }}
                                                            </span>
                                                            <small class="d-block text-danger">Requiere acción</small>
                                                        @else
                                                            <span class="text-success">
                                                                <i class="bi bi-check-circle"></i>
                                                                {{ $contrato->info_estado }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="bi bi-dash-circle"></i> 
                                                            {{ $contrato->info_estado }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- Acciones --}}
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    {{-- Ver detalles --}}
                                                    <button type="button" 
                                                            class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detalleContratoModal"
                                                            data-contrato="{{ json_encode([
                                                                'id' => $contrato->id_contrato,
                                                                'tipo_contrato' => $contrato->tipo_contrato,
                                                                'tipo_duracion' => $contrato->tipo_duracion,
                                                                'inicio' => $contrato->fecha_inicio_contrato->format('d/m/Y'),
                                                                'fin' => $contrato->tipo_contrato === 'indeterminado' ? 'Sin fecha fin' : $contrato->fecha_fin_contrato->format('d/m/Y'),
                                                                'duracion' => $contrato->tipo_contrato === 'indeterminado' ? 'Tiempo Indeterminado' : $contrato->duracion_completa,
                                                                'duracion_texto' => $contrato->tipo_contrato === 'indeterminado' ? 'Sin límite de tiempo' : $contrato->duracion_texto,
                                                                'es_por_dias' => $contrato->tipo_contrato === 'indeterminado' ? false : $contrato->esPorDias(),
                                                                'es_por_meses' => $contrato->tipo_contrato === 'indeterminado' ? false : $contrato->esPorMeses(),
                                                                'estado' => $contrato->estado_final_calculado,
                                                                'texto_estado' => $contrato->texto_estado_final,
                                                                'info_estado' => $contrato->tipo_contrato === 'indeterminado' ? 'Vigente indefinidamente' : $contrato->info_estado,
                                                                'es_renovacion' => $contrato->esRenovacion(),
                                                                'contrato_anterior_id' => $contrato->contrato_anterior_id,
                                                                'observaciones' => $contrato->observaciones,
                                                                'esta_vigente' => $contrato->esta_vigente_bool,
                                                                'ya_expiro' => $contrato->tipo_contrato === 'indeterminado' ? false : $contrato->ya_expiro_bool
                                                            ]) }}"
                                                            title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    {{-- Descargar PDF --}}
                                                    @if($contrato->archivo_existe)
                                                        <a href="{{ route('trabajadores.contratos.descargar', [$trabajador, $contrato]) }}" 
                                                           class="btn btn-outline-primary"
                                                           title="Descargar PDF">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @endif

                                                    {{-- ✅ Renovar contrato (solo para determinados que pueden renovarse) --}}
                                                    @if($contrato->tipo_contrato === 'determinado' && $contrato->puede_renovarse_bool)
                                                        <button type="button" 
                                                                class="btn btn-outline-warning"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalRenovarContrato"
                                                                data-contrato-id="{{ $contrato->id_contrato }}"
                                                                data-contrato-fin="{{ $contrato->fecha_fin_contrato->format('Y-m-d') }}"
                                                                title="Renovar contrato">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar contrato --}}
                                                    @if($contrato->esta_vigente_bool)
                                                        <button type="button" 
                                                                class="btn btn-outline-danger"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEliminarContrato"
                                                                data-contrato-id="{{ $contrato->id_contrato }}"
                                                                data-contrato-info="{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }} - {{ $contrato->tipo_contrato === 'indeterminado' ? 'Sin fecha fin' : $contrato->fecha_fin_contrato->format('d/m/Y') }}"
                                                                title="Eliminar contrato">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ✅ ACTUALIZADO: Información del contrato vigente con manejo de indeterminados --}}
                @if($estadisticas['tiene_contrato_vigente'] && $estadisticas['contrato_actual'])
                    @php
                        $contratoActual = $estadisticas['contrato_actual'];
                    @endphp
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-check"></i>
                                        Contrato Vigente
                                        
                                        {{-- Tipo de contrato --}}
                                        @if($contratoActual->tipo_contrato === 'indeterminado')
                                            <span class="ms-2 badge bg-info">
                                                <i class="bi bi-infinity"></i> Tiempo Indeterminado
                                            </span>
                                        @else
                                            <span class="ms-2 badge bg-primary">
                                                <i class="bi bi-calendar-range"></i> Tiempo Determinado
                                            </span>
                                        @endif
                                        
                                        @if($contratoActual->esRenovacion())
                                            <small class="ms-2">
                                                <i class="bi bi-arrow-repeat"></i> 
                                                Renovación de #{{ $contratoActual->contrato_anterior_id }}
                                            </small>
                                        @endif
                                        
                                        @if($contratoActual->tipo_contrato === 'determinado' && $contratoActual->yaExpiro())
                                            <small class="ms-2 text-warning">
                                                <i class="bi bi-exclamation-triangle"></i> 
                                                Expirado - Requiere acción
                                            </small>
                                        @endif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Período:</strong><br>
                                            {{ $contratoActual->fecha_inicio_contrato->format('d/m/Y') }} -
                                            @if($contratoActual->tipo_contrato === 'indeterminado')
                                                <span class="text-info">Sin fecha fin</span>
                                            @else
                                                {{ $contratoActual->fecha_fin_contrato->format('d/m/Y') }}
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <strong>Duración:</strong><br>
                                            @if($contratoActual->tipo_contrato === 'indeterminado')
                                                <span class="text-info">
                                                    <i class="bi bi-infinity"></i> Tiempo Indeterminado
                                                </span>
                                            @else
                                                {{ $contratoActual->duracion_texto }}
                                                <div class="mt-1">
                                                    @if($contratoActual->esPorDias())
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-calendar-day"></i> Por días
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-calendar3"></i> Por meses
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <strong>Estado:</strong><br>
                                            @if($contratoActual->tipo_contrato === 'indeterminado')
                                                <span class="fw-bold text-success">Vigente Indefinidamente</span>
                                            @else
                                                <span class="fw-bold {{ $contratoActual->yaExpiro() ? 'text-danger' : ($contratoActual->estaProximoAVencer() ? 'text-warning' : 'text-success') }}">
                                                    {{ $contratoActual->info_estado }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <strong>Acciones:</strong><br>
                                            @if($contratoActual->tipo_contrato === 'determinado' && $contratoActual->puedeRenovarse())
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalRenovarContrato"
                                                        data-contrato-id="{{ $contratoActual->id_contrato }}"
                                                        data-contrato-fin="{{ $contratoActual->fecha_fin_contrato->format('Y-m-d') }}">
                                                    <i class="bi bi-arrow-repeat"></i> Renovar
                                                </button>
                                            @elseif($contratoActual->tipo_contrato === 'determinado' && $contratoActual->yaExpiro())
                                                <span class="text-muted">Expirado - Renovar o eliminar</span>
                                            @elseif($contratoActual->tipo_contrato === 'indeterminado')
                                                <span class="text-success">Sin fecha de vencimiento</span>
                                            @else
                                                <span class="text-success">En vigencia</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($contratoActual->observaciones)
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>Observaciones:</strong><br>
                                                <small class="text-muted">{{ $contratoActual->observaciones }}</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- Estado vacío --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-file-earmark-text text-muted mb-3" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mb-3">Sin Contratos Registrados</h5>
                        <p class="text-muted mb-4">Este trabajador no tiene contratos en el sistema.</p>
                        
                        @if($trabajador->fichaTecnica)
                            <button type="button" 
                                    class="btn btn-primary btn-lg"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalCrearContrato">
                                <i class="bi bi-plus-lg"></i> Crear Primer Contrato
                            </button>
                            <div class="alert alert-info d-inline-block mt-3">
                                <i class="bi bi-info-circle"></i>
                                El trabajador tiene ficha técnica completa
                            </div>
                        @else
                            <div class="alert alert-warning d-inline-block">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Ficha técnica requerida</strong><br>
                                Complete primero los datos laborales
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('trabajadores.perfil.show', $trabajador) }}?tab=laborales" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-briefcase"></i> Completar Ficha Técnica
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ MODALES ACTUALIZADOS --}}

{{-- ✅ ACTUALIZADO: Modal de detalles con soporte completo para indeterminados --}}
<div class="modal fade" id="detalleContratoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text"></i> Detalles del Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <strong>Trabajador:</strong><br>
                        {{ $trabajador->nombre_completo }}
                    </div>
                    <div class="col-6">
                        <strong>Categoría:</strong><br>
                        {{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'N/A' }}
                    </div>
                </div>
                <hr>
                <div id="detalle-contenido">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal crear contrato (actualizado) --}}
@include('trabajadores.modales.crear_contrato', ['trabajador' => $trabajador])

{{-- ✅ MODAL RENOVAR CONTRATO CORREGIDO - Reemplazar solo esta sección --}}
<div class="modal fade" id="modalRenovarContrato" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Renovar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRenovarContrato" method="POST" data-trabajador-id="{{ $trabajador->id_trabajador }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Renovando contrato próximo a vencer
                    </div>
                    
                    <div class="row">
                        {{-- ✅ FECHA INICIO CORREGIDA: tipo text con formato global --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Inicio *</label>
                            <input type="text" 
                                   name="fecha_inicio" 
                                   id="fecha_inicio_renovar"
                                   class="form-control formato-fecha" 
                                   placeholder="DD/MM/YYYY"
                                   maxlength="10"
                                   autocomplete="off"
                                   required>
                            <div class="form-text">Formato: DD/MM/YYYY</div>
                        </div>
                        
                        {{-- ✅ FECHA FIN CORREGIDA: tipo text con formato global --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Fin *</label>
                            <input type="text" 
                                   name="fecha_fin" 
                                   id="fecha_fin_renovar"
                                   class="form-control formato-fecha" 
                                   placeholder="DD/MM/YYYY"
                                   maxlength="10"
                                   autocomplete="off"
                                   required>
                            <div class="form-text">Formato: DD/MM/YYYY</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Duración</label>
                            <div class="form-control bg-light d-flex align-items-center">
                                <span id="tipo-duracion-renovar" class="text-muted">Seleccione las fechas</span>
                            </div>
                            <input type="hidden" name="tipo_duracion" id="tipo_duracion_renovar">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duración Calculada</label>
                            <div class="form-control bg-light d-flex align-items-center">
                                <span id="duracion-renovar" class="text-muted">Seleccione las fechas</span>
                            </div>
                        </div>
                    </div>

                    <div id="resumen-renovacion" class="row mt-3" style="display: none;">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="bi bi-arrow-repeat"></i> Resumen de Renovación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Inicio:</small>
                                            <div class="fw-bold" id="resumen-inicio-renovar">-</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Fin:</small>
                                            <div class="fw-bold" id="resumen-fin-renovar">-</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Duración:</small>
                                            <div class="fw-bold text-warning" id="resumen-duracion-renovar">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones de Renovación (Opcional)</label>
                        <textarea name="observaciones_renovacion" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Motivo o detalles de la renovación"
                                  maxlength="500"></textarea>
                        <div class="form-text">Máximo 500 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Renovar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal eliminar contrato --}}
<div class="modal fade" id="modalEliminarContrato" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash"></i> Eliminar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEliminarContrato" method="POST" data-trabajador-id="{{ $trabajador->id_trabajador }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>¡Atención!</strong> Esta acción eliminará permanentemente el contrato y no se puede deshacer.
                    </div>
                    <div class="mb-3">
                        <strong>Período del contrato:</strong>
                        <span id="contrato-periodo-info"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de Eliminación *</label>
                        <textarea name="motivo_eliminacion" class="form-control" rows="3" required 
                                  placeholder="Especifique el motivo por el cual se elimina este contrato"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar Permanentemente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>