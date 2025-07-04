{{-- 
    ✅ COMPONENTE REUTILIZABLE DE TARJETAS ESTADÍSTICAS
    
    Uso:
    @include('components.estadisticas-cards', [
        'tipo' => 'trabajadores|despidos|permisos',
        'stats' => $estadisticas,
        'configuracion' => $configuracionTarjetas (opcional)
    ])
--}}

@php
    use App\Http\Controllers\EstadisticasController;
    
    // Obtener configuración de tarjetas si no se proporciona
    $configuracion = $configuracion ?? EstadisticasController::obtenerConfiguracionTarjetas($tipo);
    
    // Definir orden de tarjetas por tipo
    $ordenTarjetas = [
        'trabajadores' => ['activos', 'con_permiso', 'suspendidos', 'en_prueba', 'total', 'por_estado.inactivo'],
        'despidos' => ['total_activos', 'este_mes', 'este_año', 'total_cancelados'],
        'permisos' => ['activos', 'total', 'este_mes', 'finalizados', 'vencidos']
    ];
    
    $orden = $ordenTarjetas[$tipo] ?? array_keys($configuracion);
    
    // Función helper para obtener valor anidado
    function obtenerValorAnidado($array, $clave) {
        $partes = explode('.', $clave);
        $valor = $array;
        
        foreach ($partes as $parte) {
            if (isset($valor[$parte])) {
                $valor = $valor[$parte];
            } else {
                return 0;
            }
        }
        
        return $valor;
    }
@endphp

<div class="row mb-4" id="estadisticas-{{ $tipo }}">
    @foreach($orden as $clave)
        @if(isset($configuracion[$clave]))
            @php
                $config = $configuracion[$clave];
                $valor = obtenerValorAnidado($stats, $clave);
            @endphp
            
            <div class="col-md-{{ $tipo === 'trabajadores' ? '2' : ($tipo === 'despidos' ? '3' : '2') }}">
                <div class="card border-0 shadow-sm bg-{{ $config['color'] }} text-white estadistica-card" 
                     data-tipo="{{ $tipo }}" 
                     data-clave="{{ $clave }}"
                     data-valor="{{ $valor }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="{{ $config['icono'] }} fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="fs-5 fw-bold estadistica-valor">{{ $valor }}</div>
                                <div class="small {{ $config['color'] === 'warning' ? 'text-dark' : 'text-white-75' }}">
                                    {{ $config['titulo'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Indicador de carga (oculto por defecto) --}}
                    <div class="card-footer bg-transparent border-0 p-1 d-none estadistica-loading">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm text-white" role="status">
                                <span class="visually-hidden">Actualizando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

{{-- ✅ SCRIPT PARA ACTUALIZACIÓN AUTOMÁTICA (OPCIONAL) --}}
@push('scripts')
<script src="{{ asset('js/estadisticas-cards.js') }}"></script>
@endpush

{{-- ✅ ESTILOS ADICIONALES --}}
@push('styles')
<style>
.estadistica-card {
    transition: all 0.3s ease;
    cursor: default;
}

.estadistica-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.estadistica-valor {
    font-family: 'Arial', sans-serif;
    font-weight: 700;
}

.estadistica-loading .spinner-border {
    width: 1rem;
    height: 1rem;
}

/* Animación para cambios de valor */
@keyframes estadistica-actualizada {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.estadistica-actualizada {
    animation: estadistica-actualizada 0.6s ease-in-out;
}

/* Colores personalizados para mejor contraste */
.bg-warning .small {
    color: rgba(0, 0, 0, 0.7) !important;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}
</style>
@endpush