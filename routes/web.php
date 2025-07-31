<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AreaCategoriaController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ActPerfilTrabajadorController;
use App\Http\Controllers\DespidosController;
use App\Http\Controllers\PermisosLaboralesController;
use App\Http\Controllers\HistorialesPerfilController;
use App\Http\Controllers\HorasExtraController;
use App\Http\Controllers\FormatoPermisosController;
use App\Http\Controllers\GerenteController;
use App\Http\Controllers\BusquedaTrabajadoresController;
use App\Http\Controllers\PlantillaContratoController;
use App\Http\Controllers\VariableContratoController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminContratosController; 
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\VacacionesController;
use App\Http\Controllers\DocumentosVacacionesController;
use Illuminate\Support\Facades\Route;

// Redirigir la ruta raíz al login
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
 
    // ✅ RUTAS DE CONFIGURACIÓN DE USUARIO
    Route::prefix('configuracion')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'configMenu'])->name('config');
        Route::middleware('check.gerencia')->group(function () {
            Route::get('/usuarios', [UserController::class, 'manageUsers'])->name('manage');
            Route::get('/sistema', [UserController::class, 'systemConfig'])->name('system.config');
        });
    });

    Route::prefix('configuracion')->group(function () {
        Route::get('/areas-categorias', [AreaCategoriaController::class, 'index'])->name('areas.categorias.index');
        Route::post('/areas', [AreaCategoriaController::class, 'storeArea'])->name('areas.store');
        Route::post('/categorias', [AreaCategoriaController::class, 'storeCategoria'])->name('categorias.store');
        Route::put('/areas/{area}', [AreaCategoriaController::class, 'updateArea'])->name('areas.update');
        Route::delete('/areas/{area}', [AreaCategoriaController::class, 'destroyArea'])->name('areas.destroy');
        Route::put('/categorias/{categoria}', [AreaCategoriaController::class, 'updateCategoria'])->name('categorias.update');
        Route::delete('/categorias/{categoria}', [AreaCategoriaController::class, 'destroyCategoria'])->name('categorias.destroy');
        Route::get('/areas-categorias/estadisticas', [AreaCategoriaController::class, 'estadisticas'])->name('areas.categorias.estadisticas');
        Route::delete('/categorias/multiple', [AreaCategoriaController::class, 'destroyMultipleCategories'])
            ->name('categorias.multiple.destroy');
        Route::post('/departamentos', [AreaCategoriaController::class, 'storeDepartamento'])->name('departamentos.store');
        Route::put('/departamentos/{departamento}', [AreaCategoriaController::class, 'updateDepartamento'])->name('departamentos.update');
        Route::delete('/departamentos/{departamento}', [AreaCategoriaController::class, 'destroyDepartamento'])->name('departamentos.destroy');
        Route::get('/api/departamentos/{departamento}/areas', [AreaCategoriaController::class, 'getAreasPorDepartamento'])->name('api.departamentos.areas');
           // ✅ NUEVAS RUTAS DE GERENTES
        Route::prefix('gerentes')->name('gerentes.')->controller(GerenteController::class)->group(function () {
            // Vista principal con listado
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{gerente}', 'update')->name('update');
            Route::patch('/{gerente}/toggle-estatus', 'toggleEstatus')->name('toggle-estatus');
            Route::delete('/{gerente}', 'destroy')->name('destroy');
            Route::get('/api/lista', 'apiGerentes')->name('api.lista');
        });

            // ✅ RUTAS DE PLANTILLAS DE CONTRATO
    // ✅ RUTAS DE PLANTILLAS DE CONTRATO
    Route::prefix('plantillas-contrato')->name('configuracion.plantillas.')->controller(PlantillaContratoController::class)->group(function () {
        // Vista principal del editor
        Route::get('/', 'index')->name('index');
        
        // Crear nueva plantilla
        Route::get('/crear', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        
        // Ver plantilla específica
        Route::get('/{plantilla}', 'show')->name('show');
        
        // Editar plantilla
        Route::get('/{plantilla}/editar', 'edit')->name('edit');
        Route::put('/{plantilla}', 'update')->name('update');
        
        // Activar/Desactivar plantilla
        Route::patch('/{plantilla}/toggle', 'toggleActivacion')->name('toggle');
        
        // Vista previa
        Route::post('/preview', 'preview')->name('preview');
        
        // Obtener variables (AJAX)
        Route::get('/api/variables', 'obtenerVariables')->name('api.variables');
        
        // Exportar plantilla
        Route::get('/exportar/{tipo?}', 'exportar')->name('exportar');
    });
    
    // ✅ RUTAS DE VARIABLES DE CONTRATO (opcional, para gestión avanzada)
    Route::prefix('variables-contrato')->name('variables.')->controller(VariableContratoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{variable}', 'update')->name('update');
        Route::patch('/{variable}/toggle', 'toggleEstado')->name('toggle');
        Route::delete('/{variable}', 'destroy')->name('destroy');
    });

    });
    

    // Rutas para búsqueda de trabajadores
    Route::get('/trabajadores/buscar', [BusquedaTrabajadoresController::class, 'index'])
        ->name('trabajadores.buscar');
    Route::get('/api/trabajadores/busqueda-rapida', [BusquedaTrabajadoresController::class, 'busquedaRapida'])
        ->name('trabajadores.busqueda.rapida');
    Route::get('/api/trabajadores/sugerencias', [BusquedaTrabajadoresController::class, 'sugerencias'])
        ->name('trabajadores.sugerencias');
    Route::get('/api/trabajadores/estadisticas', [BusquedaTrabajadoresController::class, 'estadisticas'])
        ->name('trabajadores.estadisticas');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ RUTAS DE IMPORTACIÓN MASIVA
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/plantilla', [ImportController::class, 'descargarPlantilla'])->name('plantilla');
        Route::post('/procesar', [ImportController::class, 'importarTrabajadores'])->name('procesar');
    });

   // ✅ RUTAS DE TRABAJADORES - Gestión General
    Route::prefix('trabajadores')->name('trabajadores.')->group(function () {
        // CRUD básico de trabajadores
        Route::get('/', [TrabajadorController::class, 'index'])->name('index');
        Route::get('/crear', [TrabajadorController::class, 'create'])->name('create');
        Route::post('/', [TrabajadorController::class, 'store'])->name('store');
        Route::get('/{trabajador}', [TrabajadorController::class, 'show'])->name('show');
        Route::get('/{trabajador}/editar', [TrabajadorController::class, 'edit'])->name('edit');
        Route::put('/{trabajador}', [TrabajadorController::class, 'update'])->name('update');
        Route::delete('/{trabajador}', [TrabajadorController::class, 'destroy'])->name('destroy');

        // ✅ RUTAS DE ACCIONES ESPECÍFICAS
        Route::post('/{trabajador}/despedir', [DespidosController::class, 'store'])->name('despedir');
        Route::post('/{trabajador}/permisos', [PermisosLaboralesController::class, 'store'])->name('permisos.store');
        Route::get('/{trabajador}/historial-promociones', [ActPerfilTrabajadorController::class, 'verHistorialCompleto'])
            ->name('historial-promociones');

        // ✅ RUTAS DE PERFIL AVANZADO
        Route::prefix('{trabajador}/perfil')->name('perfil.')->group(function () {
            Route::get('/', [ActPerfilTrabajadorController::class, 'show'])->name('show');
            Route::put('/datos', [ActPerfilTrabajadorController::class, 'updateDatos'])->name('update-datos');
            Route::put('/ficha-tecnica', [ActPerfilTrabajadorController::class, 'updateFichaTecnica'])->name('update-ficha');
            Route::post('/documentos', [ActPerfilTrabajadorController::class, 'uploadDocument'])->name('upload-document');
            Route::delete('/documentos', [ActPerfilTrabajadorController::class, 'deleteDocument'])->name('delete-document');
            Route::get('/areas/{area}/categorias', [ActPerfilTrabajadorController::class, 'getCategoriasPorArea'])->name('categorias');
            Route::put('/estatus', [ActPerfilTrabajadorController::class, 'updateEstatus'])->name('update-estatus');
        });

        // ✅ RUTAS DE HISTORIAL EN EL PERFIL (DENTRO DEL GRUPO DE TRABAJADORES)
        // ✅ RUTAS SIMPLIFICADAS PARA HISTORIALES (Reemplazar las existentes)

        // Dentro del grupo de trabajadores, reemplazar las rutas de historial:
        Route::prefix('{trabajador}')->name('perfil.')->group(function () {
            // ✅ HISTORIAL DE PERMISOS - VISTA COMPLETA SIN AJAX
            Route::get('/permisos/historial', [HistorialesPerfilController::class, 'permisos'])
                ->name('permisos.historial');
            
            // ✅ HISTORIAL DE BAJAS - VISTA COMPLETA SIN AJAX  
            Route::get('/bajas/historial', [HistorialesPerfilController::class, 'bajas'])
                ->name('bajas.historial');
        });

        // ✅ RUTAS DE DETALLE - NUEVAS PÁGINAS COMPLETAS
        Route::get('/permisos/{permiso}/detalle', [HistorialesPerfilController::class, 'detallePermiso'])
            ->name('permisos.detalle');

        Route::get('/despidos/{despido}/detalle', [HistorialesPerfilController::class, 'detalleBaja'])
            ->name('despidos.detalle');
        
        // ✅ RUTAS DE ADMINISTRACIÓN DE CONTRATOS - OPTIMIZADAS Y CENTRALIZADAS
        Route::prefix('{trabajador}/contratos')->name('contratos.')->controller(AdminContratosController::class)->group(function () {
            // Vista principal de contratos del trabajador
            Route::get('/', 'show')->name('show');
            
            // Crear nuevo contrato
            Route::post('/crear', 'store')->name('crear');
            
            // Renovar contrato existente
            Route::post('/{contrato}/renovar', 'renovar')->name('renovar');
            
            // ✅ ÚNICA RUTA DE DESCARGA (eliminada duplicación)
            Route::get('/{contrato}/descargar', 'descargar')->name('descargar');
            
            // Eliminar contrato permanentemente
            Route::delete('/{contrato}/eliminar', 'eliminar')->name('eliminar');
            
            // ✅ APIs para gestión de contratos
            Route::get('/api/verificar-creacion', 'verificarCreacion')->name('api.verificar');
            Route::get('/api/resumen', 'obtenerResumen')->name('api.resumen');
        });

        Route::prefix('{trabajador}/horas-extra')->name('horas-extra.')->controller(HorasExtraController::class)->group(function () {
            // ✅ ACCIONES PRINCIPALES (existentes)
            Route::post('asignar', 'asignar')->name('asignar');
            Route::post('restar', 'restar')->name('restar');
            
            // ✅ APIs PARA AJAX (existentes y nuevas)
            Route::get('saldo', 'obtenerSaldo')->name('saldo');
            Route::get('historial', 'obtenerHistorial')->name('historial');
            Route::get('estadisticas', 'obtenerEstadisticas')->name('estadisticas');
        });

        Route::prefix('{trabajador}/vacaciones')->name('vacaciones.')->group(function () {
            // Vista dedicada principal
            Route::get('/', [VacacionesController::class, 'show'])->name('show');
            
            // API para AJAX (con prefijo 'api' para evitar conflictos)
            Route::get('/api', [VacacionesController::class, 'index'])->name('api.index');
            Route::get('/estadisticas', [VacacionesController::class, 'estadisticas'])->name('estadisticas');
            Route::get('/calcular-dias', [VacacionesController::class, 'calcularDias'])->name('calcular-dias');
            
            // ✅ NUEVA RUTA: Calcular fechas considerando días laborables
            Route::post('/calcular-fechas', [VacacionesController::class, 'calcularFechasVacaciones'])->name('calcular-fechas');
            
            // Gestión de vacaciones
            Route::post('/asignar', [VacacionesController::class, 'store'])->name('asignar');
            Route::patch('/{vacacion}/iniciar', [VacacionesController::class, 'iniciar'])->name('iniciar');
            Route::patch('/{vacacion}/finalizar', [VacacionesController::class, 'finalizar'])->name('finalizar');
            Route::delete('/{vacacion}/cancelar', [VacacionesController::class, 'cancelar'])->name('cancelar');
        });

        // ✅ RUTAS DE DOCUMENTOS DE VACACIONES - MOVIDAS AQUÍ DENTRO DEL GRUPO DE TRABAJADORES
        Route::prefix('{trabajador}/documentos-vacaciones')->name('documentos-vacaciones.')->group(function () {
            // Vista principal de documentos
            Route::get('/', [DocumentosVacacionesController::class, 'index'])
                ->name('index');
            
            // ✅ NUEVA: Modal de selección de firmas
            Route::get('/seleccion-firmas', [DocumentosVacacionesController::class, 'mostrarSeleccionFirmas'])
                ->name('seleccion-firmas');
            
            // ✅ ACTUALIZADA: Generar PDF con firmas seleccionadas
            Route::post('/descargar-pdf', [DocumentosVacacionesController::class, 'descargarPDF'])
                ->name('descargar-pdf');
            
            // ✅ NUEVA: Descarga directa del PDF
            Route::get('/descargar-pdf-directo', [DocumentosVacacionesController::class, 'descargarPDFDirecto'])
                ->name('descargar-pdf-directo');
            
            // Subir documento firmado
            Route::post('/subir', [DocumentosVacacionesController::class, 'subirDocumento'])
                ->name('subir');
            
            // API para obtener lista de documentos
            Route::get('/api/documentos', [DocumentosVacacionesController::class, 'obtenerDocumentos'])
                ->name('api.documentos');
            
            // Eliminar documento
            Route::delete('/{documento}/eliminar', [DocumentosVacacionesController::class, 'eliminarDocumento'])
                ->name('eliminar');
        });

    }); // ✅ AQUÍ TERMINA EL GRUPO DE TRABAJADORES

    // En web.php, dentro del grupo de despidos:
    Route::prefix('despidos')->name('despidos.')->controller(DespidosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{despido}', 'show')->name('show');
        Route::delete('/{despido}/cancelar', 'cancelar')->name('cancelar');
        Route::get('/api/estadisticas', 'estadisticas')->name('estadisticas');
        Route::get('/{despido}/detalle', [HistorialesPerfilController::class, 'detalleBaja'])->name('detalle');
        
        // ✅ AGREGAR ESTA RUTA NUEVA:
        Route::get('/trabajador/{trabajador}/historial', 'historial')->name('trabajador.historial');
    });

    // ✅ RUTAS PARA PERMISOS LABORALES ACTUALIZADAS
    Route::prefix('permisos')->name('permisos.')->group(function () {
        Route::get('/', [PermisosLaboralesController::class, 'index'])->name('index');
        Route::get('/{permiso}', [PermisosLaboralesController::class, 'show'])->name('show');
        
        // ✅ ACCIONES DE GESTIÓN DE PERMISOS
        Route::patch('/{permiso}/finalizar', [PermisosLaboralesController::class, 'finalizar'])->name('finalizar');
        Route::patch('/{permiso}/cancelar', [PermisosLaboralesController::class, 'cancelar'])->name('cancelar'); // ✅ CANCELAR CON MOTIVO
        Route::delete('/{permiso}/eliminar', [PermisosLaboralesController::class, 'eliminar'])->name('eliminar'); // ✅ ELIMINAR DEFINITIVAMENTE
        
        // ✅ MANTENER COMPATIBILIDAD CON RUTA ANTIGUA (OPCIONAL)
        Route::delete('/{permiso}/cancelar', [PermisosLaboralesController::class, 'eliminar'])->name('cancelar.old');
        
        Route::get('/{permiso}/detalle', [HistorialesPerfilController::class, 'detallePermiso'])->name('detalle');
        
        // ✅ RUTAS DE PDFs DE PERMISOS
        Route::prefix('{permiso}/pdf')->name('pdf.')->controller(FormatoPermisosController::class)->group(function () {
            Route::get('/generar', 'generarPDF')->name('generar');
            Route::get('/descargar', 'descargarPDF')->name('descargar');
            Route::post('/regenerar', 'regenerarPDF')->name('regenerar');
        });
        Route::get('/{permiso}/pdf', [FormatoPermisosController::class, 'generarPDF'])->name('pdf'); // Compatibilidad
        
        // APIs
        Route::get('/api/estadisticas', [PermisosLaboralesController::class, 'estadisticas'])->name('estadisticas');
        Route::get('/api/motivos-por-tipo', [PermisosLaboralesController::class, 'getMotivosPorTipo'])->name('api.motivos-por-tipo');
        Route::post('/verificar-vencidos', [PermisosLaboralesController::class, 'verificarVencidos'])->name('verificar-vencidos');
    });
    
    // ✅ RUTAS ADICIONALES PARA ARCHIVOS DE PERMISOS
    Route::get('/permisos/{id}/descargar', [PermisosLaboralesController::class, 'descargar'])
        ->name('permisos.descargar');
    
    Route::post('/permisos/{permiso}/subir-archivo', [PermisosLaboralesController::class, 'subirArchivo'])
        ->name('permisos.subirArchivo');

    // ✅ RUTAS AJAX PARA CONTRATOS - OPTIMIZADAS (Solo generación)
    Route::prefix('ajax/contratos')->name('ajax.contratos.')->controller(ContratoController::class)->group(function () {
        // ✅ SOLO funciones de generación de PDFs
        Route::post('/preview', 'generarPreview')->name('preview');
        Route::get('/preview-download/{hash}', 'descargarPreview')->name('preview.download');
    });

    // ✅ APIs GENERALES
    Route::get('/api/categorias/{area}', [TrabajadorController::class, 'getCategoriasPorArea'])->name('api.categorias');
    Route::get('/api/motivos', function() {
        return response()->json([
            'todos' => \App\Models\PermisosLaborales::getTodosLosMotivos()
        ]);
    })->name('api.motivos');
    
    // ✅ NUEVA RUTA API PARA ESTADÍSTICAS
    Route::get('/api/estadisticas', [EstadisticasController::class, 'obtenerEstadisticas'])->name('api.estadisticas');
    
    Route::get('/debug-fechas-contrato', function() {
        
        // 1. ✅ Obtener el contrato más reciente
        $contrato = \App\Models\ContratoTrabajador::latest()->first();
        
        if (!$contrato) {
            return "❌ No hay contratos en la base de datos";
        }
        
        echo "<h2>🔍 DEBUG FECHAS CONTRATO</h2>";
        echo "<hr>";
        
        // 2. ✅ Mostrar fechas reales de la BD
        echo "<h3>📋 FECHAS REALES EN BD:</h3>";
        echo "Contrato ID: {$contrato->id_contrato}<br>";
        echo "Tipo: {$contrato->tipo_contrato}<br>";
        echo "Fecha Inicio BD: {$contrato->fecha_inicio_contrato->format('d/m/Y')}<br>";
        echo "Fecha Fin BD: " . ($contrato->fecha_fin_contrato ? $contrato->fecha_fin_contrato->format('d/m/Y') : 'N/A') . "<br>";
        echo "<hr>";
        
        // 3. ✅ Simular el flujo completo como lo hace AdminContratosController
        $trabajador = $contrato->trabajador;
        $trabajador->load('fichaTecnica.categoria');
        
        // ✅ SIMULAR DATOS COMO AdminContratosController
        $datosContrato = [
            'tipo_contrato' => $contrato->tipo_contrato,
            'fecha_inicio_contrato' => $contrato->fecha_inicio_contrato->format('Y-m-d'), // STRING como lo hace AdminContratosController
            'fecha_fin_contrato' => $contrato->fecha_fin_contrato ? $contrato->fecha_fin_contrato->format('Y-m-d') : null,
            'tipo_duracion' => $contrato->tipo_duracion,
            'sueldo_diarios' => $trabajador->fichaTecnica->sueldo_diarios ?? 450
        ];
        
        echo "<h3>📊 DATOS ENVIADOS (como AdminContratosController):</h3>";
        echo "Tipo: {$datosContrato['tipo_contrato']}<br>";
        echo "Fecha Inicio (string): {$datosContrato['fecha_inicio_contrato']}<br>";
        echo "Fecha Fin (string): " . ($datosContrato['fecha_fin_contrato'] ?? 'NULL') . "<br>";
        echo "<hr>";
        
        // 4. ✅ SIMULAR procesarDatosContrato()
        $request = (object) $datosContrato;
        
        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio_contrato);
        $tipoContrato = $request->tipo_contrato;
        
        $datosProcesados = [
            'tipo_contrato' => $tipoContrato,
            'fecha_inicio' => $fechaInicio,
            'salario_texto' => 'CUATROCIENTOS CINCUENTA PESOS'
        ];

        if ($tipoContrato === 'determinado') {
            $fechaFin = \Carbon\Carbon::parse($request->fecha_fin_contrato);
            $datosProcesados['fecha_fin'] = $fechaFin;
            $datosProcesados['duracion_texto'] = '1 mes';
        }
        
        echo "<h3>🔧 DATOS PROCESADOS (como ContratoController):</h3>";
        echo "Tipo: {$datosProcesados['tipo_contrato']}<br>";
        echo "Fecha Inicio (Carbon): {$datosProcesados['fecha_inicio']->format('d/m/Y H:i:s')}<br>";
        echo "Fecha Fin (Carbon): " . (isset($datosProcesados['fecha_fin']) ? $datosProcesados['fecha_fin']->format('d/m/Y H:i:s') : 'NULL') . "<br>";
        echo "<hr>";
        
        // 5. ✅ PROBAR VARIABLES DIRECTAMENTE
        echo "<h3>🧪 TEST VARIABLES:</h3>";
        
        $variableInicio = \App\Models\VariableContrato::where('nombre_variable', 'contrato_fecha_inicio')->first();
        $variableFin = \App\Models\VariableContrato::where('nombre_variable', 'contrato_fecha_fin')->first();
        
        if ($variableInicio) {
            try {
                $resultadoInicio = $variableInicio->obtenerValor($trabajador, $datosProcesados);
                echo "<strong>contrato_fecha_inicio:</strong> '{$resultadoInicio}'<br>";
            } catch (\Exception $e) {
                echo "<strong>contrato_fecha_inicio:</strong> ❌ ERROR: {$e->getMessage()}<br>";
            }
        }
        
        if ($variableFin) {
            try {
                $resultadoFin = $variableFin->obtenerValor($trabajador, $datosProcesados);
                echo "<strong>contrato_fecha_fin:</strong> '{$resultadoFin}'<br>";
            } catch (\Exception $e) {
                echo "<strong>contrato_fecha_fin:</strong> ❌ ERROR: {$e->getMessage()}<br>";
            }
        }
        
        echo "<hr>";
        
        // 6. ✅ TEST MANUAL DIRECTO
        echo "<h3>🔧 TEST MANUAL DIRECTO:</h3>";
        
        $fecha_inicio = $datosProcesados['fecha_inicio'];
        $fecha_fin = $datosProcesados['fecha_fin'] ?? null;
        
        if ($fecha_inicio) {
            $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"];
            $fechaInicioManual = $fecha_inicio->format("d") . " de " . $meses[(int)$fecha_inicio->format("n")] . " del " . $fecha_inicio->format("Y");
            echo "Test manual fecha_inicio: '{$fechaInicioManual}'<br>";
        }
        
        if ($fecha_fin) {
            $meses = [1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"];
            $fechaFinManual = $fecha_fin->format("d") . " de " . $meses[(int)$fecha_fin->format("n")] . " del " . $fecha_fin->format("Y");
            echo "Test manual fecha_fin: '{$fechaFinManual}'<br>";
        }
        
        echo "<hr>";
        echo "<h3>🎯 DIAGNÓSTICO:</h3>";
        echo "<p><strong>Si las fechas del 'TEST MANUAL' son correctas pero las 'VARIABLES' no:</strong><br>";
        echo "→ Problema en el código de las variables en la BD</p>";
        echo "<p><strong>Si ambas muestran fechas incorrectas:</strong><br>";
        echo "→ Problema en el flujo de datos entre controladores</p>";
        echo "<p><strong>Si todo se ve correcto aquí pero el PDF muestra fechas incorrectas:</strong><br>";
        echo "→ Problema en el cache de plantillas o variables</p>";
        
    });
});