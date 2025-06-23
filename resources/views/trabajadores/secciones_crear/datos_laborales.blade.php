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

        <!-- ✅ NUEVA SECCIÓN: Días Laborables -->
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">
                    <i class="bi bi-calendar-week"></i> Días Laborables *
                </label>
                <div class="border rounded p-3 bg-light">
                    <div class="row">
                        @foreach(\App\Models\FichaTecnica::DIAS_SEMANA as $valor => $texto)
                            <div class="col-md-3 col-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="dia_{{ $valor }}" 
                                           name="dias_laborables[]" 
                                           value="{{ $valor }}"
                                           {{ in_array($valor, old('dias_laborables', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dia_{{ $valor }}">
                                        {{ $texto }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Botones de selección rápida -->
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted">Selección rápida:</small>
                        <div class="btn-group btn-group-sm ms-2" role="group">
                            <button type="button" class="btn btn-outline-primary" id="btn-lunes-viernes">
                                Lun-Vie
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-lunes-sabado">
                                Lun-Sáb
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-todos-dias">
                                Todos
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-dias">
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>
                @error('dias_laborables')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('dias_laborables.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- ✅ NUEVA SECCIÓN: Beneficiario Principal -->
        <div class="row">
            <div class="col-12">
                <div class="card border-info mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-heart"></i> Beneficiario Principal (Para Contrato)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Nombre del Beneficiario -->
                            <div class="col-md-6 mb-3">
                                <label for="beneficiario_nombre" class="form-label">
                                    <i class="bi bi-person"></i> Nombre Completo
                                </label>
                                <input type="text" 
                                       class="form-control @error('beneficiario_nombre') is-invalid @enderror" 
                                       id="beneficiario_nombre" 
                                       name="beneficiario_nombre" 
                                       value="{{ old('beneficiario_nombre') }}" 
                                       placeholder="Nombre del beneficiario"
                                       maxlength="150">
                                <div class="form-text">Persona que aparecerá en el contrato</div>
                                @error('beneficiario_nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Parentesco -->
                            <div class="col-md-6 mb-3">
                                <label for="beneficiario_parentesco" class="form-label">
                                    <i class="bi bi-people"></i> Parentesco
                                </label>
                                <select class="form-select @error('beneficiario_parentesco') is-invalid @enderror" 
                                        id="beneficiario_parentesco" 
                                        name="beneficiario_parentesco">
                                    <option value="">Seleccionar parentesco...</option>
                                    @foreach(\App\Models\FichaTecnica::PARENTESCOS_BENEFICIARIO as $valor => $texto)
                                        <option value="{{ $valor }}" {{ old('beneficiario_parentesco') == $valor ? 'selected' : '' }}>
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('beneficiario_parentesco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nota informativa -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <small>
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Nota:</strong> Este beneficiario aparecerá en el contrato laboral. 
                                        Puedes dejarlo vacío y completarlo después si es necesario.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formación y Estudios (SIN ESTADO) -->
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
            <div class="col-md-6 mb-3">
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
        </div>

        <!-- ✅ NOTA INFORMATIVA SOBRE EL ESTADO -->
        <div class="alert alert-primary d-flex align-items-center">
            <i class="bi bi-info-circle me-3 fs-4"></i>
            <div>
                <strong>Estado del Trabajador:</strong> El estado inicial se configurará en el siguiente paso junto con el contrato.
            </div>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT ESPECÍFICO PARA DÍAS LABORABLES -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botones de selección rápida de días
    const btnLunesViernes = document.getElementById('btn-lunes-viernes');
    const btnLunesSabado = document.getElementById('btn-lunes-sabado');
    const btnTodosDias = document.getElementById('btn-todos-dias');
    const btnLimpiarDias = document.getElementById('btn-limpiar-dias');
    
    const diasCheckboxes = document.querySelectorAll('input[name="dias_laborables[]"]');
    
    btnLunesViernes.addEventListener('click', function() {
        limpiarDias();
        ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'].forEach(dia => {
            const checkbox = document.getElementById(`dia_${dia}`);
            if (checkbox) checkbox.checked = true;
        });
        calcularResumen();
    });
    
    btnLunesSabado.addEventListener('click', function() {
        limpiarDias();
        ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'].forEach(dia => {
            const checkbox = document.getElementById(`dia_${dia}`);
            if (checkbox) checkbox.checked = true;
        });
        calcularResumen();
    });
    
    btnTodosDias.addEventListener('click', function() {
        diasCheckboxes.forEach(checkbox => checkbox.checked = true);
        calcularResumen();
    });
    
    btnLimpiarDias.addEventListener('click', function() {
        limpiarDias();
        calcularResumen();
    });
    
    function limpiarDias() {
        diasCheckboxes.forEach(checkbox => checkbox.checked = false);
    }
    
    // Calcular resumen automático
    function calcularResumen() {
        const horaEntrada = document.getElementById('hora_entrada').value;
        const horaSalida = document.getElementById('hora_salida').value;
        const diasSeleccionados = Array.from(diasCheckboxes).filter(cb => cb.checked).length;
        
        let horasDiarias = 0;
        let turno = '-';
        
        if (horaEntrada && horaSalida) {
            const entrada = new Date(`1970-01-01T${horaEntrada}:00`);
            let salida = new Date(`1970-01-01T${horaSalida}:00`);
            
            // Si cruza medianoche
            if (salida <= entrada) {
                salida.setDate(salida.getDate() + 1);
            }
            
            horasDiarias = (salida - entrada) / (1000 * 60 * 60);
            
            // Calcular turno
            if (horaEntrada >= '06:00' && horaSalida <= '18:00') {
                turno = 'Diurno';
            } else if (horaEntrada >= '18:00' || horaSalida <= '06:00') {
                turno = 'Nocturno';
            } else {
                turno = 'Mixto';
            }
        }
        
        const horasSemanales = horasDiarias * diasSeleccionados;
        
        // Actualizar vista previa si los elementos existen
        const horasDiariasEl = document.getElementById('horas-diarias');
        const horasSemanalesEl = document.getElementById('horas-semanales');
        const diasLaborablesCountEl = document.getElementById('dias-laborables-count');
        const turnoCalculadoEl = document.getElementById('turno-calculado');
        
        if (horasDiariasEl) horasDiariasEl.textContent = horasDiarias > 0 ? horasDiarias.toFixed(1) : '-';
        if (horasSemanalesEl) horasSemanalesEl.textContent = horasSemanales > 0 ? horasSemanales.toFixed(1) : '-';
        if (diasLaborablesCountEl) diasLaborablesCountEl.textContent = diasSeleccionados || '-';
        if (turnoCalculadoEl) turnoCalculadoEl.textContent = turno;
    }
    
    // Event listeners para recalcular
    document.getElementById('hora_entrada').addEventListener('change', calcularResumen);
    document.getElementById('hora_salida').addEventListener('change', calcularResumen);
    diasCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calcularResumen);
    });
    
    // Calcular inicial
    calcularResumen();
});
</script>