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

    {{-- ✅ Alertas actualizadas --}}
    @if($contratos->count() > 0)
        {{-- ✅ ACTUALIZADO: Alerta para próximos a vencer (solo los que ya están en período vigente) --}}
        @if($estadisticas['proximos_vencer'] > 0)
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>Atención:</strong> {{ $estadisticas['proximos_vencer'] }} contrato(s) próximo(s) a vencer (30 días o menos).
                </div>
            </div>
        @endif

        {{-- ✅ SIMPLIFICADO: Sin contrato vigente --}}
        @if(!$estadisticas['tiene_contrato_vigente'])
            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <div><strong>Sin contrato vigente</strong> - Todos los contratos han terminado o se renovaron.</div>
                </div>
                <small class="text-muted">Renovar o eliminar contratos existentes</small>
            </div>
        @endif

        {{-- ✅ SIMPLIFICADO: Alerta para contratos que pueden renovarse --}}
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
                {{-- ✅ Lista de contratos (tabla actualizada) --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary"></i>
                                Historial de Contratos
                            </h5>
                            {{-- ✅ REMOVIDO: Botón crear contrato aquí, solo aparece cuando no hay contratos --}}
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Estado</th>
                                        <th>Período</th>
                                        <th>Duración</th>
                                        <th>Información</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contratos as $contrato)
                                        <tr class="{{ $contrato->esta_vigente_bool ? 'table-success' : '' }}">
                                            {{-- ✅ SIMPLIFICADO: Estado usando solo 3 estados --}}
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

                                                {{-- ✅ SIMPLIFICADO: Indicador de renovación --}}
                                                @if($contrato->esRenovacion())
                                                    <small class="d-block text-muted mt-1">
                                                        <i class="bi bi-link-45deg"></i> Renovación de #{{ $contrato->contrato_anterior_id }}
                                                    </small>
                                                @endif

                                                {{-- ✅ NUEVO: Indicador de expiración para vigentes --}}
                                                @if($contrato->esta_vigente_bool && $contrato->ya_expiro_bool)
                                                    <small class="d-block text-warning mt-1">
                                                        <i class="bi bi-exclamation-triangle"></i> Expirado
                                                    </small>
                                                @endif
                                            </td>

                                            {{-- Período --}}
                                            <td>
                                                <div>
                                                    <strong>{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }}</strong>
                                                    <small class="text-muted"> hasta </small>
                                                    <strong>{{ $contrato->fecha_fin_contrato->format('d/m/Y') }}</strong>
                                                </div>
                                            </td>

                                            {{-- Duración --}}
                                            <td>
                                                <span class="fw-bold">{{ $contrato->duracion_texto }}</span>
                                                <small class="d-block text-muted">
                                                    {{ $contrato->esPorDias() ? 'Por días' : 'Por meses' }}
                                                </small>
                                            </td>

                                            {{-- ✅ SIMPLIFICADA: Información usando info_estado --}}
                                            <td>
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
                                            </td>

                                            {{-- ✅ ACTUALIZADAS: Acciones con botón eliminar --}}
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    {{-- Ver detalles --}}
                                                    <button type="button" 
                                                            class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detalleContratoModal"
                                                            data-contrato="{{ json_encode([
                                                                'id' => $contrato->id_contrato,
                                                                'inicio' => $contrato->fecha_inicio_contrato->format('d/m/Y'),
                                                                'fin' => $contrato->fecha_fin_contrato->format('d/m/Y'),
                                                                'duracion' => $contrato->duracion_completa,
                                                                'estado' => $contrato->estado_final_calculado,
                                                                'texto_estado' => $contrato->texto_estado_final,
                                                                'info_estado' => $contrato->info_estado,
                                                                'es_renovacion' => $contrato->esRenovacion(),
                                                                'contrato_anterior_id' => $contrato->contrato_anterior_id,
                                                                'observaciones' => $contrato->observaciones,
                                                                'esta_vigente' => $contrato->esta_vigente_bool,
                                                                'ya_expiro' => $contrato->ya_expiro_bool
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

                                                    {{-- Renovar contrato (solo si puede renovarse) --}}
                                                    @if($contrato->puede_renovarse_bool)
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

                                                    {{-- ✅ SIMPLIFICADO: Eliminar contrato (solo si está vigente) --}}
                                                    @if($contrato->esta_vigente_bool)
                                                        <button type="button" 
                                                                class="btn btn-outline-danger"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEliminarContrato"
                                                                data-contrato-id="{{ $contrato->id_contrato }}"
                                                                data-contrato-info="{{ $contrato->fecha_inicio_contrato->format('d/m/Y') }} - {{ $contrato->fecha_fin_contrato->format('d/m/Y') }}"
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

                {{-- ✅ SIMPLIFICADA: Información del contrato vigente --}}
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
                                        @if($contratoActual->esRenovacion())
                                            <small class="ms-2">
                                                <i class="bi bi-arrow-repeat"></i> 
                                                Renovación de #{{ $contratoActual->contrato_anterior_id }}
                                            </small>
                                        @endif
                                        @if($contratoActual->yaExpiro())
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
                                            {{ $contratoActual->fecha_fin_contrato->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Duración:</strong><br>
                                            {{ $contratoActual->duracion_texto }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Estado:</strong><br>
                                            <span class="fw-bold {{ $contratoActual->yaExpiro() ? 'text-danger' : ($contratoActual->estaProximoAVencer() ? 'text-warning' : 'text-success') }}">
                                                {{ $contratoActual->info_estado }}
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Acciones:</strong><br>
                                            @if($contratoActual->puedeRenovarse())
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalRenovarContrato"
                                                        data-contrato-id="{{ $contratoActual->id_contrato }}"
                                                        data-contrato-fin="{{ $contratoActual->fecha_fin_contrato->format('Y-m-d') }}">
                                                    <i class="bi bi-arrow-repeat"></i> Renovar
                                                </button>
                                            @elseif($contratoActual->yaExpiro())
                                                <span class="text-muted">Expirado - Renovar o eliminar</span>
                                            @else
                                                <span class="text-success">En vigencia</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- ✅ Mostrar observaciones si existen --}}
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
                {{-- ✅ ACTUALIZADO: Estado vacío --}}
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

{{-- Modal de detalles (actualizado) --}}
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

{{-- Modal crear contrato --}}
@include('trabajadores.modales.crear_contrato', ['trabajador' => $trabajador])

{{-- Modal renovar contrato --}}
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
                    <div class="mb-3">
                        <label class="form-label">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Duración</label>
                        <select name="tipo_duracion" class="form-select" required>
                            <option value="meses" selected>Meses</option>
                            <option value="dias">Días</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones de Renovación (Opcional)</label>
                        <textarea name="observaciones_renovacion" class="form-control" rows="3" 
                                  placeholder="Motivo o detalles de la renovación"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Renovar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ NUEVO: Modal eliminar contrato --}}
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