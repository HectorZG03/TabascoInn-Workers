<!-- ✅ VISTA PREVIA SIMPLIFICADA -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-eye"></i> Vista Previa
        </h5>
        <button id="toggleVistaPrevia" class="btn btn-sm btn-outline-light">
            <i class="bi bi-eye-slash"></i> Ocultar
        </button>
    </div>
    <div class="card-body" id="contenidoVistaPrevia">
        <!-- Información Básica -->
        <div class="text-center mb-3">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                 style="width: 60px; height: 60px;">
                <i class="bi bi-person-circle fs-1 text-secondary"></i>
            </div>
            <h6 class="mt-2 mb-1 text-uppercase fw-bold" id="preview-nombre">Nombre del Trabajador</h6>
            <small class="text-muted text-uppercase" id="preview-categoria">Categoría - Área</small>
        </div>

        <!-- Datos Principales -->
        <div class="row g-2 mb-3">
            <!-- Edad -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Edad</small>
                    <span class="fw-bold" id="preview-edad">-- años</span>
                </div>
            </div>

            <!-- Sueldo -->
            <div class="col-6">
                <div class="bg-light rounded p-2 text-center">
                    <small class="text-muted d-block">Sueldo Diario</small>
                    <span class="fw-bold text-success" id="preview-sueldo">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Estado del Trabajador -->
        <div class="mb-3">
            <div class="border rounded p-2" id="preview-estado-container">
                <small class="text-muted d-block">
                    <i class="bi bi-person-gear me-1"></i>Estado
                </small>
                <div class="d-flex align-items-center">
                    <i id="preview-estado-icon" class="me-2"></i>
                    <span id="preview-estado" class="fw-bold">
                        Sin configurar
                    </span>
                </div>
            </div>
        </div>

        <!-- Información del Contrato -->
        <div class="mb-3">
            <div class="border rounded p-2 bg-light">
                <small class="text-muted d-block">
                    <i class="bi bi-file-earmark-text me-1"></i>Contrato
                </small>
                <div class="small">
                    <div><strong>Inicio:</strong> <span id="preview-contrato-inicio">Sin configurar</span></div>
                    <div><strong>Fin:</strong> <span id="preview-contrato-fin">Sin configurar</span></div>
                    <div><strong>Duración:</strong> <span id="preview-contrato-duracion">Sin configurar</span></div>
                </div>
            </div>
        </div>

        <!-- Resumen de Horarios -->
        <div class="mb-3">
            <div class="card border-0 bg-light">
                <div class="card-body p-2">
                    <h6 class="card-title mb-2 text-center">
                        <i class="bi bi-clock me-1"></i>Horario
                    </h6>
                    <div class="row g-1 text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Horas/Día</small>
                            <span class="fw-bold" id="preview-horas-dia">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Turno</small>
                            <span class="fw-bold" id="preview-turno">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="mb-3">
            <div class="bg-light rounded p-2">
                <small class="text-muted d-block">
                    <i class="bi bi-geo-alt me-1"></i>Ubicación
                </small>
                <span class="text-uppercase" id="preview-ubicacion">No especificada</span>
            </div>
        </div>

        <!-- Progreso del Formulario -->
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">Progreso del formulario</small>
                <small class="text-muted" id="progreso-porcentaje">0%</small>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar" id="progreso-barra" style="width: 0%"></div>
            </div>
        </div>

        <!-- Estado del Formulario -->
        <div class="mt-3">
            <div class="alert alert-info mb-0" id="estado-formulario">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Complete todos los campos para crear el trabajador
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnToggle = document.getElementById('toggleVistaPrevia');
    const contenido = document.getElementById('contenidoVistaPrevia');
    let visible = true;

    // Toggle vista previa
    btnToggle.addEventListener('click', function() {
        visible = !visible;
        if (visible) {
            contenido.style.display = 'block';
            btnToggle.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar';
        } else {
            contenido.style.display = 'none';
            btnToggle.innerHTML = '<i class="bi bi-eye"></i> Mostrar';
        }
    });

    // Función para actualizar vista previa
    window.actualizarVistaPrevia = function() {
        // Nombre completo
        const nombre = document.getElementById('nombre_trabajador')?.value || '';
        const apePat = document.getElementById('ape_pat')?.value || '';
        const apeMat = document.getElementById('ape_mat')?.value || '';
        const nombreCompleto = `${nombre} ${apePat} ${apeMat}`.trim() || 'Nombre del Trabajador';
        document.getElementById('preview-nombre').textContent = nombreCompleto.toUpperCase();

        // Categoría y área
        const areaSelect = document.getElementById('id_area');
        const categoriaSelect = document.getElementById('id_categoria');
        const areaText = areaSelect?.selectedOptions[0]?.text || 'Sin área';
        const categoriaText = categoriaSelect?.selectedOptions[0]?.text || 'Sin categoría';
        const categoriaCompleta = categoriaText !== 'Seleccionar categoría...' && categoriaText !== 'Sin categoría' ? 
            `${categoriaText} - ${areaText}` : 'Categoría - Área';
        document.getElementById('preview-categoria').textContent = categoriaCompleta.toUpperCase();

        // Edad
        const fechaNacimiento = document.getElementById('fecha_nacimiento')?.value;
        if (fechaNacimiento) {
            const edad = calcularEdadDesdeFecha(fechaNacimiento);
            document.getElementById('preview-edad').textContent = edad !== null ? `${edad} años` : '-- años';
        } else {
            document.getElementById('preview-edad').textContent = '-- años';
        }

        // Sueldo
        const sueldo = document.getElementById('sueldo_diarios')?.value;
        document.getElementById('preview-sueldo').textContent = sueldo ? 
            `$${parseFloat(sueldo).toFixed(2)}` : '$0.00';

        // Estado del trabajador
        actualizarEstadoPreview();

        // Información del contrato
        actualizarContratoPreview();

        // Horarios
        actualizarHorariosPreview();

        // Ubicación
        const ciudad = document.getElementById('ciudad_actual')?.value || '';
        const estado = document.getElementById('estado_actual')?.value || '';
        const ubicacion = [ciudad, estado].filter(Boolean).join(', ') || 'No especificada';
        document.getElementById('preview-ubicacion').textContent = ubicacion.toUpperCase();

        // Progreso del formulario
        calcularProgreso();
    };

    function actualizarEstadoPreview() {
        const estatusSelect = document.getElementById('estatus');
        const container = document.getElementById('preview-estado-container');
        const icon = document.getElementById('preview-estado-icon');
        const texto = document.getElementById('preview-estado');

        if (!estatusSelect?.value) {
            container.className = 'border rounded p-2';
            icon.className = 'bi bi-question-circle text-muted me-2';
            texto.textContent = 'Sin configurar';
            return;
        }

        switch (estatusSelect.value) {
            case 'activo':
                container.className = 'border border-success rounded p-2 bg-success bg-opacity-10';
                icon.className = 'bi bi-check-circle text-success me-2';
                texto.textContent = 'ACTIVO';
                break;
            case 'prueba':
                container.className = 'border border-warning rounded p-2 bg-warning bg-opacity-10';
                icon.className = 'bi bi-hourglass-split text-warning me-2';
                texto.textContent = 'PERÍODO DE PRUEBA';
                break;
        }
    }

    function actualizarContratoPreview() {
        const fechaInicio = document.getElementById('fecha_inicio_contrato')?.value;
        const fechaFin = document.getElementById('fecha_fin_contrato')?.value;
        const duracion = document.getElementById('duracionTexto')?.textContent;

        document.getElementById('preview-contrato-inicio').textContent = fechaInicio || 'Sin configurar';
        document.getElementById('preview-contrato-fin').textContent = fechaFin || 'Sin configurar';
        document.getElementById('preview-contrato-duracion').textContent = 
            duracion && duracion !== 'Seleccione las fechas' ? duracion : 'Sin configurar';
    }

    function actualizarHorariosPreview() {
        const horaEntrada = document.getElementById('hora_entrada')?.value;
        const horaSalida = document.getElementById('hora_salida')?.value;

        if (horaEntrada && horaSalida && validarFormatoHora(horaEntrada) && validarFormatoHora(horaSalida)) {
            const horas = calcularHoras(horaEntrada, horaSalida);
            const turno = calcularTurno(horaEntrada, horaSalida);
            document.getElementById('preview-horas-dia').textContent = `${horas}h`;
            document.getElementById('preview-turno').textContent = turno;
        } else {
            document.getElementById('preview-horas-dia').textContent = '-';
            document.getElementById('preview-turno').textContent = '-';
        }
    }

    function calcularProgreso() {
        const camposRequeridos = [
            'nombre_trabajador', 'ape_pat', 'fecha_nacimiento', 'curp', 'rfc', 'telefono',
            'fecha_ingreso', 'id_area', 'id_categoria', 'sueldo_diarios', 'hora_entrada',
            'hora_salida', 'estatus', 'fecha_inicio_contrato', 'fecha_fin_contrato'
        ];

        let completados = 0;
        camposRequeridos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento?.value?.trim()) {
                completados++;
            }
        });

        // Verificar días laborables
        const diasLaborables = document.querySelectorAll('input[name="dias_laborables[]"]:checked');
        if (diasLaborables.length > 0) {
            completados++;
        }

        const porcentaje = Math.round((completados / (camposRequeridos.length + 1)) * 100);
        document.getElementById('progreso-porcentaje').textContent = `${porcentaje}%`;
        document.getElementById('progreso-barra').style.width = `${porcentaje}%`;

        // Actualizar estado del formulario
        const estadoFormulario = document.getElementById('estado-formulario');
        if (porcentaje === 100) {
            estadoFormulario.className = 'alert alert-success mb-0';
            estadoFormulario.innerHTML = `
                <small>
                    <i class="bi bi-check-circle me-1"></i>
                    Formulario completo - Listo para crear trabajador
                </small>
            `;
        } else {
            estadoFormulario.className = 'alert alert-info mb-0';
            estadoFormulario.innerHTML = `
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Complete todos los campos para crear el trabajador
                </small>
            `;
        }
    }

    function calcularEdadDesdeFecha(fechaStr) {
        if (!fechaStr) return null;
        
        // Validar formato DD/MM/YYYY
        const formatoFecha = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!formatoFecha.test(fechaStr)) return null;
        
        const [dia, mes, año] = fechaStr.split('/').map(Number);
        const fechaNacimiento = new Date(año, mes - 1, dia);
        
        if (isNaN(fechaNacimiento.getTime())) return null;
        
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mesActual = hoy.getMonth() - fechaNacimiento.getMonth();
        
        if (mesActual < 0 || (mesActual === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }
        
        return edad >= 0 ? edad : null;
    }

    function validarFormatoHora(hora) {
        const formatoHora = /^([01]\d|2[0-3]):([0-5]\d)$/;
        return formatoHora.test(hora);
    }

    function calcularHoras(entrada, salida) {
        if (!validarFormatoHora(entrada) || !validarFormatoHora(salida)) return 0;
        
        const base = '2024-01-01';
        let e = new Date(`${base}T${entrada}:00`);
        let s = new Date(`${base}T${salida}:00`);
        if (s <= e) s.setDate(s.getDate() + 1);
        return Math.round((s - e) / 3600000 * 100) / 100;
    }

    function calcularTurno(entrada, salida) {
        if (!validarFormatoHora(entrada) || !validarFormatoHora(salida)) return 'INVÁLIDO';
        
        const [horaEnt, minEnt] = entrada.split(':').map(Number);
        const [horaSal, minSal] = salida.split(':').map(Number);
        
        const totalMinEnt = horaEnt * 60 + minEnt;
        const totalMinSal = horaSal * 60 + minSal;
        
        // Si cruza medianoche
        if (totalMinSal <= totalMinEnt) return 'NOCTURNO';
        
        // Diurno: 06:00 - 18:00
        if (totalMinEnt >= 360 && totalMinSal <= 1080) return 'DIURNO';
        
        // Nocturno: 18:00 - 06:00
        if (totalMinEnt >= 1080 || totalMinSal <= 360) return 'NOCTURNO';
        
        return 'MIXTO';
    }

    // Event listeners para actualizar vista previa
    const camposObservables = [
        'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios',
        'ciudad_actual', 'estado_actual', 'id_area', 'id_categoria', 'hora_entrada',
        'hora_salida', 'estatus', 'fecha_inicio_contrato', 'fecha_fin_contrato'
    ];

    camposObservables.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.addEventListener('input', actualizarVistaPrevia);
            elemento.addEventListener('change', actualizarVistaPrevia);
        }
    });

    // Días laborables
    document.querySelectorAll('input[name="dias_laborables[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', actualizarVistaPrevia);
    });

    // Inicializar vista previa
    actualizarVistaPrevia();
});
</script>