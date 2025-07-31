{{-- resources/views/trabajadores/secciones_perfil/horas_extra.blade.php --}}

<div class="row">
    {{-- ✅ RESUMEN DE HORAS EXTRA ACTUALIZADO PARA DECIMALES --}}
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-clock-fill text-warning"></i> Resumen de Horas Extra
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <!-- Horas Acumuladas -->
                    <div class="col-md-3">
                        <div class="border-end">
                            @php
                                $totalAcumuladas = $stats_horas['total_acumuladas'];
                                $formatoAcumuladas = $totalAcumuladas == floor($totalAcumuladas) ? 
                                    number_format($totalAcumuladas, 0) : 
                                    number_format($totalAcumuladas, 1);
                            @endphp
                            <div class="h3 text-success mb-1">{{ $formatoAcumuladas }}</div>
                            <div class="text-muted">{{ $totalAcumuladas == 1 ? 'Hora Acumulada' : 'Horas Acumuladas' }}</div>
                        </div>
                    </div>
                    
                    <!-- Horas Compensadas -->
                    <div class="col-md-3">
                        <div class="border-end">
                            @php
                                $totalDevueltas = $stats_horas['total_devueltas'];
                                $formatoDevueltas = $totalDevueltas == floor($totalDevueltas) ? 
                                    number_format($totalDevueltas, 0) : 
                                    number_format($totalDevueltas, 1);
                            @endphp
                            <div class="h3 text-warning mb-1">{{ $formatoDevueltas }}</div>
                            <div class="text-muted">{{ $totalDevueltas == 1 ? 'Hora Compensada' : 'Horas Compensadas' }}</div>
                        </div>
                    </div>
                    
                    <!-- Saldo Actual -->
                    <div class="col-md-3">
                        <div class="border-end">
                            @php
                                $saldoActual = $trabajador->saldo_horas_extra;
                                $formatoSaldo = $saldoActual == floor($saldoActual) ? 
                                    number_format($saldoActual, 0) : 
                                    number_format($saldoActual, 1);
                            @endphp
                            <div class="h3 text-primary mb-1">{{ $formatoSaldo }}</div>
                            <div class="text-muted">{{ $saldoActual == 1 ? 'Hora Disponible' : 'Horas Disponibles' }}</div>
                        </div>
                    </div>
                    
                    <!-- Total de Registros -->
                    <div class="col-md-3">
                        <div class="h3 text-info mb-1">{{ $stats_horas['total_registros'] }}</div>
                        <div class="text-muted">{{ $stats_horas['total_registros'] == 1 ? 'Registro Total' : 'Registros Totales' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ ACCIONES RÁPIDAS --}}
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-fill text-primary"></i> Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        @if(!$trabajador->estaSuspendido() && !$trabajador->estaInactivo())
                            <button type="button" 
                                    class="btn btn-success w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalAsignarHoras{{ $trabajador->id_trabajador }}">
                                <i class="bi bi-plus-circle"></i> Asignar Horas Extra
                                <br><small class="opacity-75">Admite decimales (ej: 1.5 hrs)</small>
                            </button>
                        @else
                            <button type="button" class="btn btn-success w-100 disabled" disabled>
                                <i class="bi bi-plus-circle"></i> Asignar Horas Extra
                                <br><small>(Trabajador {{ $trabajador->estatus_texto }})</small>
                            </button>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if(!$trabajador->estaSuspendido() && !$trabajador->estaInactivo() && $trabajador->saldo_horas_extra > 0)
                            <button type="button" 
                                    class="btn btn-warning w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalRestarHoras{{ $trabajador->id_trabajador }}">
                                <i class="bi bi-dash-circle"></i> Compensar Horas Extra
                                <br><small class="opacity-75">Admite decimales (ej: 0.5 hrs)</small>
                            </button>
                        @else
                            <button type="button" class="btn btn-warning w-100 disabled" disabled>
                                <i class="bi bi-dash-circle"></i> Compensar Horas Extra
                                <br><small>
                                    @if($trabajador->estaSuspendido() || $trabajador->estaInactivo())
                                        (Trabajador {{ $trabajador->estatus_texto }})
                                    @else
                                        (Sin horas disponibles)
                                    @endif
                                </small>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ HISTORIAL DE HORAS EXTRA --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0">
                            <i class="bi bi-list-ul"></i> Historial de Horas Extra
                        </h6>
                    </div>
                    <div class="col-md-6">
                        {{-- ✅ FILTROS SIMPLES --}}
                        <div class="d-flex gap-2 justify-content-end">
                            <select class="form-select form-select-sm" id="filtroTipo" style="width: auto;">
                                <option value="">Todos los tipos</option>
                                <option value="acumuladas">Solo Acumuladas</option>
                                <option value="devueltas">Solo Compensadas</option>
                            </select>
                            <select class="form-select form-select-sm" id="filtroPeriodo" style="width: auto;">
                                <option value="">Todo el historial</option>
                                <option value="7">Últimos 7 días</option>
                                <option value="30">Últimos 30 días</option>
                                <option value="90">Últimos 3 meses</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($historial_horas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaHistorial">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Horas</th>
                                    <th>Descripción</th>
                                    <th>Autorizado por</th>
                                    <th>Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial_horas as $registro)
                                    <tr data-tipo="{{ $registro->tipo }}" data-fecha="{{ $registro->fecha->format('Y-m-d') }}">
                                        <td>
                                            <div class="fw-medium">{{ $registro->fecha_formateada }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $registro->color_tipo }} fs-6">
                                                <i class="{{ $registro->icono_tipo }}"></i>
                                                {{ $registro->tipo_texto }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-{{ $registro->color_tipo }}">
                                                {{ $registro->horas_formateadas }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted">
                                                {{ $registro->descripcion ?? 'Sin descripción' }}
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $registro->autorizado_por }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $registro->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ INFORMACIÓN ADICIONAL ACTUALIZADA --}}
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Mostrando {{ $historial_horas->count() }} registros.
                                    @if($stats_horas['ultimo_registro'])
                                        Último movimiento: {{ $stats_horas['ultimo_registro']->format('d/m/Y') }}
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    Balance neto: 
                                    <span class="fw-bold text-{{ $trabajador->saldo_horas_extra > 0 ? 'success' : 'secondary' }}">
                                        {{ $trabajador->saldo_horas_extra == floor($trabajador->saldo_horas_extra) ? 
                                            number_format($trabajador->saldo_horas_extra, 0) : 
                                            number_format($trabajador->saldo_horas_extra, 1) }} 
                                        {{ $trabajador->saldo_horas_extra == 1 ? 'hora' : 'horas' }}
                                    </span>
                                </small>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- ✅ ESTADO VACÍO ACTUALIZADO --}}
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-clock text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">No hay registros de horas extra</h5>
                        <p class="text-muted mb-4">
                            Este trabajador aún no tiene horas extra registradas.<br>
                            <small class="opacity-75">Admite decimales para mayor precisión (ej: 1.5, 2.25 horas)</small>
                        </p>
                        @if(!$trabajador->estaSuspendido() && !$trabajador->estaInactivo())
                            <button type="button" 
                                    class="btn btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalAsignarHoras{{ $trabajador->id_trabajador }}">
                                <i class="bi bi-plus-circle"></i> Registrar Primera Hora Extra
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT PARA FILTROS (SIN CAMBIOS) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtroTipo = document.getElementById('filtroTipo');
    const filtroPeriodo = document.getElementById('filtroPeriodo');
    const tablaHistorial = document.getElementById('tablaHistorial');
    
    if (!tablaHistorial) return; // No hay tabla si no hay registros
    
    const filas = tablaHistorial.querySelectorAll('tbody tr');
    
    function aplicarFiltros() {
        const tipoSeleccionado = filtroTipo.value;
        const periodoSeleccionado = filtroPeriodo.value;
        const fechaLimite = periodoSeleccionado ? 
            new Date(Date.now() - (parseInt(periodoSeleccionado) * 24 * 60 * 60 * 1000)) : null;
        
        filas.forEach(fila => {
            let mostrar = true;
            
            // Filtro por tipo
            if (tipoSeleccionado && fila.dataset.tipo !== tipoSeleccionado) {
                mostrar = false;
            }
            
            // Filtro por período
            if (fechaLimite && mostrar) {
                const fechaRegistro = new Date(fila.dataset.fecha);
                if (fechaRegistro < fechaLimite) {
                    mostrar = false;
                }
            }
            
            fila.style.display = mostrar ? '' : 'none';
        });
        
        // Actualizar contador
        const filasVisibles = Array.from(filas).filter(f => f.style.display !== 'none').length;
        const infoElement = document.querySelector('.card-footer small');
        if (infoElement) {
            infoElement.innerHTML = `<i class="bi bi-info-circle"></i> Mostrando ${filasVisibles} de ${filas.length} registros.`;
        }
    }
    
    if (filtroTipo) {
        filtroTipo.addEventListener('change', aplicarFiltros);
    }
    
    if (filtroPeriodo) {
        filtroPeriodo.addEventListener('change', aplicarFiltros);
    }
    
    console.log('✅ Sección horas extra inicializada correctamente (con soporte para decimales)');
});
</script>