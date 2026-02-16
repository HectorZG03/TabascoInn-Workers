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
    if (btnToggle && contenido) {
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
    }

    // ✅ FUNCIÓN PARA ACTUALIZAR VISTA PREVIA CON VERIFICACIONES
    window.actualizarVistaPrevia = function() {
        try {
            // ✅ Verificar que los elementos de vista previa existan
            if (!document.getElementById('preview-nombre')) {
                console.log('⚠️ Elementos de vista previa no encontrados, saltando actualización');
                return;
            }

            // Nombre completo
            const nombre = document.getElementById('nombre_trabajador')?.value || '';
            const apePat = document.getElementById('ape_pat')?.value || '';
            const apeMat = document.getElementById('ape_mat')?.value || '';
            const nombreCompleto = `${nombre} ${apePat} ${apeMat}`.trim() || 'Nombre del Trabajador';
            
            const previewNombre = document.getElementById('preview-nombre');
            if (previewNombre) {
                previewNombre.textContent = nombreCompleto.toUpperCase();
            }

            // Categoría y área
            const areaSelect = document.getElementById('id_area');
            const categoriaSelect = document.getElementById('id_categoria');
            const areaText = areaSelect?.selectedOptions[0]?.text || 'Sin área';
            const categoriaText = categoriaSelect?.selectedOptions[0]?.text || 'Sin categoría';
            const categoriaCompleta = categoriaText !== 'Seleccionar categoría...' && categoriaText !== 'Sin categoría' ? 
                `${categoriaText} - ${areaText}` : 'Categoría - Área';
            
            const previewCategoria = document.getElementById('preview-categoria');
            if (previewCategoria) {
                previewCategoria.textContent = categoriaCompleta.toUpperCase();
            }

            // Edad usando función global
            const fechaNacimiento = document.getElementById('fecha_nacimiento')?.value;
            const previewEdad = document.getElementById('preview-edad');
            if (previewEdad) {
                if (fechaNacimiento && window.calcularEdad) {
                    const edad = window.calcularEdad(fechaNacimiento);
                    previewEdad.textContent = edad !== null ? `${edad} años` : '-- años';
                } else {
                    previewEdad.textContent = '-- años';
                }
            }

            // Sueldo
            const sueldo = document.getElementById('sueldo_diarios')?.value;
            const previewSueldo = document.getElementById('preview-sueldo');
            if (previewSueldo) {
                previewSueldo.textContent = sueldo ? `$${parseFloat(sueldo).toFixed(2)}` : '$0.00';
            }

            // Estado del trabajador
            actualizarEstadoPreview();

            // Información del contrato
            actualizarContratoPreview();

            // Horarios usando funciones globales
            actualizarHorariosPreview();

            // Ubicación
            const ciudad = document.getElementById('ciudad_actual')?.value || '';
            const estado = document.getElementById('estado_actual')?.value || '';
            const ubicacion = [ciudad, estado].filter(Boolean).join(', ') || 'No especificada';
            const previewUbicacion = document.getElementById('preview-ubicacion');
            if (previewUbicacion) {
                previewUbicacion.textContent = ubicacion.toUpperCase();
            }

            // Progreso del formulario
            calcularProgreso();

        } catch (error) {
            console.error('❌ Error en actualizarVistaPrevia:', error);
        }
    };

    function actualizarEstadoPreview() {
        const estatusSelect = document.getElementById('estatus');
        const container = document.getElementById('preview-estado-container');
        const icon = document.getElementById('preview-estado-icon');
        const texto = document.getElementById('preview-estado');

        // ✅ Verificar que los elementos existan
        if (!container || !icon || !texto) return;

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

        const previewInicio = document.getElementById('preview-contrato-inicio');
        const previewFin = document.getElementById('preview-contrato-fin');
        const previewDuracion = document.getElementById('preview-contrato-duracion');

        if (previewInicio) {
            previewInicio.textContent = fechaInicio || 'Sin configurar';
        }
        if (previewFin) {
            previewFin.textContent = fechaFin || 'Sin configurar';
        }
        if (previewDuracion) {
            previewDuracion.textContent = duracion && duracion !== 'Seleccione las fechas' ? duracion : 'Sin configurar';
        }
    }

    function actualizarHorariosPreview() {
        const horaEntrada = document.getElementById('hora_entrada')?.value;
        const horaSalida = document.getElementById('hora_salida')?.value;
        const previewHorasDia = document.getElementById('preview-horas-dia');
        const previewTurno = document.getElementById('preview-turno');

        if (!previewHorasDia || !previewTurno) return;

        // Usar funciones globales si están disponibles
        if (horaEntrada && horaSalida && window.validarFormatoHora && window.calcularHoras && window.calcularTurno) {
            if (window.validarFormatoHora(horaEntrada) && window.validarFormatoHora(horaSalida)) {
                const horas = window.calcularHoras(horaEntrada, horaSalida);
                const turno = window.calcularTurno(horaEntrada, horaSalida);
                previewHorasDia.textContent = `${horas}h`;
                previewTurno.textContent = turno;
            } else {
                previewHorasDia.textContent = '-';
                previewTurno.textContent = '-';
            }
        } else {
            previewHorasDia.textContent = '-';
            previewTurno.textContent = '-';
        }
    }

    // ✅ FUNCIÓN DE PROGRESO CON VERIFICACIONES MEJORADAS
    function calcularProgreso() {
        try {
            // ✅ Verificar que los elementos de progreso existan
            const progresoPorcentaje = document.getElementById('progreso-porcentaje');
            const progresoBarra = document.getElementById('progreso-barra');
            const estadoFormulario = document.getElementById('estado-formulario');

            if (!progresoPorcentaje || !progresoBarra || !estadoFormulario) {
                console.log('⚠️ Elementos de progreso no encontrados');
                return;
            }

            const camposRequeridos = [
                'nombre_trabajador', 'ape_pat', 'fecha_nacimiento', 'curp', 'rfc', 'telefono',
                'fecha_ingreso', 'id_area', 'id_categoria', 'sueldo_diarios', 'hora_entrada',
                'hora_salida', 'estatus', 'fecha_inicio_contrato'
            ];

            // ✅ VERIFICACIÓN CONDICIONAL para fecha_fin_contrato
            const tipoContrato = document.getElementById('tipo_contrato')?.value;
            if (tipoContrato === 'determinado') {
                camposRequeridos.push('fecha_fin_contrato');
            }

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
            
            progresoPorcentaje.textContent = `${porcentaje}%`;
            progresoBarra.style.width = `${porcentaje}%`;

            // Actualizar estado del formulario
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
                        Complete todos los campos para crear el trabajador (${porcentaje}% completado)
                    </small>
                `;
            }
        } catch (error) {
            console.error('❌ Error en calcularProgreso:', error);
        }
    }

    // ✅ CONFIGURAR EVENT LISTENERS SOLO SI LOS ELEMENTOS EXISTEN
    function configurarEventListeners() {
        const camposObservables = [
            'nombre_trabajador', 'ape_pat', 'ape_mat', 'fecha_nacimiento', 'sueldo_diarios',
            'ciudad_actual', 'estado_actual', 'id_area', 'id_categoria', 'hora_entrada',
            'hora_salida', 'estatus', 'fecha_inicio_contrato', 'fecha_fin_contrato', 'tipo_contrato'
        ];

        camposObservables.forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.addEventListener('input', actualizarVistaPrevia);
                elemento.addEventListener('change', actualizarVistaPrevia);
            }
        });

        // Días laborables
        const checkboxesDias = document.querySelectorAll('input[name="dias_laborables[]"]');
        checkboxesDias.forEach(checkbox => {
            checkbox.addEventListener('change', actualizarVistaPrevia);
        });
    }

    // ✅ INICIALIZACIÓN CON DELAY PARA ASEGURAR QUE TODO ESTÉ CARGADO
    setTimeout(() => {
        configurarEventListeners();
        if (typeof actualizarVistaPrevia === 'function') {
            actualizarVistaPrevia();
        }
    }, 100);

    console.log('✅ Vista previa inicializada correctamente');
});
</script>