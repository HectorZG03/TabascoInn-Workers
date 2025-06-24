<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ActPerfilTrabajadorController;
use App\Http\Controllers\DespidosController;
use App\Http\Controllers\PermisosLaboralesController;
use App\Http\Controllers\FormatoPermisosController;
use App\Http\Controllers\BusquedaTrabajadoresController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminContratosController; 
use Illuminate\Support\Facades\Route;

// Redirigir la ruta raíz al login
Route::get('/', function () {
    return redirect('/login');
});

/// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
 
    // ✅ RUTAS DE CONFIGURACIÓN DE USUARIO
    Route::prefix('configuracion')->name('users.')->group(function () {
        // Menú principal de configuración
        Route::get('/', [UserController::class, 'configMenu'])->name('config');
        
        // Gestión de usuarios (solo para gerencia)
        Route::middleware('check.gerencia')->group(function () {
            Route::get('/usuarios', [UserController::class, 'manageUsers'])->name('manage');
            Route::get('/sistema', [UserController::class, 'systemConfig'])->name('system.config');
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

    // ✅ NUEVA: RUTA PRINCIPAL DE ADMINISTRACIÓN DE CONTRATOS
    Route::prefix('contratos')->name('contratos.')->group(function () {
        // Vista principal de administración de todos los contratos
        Route::get('/administracion', [AdminContratosController::class, 'index'])->name('admin.index');
    });

    // ✅ RUTAS DE IMPORTACIÓN MASIVA
    Route::prefix('import')->name('import.')->group(function () {
        // Descargar plantilla Excel
        Route::get('/plantilla', [ImportController::class, 'descargarPlantilla'])->name('plantilla');
        
        // Procesar importación masiva
        Route::post('/procesar', [ImportController::class, 'importarTrabajadores'])->name('procesar');
    });

    // ✅ RUTAS DE TRABAJADORES - Gestión General
    Route::prefix('trabajadores')->name('trabajadores.')->group(function () {
        // Lista de trabajadores (index)
        Route::get('/', [TrabajadorController::class, 'index'])->name('index');
    
        // Formulario para crear nuevo trabajador
        Route::get('/crear', [TrabajadorController::class, 'create'])->name('create');
    
        // Guardar nuevo trabajador
        Route::post('/', [TrabajadorController::class, 'store'])->name('store');
    
        // Ver trabajador específico (vista básica)
        Route::get('/{trabajador}', [TrabajadorController::class, 'show'])->name('show');
    
        // Formulario para editar trabajador
        Route::get('/{trabajador}/editar', [TrabajadorController::class, 'edit'])->name('edit');
    
        // Actualizar trabajador
        Route::put('/{trabajador}', [TrabajadorController::class, 'update'])->name('update');
    
        // Dar de baja trabajador
        Route::delete('/{trabajador}', [TrabajadorController::class, 'destroy'])->name('destroy');

        // ✅ RUTAS DE DESPIDOS
        Route::post('/{trabajador}/despedir', [DespidosController::class, 'store'])->name('despedir');

        // ✅ RUTAS DE PERMISOS LABORALES
        Route::post('/{trabajador}/permisos', [PermisosLaboralesController::class, 'store'])->name('permisos.store');

        // ✅ NUEVA RUTA PARA VER HISTORIAL COMPLETO - MOVIDA AQUÍ FUERA DEL GRUPO PERFIL
        Route::get('/{trabajador}/historial-promociones', [ActPerfilTrabajadorController::class, 'verHistorialCompleto'])
            ->name('historial-promociones');

       
    // ✅ RUTAS DE ADMINISTRACIÓN DE CONTRATOS - ACTUALIZADAS
    Route::prefix('{trabajador}/contratos')->name('contratos.')->group(function () {
        // Mostrar contratos del trabajador (vista principal)
        Route::get('/', [AdminContratosController::class, 'show'])->name('show');
        
        // ✅ NUEVO: Crear contrato (formulario)
        Route::get('/crear', [AdminContratosController::class, 'create'])->name('crear.form');
        
        // ✅ NUEVO: Crear contrato (procesar)
        Route::post('/crear', [AdminContratosController::class, 'store'])->name('crear');
        
        // ✅ CORREGIDO: Vista del formulario de renovación (GET)
        Route::get('/{contrato}/renovar', [AdminContratosController::class, 'mostrarRenovacion'])->name('renovar.form');
        
        // ✅ NUEVO: Procesar renovación (POST)
        Route::post('/{contrato}/renovar', [AdminContratosController::class, 'renovar'])->name('renovar');
        
        // Descargar contrato específico (existente)
        Route::get('/{contrato}/descargar', [AdminContratosController::class, 'descargar'])->name('descargar');
        
        // ✅ ACTUALIZADO: Eliminar contrato permanentemente (cambiado de terminar)
        Route::delete('/{contrato}/eliminar', [AdminContratosController::class, 'eliminar'])->name('eliminar');
        
        // ✅ NUEVO: API para verificar si puede crear contrato
        Route::get('/api/verificar-creacion', [AdminContratosController::class, 'verificarCreacion'])->name('api.verificar');
        
        // API: Obtener resumen de contratos (existente)
        Route::get('/api/resumen', [AdminContratosController::class, 'obtenerResumen'])->name('api.resumen');
    });

    
        // ✅ RUTAS DEL PERFIL AVANZADO - Controlador Sep   arado
        Route::prefix('{trabajador}/perfil')->name('perfil.')->group(function () {
            // Mostrar perfil completo
            Route::get('/', [ActPerfilTrabajadorController::class, 'show'])->name('show');
        
            // Actualizar datos personales
            Route::put('/datos', [ActPerfilTrabajadorController::class, 'updateDatos'])->name('update-datos');
        
            // Actualizar datos laborales (ficha técnica)
            Route::put('/ficha-tecnica', [ActPerfilTrabajadorController::class, 'updateFichaTecnica'])->name('update-ficha');
        
            // Subir documento
            Route::post('/documentos', [ActPerfilTrabajadorController::class, 'uploadDocument'])->name('upload-document');
        
            // Eliminar documento
            Route::delete('/documentos', [ActPerfilTrabajadorController::class, 'deleteDocument'])->name('delete-document');
        
            // API para categorías por área (AJAX) - Específico para perfil
            Route::get('/areas/{area}/categorias', [ActPerfilTrabajadorController::class, 'getCategoriasPorArea'])->name('categorias');
        });
    });

    // ✅ RUTAS PARA EL SISTEMA DE DESPIDOS ACTUALIZADO
    // Agrega estas rutas en tu archivo routes/web.php

    // Rutas principales de despidos
    Route::prefix('despidos')->name('despidos.')->group(function () {
        // Listar todas las bajas (con filtros por estado)
        Route::get('/', [DespidosController::class, 'index'])->name('index');
        
        // Ver detalles de una baja específica
        Route::get('/{despido}', [DespidosController::class, 'show'])->name('show');
        
        // Cancelar/revertir un despido (mantiene historial)
        Route::delete('/{despido}/cancelar', [DespidosController::class, 'cancelar'])->name('cancelar');
        
        // Estadísticas para dashboard
        Route::get('/api/estadisticas', [DespidosController::class, 'estadisticas'])->name('estadisticas');
    });

    // Rutas de despidos relacionadas con trabajadores
    Route::prefix('trabajadores')->name('trabajadores.')->group(function () {
        // Mostrar formulario de despido
        Route::get('/{trabajador}/despedir', [DespidosController::class, 'create'])->name('despedir.create');
        
        // Procesar despido
        Route::post('/{trabajador}/despedir', [DespidosController::class, 'store'])->name('despedir.store');
        
        // ✅ NUEVO: Obtener historial completo de bajas de un trabajador
        Route::get('/{trabajador}/historial-despidos', [DespidosController::class, 'historial'])->name('historial.despidos');
    });

   // ✅ RUTAS DE GESTIÓN DE PERMISOS LABORALES - REFACTORIZADAS
Route::prefix('permisos')->name('permisos.')->group(function () {
    // Lista de permisos y suspensiones
    Route::get('/', [PermisosLaboralesController::class, 'index'])->name('index');
    
    // Ver detalles de un permiso específico
    Route::get('/{permiso}', [PermisosLaboralesController::class, 'show'])->name('show');
    
    // Finalizar permiso/suspensión anticipadamente
    Route::patch('/{permiso}/finalizar', [PermisosLaboralesController::class, 'finalizar'])->name('finalizar');
    
    // Cancelar permiso/suspensión (eliminar y reactivar)
    Route::delete('/{permiso}/cancelar', [PermisosLaboralesController::class, 'cancelar'])->name('cancelar');
    
    // ✅ NUEVAS RUTAS PARA GESTIÓN DE PDFs
    Route::prefix('{permiso}/pdf')->name('pdf.')->controller(FormatoPermisosController::class)->group(function () {
        // Generar y descargar PDF del permiso
        Route::get('/generar', 'generarPDF')->name('generar');
        
        // Descargar PDF existente (si no existe, lo genera)
        Route::get('/descargar', 'descargarPDF')->name('descargar');
        
        // Regenerar PDF (elimina el anterior y crea uno nuevo)
        Route::post('/regenerar', 'regenerarPDF')->name('regenerar');
    });
    
    // ✅ RUTA DIRECTA PARA COMPATIBILIDAD (la que ya está referenciada en la vista)
    Route::get('/{permiso}/pdf', [FormatoPermisosController::class, 'generarPDF'])->name('pdf');
    
    // API para estadísticas
    Route::get('/api/estadisticas', [PermisosLaboralesController::class, 'estadisticas'])->name('estadisticas');
    
    // ✅ NUEVA: API para obtener motivos según tipo de permiso
    Route::get('/api/motivos-por-tipo', [PermisosLaboralesController::class, 'getMotivosPorTipo'])
        ->name('api.motivos-por-tipo');
    
    // Verificar permisos vencidos (tarea programada)
    Route::post('/verificar-vencidos', [PermisosLaboralesController::class, 'verificarVencidos'])->name('verificar-vencidos');
});

    // ✅ API GENERAL para categorías (para otros formularios)
    Route::get('/api/categorias/{area}', [TrabajadorController::class, 'getCategoriasPorArea'])->name('api.categorias');

    // RUTA OPCIONAL PARA OBTENER TODOS LOS MOTIVOS
    Route::get('/api/motivos', function() {
        return response()->json([

            'todos' => \App\Models\PermisosLaborales::getTodosLosMotivos()
        ]);
    })->name('api.motivos')->middleware('auth');

    // Rutas AJAX para contratos durante creación
    Route::prefix('ajax/contratos')->name('ajax.contratos.')->group(function () {
        // Generar preview del contrato (sin guardar trabajador aún)
        Route::post('/preview', [ContratoController::class, 'generarPreview'])->name('preview');
        
        // Descargar contrato temporal
        Route::get('/preview-download/{hash}', [ContratoController::class, 'descargarPreview'])->name('preview.download');

         // Nueva ruta para generar preview
        Route::post('/preview', [ContratoController::class, 'generarPreview'])
            ->name('preview');
        });

    
});