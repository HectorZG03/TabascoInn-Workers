<!-- ✅ SECCIÓN: CONTACTO DE EMERGENCIA -->
<div class="card shadow mb-4">
    <div class="card-header bg-warning text-dark">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-person-hearts"></i> Contacto de Emergencia
            </h5>
            <small class="text-muted">Opcional - Se puede agregar después</small>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Nombre Completo -->
            <div class="col-md-6 mb-3">
                <label for="contacto_nombre_completo" class="form-label">
                    <i class="bi bi-person"></i> Nombre Completo
                </label>
                <input type="text" 
                    class="form-control @error('contacto_nombre_completo') is-invalid @enderror" 
                    id="contacto_nombre_completo" 
                    name="contacto_nombre_completo" 
                    value="{{ old('contacto_nombre_completo') }}" 
                    placeholder="Nombre completo del contacto"
                    maxlength="150">
                <div class="form-text">Nombre y apellidos completos</div>
                @error('contacto_nombre_completo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Parentesco -->
            <div class="col-md-6 mb-3">
                <label for="contacto_parentesco" class="form-label">
                    <i class="bi bi-people"></i> Parentesco
                </label>
                <select class="form-select @error('contacto_parentesco') is-invalid @enderror" 
                        id="contacto_parentesco" 
                        name="contacto_parentesco">
                    <option value="">Seleccionar...</option>
                    <option value="Padre" {{ old('contacto_parentesco') == 'Padre' ? 'selected' : '' }}>Padre</option>
                    <option value="Madre" {{ old('contacto_parentesco') == 'Madre' ? 'selected' : '' }}>Madre</option>
                    <option value="Esposo/a" {{ old('contacto_parentesco') == 'Esposo/a' ? 'selected' : '' }}>Esposo/a</option>
                    <option value="Hijo/a" {{ old('contacto_parentesco') == 'Hijo/a' ? 'selected' : '' }}>Hijo/a</option>
                    <option value="Hermano/a" {{ old('contacto_parentesco') == 'Hermano/a' ? 'selected' : '' }}>Hermano/a</option>
                    <option value="Abuelo/a" {{ old('contacto_parentesco') == 'Abuelo/a' ? 'selected' : '' }}>Abuelo/a</option>
                    <option value="Tío/a" {{ old('contacto_parentesco') == 'Tío/a' ? 'selected' : '' }}>Tío/a</option>
                    <option value="Primo/a" {{ old('contacto_parentesco') == 'Primo/a' ? 'selected' : '' }}>Primo/a</option>
                    <option value="Amigo/a" {{ old('contacto_parentesco') == 'Amigo/a' ? 'selected' : '' }}>Amigo/a</option>
                    <option value="Otro" {{ old('contacto_parentesco') == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('contacto_parentesco')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <!-- Teléfono Principal -->
            <div class="col-md-6 mb-3">
                <label for="contacto_telefono_principal" class="form-label">
                    <i class="bi bi-telephone"></i> Teléfono Principal
                </label>
                <input type="tel" 
                    class="form-control @error('contacto_telefono_principal') is-invalid @enderror" 
                    id="contacto_telefono_principal" 
                    name="contacto_telefono_principal" 
                    value="{{ old('contacto_telefono_principal') }}" 
                    placeholder="9931234567"
                    maxlength="10"
                    pattern="[0-9]{10}">
                @error('contacto_telefono_principal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Teléfono Secundario -->
            <div class="col-md-6 mb-3">
                <label for="contacto_telefono_secundario" class="form-label">
                    <i class="bi bi-telephone-plus"></i> Teléfono Secundario
                </label>
                <input type="tel" 
                    class="form-control @error('contacto_telefono_secundario') is-invalid @enderror" 
                    id="contacto_telefono_secundario" 
                    name="contacto_telefono_secundario" 
                    value="{{ old('contacto_telefono_secundario') }}" 
                    placeholder="9931234567"
                    maxlength="10"
                    pattern="[0-9]{10}">
                <div class="form-text">Opcional</div>
                @error('contacto_telefono_secundario')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <!-- Dirección del Contacto -->
            <div class="col-md-12 mb-3">
                <label for="contacto_direccion" class="form-label">
                    <i class="bi bi-geo-alt"></i> Dirección del Contacto
                </label>
                <textarea class="form-control @error('contacto_direccion') is-invalid @enderror" 
                        id="contacto_direccion" 
                        name="contacto_direccion" 
                        rows="2" 
                        placeholder="Dirección completa del contacto de emergencia">{{ old('contacto_direccion') }}</textarea>
                <div class="form-text">Opcional - Dirección donde se puede localizar al contacto</div>
                @error('contacto_direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Nota informativa -->
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <div>
                <strong>Información:</strong> El contacto de emergencia es opcional durante la creación. 
                Puede agregarse o modificarse posteriormente desde el perfil del trabajador.
            </div>
        </div>
    </div>
</div>