{{-- resources/views/trabajadores/secciones_perfil/datos_personales.blade.php --}}
{{-- ✅ CON CONTACTO DE EMERGENCIA INCLUIDO --}}

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-person-circle"></i> Datos Personales
        </h5>
    </div>
    <div class="card-body">
        {{-- FORMULARIO DE DATOS PERSONALES --}}
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
                <div class="col-md-3 mb-3">
                    <label for="fecha_nacimiento" class="form-label">
                        <i class="bi bi-calendar"></i> Fecha de Nacimiento *
                    </label>
                    <input type="text" 
                           class="form-control formato-fecha @error('fecha_nacimiento') is-invalid @enderror" 
                           id="fecha_nacimiento" 
                           name="fecha_nacimiento" 
                           value="{{ old('fecha_nacimiento', $trabajador->fecha_nacimiento ? $trabajador->fecha_nacimiento->format('d/m/Y') : '') }}" 
                           placeholder="DD/MM/YYYY"
                           maxlength="10"
                           autocomplete="off"
                           required>
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Estado Civil -->
                <div class="col-md-3 mb-3">
                    <label for="estado_civil" class="form-label">
                        <i class="bi bi-heart"></i> Estado Civil *
                    </label>
                    <select class="form-select @error('estado_civil') is-invalid @enderror" 
                            id="estado_civil" 
                            name="estado_civil" 
                            required>
                        <option value="">Seleccionar...</option>
                        @foreach(\App\Models\Trabajador::ESTADOS_CIVILES as $key => $valor)
                            <option value="{{ $key }}" 
                                    {{ old('estado_civil', $trabajador->estado_civil) == $key ? 'selected' : '' }}>
                                {{ $valor }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado_civil')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Lugar de Nacimiento -->
                <div class="col-md-3 mb-3">
                    <label for="lugar_nacimiento" class="form-label">
                        <i class="bi bi-geo"></i> Lugar de Nacimiento
                    </label>
                    <input type="text" 
                           class="form-control @error('lugar_nacimiento') is-invalid @enderror" 
                           id="lugar_nacimiento" 
                           name="lugar_nacimiento" 
                           value="{{ old('lugar_nacimiento', $trabajador->lugar_nacimiento) }}"
                           placeholder="Ej: Villahermosa, Tabasco"
                           maxlength="100">
                    @error('lugar_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CURP -->
                <div class="col-md-3 mb-3">
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
            </div>

            <div class="row">
                <!-- RFC -->
                <div class="col-md-3 mb-3">
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

                <!-- NSS -->
                <div class="col-md-3 mb-3">
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
                <div class="col-md-3 mb-3">
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
                <div class="col-md-3 mb-3">
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
                <!-- Estado Actual -->
                <div class="col-md-2 mb-3">
                    <label for="estado_actual" class="form-label">
                        <i class="bi bi-map"></i> Estado Actual
                    </label>
                    <input type="text" 
                           class="form-control @error('estado_actual') is-invalid @enderror" 
                           id="estado_actual" 
                           name="estado_actual" 
                           value="{{ old('estado_actual', $trabajador->estado_actual) }}"
                           placeholder="Ej: Tabasco"
                           maxlength="50">
                    <div class="form-text">Estado donde vive</div>
                    @error('estado_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ciudad Actual -->
                <div class="col-md-2 mb-3">
                    <label for="ciudad_actual" class="form-label">
                        <i class="bi bi-building"></i> Ciudad Actual
                    </label>
                    <input type="text" 
                           class="form-control @error('ciudad_actual') is-invalid @enderror" 
                           id="ciudad_actual" 
                           name="ciudad_actual" 
                           value="{{ old('ciudad_actual', $trabajador->ciudad_actual) }}"
                           placeholder="Ej: Villahermosa"
                           maxlength="50">
                    @error('ciudad_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Código Postal -->
                <div class="col-md-2 mb-3">
                    <label for="codigo_postal" class="form-label">
                        <i class="bi bi-mailbox"></i> Código Postal *
                    </label>
                    <input type="text" 
                           class="form-control @error('codigo_postal') is-invalid @enderror" 
                           id="codigo_postal" 
                           name="codigo_postal" 
                           value="{{ old('codigo_postal', $trabajador->codigo_postal) }}"
                           maxlength="5"
                           pattern="[0-9]{5}"
                           placeholder="Ej: 86000"
                           required>
                    <div class="form-text">5 dígitos</div>
                    @error('codigo_postal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Dirección -->
                <div class="col-md-4 mb-3">
                    <label for="direccion" class="form-label">
                        <i class="bi bi-geo-alt"></i> Dirección Completa
                    </label>
                    <input type="text" 
                           class="form-control @error('direccion') is-invalid @enderror" 
                           id="direccion" 
                           name="direccion" 
                           value="{{ old('direccion', $trabajador->direccion) }}"
                           placeholder="Calle, número, colonia">
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Fecha de Ingreso -->
                <div class="col-md-2 mb-3">
                    <label for="fecha_ingreso" class="form-label">
                        <i class="bi bi-calendar-check"></i> Fecha de Ingreso *
                    </label>
                    <input type="text" 
                           class="form-control formato-fecha @error('fecha_ingreso') is-invalid @enderror" 
                           id="fecha_ingreso" 
                           name="fecha_ingreso" 
                           value="{{ old('fecha_ingreso', $trabajador->fecha_ingreso ? $trabajador->fecha_ingreso->format('d/m/Y') : '') }}" 
                           placeholder="DD/MM/YYYY"
                           maxlength="10"
                           autocomplete="off"
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

        {{-- ✅ SEPARADOR VISUAL --}}
        <hr class="my-4">

        {{-- ✅ SECCIÓN DE CONTACTO DE EMERGENCIA --}}
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-secondary">
                    <i class="bi bi-exclamation-triangle"></i> Contacto de Emergencia
                </h5>
                @if(isset($contactoEmergencia) && $contactoEmergencia)
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Registrado
                    </span>
                @else
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-exclamation-circle"></i> Sin contacto registrado
                    </span>
                @endif
            </div>

            <form action="{{ route('trabajadores.perfil.update-contacto', $trabajador) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Nombre Completo del Contacto -->
                    <div class="col-md-4 mb-3">
                        <label for="contacto_nombre_completo" class="form-label">
                            <i class="bi bi-person-fill"></i> Nombre Completo
                        </label>
                        <input type="text" 
                               class="form-control @error('contacto_nombre_completo') is-invalid @enderror" 
                               id="contacto_nombre_completo" 
                               name="contacto_nombre_completo" 
                               value="{{ old('contacto_nombre_completo', $contactoEmergencia->nombre_completo ?? '') }}"
                               placeholder="Nombre completo del contacto"
                               maxlength="150">
                        <div class="form-text">Opcional - Persona a contactar en caso de emergencia</div>
                        @error('contacto_nombre_completo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Parentesco -->
                    <div class="col-md-2 mb-3">
                        <label for="contacto_parentesco" class="form-label">
                            <i class="bi bi-diagram-3"></i> Parentesco
                        </label>
                        <input type="text" 
                               class="form-control @error('contacto_parentesco') is-invalid @enderror" 
                               id="contacto_parentesco" 
                               name="contacto_parentesco" 
                               value="{{ old('contacto_parentesco', $contactoEmergencia->parentesco ?? '') }}"
                               placeholder="Ej: Esposo(a), Padre, Madre"
                               maxlength="50">
                        @error('contacto_parentesco')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfono Principal -->
                    <div class="col-md-3 mb-3">
                        <label for="contacto_telefono_principal" class="form-label">
                            <i class="bi bi-telephone-fill"></i> Teléfono Principal
                        </label>
                        <input type="tel" 
                               class="form-control @error('contacto_telefono_principal') is-invalid @enderror" 
                               id="contacto_telefono_principal" 
                               name="contacto_telefono_principal" 
                               value="{{ old('contacto_telefono_principal', $contactoEmergencia->telefono_principal ?? '') }}"
                               placeholder="10 dígitos"
                               maxlength="10">
                        @error('contacto_telefono_principal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfono Secundario -->
                    <div class="col-md-3 mb-3">
                        <label for="contacto_telefono_secundario" class="form-label">
                            <i class="bi bi-telephone"></i> Teléfono Secundario
                        </label>
                        <input type="tel" 
                               class="form-control @error('contacto_telefono_secundario') is-invalid @enderror" 
                               id="contacto_telefono_secundario" 
                               name="contacto_telefono_secundario" 
                               value="{{ old('contacto_telefono_secundario', $contactoEmergencia->telefono_secundario ?? '') }}"
                               placeholder="10 dígitos (opcional)"
                               maxlength="10">
                        @error('contacto_telefono_secundario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <!-- Dirección del Contacto -->
                    <div class="col-md-12 mb-3">
                        <label for="contacto_direccion" class="form-label">
                            <i class="bi bi-house"></i> Dirección del Contacto
                        </label>
                        <textarea class="form-control @error('contacto_direccion') is-invalid @enderror" 
                                  id="contacto_direccion" 
                                  name="contacto_direccion" 
                                  rows="2"
                                  placeholder="Dirección completa del contacto de emergencia (opcional)"
                                  maxlength="500">{{ old('contacto_direccion', $contactoEmergencia->direccion ?? '') }}</textarea>
                        @error('contacto_direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            El contacto de emergencia es opcional pero recomendado
                        </small>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-save"></i> Guardar Contacto de Emergencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>