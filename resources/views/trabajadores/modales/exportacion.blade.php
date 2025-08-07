<div class="modal fade" id="modalExportacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Exportar Lista de Trabajadores
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExportacion" method="GET" action="{{ route('trabajadores.exportar') }}">
                <div class="modal-body">
                    <!-- Descripción -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Selecciona el tipo de lista que deseas exportar en formato Excel (.xlsx)
                    </div>

                    <div class="mb-4">
                        <label for="tipo_exportacion" class="form-label fw-bold">
                            <i class="bi bi-list-ul me-1"></i> Tipo de Lista
                        </label>
                        <select class="form-select form-select-lg" id="tipo_exportacion" name="tipo" required>
                            <option value="" selected disabled>Seleccione una opción</option>
                            
                            <!-- ✅ OPCIÓN GENERAL -->
                            <option value="generales" data-icon="bi-people-fill" data-color="primary">
                                📋 Lista General de Trabajadores
                            </option>
                            
                            <!-- ✅ OPCIÓN INACTIVOS -->
                            <option value="inactivos" data-icon="bi-person-x" data-color="danger">
                                ❌ Trabajadores Inactivos o Suspendidos
                            </option>
                            
                            <!-- ✅ OPCIÓN PERMISOS (SEPARADA DE VACACIONES) -->
                            <option value="permisos" data-icon="bi-calendar-event" data-color="info">
                                🏥 Trabajadores en Permisos Laborales
                            </option>

                            <!-- ✅ NUEVA OPCIÓN: SOLO VACACIONES -->
                            <option value="vacaciones" data-icon="bi-calendar-heart" data-color="success">
                                🏖️ Trabajadores en Vacaciones
                            </option>
                            
                            <!-- ✅ OPCIÓN CUMPLEAÑOS -->
                            <option value="cumpleaños" data-icon="bi-gift" data-color="warning">
                                🎂 Trabajadores por Mes de Cumpleaños
                            </option>
                        </select>
                        <div class="form-text">
                            <small>Cada opción generará un archivo Excel con columnas específicas para ese tipo de información.</small>
                        </div>
                    </div>

                    <!-- ✅ CONTENEDOR PARA MES DE CUMPLEAÑOS -->
                    <div class="mb-3 d-none" id="mesContainer">
                        <label for="mes" class="form-label fw-bold">
                            <i class="bi bi-calendar3 me-1"></i> Mes de Cumpleaños
                        </label>
                        <select class="form-select" id="mes" name="mes">
                            <option value="1">📅 Enero</option>
                            <option value="2">📅 Febrero</option>
                            <option value="3">📅 Marzo</option>
                            <option value="4">📅 Abril</option>
                            <option value="5">📅 Mayo</option>
                            <option value="6">📅 Junio</option>
                            <option value="7">📅 Julio</option>
                            <option value="8">📅 Agosto</option>
                            <option value="9">📅 Septiembre</option>
                            <option value="10">📅 Octubre</option>
                            <option value="11">📅 Noviembre</option>
                            <option value="12">📅 Diciembre</option>
                        </select>
                        <div class="form-text">
                            <small>Selecciona el mes para el cual deseas generar el reporte de cumpleaños.</small>
                        </div>
                    </div>

                    <!-- ✅ INFORMACIÓN ADICIONAL SEGÚN EL TIPO SELECCIONADO -->
                    <div id="infoAdicional" class="d-none">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2">
                                    <i class="bi bi-info-circle-fill me-1"></i> 
                                    Información de la Exportación
                                </h6>
                                <div id="descripcionTipo" class="small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnExportar" disabled>
                        <i class="bi bi-download me-1"></i> Exportar Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_exportacion');
    const mesContainer = document.getElementById('mesContainer');
    const infoAdicional = document.getElementById('infoAdicional');
    const descripcionTipo = document.getElementById('descripcionTipo');
    const btnExportar = document.getElementById('btnExportar');
    
    // ✅ DESCRIPCIONES PARA CADA TIPO DE EXPORTACIÓN
    const descripciones = {
        'generales': {
            texto: 'Incluye todos los trabajadores con información básica: datos personales, área, categoría, sueldo, estado y antigüedad.',
            icono: 'bi-people-fill',
            color: 'primary'
        },
        'inactivos': {
            texto: 'Lista de trabajadores con estado "Inactivo" o "Suspendido", incluyendo información de la baja: motivo, fecha, condición de salida.',
            icono: 'bi-person-x',
            color: 'danger'
        },
        'permisos': {
            texto: 'Trabajadores actualmente en permisos laborales: tipo de permiso, fechas, duración, contactos de emergencia.',
            icono: 'bi-calendar-event',
            color: 'info'
        },
        'vacaciones': {
            texto: 'Trabajadores actualmente en vacaciones: período vacacional, días correspondientes, fechas de inicio y reintegro.',
            icono: 'bi-calendar-heart',
            color: 'success'
        },
        'cumpleaños': {
            texto: 'Lista de trabajadores que cumplen años en el mes seleccionado, ordenados por día de cumpleaños.',
            icono: 'bi-gift',
            color: 'warning'
        }
    };
    
    tipoSelect.addEventListener('change', function() {
        const tipoSeleccionado = this.value;
        
        // ✅ MOSTRAR/OCULTAR SELECTOR DE MES
        if (tipoSeleccionado === 'cumpleaños') {
            mesContainer.classList.remove('d-none');
            // Establecer mes actual por defecto
            document.getElementById('mes').value = new Date().getMonth() + 1;
        } else {
            mesContainer.classList.add('d-none');
        }
        
        // ✅ MOSTRAR INFORMACIÓN ADICIONAL
        if (tipoSeleccionado && descripciones[tipoSeleccionado]) {
            const info = descripciones[tipoSeleccionado];
            descripcionTipo.innerHTML = `
                <i class="${info.icono} text-${info.color} me-1"></i>
                ${info.texto}
            `;
            infoAdicional.classList.remove('d-none');
        } else {
            infoAdicional.classList.add('d-none');
        }
        
        // ✅ HABILITAR/DESHABILITAR BOTÓN
        btnExportar.disabled = !tipoSeleccionado;
        
        // ✅ CAMBIAR COLOR DEL BOTÓN SEGÚN EL TIPO
        if (tipoSeleccionado && descripciones[tipoSeleccionado]) {
            const info = descripciones[tipoSeleccionado];
            btnExportar.className = `btn btn-${info.color}`;
        }
    });
    
    // ✅ VALIDAR FORMULARIO ANTES DE ENVIAR
    document.getElementById('formExportacion').addEventListener('submit', function(e) {
        const tipo = tipoSelect.value;
        const mes = document.getElementById('mes').value;
        
        if (!tipo) {
            e.preventDefault();
            alert('Por favor selecciona un tipo de exportación');
            return;
        }
        
        if (tipo === 'cumpleaños' && !mes) {
            e.preventDefault();
            alert('Por favor selecciona un mes para el reporte de cumpleaños');
            return;
        }
        
        // ✅ MOSTRAR INDICADOR DE CARGA
        btnExportar.disabled = true;
        btnExportar.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spin"></i> Generando...';
        
        // ✅ RESTAURAR BOTÓN DESPUÉS DE UN TIEMPO
        setTimeout(() => {
            btnExportar.disabled = false;
            btnExportar.innerHTML = '<i class="bi bi-download me-1"></i> Exportar Excel';
        }, 3000);
    });
    
    // ✅ RESETEAR FORMULARIO AL CERRAR MODAL
    document.getElementById('modalExportacion').addEventListener('hidden.bs.modal', function() {
        tipoSelect.value = '';
        mesContainer.classList.add('d-none');
        infoAdicional.classList.add('d-none');
        btnExportar.disabled = true;
        btnExportar.className = 'btn btn-primary';
        btnExportar.innerHTML = '<i class="bi bi-download me-1"></i> Exportar Excel';
    });
});
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Mejorar apariencia de las opciones del select */
#tipo_exportacion option {
    padding: 8px;
}

/* Estilo para el card de información */
.card.border-0.bg-light {
    background-color: #f8f9fa !important;
    border-radius: 8px;
}
</style>