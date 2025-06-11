    {{-- 
    ✅ COMPONENTE UNIFICADO DE ALERTAS
    Archivo: resources/views/components/alertas.blade.php
    
    Uso: @include('layouts.alertas')
    
    Maneja todos los tipos de alertas del sistema de forma centralizada
--}}

{{-- 🎯 ALERTA DE ÉXITO --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

{{-- ❌ ALERTA DE ERROR --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

{{-- ⚠️ ALERTA DE ADVERTENCIA (con detalles opcionales) --}}
@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert" id="warning-alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Advertencia:</strong> {{ session('warning') }}
        
        {{-- Detalles adicionales de errores si existen --}}
        @if (session('errores_detalle') && is_array(session('errores_detalle')) && count(session('errores_detalle')) > 0)
            <hr class="my-2">
            <h6 class="mb-2">
                <i class="bi bi-list-ul me-1"></i>Detalles de errores:
            </h6>
            <ul class="mb-0">
                @foreach (session('errores_detalle') as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

{{-- 📝 ERRORES DE VALIDACIÓN --}}
@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert" id="validation-alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Hay errores en el formulario:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li class="small">{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

{{-- ℹ️ ALERTA INFORMATIVA (opcional) --}}
@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert" id="info-alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Información:</strong> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

{{-- ✅ JAVASCRIPT PARA AUTO-OCULTAR ALERTAS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-ocultar alertas después de 5 segundos (excepto errores de validación)
    const alertas = document.querySelectorAll('.alert:not(#validation-alert)');
    
    alertas.forEach(function(alerta) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alerta);
                bsAlert.close();
            } else {
                // Fallback si Bootstrap no está disponible
                alerta.style.transition = 'opacity 0.5s';
                alerta.style.opacity = '0';
                setTimeout(() => alerta.remove(), 500);
            }
        }, 5000);
    });
    
    // Ocultar alertas de validación después de 10 segundos
    const validationAlert = document.getElementById('validation-alert');
    if (validationAlert) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(validationAlert);
                bsAlert.close();
            } else {
                validationAlert.style.transition = 'opacity 0.5s';
                validationAlert.style.opacity = '0';
                setTimeout(() => validationAlert.remove(), 500);
            }
        }, 10000);
    }
});
</script>

{{-- ✅ ESTILOS ADICIONALES PARA MEJORAR LA APARIENCIA --}}
<style>
/* Mejoras visuales para las alertas */
.alert {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.alert i {
    font-size: 1.1em;
    vertical-align: middle;
}

.alert strong {
    font-weight: 600;
}

.alert ul {
    margin-left: 1rem;
}

.alert li.small {
    font-size: 0.9em;
    line-height: 1.4;
}

/* Animación suave para el cierre */
.alert.fade {
    transition: opacity 0.3s ease-in-out;
}

/* Colores personalizados más profesionales */
.alert-success {
    background-color: #d1f2eb;
    border-left: 4px solid #28a745;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
    color: #0c5460;
}
</style>