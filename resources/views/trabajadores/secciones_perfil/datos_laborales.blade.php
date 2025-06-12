{{-- resources/views/trabajadores/secciones_perfil/datos_laborales.blade.php --}}

<div class="row">
    <!-- Formulario de Datos Laborales -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase-fill"></i> Datos Laborales
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('trabajadores.perfil.update-ficha', $trabajador) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Área -->
                        <div class="col-md-6 mb-3">
                            <label for="id_area" class="form-label">
                                <i class="bi bi-building"></i> Área *
                            </label>
                            <select class="form-select @error('id_area') is-invalid @enderror" 
                                    id="id_area" 
                                    name="id_area" 
                                    required>
                                <option value="">Seleccionar área...</option>
                                @if(isset($areas))
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" 
                                                {{ old('id_area', $trabajador->fichaTecnica->categoria->id_area ?? '') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre_area }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('id_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-6 mb-3">
                            <label for="id_categoria" class="form-label">
                                <i class="bi bi-person-badge"></i> Categoría *
                            </label>
                            <select class="form-select @error('id_categoria') is-invalid @enderror" 
                                    id="id_categoria" 
                                    name="id_categoria" 
                                    required>
                                <option value="">Seleccionar categoría...</option>
                                @if(isset($categorias))
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}" 
                                                {{ old('id_categoria', $trabajador->fichaTecnica->id_categoria ?? '') == $categoria->id_categoria ? 'selected' : '' }}>
                                            {{ $categoria->nombre_categoria }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('id_categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Sueldo Diario -->
                        <div class="col-md-6 mb-3">
                            <label for="sueldo_diarios" class="form-label">
                                <i class="bi bi-cash"></i> Sueldo Diario *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control @error('sueldo_diarios') is-invalid @enderror" 
                                       id="sueldo_diarios" 
                                       name="sueldo_diarios" 
                                       value="{{ old('sueldo_diarios', $trabajador->fichaTecnica->sueldo_diarios ?? '') }}" 
                                       step="0.01"
                                       min="1"
                                       required>
                            </div>
                            @error('sueldo_diarios')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tipo de Cambio -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_cambio" class="form-label">
                                <i class="bi bi-arrow-up-circle"></i> Tipo de Cambio
                            </label>
                            <select class="form-select @error('tipo_cambio') is-invalid @enderror" 
                                    id="tipo_cambio" 
                                    name="tipo_cambio">
                                <option value="">Determinar automáticamente</option>
                                <option value="promocion" {{ old('tipo_cambio') == 'promocion' ? 'selected' : '' }}>
                                    🎉 Promoción
                                </option>
                                <option value="transferencia" {{ old('tipo_cambio') == 'transferencia' ? 'selected' : '' }}>
                                    🔄 Transferencia
                                </option>
                                <option value="aumento_sueldo" {{ old('tipo_cambio') == 'aumento_sueldo' ? 'selected' : '' }}>
                                    💰 Aumento de Sueldo
                                </option>
                                <option value="reclasificacion" {{ old('tipo_cambio') == 'reclasificacion' ? 'selected' : '' }}>
                                    📋 Reclasificación
                                </option>
                                <option value="ajuste_salarial" {{ old('tipo_cambio') == 'ajuste_salarial' ? 'selected' : '' }}>
                                    ⚖️ Ajuste Salarial
                                </option>
                            </select>
                            <small class="text-muted">Si no seleccionas, se determinará automáticamente</small>
                            @error('tipo_cambio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Motivo del Cambio -->
                        <div class="col-md-12 mb-3">
                            <label for="motivo_cambio" class="form-label">
                                <i class="bi bi-chat-text"></i> Motivo del Cambio
                            </label>
                            <input type="text" 
                                   class="form-control @error('motivo_cambio') is-invalid @enderror" 
                                   id="motivo_cambio" 
                                   name="motivo_cambio" 
                                   value="{{ old('motivo_cambio') }}"
                                   placeholder="Ej: Promoción por excelente desempeño, Transferencia por necesidades operativas...">
                            <small class="text-muted">Opcional - Se registrará en el historial de cambios</small>
                            @error('motivo_cambio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Formación -->
                        <div class="col-md-6 mb-3">
                            <label for="formacion" class="form-label">
                                <i class="bi bi-mortarboard"></i> Formación Académica
                            </label>
                            <select class="form-select @error('formacion') is-invalid @enderror" 
                                    id="formacion" 
                                    name="formacion">
                                <option value="">Seleccionar...</option>
                                @foreach(['Sin estudios', 'Primaria', 'Secundaria', 'Preparatoria', 'Universidad', 'Posgrado'] as $nivel)
                                    <option value="{{ $nivel }}" 
                                            {{ old('formacion', $trabajador->fichaTecnica->formacion ?? '') == $nivel ? 'selected' : '' }}>
                                        {{ $nivel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('formacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Grado de Estudios -->
                        <div class="col-md-6 mb-3">
                            <label for="grado_estudios" class="form-label">
                                <i class="bi bi-award"></i> Grado de Estudios
                            </label>
                            <input type="text" 
                                   class="form-control @error('grado_estudios') is-invalid @enderror" 
                                   id="grado_estudios" 
                                   name="grado_estudios" 
                                   value="{{ old('grado_estudios', $trabajador->fichaTecnica->grado_estudios ?? '') }}">
                            @error('grado_estudios')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Actualizar Datos Laborales
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ PANEL DE HISTORIAL CON VERIFICACIONES DE SEGURIDAD -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up-arrow"></i> Historial de Cambios
                    </h6>
                    @if(Route::has('trabajadores.historial-promociones'))
                        <a href="{{ route('trabajadores.historial-promociones', $trabajador) }}" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-eye"></i> Ver Todo
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-2">
                @if(isset($statsPromociones) && $statsPromociones['total_cambios'] > 0)
                    <!-- ✅ ESTADÍSTICAS RÁPIDAS (CON VERIFICACIONES) -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="text-success fw-bold">{{ $statsPromociones['promociones'] ?? 0 }}</div>
                            <small class="text-muted">Promociones</small>
                        </div>
                        <div class="col-4">
                            <div class="text-primary fw-bold">{{ $statsPromociones['transferencias'] ?? 0 }}</div>
                            <small class="text-muted">Transferencias</small>
                        </div>
                        <div class="col-4">
                            <div class="text-info fw-bold">{{ $statsPromociones['total_cambios'] ?? 0 }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>

                    <!-- ✅ ÚLTIMOS CAMBIOS (CON VERIFICACIONES) -->
                    @if(isset($historialReciente) && $historialReciente->isNotEmpty())
                        <div class="timeline-sm">
                            @foreach($historialReciente->take(3) as $cambio)
                                <div class="timeline-item mb-2">
                                    <div class="d-flex">
                                        <div class="me-2">
                                            <span class="badge bg-{{ $cambio->color_tipo_cambio ?? 'secondary' }} rounded-pill p-1">
                                                @if($cambio->tipo_cambio == 'promocion')
                                                    <i class="bi bi-arrow-up"></i>
                                                @elseif($cambio->tipo_cambio == 'transferencia')
                                                    <i class="bi bi-arrow-left-right"></i>
                                                @elseif($cambio->tipo_cambio == 'aumento_sueldo')
                                                    <i class="bi bi-cash"></i>
                                                @else
                                                    <i class="bi bi-gear"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold">{{ $cambio->tipo_cambio_texto ?? 'Cambio' }}</div>
                                            <div class="text-muted small">
                                                {{ $cambio->categoriaNueva->nombre_categoria ?? 'Sin categoría' }}
                                            </div>
                                            <div class="text-success small">
                                                ${{ number_format($cambio->sueldo_nuevo ?? 0, 2) }}
                                                @if(isset($cambio->sueldo_anterior) && $cambio->sueldo_anterior > 0)
                                                    <small class="text-muted">
                                                        ({{ ($cambio->diferencia_sueldo ?? 0) >= 0 ? '+' : '' }}${{ number_format($cambio->diferencia_sueldo ?? 0, 2) }})
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                {{ $cambio->fecha_cambio ? $cambio->fecha_cambio->format('d/m/Y') : 'Fecha no disponible' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-graph-up fs-2 opacity-50"></i>
                            <p class="mb-0 small">Sin historial reciente</p>
                        </div>
                    @endif
                @else
                    <!-- ✅ ESTADO VACÍO -->
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-graph-up fs-2 opacity-50"></i>
                        <p class="mb-0 small">Sin historial de cambios</p>
                        <small class="text-muted">Los cambios aparecerán aquí cuando actualices los datos laborales</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ✅ DEBUG TEMPORAL (remover en producción) --}}
@if(config('app.debug'))
    <div class="mt-3">
        <details class="border rounded p-2 bg-light">
            <summary class="text-muted small">🔍 Debug Info</summary>
            <div class="mt-2 small">
                <strong>Variables disponibles:</strong><br>
                - $statsPromociones: {{ isset($statsPromociones) ? '✅' : '❌' }}<br>
                - $historialReciente: {{ isset($historialReciente) ? '✅' : '❌' }}<br>
                - $areas: {{ isset($areas) ? '✅' : '❌' }}<br>
                - $categorias: {{ isset($categorias) ? '✅' : '❌' }}<br>
                
                @if(isset($statsPromociones))
                    <strong>Stats:</strong> {{ json_encode($statsPromociones) }}<br>
                @endif
                
                @if(isset($historialReciente))
                    <strong>Historial count:</strong> {{ $historialReciente->count() }}<br>
                @endif
            </div>
        </details>
    </div>
@endif