<!-- ✅ SECCIÓN: DATOS LABORALES -->
<div class="card shadow mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-briefcase-fill"></i> Datos Laborales
        </h5>
    </div>
    <div class="card-body">
        <!-- Área, Categoría y Sueldo -->
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

        <!-- Horarios: Hora de Entrada y Salida -->
        <div class="row">
            <!-- Hora de Entrada -->
            <div class="col-md-6 mb-3">
                <label for="hora_entrada" class="form-label">
                    <i class="bi bi-clock"></i> Hora de Entrada *
                </label>
                <input type="time" 
                       class="form-control @error('hora_entrada') is-invalid @enderror" 
                       id="hora_entrada" 
                       name="hora_entrada" 
                       value="{{ old('hora_entrada') }}" 
                       required>
                <div class="form-text">Hora de inicio de la jornada laboral</div>
                @error('hora_entrada')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Hora de Salida -->
            <div class="col-md-6 mb-3">
                <label for="hora_salida" class="form-label">
                    <i class="bi bi-clock-fill"></i> Hora de Salida *
                </label>
                <input type="time" 
                       class="form-control @error('hora_salida') is-invalid @enderror" 
                       id="hora_salida" 
                       name="hora_salida" 
                       value="{{ old('hora_salida') }}" 
                       required>
                <div class="form-text">Hora de fin de la jornada laboral</div>
                @error('hora_salida')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Formación, Estudios y Estado -->
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

            <!-- Estado Inicial -->
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