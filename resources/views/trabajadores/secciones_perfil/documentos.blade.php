{{-- resources/views/trabajadores/tabs/documentos.blade.php --}}

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