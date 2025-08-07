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
use App\Http\Controllers\ExportacionController; 
use App\Http\Controllers\DiasAntiguedadController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminContratosController; 
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\VacacionesController;
use App\Http\Controllers\DocumentosVacacionesController;
use App\Http\Controllers\GestionUsuariosOperativosController;
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

    // ✅ RUTAS DE GESTIÓN DE USUARIOS OPERATIVOS (Solo para Gerencia y RRHH)
    Route::prefix('usuarios-operativos')->name('usuarios.operativos.')->middleware('check.user.type:Gerencia,Recursos_Humanos')->group(function () {
        Route::get('/', [GestionUsuariosOperativosController::class, 'listaUsuarios'])->name('lista');
        Route::get('/crear', [GestionUsuariosOperativosController::class, 'formularioCrear'])->name('crear');
        Route::post('/crear', [GestionUsuariosOperativosController::class, 'crearUsuario'])->name('guardar');
        Route::get('/{id}/editar', [GestionUsuariosOperativosController::class, 'formularioEditar'])->name('editar');
        Route::put('/{id}', [GestionUsuariosOperativosController::class, 'actualizarUsuario'])->name('actualizar');
        Route::patch('/{id}/estado', [GestionUsuariosOperativosController::class, 'cambiarEstado'])->name('cambiar-estado');
        Route::delete('/{id}', [GestionUsuariosOperativosController::class, 'eliminarUsuario'])->name('eliminar');
    });

    Route::prefix('configuracion')->group(function () {
        // ✅ ÁREAS Y CATEGORÍAS CON PERMISOS
        Route::middleware('check.permiso:areas_categorias')->group(function () {
            Route::get('/areas-categorias', [AreaCategoriaController::class, 'index'])->name('areas.categorias.index');
            Route::get('/areas-categorias/estadisticas', [AreaCategoriaController::class, 'estadisticas'])->name('areas.categorias.estadisticas');
            Route::get('/api/departamentos/{departamento}/areas', [AreaCategoriaController::class, 'getAreasPorDepartamento'])->name('api.departamentos.areas');
            
            // Crear - requiere permiso crear
            Route::middleware('check.permiso:areas_categorias,crear')->group(function () {
                Route::post('/areas', [AreaCategoriaController::class, 'storeArea'])->name('areas.store');
                Route::post('/categorias', [AreaCategoriaController::class, 'storeCategoria'])->name('categorias.store');
                Route::post('/departamentos', [AreaCategoriaController::class, 'storeDepartamento'])->name('departamentos.store');
            });
            
            // Editar - requiere permiso editar
            Route::middleware('check.permiso:areas_categorias,editar')->group(function () {
                Route::put('/areas/{area}', [AreaCategoriaController::class, 'updateArea'])->name('areas.update');
                Route::put('/categorias/{categoria}', [AreaCategoriaController::class, 'updateCategoria'])->name('categorias.update');
                Route::put('/departamentos/{departamento}', [AreaCategoriaController::class, 'updateDepartamento'])->name('departamentos.update');
            });
            
            // Eliminar - requiere permiso eliminar
            Route::middleware('check.permiso:areas_categorias,eliminar')->group(function () {
                Route::delete('/areas/{area}', [AreaCategoriaController::class, 'destroyArea'])->name('areas.destroy');
                Route::delete('/categorias/{categoria}', [AreaCategoriaController::class, 'destroyCategoria'])->name('categorias.destroy');
                Route::delete('/categorias/multiple', [AreaCategoriaController::class, 'destroyMultipleCategories'])->name('categorias.multiple.destroy');
                Route::delete('/departamentos/{departamento}', [AreaCategoriaController::class, 'destroyDepartamento'])->name('departamentos.destroy');
            });
        });

        // ✅ GERENTES CON PERMISOS
        Route::prefix('gerentes')->name('gerentes.')->controller(GerenteController::class)
            ->middleware('check.permiso:gerentes')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/api/lista', 'apiGerentes')->name('api.lista');
                
                Route::middleware('check.permiso:gerentes,crear')->group(function () {
                    Route::post('/', 'store')->name('store');
                });
                
                Route::middleware('check.permiso:gerentes,editar')->group(function () {
                    Route::put('/{gerente}', 'update')->name('update');
                    Route::patch('/{gerente}/toggle-estatus', 'toggleEstatus')->name('toggle-estatus');
                });
                
                Route::middleware('check.permiso:gerentes,eliminar')->group(function () {
                    Route::delete('/{gerente}', 'destroy')->name('destroy');
                });
        });

        // ✅ PLANTILLAS DE CONTRATO CON PERMISOS
        Route::prefix('plantillas-contrato')->name('configuracion.plantillas.')
            ->controller(PlantillaContratoController::class)
            ->middleware('check.permiso:plantillas_contrato')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/api/variables', 'obtenerVariables')->name('api.variables');
                Route::get('/exportar/{tipo?}', 'exportar')->name('exportar');
                Route::get('/{plantilla}', 'show')->name('show');
                Route::post('/preview', 'preview')->name('preview');
                
                Route::middleware('check.permiso:plantillas_contrato,crear')->group(function () {
                    Route::get('/crear', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                });
                
                Route::middleware('check.permiso:plantillas_contrato,editar')->group(function () {
                    Route::get('/{plantilla}/editar', 'edit')->name('edit');
                    Route::put('/{plantilla}', 'update')->name('update');
                    Route::patch('/{plantilla}/toggle', 'toggleActivacion')->name('toggle');
                });
                
                Route::middleware('check.permiso:plantillas_contrato,eliminar')->group(function () {
                    Route::delete('/{plantilla}', 'destroy')->name('destroy');
                });
        });
        
        // ✅ VARIABLES DE CONTRATO (sin permisos específicos, solo administradores)
        Route::prefix('variables-contrato')->name('variables.')
            ->controller(VariableContratoController::class)
            ->middleware('check.user.type:Gerencia,Recursos_Humanos')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{variable}', 'update')->name('update');
                Route::patch('/{variable}/toggle', 'toggleEstado')->name('toggle');
                Route::delete('/{variable}', 'destroy')->name('destroy');
        });

        // ✅ DÍAS POR ANTIGÜEDAD CON PERMISOS
        Route::prefix('configuracion/dias-antiguedad')->name('configuracion.dias_antiguedad.')
            ->middleware('check.permiso:dias_antiguedad')->group(function () {
                Route::get('/', [DiasAntiguedadController::class, 'index'])->name('index');
                
                Route::middleware('check.permiso:dias_antiguedad,crear')->group(function () {
                    Route::post('/', [DiasAntiguedadController::class, 'store'])->name('store');
                });
                
                Route::middleware('check.permiso:dias_antiguedad,editar')->group(function () {
                    Route::put('/{diaAntiguedad}', [DiasAntiguedadController::class, 'update'])->name('update');
                });
                
                Route::middleware('check.permiso:dias_antiguedad,eliminar')->group(function () {
                    Route::delete('/{diaAntiguedad}', [DiasAntiguedadController::class, 'destroy'])->name('destroy');
                });
        });
    });
    
    // ✅ BÚSQUEDA DE TRABAJADORES CON PERMISOS
    Route::middleware('check.permiso:trabajadores')->group(function () {
        Route::get('/trabajadores/buscar', [BusquedaTrabajadoresController::class, 'index'])->name('trabajadores.buscar');
        Route::get('/api/trabajadores/busqueda-rapida', [BusquedaTrabajadoresController::class, 'busquedaRapida'])->name('trabajadores.busqueda.rapida');
        Route::get('/api/trabajadores/sugerencias', [BusquedaTrabajadoresController::class, 'sugerencias'])->name('trabajadores.sugerencias');
        Route::get('/api/trabajadores/estadisticas', [BusquedaTrabajadoresController::class, 'estadisticas'])->name('trabajadores.estadisticas');
    });
    
    // Dashboard - accesible para todos los usuarios autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ✅ TRABAJADORES CON PERMISOS REFINADOS Y ORDEN CORRECTO
    // ✅ TRABAJADORES CON PERMISOS REFINADOS Y ORDEN CORRECTO
    Route::prefix('trabajadores')->name('trabajadores.')->group(function () {
        
        // ✅ 1. PRIMERO: RUTAS ESPECÍFICAS (sin parámetros dinámicos)
        
        // Lista de trabajadores - requiere solo "ver"
        Route::get('/', [TrabajadorController::class, 'index'])
            ->middleware('check.permiso:trabajadores')
            ->name('index');
        
            Route::get('/exportar', [ExportacionController::class, 'exportar'])
            ->middleware('check.permiso:trabajadores')
            ->name('exportar');
        
        // Crear trabajador - requiere "crear" 
        Route::get('/crear', [TrabajadorController::class, 'create'])
            ->middleware('check.permiso:trabajadores,crear')
            ->name('create');
            
        // Guardar trabajador - requiere "crear"
        Route::post('/', [TrabajadorController::class, 'store'])
            ->middleware('check.permiso:trabajadores,crear')
            ->name('store');
        
        // Buscar trabajadores - requiere "ver"
        Route::get('/buscar', [BusquedaTrabajadoresController::class, 'index'])
            ->middleware('check.permiso:trabajadores')
            ->name('buscar');
        
        // ✅ 2. SEGUNDO: RUTAS CON PARÁMETROS DINÁMICOS
        
        // Ver perfil de trabajador específico - requiere solo "ver"
        Route::get('/{trabajador}', [TrabajadorController::class, 'show'])
            ->middleware('check.permiso:trabajadores')
            ->name('show');
        
        // Eliminar trabajador - requiere "eliminar"
        Route::delete('/{trabajador}', [TrabajadorController::class, 'destroy'])
            ->middleware('check.permiso:trabajadores,eliminar')
            ->name('destroy');

        // ✅ 3. RUTAS DEL PERFIL - VISUALIZACIÓN (requiere solo "ver")
        Route::prefix('{trabajador}/perfil')->name('perfil.')->middleware('check.permiso:trabajadores')->group(function () {
            Route::get('/', [ActPerfilTrabajadorController::class, 'show'])->name('show');
            Route::get('/areas/{area}/categorias', [ActPerfilTrabajadorController::class, 'getCategoriasPorArea'])->name('categorias');
        });
        
        // ✅ 4. RUTAS DEL PERFIL - EDICIÓN (requieren "editar")
        Route::prefix('{trabajador}/perfil')->name('perfil.')->middleware('check.permiso:trabajadores,editar')->group(function () {
            Route::put('/datos', [ActPerfilTrabajadorController::class, 'updateDatos'])->name('update-datos');
            Route::put('/ficha-tecnica', [ActPerfilTrabajadorController::class, 'updateFichaTecnica'])->name('update-ficha');
            Route::post('/documentos', [ActPerfilTrabajadorController::class, 'uploadDocument'])->name('upload-document');
            Route::delete('/documentos', [ActPerfilTrabajadorController::class, 'deleteDocument'])->name('delete-document');
            Route::put('/estatus', [ActPerfilTrabajadorController::class, 'updateEstatus'])->name('update-estatus');
        });

        // ✅ 5. HISTORIAL DE PROMOCIONES - requiere solo "ver"
        Route::get('/{trabajador}/historial-promociones', [ActPerfilTrabajadorController::class, 'verHistorialCompleto'])
            ->middleware('check.permiso:trabajadores')
            ->name('historial-promociones');

        // ✅ 6. HISTORIALES CON PERMISOS ESPECÍFICOS
        Route::prefix('{trabajador}')->name('perfil.')->group(function () {
            Route::get('/permisos/historial', [HistorialesPerfilController::class, 'permisos'])
                ->middleware('check.permiso:permisos_laborales')->name('permisos.historial');
            Route::get('/bajas/historial', [HistorialesPerfilController::class, 'bajas'])
                ->middleware('check.permiso:despidos')->name('bajas.historial');
        });

        // ✅ 7. **AGREGAR ESTAS RUTAS AQUÍ** - PERMISOS LABORALES (CREAR)
        Route::prefix('{trabajador}')->middleware('check.permiso:permisos_laborales')->group(function () {
            // Mostrar formulario para crear permiso
            Route::get('/permisos/crear', [PermisosLaboralesController::class, 'create'])
                ->middleware('check.permiso:permisos_laborales,crear')
                ->name('permisos.crear');
                
            // Procesar creación de permiso
            Route::post('/permisos', [PermisosLaboralesController::class, 'store'])
                ->middleware('check.permiso:permisos_laborales,crear')
                ->name('permisos.store');
        });

        // ✅ 8. **AGREGAR ESTAS RUTAS AQUÍ** - DESPIDOS (CREAR)
        Route::prefix('{trabajador}')->middleware('check.permiso:despidos')->group(function () {
            // Mostrar formulario para crear despido
            Route::get('/despedir', [DespidosController::class, 'create'])
                ->middleware('check.permiso:despidos,crear')
                ->name('despedir.crear');
                
            // Procesar creación de despido
            Route::post('/despedir', [DespidosController::class, 'store'])
                ->middleware('check.permiso:despidos,crear')
                ->name('despedir.store');
        });

        // ✅ 9. CONTRATOS CON PERMISOS ESPECÍFICOS
        Route::prefix('{trabajador}/contratos')->name('contratos.')
            ->controller(AdminContratosController::class)
            ->middleware('check.permiso:contratos')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/{contrato}/descargar', 'descargar')->name('descargar');
                Route::get('/api/verificar-creacion', 'verificarCreacion')->name('api.verificar');
                Route::get('/api/resumen', 'obtenerResumen')->name('api.resumen');
                
                Route::middleware('check.permiso:contratos,crear')->group(function () {
                    Route::post('/crear', 'store')->name('crear');
                });
                
                Route::middleware('check.permiso:contratos,editar')->group(function () {
                    Route::post('/{contrato}/renovar', 'renovar')->name('renovar');
                });
                
                Route::middleware('check.permiso:contratos,eliminar')->group(function () {
                    Route::delete('/{contrato}/eliminar', 'eliminar')->name('eliminar');
                });
        });

        // ✅ 10. HORAS EXTRA CON PERMISOS ESPECÍFICOS
        Route::prefix('{trabajador}/horas-extra')->name('horas-extra.')
            ->controller(HorasExtraController::class)
            ->middleware('check.permiso:horas_extra')->group(function () {
                Route::get('saldo', 'obtenerSaldo')->name('saldo');
                Route::get('historial', 'obtenerHistorial')->name('historial');
                Route::get('estadisticas', 'obtenerEstadisticas')->name('estadisticas');
                
                Route::middleware('check.permiso:horas_extra,editar')->group(function () {
                    Route::post('asignar', 'asignar')->name('asignar');
                    Route::post('restar', 'restar')->name('restar');
                });
        });

        // ✅ 11. VACACIONES CON PERMISOS ESPECÍFICOS
        Route::prefix('{trabajador}/vacaciones')->name('vacaciones.')
            ->middleware('check.permiso:vacaciones')->group(function () {
                Route::get('/', [VacacionesController::class, 'show'])->name('show');
                Route::get('/api', [VacacionesController::class, 'index'])->name('api.index');
                Route::get('/estadisticas', [VacacionesController::class, 'estadisticas'])->name('estadisticas');
                Route::get('/calcular-dias', [VacacionesController::class, 'calcularDias'])->name('calcular-dias');
                Route::post('/calcular-fechas', [VacacionesController::class, 'calcularFechasVacaciones'])->name('calcular-fechas');
                
                Route::middleware('check.permiso:vacaciones,crear')->group(function () {
                    Route::post('/asignar', [VacacionesController::class, 'store'])->name('asignar');
                });
                
                Route::middleware('check.permiso:vacaciones,editar')->group(function () {
                    Route::patch('/{vacacion}/iniciar', [VacacionesController::class, 'iniciar'])->name('iniciar');
                    Route::patch('/{vacacion}/finalizar', [VacacionesController::class, 'finalizar'])->name('finalizar');
                });
                
                Route::middleware('check.permiso:vacaciones,eliminar')->group(function () {
                    Route::delete('/{vacacion}/cancelar', [VacacionesController::class, 'cancelar'])->name('cancelar');
                });
        });

        // ✅ 12. DOCUMENTOS DE VACACIONES CON PERMISOS ESPECÍFICOS
        Route::prefix('{trabajador}/documentos-vacaciones')->name('documentos-vacaciones.')
            ->middleware('check.permiso:vacaciones')->group(function () {
                Route::get('/', [DocumentosVacacionesController::class, 'index'])->name('index');
                Route::get('/seleccion-firmas', [DocumentosVacacionesController::class, 'mostrarSeleccionFirmas'])->name('seleccion-firmas');
                Route::post('/descargar-pdf', [DocumentosVacacionesController::class, 'descargarPDF'])->name('descargar-pdf');
                Route::get('/descargar-pdf-directo', [DocumentosVacacionesController::class, 'descargarPDFDirecto'])->name('descargar-pdf-directo');
                Route::get('/api/documentos', [DocumentosVacacionesController::class, 'obtenerDocumentos'])->name('api.documentos');
                
                Route::middleware('check.permiso:vacaciones,crear')->group(function () {
                    Route::post('/subir', [DocumentosVacacionesController::class, 'subirDocumento'])->name('subir');
                });
                
                Route::middleware('check.permiso:vacaciones,eliminar')->group(function () {
                    Route::delete('/{documento}/eliminar', [DocumentosVacacionesController::class, 'eliminarDocumento'])->name('eliminar');
                });
        });

    });
        
    // DETALLES CON PERMISOS ESPECÍFICOS
    Route::get('/trabajadores/permisos/{permiso}/detalle', [HistorialesPerfilController::class, 'detallePermiso'])
        ->middleware('check.permiso:permisos_laborales')->name('trabajadores.permisos.detalle');
    Route::get('/trabajadores/despidos/{despido}/detalle', [HistorialesPerfilController::class, 'detalleBaja'])
        ->middleware('check.permiso:despidos')->name('trabajadores.despidos.detalle');

    // ✅ BÚSQUEDA DE TRABAJADORES CON PERMISOS (APIs)
    Route::middleware('check.permiso:trabajadores')->group(function () {
        Route::get('/api/trabajadores/busqueda-rapida', [BusquedaTrabajadoresController::class, 'busquedaRapida'])->name('trabajadores.busqueda.rapida');
        Route::get('/api/trabajadores/sugerencias', [BusquedaTrabajadoresController::class, 'sugerencias'])->name('trabajadores.sugerencias');
        Route::get('/api/trabajadores/estadisticas', [BusquedaTrabajadoresController::class, 'estadisticas'])->name('trabajadores.estadisticas');
    });

    // ✅ DESPIDOS CON PERMISOS ESPECÍFICOS
    Route::prefix('despidos')->name('despidos.')
        ->controller(DespidosController::class)
        ->middleware('check.permiso:despidos')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{despido}', 'show')->name('show');
            Route::get('/api/estadisticas', 'estadisticas')->name('estadisticas');
            Route::get('/{despido}/detalle', [HistorialesPerfilController::class, 'detalleBaja'])->name('detalle');
            Route::get('/trabajador/{trabajador}/historial', 'historial')->name('trabajador.historial');
            
            Route::middleware('check.permiso:despidos,eliminar')->group(function () {
                Route::delete('/{despido}/cancelar', 'cancelar')->name('cancelar');
            });
    });

    // ✅ PERMISOS LABORALES CON PERMISOS ESPECÍFICOS
    Route::prefix('permisos')->name('permisos.')
        ->middleware('check.permiso:permisos_laborales')->group(function () {
            Route::get('/', [PermisosLaboralesController::class, 'index'])->name('index');
            Route::get('/{permiso}', [PermisosLaboralesController::class, 'show'])->name('show');
            Route::get('/{permiso}/detalle', [HistorialesPerfilController::class, 'detallePermiso'])->name('detalle');
            Route::get('/api/estadisticas', [PermisosLaboralesController::class, 'estadisticas'])->name('estadisticas');
            Route::get('/api/motivos-por-tipo', [PermisosLaboralesController::class, 'getMotivosPorTipo'])->name('api.motivos-por-tipo');
            Route::post('/verificar-vencidos', [PermisosLaboralesController::class, 'verificarVencidos'])->name('verificar-vencidos');
            
            Route::middleware('check.permiso:permisos_laborales,editar')->group(function () {
                Route::patch('/{permiso}/finalizar', [PermisosLaboralesController::class, 'finalizar'])->name('finalizar');
                Route::patch('/{permiso}/cancelar', [PermisosLaboralesController::class, 'cancelar'])->name('cancelar');
                Route::post('/permisos/{permiso}/subir-archivo', [PermisosLaboralesController::class, 'subirArchivo'])->name('subirArchivo');
            });
            
            Route::middleware('check.permiso:permisos_laborales,eliminar')->group(function () {
                Route::delete('/{permiso}/eliminar', [PermisosLaboralesController::class, 'eliminar'])->name('eliminar');
                Route::delete('/{permiso}/cancelar', [PermisosLaboralesController::class, 'eliminar'])->name('cancelar.old');
            });
            
            // PDFs de permisos
            Route::prefix('{permiso}/pdf')->name('pdf.')
                ->controller(FormatoPermisosController::class)->group(function () {
                    Route::get('/generar', 'generarPDF')->name('generar');
                    Route::get('/descargar', 'descargarPDF')->name('descargar');
                    Route::post('/regenerar', 'regenerarPDF')
                        ->middleware('check.permiso:permisos_laborales,editar')->name('regenerar');
            });
            Route::get('/{permiso}/pdf', [FormatoPermisosController::class, 'generarPDF'])->name('pdf');
    });

    // ✅ DESCARGA DE ARCHIVOS DE PERMISOS
    Route::get('/permisos/{id}/descargar', [PermisosLaboralesController::class, 'descargar'])
        ->middleware('check.permiso:permisos_laborales')
        ->name('permisos.descargar');

    // ✅ AJAX PARA CONTRATOS
    Route::prefix('ajax/contratos')->name('ajax.contratos.')
        ->controller(ContratoController::class)
        ->middleware('check.permiso:contratos')->group(function () {
            Route::post('/preview', 'generarPreview')->name('preview');
            Route::get('/preview-download/{hash}', 'descargarPreview')->name('preview.download');
    });

    // ✅ APIs GENERALES - Accesibles para usuarios autenticados
    Route::get('/api/categorias/{area}', [TrabajadorController::class, 'getCategoriasPorArea'])->name('api.categorias');
    Route::get('/api/motivos', function() {
        return response()->json([
            'todos' => \App\Models\PermisosLaborales::getTodosLosMotivos()
        ]);
    })->name('api.motivos');
    
    // ✅ API DE ESTADÍSTICAS - Accesible para usuarios autenticados
    Route::get('/api/estadisticas', [EstadisticasController::class, 'obtenerEstadisticas'])->name('api.estadisticas');
});