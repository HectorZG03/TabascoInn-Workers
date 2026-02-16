@extends('layouts.app')

@section('title', 'Días Festivos - Hotel TABASCO INN')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header py-3" style="background-color: #dc3545;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white">
                            <i class="bi bi-calendar-event-fill"></i> Gestión de Días Festivos
                        </h3>
                        <a href="{{ route('users.config') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver a Configuración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Año y Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Seleccionar Año:</label>
                            <select class="form-select" id="selectorAño" onchange="cambiarAño(this.value)">
                                @foreach($añosDisponibles as $año)
                                    <option value="{{ $año }}" {{ $año == $añoActual ? 'selected' : '' }}>
                                        {{ $año }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-0 text-primary">{{ $estadisticas['total_dias'] }}</div>
                                    <small class="text-muted">Total Días</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-success">{{ $estadisticas['dias_oficiales'] }}</div>
                                    <small class="text-muted">Oficiales</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-info">{{ $estadisticas['dias_adicionales'] }}</div>
                                    <small class="text-muted">Adicionales</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">Acciones Rápidas</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarDia">
                            <i class="bi bi-plus-lg"></i> Agregar Día Festivo
                        </button>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarOficiales">
                            <i class="bi bi-magic"></i> Generar Días Oficiales
                        </button>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalCopiarAño">
                            <i class="bi bi-files"></i> Copiar de Otro Año
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Días Festivos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar3"></i> Días Festivos del {{ $añoActual }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($diasFestivos->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle fs-1 mb-3"></i>
                            <h5>No hay días festivos registrados para el año {{ $añoActual }}</h5>
                            <p>Puede generar los días oficiales o agregarlos manualmente.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="120">Fecha</th>
                                        <th width="100">Día</th>
                                        <th>Nombre</th>
                                        <th width="100">Tipo</th>
                                        <th>Observaciones</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($diasFestivos as $dia)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $dia->fecha_formateada }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $dia->dia_semana }}</span>
                                            </td>
                                            <td>
                                                <i class="bi bi-calendar-heart text-danger me-2"></i>
                                                {{ $dia->nombre }}
                                            </td>
                                            <td>
                                                @if($dia->es_oficial)
                                                    <span class="badge bg-success">Oficial</span>
                                                @else
                                                    <span class="badge bg-info">Adicional</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $dia->observaciones ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editarDiaFestivo({{ $dia->id }}, '{{ $dia->fecha_formateada }}', '{{ $dia->nombre }}', {{ $dia->es_oficial ? 'true' : 'false' }}, '{{ $dia->observaciones }}')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('configuracion.dias_festivos.destroy', $dia) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('¿Eliminar este día festivo?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Día Festivo - ✅ ACTUALIZADO: Campo de fecha con formato global -->
<div class="modal fade" id="modalAgregarDia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('configuracion.dias_festivos.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus"></i> Agregar Día Festivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="año" value="{{ $añoActual }}">
                    
                    <!-- ✅ ACTUALIZADO: Campo de fecha con formato global DD/MM/YYYY -->
                    <div class="mb-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="fecha" 
                               class="form-control formato-fecha" 
                               required 
                               placeholder="DD/MM/YYYY"
                               maxlength="10">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> 
                            Ingrese la fecha en formato DD/MM/YYYY. El año debe ser {{ $añoActual }}.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Día Festivo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required maxlength="100"
                               placeholder="Ej: Día de la Independencia">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="es_oficial" value="1" checked>
                            <label class="form-check-label">Día Festivo Oficial</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Día Festivo - ✅ ACTUALIZADO: Campo de fecha con formato global -->
<div class="modal fade" id="modalEditarDia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditarDia" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i> Editar Día Festivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- ✅ ACTUALIZADO: Campo de fecha con formato global DD/MM/YYYY -->
                    <div class="mb-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="editFecha" 
                               name="fecha" 
                               class="form-control formato-fecha" 
                               required 
                               placeholder="DD/MM/YYYY"
                               maxlength="10">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> 
                            Puede editar la fecha. El año debe coincidir con el año del día festivo.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Día Festivo <span class="text-danger">*</span></label>
                        <input type="text" id="editNombre" name="nombre" class="form-control" required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editEsOficial" name="es_oficial" value="1">
                            <label class="form-check-label">Día Festivo Oficial</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea id="editObservaciones" name="observaciones" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Generar Días Oficiales -->
<div class="modal fade" id="modalGenerarOficiales" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('configuracion.dias_festivos.generar') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-magic"></i> Generar Días Festivos Oficiales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="año" value="{{ $añoActual }}">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Se generarán los siguientes días festivos oficiales para {{ $añoActual }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Año Nuevo (1 de enero)</li>
                            <li>Día de la Constitución (primer lunes de febrero)</li>
                            <li>Natalicio de Benito Juárez (tercer lunes de marzo)</li>
                            <li>Día del Trabajo (1 de mayo)</li>
                            <li>Día de la Independencia (16 de septiembre)</li>
                            <li>Día de la Revolución (tercer lunes de noviembre)</li>
                            <li>Navidad (25 de diciembre)</li>
                            @if(($añoActual - 2024) % 6 === 0 && $añoActual >= 2024)
                                <li><strong>Transmisión del Poder Ejecutivo (1 de octubre)</strong> ✅</li>
                            @endif
                        </ul>
                        @if(($añoActual - 2024) % 6 === 0 && $añoActual >= 2024)
                            <div class="mt-2 p-2 bg-warning bg-opacity-25 rounded">
                                <small><i class="bi bi-calendar-check"></i> <strong>Año de transmisión presidencial:</strong> Se incluirá el 1 de octubre como día festivo oficial.</small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Generar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Copiar Año -->
<div class="modal fade" id="modalCopiarAño" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('configuracion.dias_festivos.copiar') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-files"></i> Copiar Días Festivos de Otro Año
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Año de Origen <span class="text-danger">*</span></label>
                        <select name="año_origen" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($añosDisponibles as $año)
                                @if($año != $añoActual)
                                    <option value="{{ $año }}">{{ $año }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Año de Destino</label>
                        <input type="number" name="año_destino" class="form-control" 
                               value="{{ $añoActual }}" readonly>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Los días festivos se ajustarán automáticamente al año destino.
                        No se copiarán días que ya existan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-check-lg"></i> Copiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{asset('js/app-routes.js')}}"></script>
<script src="{{asset('js/formato_global.js')}}"></script>


<script>
// ✅ FUNCIÓN CORREGIDA: Usar AppRoutes para construir URLs correctamente
function cambiarAño(año) {
    const url = AppRoutes.configuracion('dias-festivos') + '?año=' + año;
    window.location.href = url;
}

// ✅ FUNCIÓN CORREGIDA: Usar fecha en formato DD/MM/YYYY y AppRoutes
function editarDiaFestivo(id, fechaFormateada, nombre, esOficial, observaciones) {
    // ✅ Establecer la fecha en formato DD/MM/YYYY (ya viene formateada desde el backend)
    document.getElementById('editFecha').value = fechaFormateada;
    
    // Establecer el nombre
    document.getElementById('editNombre').value = nombre;
    
    // Establecer si es oficial
    document.getElementById('editEsOficial').checked = esOficial;
    
    // Establecer observaciones
    document.getElementById('editObservaciones').value = observaciones || '';
    
    // ✅ CORREGIDO: Usar AppRoutes para construir la URL correctamente
    const form = document.getElementById('formEditarDia');
    form.action = AppRoutes.configuracion('dias-festivos/' + id);
    
    // Mostrar el modal
    new bootstrap.Modal(document.getElementById('modalEditarDia')).show();
}

// ✅ NUEVO: Validación adicional para el año en fechas
document.addEventListener('DOMContentLoaded', function() {
    // Validación personalizada para fechas que deben coincidir con el año actual
    const camposFecha = document.querySelectorAll('#modalAgregarDia .formato-fecha, #modalEditarDia .formato-fecha');
    
    camposFecha.forEach(campo => {
        campo.addEventListener('blur', function() {
            const fecha = this.value.trim();
            if (fecha && FormatoGlobal.validarFormatoFecha(fecha)) {
                const [dia, mes, año] = fecha.split('/').map(Number);
                const añoActual = {{ $añoActual }};
                
                if (año !== añoActual) {
                    FormatoGlobal.mostrarError(this, `El año debe ser ${añoActual}`);
                    return false;
                }
            }
        });
    });
    
    // ✅ Inicializar tooltips si existen
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection