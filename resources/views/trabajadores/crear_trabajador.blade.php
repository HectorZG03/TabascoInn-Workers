@extends('layouts.app')

@section('title', 'Nuevo Trabajador - Hotel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-person-plus-fill text-primary"></i> Nuevo Trabajador
                    </h2>
                    <p class="text-muted mb-0">Registrar un nuevo empleado en el sistema</p>
                </div>
                <!-- ✅ ENLACE CORREGIDO -->
                <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Ver Lista de Trabajadores
                </a>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="validation-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Hay errores en el formulario:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('trabajadores.store') }}" method="POST" enctype="multipart/form-data" id="formTrabajador">
        @csrf
        
        <div class="row">
            <!-- Columna Principal (Formulario) -->
            <div class="col-lg-8">
                
                <!-- SECCIÓN 1: DATOS PERSONALES -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> Datos Personales
                        </h5>
                    </div>
                    <div class="card-body">
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
                                       value="{{ old('nombre_trabajador') }}" 
                                       placeholder="Nombre completo"
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
                                       value="{{ old('ape_pat') }}" 
                                       placeholder="Apellido paterno"
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
                                       value="{{ old('ape_mat') }}" 
                                       placeholder="Apellido materno">
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
                                       value="{{ old('fecha_nacimiento') }}" 
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
                                       value="{{ old('curp') }}" 
                                       placeholder="18 caracteres"
                                       maxlength="18"
                                       pattern="[A-Z0-9]{18}"
                                       required>
                                <div class="form-text">Ejemplo: AAAA000000HDFRRR01</div>
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
                                       value="{{ old('rfc') }}" 
                                       placeholder="13 caracteres"
                                       maxlength="13"
                                       pattern="[A-Z0-9]{13}"
                                       required>
                                <div class="form-text">Ejemplo: AAAA000000AA0</div>
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
                                    value="{{ old('no_nss') }}" 
                                    placeholder="Número de Seguro Social"
                                    maxlength="11">
                                <div class="form-text">Ejemplo: 12345678901</div>
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
                                       value="{{ old('telefono') }}" 
                                       placeholder="9931234567"
                                       maxlength="10"
                                       pattern="[0-9]{10}"
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
                                       value="{{ old('correo') }}" 
                                       placeholder="ejemplo@email.com">
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
                                       value="{{ old('direccion') }}" 
                                       placeholder="Calle, número, colonia">
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
                                    value="{{ old('fecha_ingreso', date('Y-m-d')) }}" 
                                    max="{{ date('Y-m-d') }}"
                                    required>
                                <div class="form-text">Fecha en que ingresó a la empresa</div>
                                @error('fecha_ingreso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DATOS LABORALES -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-briefcase-fill"></i> Datos Laborales
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Área -->
                            <div class="col-md-4 mb-3">
                                <label for="id_area" class="form-label">
                                    <i class="bi bi-building"></i> Área *
                                </label>
                                <select class="form-select @error('id_area') is-invalid @enderror" 
                                        id="id_area" 
                                        name="id_area" 
                                        required>
                                    <option value="">Seleccionar área...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" {{ old('id_area') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre_area }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-4 mb-3">
                                <label for="id_categoria" class="form-label">
                                    <i class="bi bi-person-badge"></i> Categoría *
                                </label>
                                <select class="form-select @error('id_categoria') is-invalid @enderror" 
                                        id="id_categoria" 
                                        name="id_categoria" 
                                        required 
                                        disabled>
                                    <option value="">Primero selecciona un área</option>
                                </select>
                                @error('id_categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sueldo Diario -->
                            <div class="col-md-4 mb-3">
                                <label for="sueldo_diarios" class="form-label">
                                    <i class="bi bi-cash"></i> Sueldo Diario *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('sueldo_diarios') is-invalid @enderror" 
                                           id="sueldo_diarios" 
                                           name="sueldo_diarios" 
                                           value="{{ old('sueldo_diarios') }}" 
                                           placeholder="0.00"
                                           step="0.01"
                                           min="1"
                                           required>
                                </div>
                                @error('sueldo_diarios')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

<div class="row">
                            <!-- Formación -->
                            <div class="col-md-4 mb-3">
                                <label for="formacion" class="form-label">
                                    <i class="bi bi-mortarboard"></i> Formación Académica
                                </label>
                                <select class="form-select @error('formacion') is-invalid @enderror" 
                                        id="formacion" 
                                        name="formacion">
                                    <option value="">Seleccionar...</option>
                                    <option value="Sin estudios" {{ old('formacion') == 'Sin estudios' ? 'selected' : '' }}>Sin estudios</option>
                                    <option value="Primaria" {{ old('formacion') == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                                    <option value="Secundaria" {{ old('formacion') == 'Secundaria' ? 'selected' : '' }}>Secundaria</option>
                                    <option value="Preparatoria" {{ old('formacion') == 'Preparatoria' ? 'selected' : '' }}>Preparatoria</option>
                                    <option value="Universidad" {{ old('formacion') == 'Universidad' ? 'selected' : '' }}>Universidad</option>
                                    <option value="Posgrado" {{ old('formacion') == 'Posgrado' ? 'selected' : '' }}>Posgrado</option>
                                </select>
                                @error('formacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Grado de Estudios -->
                            <div class="col-md-4 mb-3">
                                <label for="grado_estudios" class="form-label">
                                    <i class="bi bi-award"></i> Grado de Estudios
                                </label>
                                <input type="text" 
                                       class="form-control @error('grado_estudios') is-invalid @enderror" 
                                       id="grado_estudios" 
                                       name="grado_estudios" 
                                       value="{{ old('grado_estudios') }}" 
                                       placeholder="Ej: Licenciatura en Administración">
                                @error('grado_estudios')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ✅ ESTADO INICIAL -->
                            <div class="col-md-4 mb-3">
                                <label for="estatus" class="form-label">
                                    <i class="bi bi-person-check"></i> Estado Inicial
                                </label>
                                <select class="form-select @error('estatus') is-invalid @enderror" 
                                        id="estatus" 
                                        name="estatus">
                                    <option value="">Por defecto (Activo)</option>
                                    @foreach(\App\Models\Trabajador::TODOS_ESTADOS as $valor => $texto)
                                        <option value="{{ $valor }}" 
                                                {{ old('estatus') == $valor ? 'selected' : '' }}
                                                data-color="{{ 
                                                    $valor === 'activo' ? 'success' : 
                                                    ($valor === 'inactivo' ? 'secondary' : 
                                                    ($valor === 'suspendido' ? 'danger' : 'info'))
                                                }}">
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        Por defecto se creará como "Activo". Solo cambia si es necesario.
                                    </small>
                                </div>
                                @error('estatus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: DOCUMENTOS -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark"></i> Documentos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Importante:</strong> Los documentos son opcionales al crear el trabajador. 
                            Puedes subirlos posteriormente desde el perfil del empleado.
                        </div>
                        
                        <div class="row">
                            <!-- INE -->
                            <div class="col-md-6 mb-3">
                                <label for="ine" class="form-label">
                                    <i class="bi bi-card-image"></i> INE/IFE
                                </label>
                                <input type="file" 
                                       class="form-control @error('ine') is-invalid @enderror" 
                                       id="ine" 
                                       name="ine" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('ine')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Acta de Nacimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="acta_nacimiento" class="form-label">
                                    <i class="bi bi-file-text"></i> Acta de Nacimiento
                                </label>
                                <input type="file" 
                                       class="form-control @error('acta_nacimiento') is-invalid @enderror" 
                                       id="acta_nacimiento" 
                                       name="acta_nacimiento" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('acta_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- NSS -->
                            <div class="col-md-6 mb-3">
                                <label for="nss" class="form-label">
                                    <i class="bi bi-shield-check"></i> Número de Seguro Social
                                </label>
                                <input type="file" 
                                       class="form-control @error('nss') is-invalid @enderror" 
                                       id="nss" 
                                       name="nss" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('nss')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Comprobante de Domicilio -->
                            <div class="col-md-6 mb-3">
                                <label for="comprobante_domicilio" class="form-label">
                                    <i class="bi bi-house"></i> Comprobante de Domicilio
                                </label>
                                <input type="file" 
                                       class="form-control @error('comprobante_domicilio') is-invalid @enderror" 
                                       id="comprobante_domicilio" 
                                       name="comprobante_domicilio" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('comprobante_domicilio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Acta de Residencia -->
                            <div class="col-md-6 mb-3">
                                <label for="acta_residencia" class="form-label">
                                    <i class="bi bi-geo-alt-fill"></i> Acta de Residencia
                                </label>
                                <input type="file" 
                                       class="form-control @error('acta_residencia') is-invalid @enderror" 
                                       id="acta_residencia" 
                                       name="acta_residencia" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('acta_residencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CURP Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="curp_documento" class="form-label">
                                    <i class="bi bi-card-text"></i> CURP (Documento)
                                </label>
                                <input type="file" 
                                       class="form-control @error('curp_documento') is-invalid @enderror" 
                                       id="curp_documento" 
                                       name="curp_documento" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('curp_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- RFC Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="rfc_documento" class="form-label">
                                    <i class="bi bi-receipt"></i> RFC (Documento)
                                </label>
                                <input type="file" 
                                       class="form-control @error('rfc_documento') is-invalid @enderror" 
                                       id="rfc_documento" 
                                       name="rfc_documento" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Formatos: PDF, JPG, PNG (máx. 2MB)</div>
                                @error('rfc_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Espacio para futuros documentos -->
                            <div class="col-md-6"></div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <!-- ✅ ENLACE CORREGIDO -->
                            <a href="{{ route('trabajadores.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div>
                                <button type="submit" class="btn btn-success btn-lg" id="btnGuardar">
                                    <i class="bi bi-save"></i> Guardar Trabajador
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral (Información y Ayuda) -->
            <div class="col-lg-4">
                <!-- Vista Previa -->
                <div class="card shadow mb-4 sticky-top">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-eye"></i> Vista Previa
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-preview mb-2">
                                <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h6 id="preview-nombre" class="text-muted">Nombre del Trabajador</h6>
                            <small id="preview-categoria" class="text-muted">Categoría - Área</small>
                        </div>
                        
                        <hr>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <i class="bi bi-cash text-success"></i>
                                <div class="fw-bold text-success" id="preview-sueldo">$0.00</div>
                                <small class="text-muted">Sueldo Diario</small>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-calendar text-primary"></i>
                                <div class="fw-bold text-primary" id="preview-edad">-- años</div>
                                <small class="text-muted">Edad</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ayuda -->
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-question-circle"></i> Ayuda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p><strong>Campos obligatorios:</strong></p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Nombre y apellidos</li>
                                <li><i class="bi bi-check-circle text-success"></i> Fecha de nacimiento</li>
                                <li><i class="bi bi-check-circle text-success"></i> CURP y RFC</li>
                                <li><i class="bi bi-check-circle text-success"></i> Teléfono</li>
                                <li><i class="bi bi-check-circle text-success"></i> Área y categoría</li>
                                <li><i class="bi bi-check-circle text-success"></i> Sueldo diario</li>
                            </ul>
                            
                            <p><strong>Tips:</strong></p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-lightbulb text-warning"></i> Los documentos pueden subirse después</li>
                                <li><i class="bi bi-lightbulb text-warning"></i> La categoría se filtra por área</li>
                                <li><i class="bi bi-lightbulb text-warning"></i> Edad mínima: 18 años</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="{{ asset('js/crear_trabajador.js') }}"></script>
<style src="{{ asset('css/dashboard.css') }}"></style>
@endsection