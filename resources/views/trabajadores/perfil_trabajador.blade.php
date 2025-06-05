@extends('layouts.app')

@section('title', 'Perfil de ' . $trabajador->nombre_completo . ' - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header del Perfil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
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
                                @if($trabajador->es_nuevo)
                                    <span class="badge bg-info">
                                        <i class="bi bi-star"></i> Nuevo
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 text-primary mb-0">{{ $stats['antiguedad_texto'] }}</div>
                                    <small class="text-muted">Antigüedad</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-success mb-0">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}</div>
                                    <small class="text-muted">Sueldo Diario</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-info mb-0">{{ $stats['porcentaje_documentos'] }}%</div>
                                    <small class="text-muted">Documentos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación -->
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

    <!-- Alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Hay errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="nav-tabContent">
        
        <!-- DATOS PERSONALES -->
        <div class="tab-pane fade show active" id="nav-datos" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> Datos Personales
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('trabajadores.perfil.update-datos', $trabajador) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-4 mb-3">
                                <label for="nombre_trabajador" class="form-label">
                                    <i class="bi bi-person"></i> Nombre(s) *
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre_trabajador') is-invalid @enderror" 
                                       id="nombre_trabajador" 
                                       name="nombre_trabajador" 
                                       value="{{ old('nombre_trabajador', $trabajador->nombre_trabajador) }}" 
                                       required>
                                @error('nombre_trabajador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Apellido Paterno -->
                            <div class="col-md-4 mb-3">
                                <label for="ape_pat" class="form-label">
                                    <i class="bi bi-person"></i> Apellido Paterno *
                                </label>
                                <input type="text" 
                                       class="form-control @error('ape_pat') is-invalid @enderror" 
                                       id="ape_pat" 
                                       name="ape_pat" 
                                       value="{{ old('ape_pat', $trabajador->ape_pat) }}" 
                                       required>
                                @error('ape_pat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Apellido Materno -->
                            <div class="col-md-4 mb-3">
                                <label for="ape_mat" class="form-label">
                                    <i class="bi bi-person"></i> Apellido Materno
                                </label>
                                <input type="text" 
                                       class="form-control @error('ape_mat') is-invalid @enderror" 
                                       id="ape_mat" 
                                       name="ape_mat" 
                                       value="{{ old('ape_mat', $trabajador->ape_mat) }}">
                                @error('ape_mat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Fecha de Nacimiento -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_nacimiento" class="form-label">
                                    <i class="bi bi-calendar"></i> Fecha de Nacimiento *
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                       id="fecha_nacimiento" 
                                       name="fecha_nacimiento" 
                                       value="{{ old('fecha_nacimiento', $trabajador->fecha_nacimiento?->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                       required>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CURP -->
                            <div class="col-md-4 mb-3">
                                <label for="curp" class="form-label">
                                    <i class="bi bi-card-text"></i> CURP *
                                </label>
                                <input type="text" 
                                       class="form-control @error('curp') is-invalid @enderror" 
                                       id="curp" 
                                       name="curp" 
                                       value="{{ old('curp', $trabajador->curp) }}" 
                                       maxlength="18"
                                       required>
                                @error('curp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- RFC -->
                            <div class="col-md-4 mb-3">
                                <label for="rfc" class="form-label">
                                    <i class="bi bi-card-text"></i> RFC *
                                </label>
                                <input type="text" 
                                       class="form-control @error('rfc') is-invalid @enderror" 
                                       id="rfc" 
                                       name="rfc" 
                                       value="{{ old('rfc', $trabajador->rfc) }}" 
                                       maxlength="13"
                                       required>
                                @error('rfc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- NSS -->
                            <div class="col-md-4 mb-3">
                                <label for="no_nss" class="form-label">
                                    <i class="bi bi-shield-check"></i> NSS
                                </label>
                                <input type="text" 
                                       class="form-control @error('no_nss') is-invalid @enderror" 
                                       id="no_nss" 
                                       name="no_nss" 
                                       value="{{ old('no_nss', $trabajador->no_nss) }}" 
                                       maxlength="11">
                                @error('no_nss')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-4 mb-3">
                                <label for="telefono" class="form-label">
                                    <i class="bi bi-telephone"></i> Teléfono *
                                </label>
                                <input type="tel" 
                                       class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="{{ old('telefono', $trabajador->telefono) }}" 
                                       maxlength="10"
                                       required>
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Correo -->
                            <div class="col-md-4 mb-3">
                                <label for="correo" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico
                                </label>
                                <input type="email" 
                                       class="form-control @error('correo') is-invalid @enderror" 
                                       id="correo" 
                                       name="correo" 
                                       value="{{ old('correo', $trabajador->correo) }}">
                                @error('correo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dirección -->
                            <div class="col-md-8 mb-3">
                                <label for="direccion" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Dirección
                                </label>
                                <input type="text" 
                                       class="form-control @error('direccion') is-invalid @enderror" 
                                       id="direccion" 
                                       name="direccion" 
                                       value="{{ old('direccion', $trabajador->direccion) }}">
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Fecha de Ingreso -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_ingreso" class="form-label">
                                    <i class="bi bi-calendar-check"></i> Fecha de Ingreso *
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_ingreso') is-invalid @enderror" 
                                       id="fecha_ingreso" 
                                       name="fecha_ingreso" 
                                       value="{{ old('fecha_ingreso', $trabajador->fecha_ingreso?->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}"
                                       required>
                                @error('fecha_ingreso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar Datos Personales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<!-- ✅ REEMPLAZAR LA SECCIÓN DE DATOS LABORALES EN perfil_trabajador.blade.php -->

<!-- DATOS LABORALES -->
<div class="tab-pane fade" id="nav-laborales" role="tabpanel">
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
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" 
                                                {{ old('id_area', $trabajador->fichaTecnica->categoria->id_area ?? '') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre_area }}
                                        </option>
                                    @endforeach
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
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}" 
                                                {{ old('id_categoria', $trabajador->fichaTecnica->id_categoria ?? '') == $categoria->id_categoria ? 'selected' : '' }}>
                                            {{ $categoria->nombre_categoria }}
                                        </option>
                                    @endforeach
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

                            <!-- ✅ NUEVO: Tipo de Cambio -->
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

        <!-- ✅ PANEL DE HISTORIAL SIN CRECIMIENTO -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up-arrow"></i> Historial de Cambios
                        </h6>
                        <a href="{{ route('trabajadores.historial-promociones', $trabajador) }}" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-eye"></i> Ver Todo
                        </a>
                    </div>
                </div>
                <div class="card-body p-2">
                    @if(isset($statsPromociones) && $statsPromociones['total_cambios'] > 0)
                        <!-- Estadísticas Rápidas (SIN CRECIMIENTO) -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="text-success fw-bold">{{ $statsPromociones['promociones'] }}</div>
                                <small class="text-muted">Promociones</small>
                            </div>
                            <div class="col-4">
                                <div class="text-primary fw-bold">{{ $statsPromociones['transferencias'] }}</div>
                                <small class="text-muted">Transferencias</small>
                            </div>
                            <div class="col-4">
                                <div class="text-info fw-bold">{{ $statsPromociones['total_cambios'] }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>

                        <!-- Últimos 3 Cambios -->
                        <div class="timeline-sm">
                            @foreach($historialReciente->take(3) as $cambio)
                                <div class="timeline-item mb-2">
                                    <div class="d-flex">
                                        <div class="me-2">
                                            <span class="badge bg-{{ $cambio->color_tipo_cambio }} rounded-pill p-1">
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
                                            <div class="small fw-bold">{{ $cambio->tipo_cambio_texto }}</div>
                                            <div class="text-muted small">
                                                {{ $cambio->categoriaNueva->nombre_categoria ?? 'Sin categoría' }}
                                            </div>
                                            <div class="text-success small">
                                                ${{ number_format($cambio->sueldo_nuevo, 2) }}
                                                @if($cambio->sueldo_anterior)
                                                    <small class="text-muted">
                                                        ({{ $cambio->diferencia_sueldo >= 0 ? '+' : '' }}${{ number_format($cambio->diferencia_sueldo, 2) }})
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                {{ $cambio->fecha_cambio->format('d/m/Y') }}
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
                            <p class="mb-0 small">Sin historial de cambios</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- DOCUMENTOS -->
        <div class="tab-pane fade" id="nav-documentos" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-files"></i> Documentos del Trabajador
                        </h5>
                        <div>
                            <span class="badge bg-{{ $trabajador->documentos?->color_progreso ?? 'secondary' }} fs-6">
                                {{ $stats['porcentaje_documentos'] }}% Completado
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progreso de Documentos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-{{ $trabajador->documentos?->color_progreso ?? 'secondary' }}" 
                                     style="width: {{ $stats['porcentaje_documentos'] }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>{{ count(\App\Models\DocumentoTrabajador::TODOS_DOCUMENTOS) - $stats['documentos_faltantes'] }} de {{ count(\App\Models\DocumentoTrabajador::TODOS_DOCUMENTOS) }} documentos</span>
                                <span>{{ $stats['estado_documentos'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Documentos -->
                    <div class="row">
                        @foreach(\App\Models\DocumentoTrabajador::TODOS_DOCUMENTOS as $campo => $nombre)
                            @php
                                $tieneDocumento = $trabajador->documentos && !empty($trabajador->documentos->$campo);
                                $esBasico = array_key_exists($campo, \App\Models\DocumentoTrabajador::DOCUMENTOS_BASICOS);
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-{{ $tieneDocumento ? 'success' : ($esBasico ? 'warning' : 'light') }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                {{ $nombre }}
                                                @if($esBasico)
                                                    <span class="badge bg-warning text-dark ms-1">Básico</span>
                                                @endif
                                            </h6>
                                            @if($tieneDocumento)
                                                <i class="bi bi-check-circle text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle text-muted"></i>
                                            @endif
                                        </div>
                                        
                                        @if($tieneDocumento)
                                            <div class="d-flex gap-2">
                                                <a href="{{ Storage::disk('public')->url($trabajador->documentos->$campo) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadModal"
                                                        data-tipo="{{ $campo }}"
                                                        data-nombre="{{ $nombre }}">
                                                    <i class="bi bi-arrow-repeat"></i> Cambiar
                                                </button>
                                                <form action="{{ route('trabajadores.perfil.delete-document', $trabajador) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Estás seguro de eliminar este documento?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="tipo_documento" value="{{ $campo }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadModal"
                                                    data-tipo="{{ $campo }}"
                                                    data-nombre="{{ $nombre }}">
                                                <i class="bi bi-cloud-upload"></i> Subir
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ INCLUIR MODALES SEPARADOS --}}
@include('trabajadores.modales.subir_documento', ['trabajador' => $trabajador])

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ✅ CARGAR CATEGORÍAS CUANDO CAMBIA EL ÁREA
    const areaSelect = document.getElementById('id_area');
    const categoriaSelect = document.getElementById('id_categoria');
    
    if (areaSelect && categoriaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            
            // Limpiar categorías
            categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';
            categoriaSelect.disabled = true;
            
            if (areaId) {
                // ✅ Usar la ruta API general
                fetch(`/api/categorias/${areaId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(categorias => {
                        // Limpiar y agregar opción por defecto
                        categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                        
                        // Agregar categorías
                        categorias.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.id_categoria;
                            option.textContent = categoria.nombre_categoria;
                            categoriaSelect.appendChild(option);
                        });
                        
                        categoriaSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error al cargar categorías:', error);
                        categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                        categoriaSelect.disabled = false;
                        
                        // Mostrar alerta al usuario
                        alert('Error al cargar las categorías. Por favor, recarga la página.');
                    });
            } else {
                // Si no hay área seleccionada, limpiar categorías
                categoriaSelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                categoriaSelect.disabled = false;
            }
        });
    }

    // Auto-hide alerts (evitar cerrar alertas persistentes)
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
        alerts.forEach(alert => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);

    // Mensaje de debug
    console.log('✅ Vista Perfil Trabajador inicializada correctamente');
});
</script>

@endsection