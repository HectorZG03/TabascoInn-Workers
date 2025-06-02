<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ActPerfilTrabajadorController;
use App\Http\Controllers\DespidosController;
use App\Http\Controllers\PermisosLaboralesController;
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
   
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
   
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