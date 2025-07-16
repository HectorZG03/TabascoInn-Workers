<!-- ✅ SECCIÓN: DATOS PERSONALES -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-person-circle"></i> Datos Personales
        </h5>
    </div>
    <div class="card-body">
        <!-- Nombres y Apellidos -->
        <div class="row">
            <!-- Nombre -->
            <div class="col-md-4 mb-3">
                <label for="nombre_trabajador" class="form-label">
                    <i class="bi bi-person"></i> Nombre(s) *
                </label>
                <input type="text" 
                       class="form-control @error('nombre_trabajador') is-invalid @enderror" 
                       id="nombre_trabajador" 
                       style="text-transform: uppercase"
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
                       style="text-transform: uppercase"
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
                       style="text-transform: uppercase"
                       name="ape_mat" 
                       value="{{ old('ape_mat') }}" 
                       placeholder="Apellido materno">
                @error('ape_mat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Fecha de Nacimiento y Documentos -->
        <div class="row">
            <!-- Fecha de Nacimiento -->
            <div class="col-md-4 mb-3">
                <label for="fecha_nacimiento" class="form-label">
                    <i class="bi bi-calendar"></i> Fecha de Nacimiento *
                </label>
                <input type="text" 
                       class="form-control formato-fecha @error('fecha_nacimiento') is-invalid @enderror" 
                       id="fecha_nacimiento" 
                       name="fecha_nacimiento" 
                       value="{{ old('fecha_nacimiento') }}" 
                       placeholder="DD/MM/YYYY"
                       maxlength="10"
                       required>
                <div class="form-text">Formato: DD/MM/YYYY (debe ser mayor de 18 años)</div>
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
                       style="text-transform: uppercase "
                       name="curp" 
                       value="{{ old('curp') }}" 
                       placeholder="18 caracteres"
                       maxlength="18"
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
                       style="text-transform: uppercase"
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

        <!-- NSS, Teléfono y Correo -->
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

        <!-- Dirección Actual, Lugar de Nacimiento y Fecha de Ingreso -->
        <div class="row">
            <!-- Dirección Actual -->
            <div class="col-md-4 mb-3">
                <label for="direccion" class="form-label">
                    <i class="bi bi-geo-alt-fill"></i> Dirección Actual
                </label>
                <input type="text" 
                       class="form-control @error('direccion') is-invalid @enderror" 
                       id="direccion" 
                       style="text-transform: uppercase"
                       name="direccion" 
                       value="{{ old('direccion') }}" 
                       placeholder="Calle, número, colonia">
                <div class="form-text">Dirección donde vive actualmente</div>
                @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Lugar de Nacimiento -->
            <div class="col-md-4 mb-3">
                <label for="lugar_nacimiento" class="form-label">
                    <i class="bi bi-geo"></i> Lugar de Nacimiento
                </label>
                <input type="text" 
                       class="form-control @error('lugar_nacimiento') is-invalid @enderror" 
                       id="lugar_nacimiento" 
                       style="text-transform: uppercase"
                       name="lugar_nacimiento" 
                       value="{{ old('lugar_nacimiento') }}" 
                       placeholder="Ciudad, Estado"
                       maxlength="100">
                <div class="form-text">Ejemplo: Villahermosa, Tabasco</div>
                @error('lugar_nacimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
        <!-- Fecha de Ingreso -->
            <div class="col-md-4 mb-3">
                <label for="fecha_ingreso" class="form-label">
                    <i class="bi bi-calendar-check"></i> Fecha de Ingreso *
                </label>
                <input type="text" 
                    class="form-control formato-fecha @error('fecha_ingreso') is-invalid @enderror" 
                    id="fecha_ingreso" 
                    name="fecha_ingreso" 
                    value="{{ old('fecha_ingreso') }}" 
                    placeholder="DD/MM/YYYY"
                    maxlength="10"
                    required>
                <div class="form-text">Formato: DD/MM/YYYY (fecha real de ingreso del trabajador)</div>
                @error('fecha_ingreso')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Estado y Ciudad Actual -->
        <div class="row">
            <!-- Estado Actual -->
            <div class="col-md-6 mb-3">
                <label for="estado_actual" class="form-label">
                    <i class="bi bi-map"></i> Estado Actual
                </label>
                <input type="text" 
                    class="form-control @error('estado_actual') is-invalid @enderror" 
                    id="estado_actual" 
                    name="estado_actual"
                    style="text-transform: uppercase"
                    value="{{ old('estado_actual') }}" 
                    placeholder="Estado donde vive actualmente"
                    maxlength="50">
                <div class="form-text">Estado donde vive actualmente</div>
                @error('estado_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Ciudad Actual -->
            <div class="col-md-6 mb-3">
                <label for="ciudad_actual" class="form-label">
                    <i class="bi bi-building"></i> Ciudad Actual
                </label>
                <input type="text" 
                       class="form-control @error('ciudad_actual') is-invalid @enderror" 
                       id="ciudad_actual" 
                       name="ciudad_actual" 
                       style="text-transform: uppercase"
                       value="{{ old('ciudad_actual') }}" 
                       placeholder="Ciudad donde vive"
                       maxlength="50">
                <div class="form-text">Ciudad donde reside actualmente</div>
                @error('ciudad_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>