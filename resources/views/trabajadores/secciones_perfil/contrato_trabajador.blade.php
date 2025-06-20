{{-- resources/views/trabajadores/secciones_perfil/contrato_trabajador.blade.php --}}

<div class="container-fluid">
    {{-- ✅ Header simplificado --}}
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
                            @else
                                @if($trabajador->fichaTecnica)
                                    <button type="button" 
                                            class="btn btn-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCrearContrato">
                                        <i class="bi bi-plus-lg"></i> Crear Contrato
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Alertas importantes (simplificadas) --}}
    @if($contratos->count() > 0)
        @if($estadisticas['proximos_vencer'] > 0)
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>Atención:</strong> {{ $estadisticas['proximos_vencer'] }} contrato(s) próximo(s) a vencer.
                </div>
            </div>
        @endif

        @if(!$estadisticas['tiene_contrato_vigente'])
            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <div><strong>Sin contrato vigente</strong></div>
                </div>
                @if($trabajador->fichaTecnica)
                    <button type="button" 
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalCrearContrato">
                        <i class="bi bi-plus-lg"></i> Crear Contrato
                    </button>
                @endif
            </div>
        @endif
    @endif

    {{-- ✅ Contenido principal --}}
    <div class="row">
        <div class="col-12">
            @if($contratos->count() > 0)
                {{-- ✅ Lista de contratos (tabla simplificada) --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary"></i>
                                Historial de Contratos
                            </h5>
                            @if(!$estadisticas['tiene_contrato_vigente'] && $trabajador->fichaTecnica)
                                <button type="button" 
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalCrearContrato">
                                    <i class="bi bi-plus-lg"></i> Nuevo Contrato
                                </button>
                            @endif
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
                                        <th>Días Restantes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contratos as $contrato)
                                        <tr class="{{ $contrato->esta_vigente_bool ? 'table-success' : '' }}">
                                            {{-- Estado --}}
                                            <td>
                                                <span class="badge bg-{{ $contrato->color_estado }}">
                                                    @if($contrato->estado_calculado === 'vigente')
                                                        <i class="bi bi-check-circle"></i>
                                                    @elseif($contrato->estado_calculado === 'expirado')
                                                        <i class="bi bi-x-circle"></i>
                                                    @else
                                                        <i class="bi bi-clock"></i>
                                                    @endif
                                                    {{ ucfirst($contrato->estado_calculado) }}
                                                </span>
                                                @if($contrato->esta_vigente_bool)
                                                    <div><small class="text-success"><i class="bi bi-star-fill"></i> Activo</small></div>
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

                                            {{-- Días restantes --}}
                                            <td>
                                                @if($contrato->esta_vigente_bool)
                                                    @if($contrato->dias_restantes_calculados <= 30)
                                                        <span class="text-warning fw-bold">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            {{ $contrato->dias_restantes_calculados }} días
                                                        </span>
                                                    @else
                                                        <span class="text-success">
                                                            {{ $contrato->dias_restantes_calculados }} días
                                                        </span>
                                                    @endif
                                                @elseif($contrato->estado_calculado === 'expirado')
                                                    <span class="text-danger">
                                                        <i class="bi bi-x-circle"></i> Expirado
                                                    </span>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="bi bi-clock"></i> Pendiente
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Acciones simplificadas --}}
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    {{-- Ver detalles --}}
                                                    <button type="button" 
                                                            class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detalleContratoModal"
                                                            data-contrato="{{ json_encode([
                                                                'inicio' => $contrato->fecha_inicio_contrato->format('d/m/Y'),
                                                                'fin' => $contrato->fecha_fin_contrato->format('d/m/Y'),
                                                                'duracion' => $contrato->duracion_completa,
                                                                'estado' => $contrato->estado_calculado,
                                                                'dias_restantes' => $contrato->dias_restantes_calculados
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

                                                    {{-- Renovar contrato --}}
                                                    @if($contrato->esta_vigente_bool && $contrato->dias_restantes_calculados <= 30)
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

                                                    {{-- ✅ NUEVO: Terminar contrato --}}
                                                    @if($contrato->esta_vigente_bool)
                                                        <button type="button" 
                                                                class="btn btn-outline-danger"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalTerminarContrato"
                                                                data-contrato-id="{{ $contrato->id_contrato }}"
                                                                title="Terminar contrato">
                                                            <i class="bi bi-x-lg"></i>
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

                {{-- ✅ Información del contrato vigente (simplificada) --}}
                @if($estadisticas['contrato_actual'])
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-check"></i>
                                        Contrato Vigente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Período:</strong><br>
                                            {{ $estadisticas['contrato_actual']->fecha_inicio_contrato->format('d/m/Y') }} -
                                            {{ $estadisticas['contrato_actual']->fecha_fin_contrato->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Duración:</strong><br>
                                            {{ $estadisticas['contrato_actual']->duracion_texto }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Días Restantes:</strong><br>
                                            <span class="fw-bold {{ $estadisticas['contrato_actual']->diasRestantes() <= 30 ? 'text-warning' : 'text-success' }}">
                                                {{ $estadisticas['contrato_actual']->diasRestantes() }} días
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Vencimiento:</strong><br>
                                            {{ $estadisticas['contrato_actual']->fecha_fin_contrato->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- ✅ Estado vacío simplificado --}}
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

{{-- ✅ MODALES SIMPLIFICADOS --}}

{{-- Modal de detalles (simplificado) --}}
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

{{-- ✅ NUEVO: Modal terminar contrato --}}
<div class="modal fade" id="modalTerminarContrato" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-lg"></i> Terminar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTerminarContrato" method="POST" data-trabajador-id="{{ $trabajador->id_trabajador }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>¿Está seguro?</strong> Esta acción terminará el contrato vigente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de Terminación *</label>
                        <textarea name="motivo_terminacion" class="form-control" rows="3" required 
                                  placeholder="Especifique el motivo de la terminación del contrato"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg"></i> Terminar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>