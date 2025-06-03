<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ActPerfilTrabajadorController;
use App\Http\Controllers\DespidosController;
use App\Http\Controllers\PermisosLaboralesController;
use App\Http\Controllers\BusquedaTrabajadoresController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
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
        
        // Perfil personal
        Route::get('/perfil', [UserController::class, 'profile'])->name('profile');
        Route::put('/perfil', [UserController::class, 'updateProfile'])->name('profile.update');
        
        // Cambio de contraseña
        Route::get('/seguridad', [UserController::class, 'changePassword'])->name('change-password');
        Route::put('/seguridad', [UserController::class, 'updatePassword'])->name('password.update');
        
        // Preferencias del usuario
        Route::get('/preferencias', [UserController::class, 'preferences'])->name('preferences');
        Route::put('/preferencias', [UserController::class, 'updatePreferences'])->name('preferences.update');
        
        // Actividad reciente
        Route::get('/actividad', [UserController::class, 'activity'])->name('activity');
        
        // Gestión de usuarios (solo para gerencia)
        Route::middleware('check.gerencia')->group(function () {
            Route::get('/usuarios', [UserController::class, 'manageUsers'])->name('manage');
            Route::get('/sistema', [UserController::class, 'systemConfig'])->name('system.config');
        });
    });

    // ✅ RUTAS DE AYUDA
    Route::prefix('ayuda')->name('help.')->group(function () {
        Route::get('/', [UserController::class, 'helpIndex'])->name('index');
        Route::get('/manual', [UserController::class, 'manual'])->name('manual');
        Route::get('/soporte', [UserController::class, 'support'])->name('support');
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
       
        // ✅ RUTAS DEL PERFIL AVANZADO - Controlador Separado
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

    // ✅ RUTAS DE GESTIÓN DE DESPIDOS
    Route::prefix('despidos')->name('despidos.')->group(function () {
        // Lista de despidos
        Route::get('/', [DespidosController::class, 'index'])->name('index');
        
        // Ver detalles de un despido específico
        Route::get('/{despido}', [DespidosController::class, 'show'])->name('show');
        
        // Cancelar despido (reactivar trabajador)
        Route::delete('/{despido}/cancelar', [DespidosController::class, 'cancelar'])->name('cancelar');
        
        // API para estadísticas
        Route::get('/api/estadisticas', [DespidosController::class, 'estadisticas'])->name('estadisticas');
    });

    // ✅ RUTAS DE GESTIÓN DE PERMISOS LABORALES
    Route::prefix('permisos')->name('permisos.')->group(function () {
        // Lista de permisos laborales
        Route::get('/', [PermisosLaboralesController::class, 'index'])->name('index');
        
        // Ver detalles de un permiso específico
        Route::get('/{permiso}', [PermisosLaboralesController::class, 'show'])->name('show');
        
        // Finalizar permiso anticipadamente
        Route::patch('/{permiso}/finalizar', [PermisosLaboralesController::class, 'finalizar'])->name('finalizar');
        
        // Cancelar permiso (eliminar y reactivar)
        Route::delete('/{permiso}/cancelar', [PermisosLaboralesController::class, 'cancelar'])->name('cancelar');
        
        // API para estadísticas
        Route::get('/api/estadisticas', [PermisosLaboralesController::class, 'estadisticas'])->name('estadisticas');
        
        // Verificar permisos vencidos (tarea programada)
        Route::post('/verificar-vencidos', [PermisosLaboralesController::class, 'verificarVencidos'])->name('verificar-vencidos');
    });
   
    // ✅ API GENERAL para categorías (para otros formularios)
    Route::get('/api/categorias/{area}', [TrabajadorController::class, 'getCategoriasPorArea'])->name('api.categorias');
   
});