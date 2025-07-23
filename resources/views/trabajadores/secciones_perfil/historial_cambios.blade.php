{{-- resources/views/trabajadores/secciones_perfil/historial_cambios.blade.php --}}

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Historial Completo de Cambios
                    </h5>
                </div>
            </div>

            {{-- ✅ ESTADÍSTICAS GENERALES --}}
            @if(isset($estadisticasHistorial) && $estadisticasHistorial['total_cambios'] > 0)
            <div class="card-body border-bottom bg-light">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="h4 text-primary mb-0">{{ $estadisticasHistorial['total_cambios'] }}</div>
                        <small class="text-muted">Total Cambios</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 text-success mb-0">{{ $estadisticasHistorial['promociones'] }}</div>
                        <small class="text-muted">Promociones</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 text-info mb-0">{{ $estadisticasHistorial['transferencias'] }}</div>
                        <small class="text-muted">Transferencias</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 text-warning mb-0">{{ $estadisticasHistorial['aumentos_sueldo'] }}</div>
                        <small class="text-muted">Aumentos</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 text-secondary mb-0">{{ $estadisticasHistorial['reclasificaciones'] }}</div>
                        <small class="text-muted">Reclasificaciones</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 text-dark mb-0">{{ $estadisticasHistorial['ajustes_salariales'] }}</div>
                        <small class="text-muted">Ajustes</small>
                    </div>
                </div>
            </div>
            @endif

            {{-- ✅ FILTROS BÁSICOS --}}
            <div class="card-body border-bottom">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="filtro-tipo" class="form-label">Filtrar por Tipo</label>
                        <select class="form-select" id="filtro-tipo">
                            <option value="">Todos los tipos</option>
                            <option value="promocion">Promociones</option>
                            <option value="transferencia">Transferencias</option>
                            <option value="aumento_sueldo">Aumentos de Sueldo</option>
                            <option value="reclasificacion">Reclasificaciones</option>
                            <option value="ajuste_salarial">Ajustes Salariales</option>
                            <option value="inicial">Registro Inicial</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro-fecha-desde" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="filtro-fecha-desde">
                    </div>
                    <div class="col-md-3">
                        <label for="filtro-fecha-hasta" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="filtro-fecha-hasta">
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btn-aplicar-filtros">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-filtros">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ CONTENIDO PRINCIPAL --}}
            <div class="card-body p-0">
                @if(isset($historialCompleto) && $historialCompleto->count() > 0)
                    {{-- Tabla responsiva --}}
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tabla-historial">
                            <thead class="table-light">
                                <tr>
                                    <th width="12%">Fecha</th>
                                    <th width="15%">Tipo de Cambio</th>
                                    <th width="20%">Categoría Anterior</th>
                                    <th width="20%">Categoría Nueva</th>
                                    <th width="12%">Sueldo Anterior</th>
                                    <th width="12%">Sueldo Nuevo</th>
                                    <th width="9%">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historialCompleto as $cambio)
                                <tr class="historial-row" 
                                    data-tipo="{{ $cambio->tipo_cambio }}"
                                    data-fecha="{{ $cambio->fecha_cambio->format('Y-m-d') }}">
                                    {{-- Fecha --}}
                                    <td>
                                        <div class="fw-bold">{{ $cambio->fecha_cambio->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $cambio->fecha_cambio->format('H:i') }}</small>
                                    </td>

                                    {{-- Tipo de Cambio --}}
                                    <td>
                                        <span class="badge bg-{{ $cambio->color_tipo_cambio ?? 'secondary' }}">
                                            @if($cambio->tipo_cambio == 'promocion')
                                                <i class="bi bi-arrow-up"></i>
                                            @elseif($cambio->tipo_cambio == 'transferencia')
                                                <i class="bi bi-arrow-left-right"></i>
                                            @elseif($cambio->tipo_cambio == 'aumento_sueldo')
                                                <i class="bi bi-cash"></i>
                                            @elseif($cambio->tipo_cambio == 'inicial')
                                                <i class="bi bi-star"></i>
                                            @else
                                                <i class="bi bi-gear"></i>
                                            @endif
                                            {{ $cambio->tipo_cambio_texto }}
                                        </span>
                                    </td>

                                    {{-- Categoría Anterior --}}
                                    <td>
                                        @if($cambio->categoriaAnterior)
                                            <div class="fw-bold">{{ $cambio->categoriaAnterior->nombre_categoria }}</div>
                                            <small class="text-muted">{{ $cambio->categoriaAnterior->area->nombre_area ?? '' }}</small>
                                        @else
                                            <span class="text-muted fst-italic">Registro inicial</span>
                                        @endif
                                    </td>

                                    {{-- Categoría Nueva --}}
                                    <td>
                                        @if($cambio->categoriaNueva)
                                            <div class="fw-bold">{{ $cambio->categoriaNueva->nombre_categoria }}</div>
                                            <small class="text-muted">{{ $cambio->categoriaNueva->area->nombre_area ?? '' }}</small>
                                        @else
                                            <span class="text-muted">No disponible</span>
                                        @endif
                                    </td>

                                    {{-- Sueldo Anterior --}}
                                    <td>
                                        @if($cambio->sueldo_anterior)
                                            <span class="fw-bold">${{ number_format($cambio->sueldo_anterior, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Sueldo Nuevo --}}
                                    <td>
                                        <span class="fw-bold text-success">${{ number_format($cambio->sueldo_nuevo, 2) }}</span>
                                    </td>

                                    {{-- Diferencia --}}
                                    <td>
                                        @if($cambio->sueldo_anterior && $cambio->diferencia_sueldo != 0)
                                            @php
                                                $diferencia = $cambio->diferencia_sueldo;
                                                $color = $diferencia > 0 ? 'success' : 'danger';
                                                $icono = $diferencia > 0 ? 'arrow-up' : 'arrow-down';
                                                $signo = $diferencia > 0 ? '+' : '';
                                            @endphp
                                            <span class="fw-bold text-{{ $color }}">
                                                <i class="bi bi-{{ $icono }}"></i>
                                                {{ $signo }}${{ number_format(abs($diferencia), 2) }}
                                            </span>
                                            @if($diferencia > 0)
                                                <small class="d-block text-muted">+{{ number_format($cambio->porcentaje_aumento, 1) }}%</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Fila expandible con detalles --}}
                                @if($cambio->motivo || $cambio->observaciones || $cambio->usuario_cambio)
                                <tr class="collapse" id="detalle-{{ $cambio->id_promocion }}">
                                    <td colspan="7" class="bg-light border-top-0">
                                        <div class="p-3">
                                            <div class="row">
                                                @if($cambio->motivo)
                                                <div class="col-md-6">
                                                    <strong>Motivo:</strong>
                                                    <p class="mb-1">{{ $cambio->motivo }}</p>
                                                </div>
                                                @endif
                                                
                                                @if($cambio->observaciones)
                                                <div class="col-md-6">
                                                    <strong>Observaciones:</strong>
                                                    <p class="mb-1">{{ $cambio->observaciones }}</p>
                                                </div>
                                                @endif
                                                
                                                @if($cambio->usuario_cambio)
                                                <div class="col-md-6">
                                                    <strong>Procesado por:</strong>
                                                    <p class="mb-1">{{ $cambio->usuario_cambio }}</p>
                                                </div>
                                                @endif

                                                {{-- Datos adicionales si existen --}}
                                                @if($cambio->datos_adicionales && is_array($cambio->datos_adicionales))
                                                <div class="col-12">
                                                    <strong>Detalles adicionales:</strong>
                                                    <div class="row mt-2">
                                                        @foreach($cambio->datos_adicionales as $key => $value)
                                                            @if($value)
                                                            <div class="col-md-4">
                                                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</small>
                                                                <div class="fw-bold">{{ $value }}</div>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            {{-- Botón para colapsar --}}
                                            <div class="text-end mt-2">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#detalle-{{ $cambio->id_promocion }}">
                                                    <i class="bi bi-chevron-up"></i> Ocultar detalles
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ INFORMACIÓN ADICIONAL AL FINAL --}}
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-md-8">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Mostrando {{ $historialCompleto->count() }} registros totales.
                                    Haga clic en las filas con detalles para ver más información.
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    Última actualización: {{ $trabajador->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- ✅ ESTADO VACÍO --}}
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history display-1 text-muted opacity-50"></i>
                        <h5 class="mt-3 text-muted">Sin historial de cambios</h5>
                        <p class="text-muted">
                            No se han registrado cambios para este trabajador.<br>
                            Los cambios aparecerán aquí cuando se actualicen los datos laborales.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ✅ SCRIPT PARA FILTROS Y INTERACTIVIDAD --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos de filtros
    const filtroTipo = document.getElementById('filtro-tipo');
    const filtroFechaDesde = document.getElementById('filtro-fecha-desde');
    const filtroFechaHasta = document.getElementById('filtro-fecha-hasta');
    const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    
    // ✅ FUNCIÓN PARA APLICAR FILTROS
    function aplicarFiltros() {
        const tipo = filtroTipo.value;
        const fechaDesde = filtroFechaDesde.value;
        const fechaHasta = filtroFechaHasta.value;
        
        const filas = document.querySelectorAll('.historial-row');
        let contadorVisible = 0;
        
        filas.forEach(fila => {
            let mostrar = true;
            
            // Filtro por tipo
            if (tipo && fila.dataset.tipo !== tipo) {
                mostrar = false;
            }
            
            // Filtro por fecha
            const fechaFila = fila.dataset.fecha;
            if (fechaDesde && fechaFila < fechaDesde) {
                mostrar = false;
            }
            if (fechaHasta && fechaFila > fechaHasta) {
                mostrar = false;
            }
            
            // Mostrar/ocultar fila
            if (mostrar) {
                fila.style.display = '';
                contadorVisible++;
            } else {
                fila.style.display = 'none';
            }
        });
        
        // Mostrar mensaje si no hay resultados
        mostrarMensajeSinResultados(contadorVisible === 0);
        
        console.log(`✅ Filtros aplicados. ${contadorVisible} registros visibles.`);
    }
    
    // ✅ FUNCIÓN PARA LIMPIAR FILTROS
    function limpiarFiltros() {
        filtroTipo.value = '';
        filtroFechaDesde.value = '';
        filtroFechaHasta.value = '';
        
        // Mostrar todas las filas
        const filas = document.querySelectorAll('.historial-row');
        filas.forEach(fila => {
            fila.style.display = '';
        });
        
        // Ocultar mensaje de sin resultados
        mostrarMensajeSinResultados(false);
        
        console.log('✅ Filtros limpiados.');
    }
    
    // ✅ FUNCIÓN PARA MOSTRAR MENSAJE SIN RESULTADOS
    function mostrarMensajeSinResultados(mostrar) {
        let mensajeExistente = document.getElementById('mensaje-sin-resultados');
        
        if (mostrar && !mensajeExistente) {
            const tabla = document.getElementById('tabla-historial');
            const mensaje = document.createElement('div');
            mensaje.id = 'mensaje-sin-resultados';
            mensaje.className = 'text-center py-4';
            mensaje.innerHTML = `
                <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                <h6 class="mt-2 text-muted">No se encontraron registros</h6>
                <p class="text-muted mb-0">Prueba ajustando los filtros de búsqueda.</p>
            `;
            tabla.parentNode.insertBefore(mensaje, tabla.nextSibling);
        } else if (!mostrar && mensajeExistente) {
            mensajeExistente.remove();
        }
    }
    
    // ✅ EVENT LISTENERS
    if (btnAplicarFiltros) {
        btnAplicarFiltros.addEventListener('click', aplicarFiltros);
    }
    
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
    }
    
    // Aplicar filtros en tiempo real al cambiar valores
    [filtroTipo, filtroFechaDesde, filtroFechaHasta].forEach(element => {
        if (element) {
            element.addEventListener('change', aplicarFiltros);
        }
    });
    
    // ✅ HACER FILAS CLICABLES PARA VER DETALLES
    document.querySelectorAll('.historial-row').forEach(fila => {
        const id = fila.dataset.fecha; // Usar algo único para identificar
        const detalleId = fila.nextElementSibling?.id;
        
        if (detalleId && detalleId.startsWith('detalle-')) {
            fila.style.cursor = 'pointer';
            fila.addEventListener('click', function() {
                const detalle = document.getElementById(detalleId);
                if (detalle) {
                    const collapse = new bootstrap.Collapse(detalle, {
                        toggle: true
                    });
                }
            });
            
            // Agregar indicador visual
            const primeraCelda = fila.querySelector('td:first-child');
            if (primeraCelda) {
                primeraCelda.innerHTML += '<br><small class="text-muted"><i class="bi bi-chevron-down"></i> Clic para detalles</small>';
            }
        }
    });
    
    console.log('✅ Historial de cambios inicializado correctamente');
});
</script>

{{-- ✅ ESTILOS ESPECÍFICOS PARA LA TABLA --}}
<style>
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

.historial-row:hover {
    background-color: rgba(0,123,255,0.1) !important;
}

.historial-row[style*="cursor: pointer"]:hover {
    background-color: rgba(0,123,255,0.2) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa !important;
}

.badge {
    font-size: 0.8rem;
}

#filtro-tipo, #filtro-fecha-desde, #filtro-fecha-hasta {
    font-size: 0.9rem;
}
</style>