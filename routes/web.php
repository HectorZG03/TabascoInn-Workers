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
use App\Http\Controllers\BusquedaTrabajadoresController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminContratosController; 
use App\Http\Controllers\EstadisticasController; // ✅ NUEVO CONTROLADOR
use App\Http\Controllers\VacacionesController; // ✅ NUEVO CONTROLADOR
use App\Http\Controllers\DocumentosVacacionesController; // ✅ NUEVO CONTROLADOR
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
        // En tu web.php, dentro del grupo de configuración:
        Route::get('/areas-categorias/estadisticas', [AreaCategoriaController::class, 'estadisticas'])->name('areas.categorias.estadisticas');
        Route::delete('/categorias/multiple', [AreaCategoriaController::class, 'destroyMultipleCategories'])
            ->name('categorias.multiple.destroy');
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
            
            // Descargar PDF de amortización (sin guardar en BD)
            Route::get('/descargar-pdf', [DocumentosVacacionesController::class, 'descargarPDF'])
                ->name('descargar-pdf');
            
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
    
});