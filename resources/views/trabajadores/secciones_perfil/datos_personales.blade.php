{{-- resources/views/trabajadores/tabs/datos_personales.blade.php --}}

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

                <!-- ✅ NUEVO: Lugar de Nacimiento -->
                <div class="col-md-4 mb-3">
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
            </div>

            <div class="row">
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
            </div>

            <div class="row">
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

                <!-- ✅ NUEVO: Estado Actual -->
                <div class="col-md-4 mb-3">
                    <label for="estado_actual" class="form-label">
                        <i class="bi bi-map"></i> Estado Actual
                    </label>
                    <select class="form-select @error('estado_actual') is-invalid @enderror" 
                            id="estado_actual" 
                            name="estado_actual">
                        <option value="">Seleccionar estado...</option>
                        @php
                            $estados = \App\Models\Trabajador::ESTADOS_MEXICO ?? [
                                'Aguascalientes' => 'Aguascalientes',
                                'Baja California' => 'Baja California',
                                'Baja California Sur' => 'Baja California Sur',
                                'Campeche' => 'Campeche',
                                'Chiapas' => 'Chiapas',
                                'Chihuahua' => 'Chihuahua',
                                'Ciudad de México' => 'Ciudad de México',
                                'Coahuila' => 'Coahuila',
                                'Colima' => 'Colima',
                                'Durango' => 'Durango',
                                'Estado de México' => 'Estado de México',
                                'Guanajuato' => 'Guanajuato',
                                'Guerrero' => 'Guerrero',
                                'Hidalgo' => 'Hidalgo',
                                'Jalisco' => 'Jalisco',
                                'Michoacán' => 'Michoacán',
                                'Morelos' => 'Morelos',
                                'Nayarit' => 'Nayarit',
                                'Nuevo León' => 'Nuevo León',
                                'Oaxaca' => 'Oaxaca',
                                'Puebla' => 'Puebla',
                                'Querétaro' => 'Querétaro',
                                'Quintana Roo' => 'Quintana Roo',
                                'San Luis Potosí' => 'San Luis Potosí',
                                'Sinaloa' => 'Sinaloa',
                                'Sonora' => 'Sonora',
                                'Tabasco' => 'Tabasco',
                                'Tamaulipas' => 'Tamaulipas',
                                'Tlaxcala' => 'Tlaxcala',
                                'Veracruz' => 'Veracruz',
                                'Yucatán' => 'Yucatán',
                                'Zacatecas' => 'Zacatecas',
                            ];
                        @endphp
                        @foreach($estados as $clave => $nombre)
                            <option value="{{ $clave }}" 
                                    {{ old('estado_actual', $trabajador->estado_actual) == $clave ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- ✅ NUEVO: Ciudad Actual -->
                <div class="col-md-4 mb-3">
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
            </div>

            <div class="row">
                <!-- Dirección -->
                <div class="col-md-8 mb-3">
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