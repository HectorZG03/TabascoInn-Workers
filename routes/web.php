<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ActPerfilTrabajadorController;
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
        
        // ✅ RUTAS DEL PERFIL AVANZADO - Controlador Separado
        Route::prefix('{trabajador}/perfil')->name('perfil.')->group(function () {
            // Mostrar perfil completo
            Route::get('/', [ActPerfilTrabajadorController::class, 'show'])->name('show');
            
            // Actualizar datos personales
            Route::put('/datos', [ActPerfilTrabajadorController::class, 'updateDatos'])->name('update-datos');
            
            // Actualizar datos laborales (ficha técnica)
            Route::put('/ficha-tecnica', [ActPerfilTrabajadorController::class, 'updateFichaTecnica'])->name('update-ficha');
            
            // Cambiar estado del trabajador
            Route::put('/estado', [ActPerfilTrabajadorController::class, 'updateEstado'])->name('update-estado');
            
            // Subir documento
            Route::post('/documentos', [ActPerfilTrabajadorController::class, 'uploadDocument'])->name('upload-document');
            
            // Eliminar documento
            Route::delete('/documentos', [ActPerfilTrabajadorController::class, 'deleteDocument'])->name('delete-document');
            
            // API para categorías por área (AJAX) - Específico para perfil
            Route::get('/areas/{area}/categorias', [ActPerfilTrabajadorController::class, 'getCategoriasPorArea'])->name('categorias');
        });
    });
    
    // ✅ API GENERAL para categorías (para otros formularios)
    Route::get('/api/categorias/{area}', [TrabajadorController::class, 'getCategoriasPorArea'])->name('api.categorias');
    
});