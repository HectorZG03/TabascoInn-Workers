<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\DocumentoTrabajador;
use App\Models\HistorialPromocion; // ✅ NUEVA IMPORTACIÓN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ActPerfilTrabajadorController extends Controller
{
    /**
     * Mostrar perfil completo del trabajador ✅ CORREGIDO
     */
    public function show(Trabajador $trabajador)
    {
        $trabajador->load([
            'fichaTecnica.categoria.area', 
            'documentos', 
            'despido',
            'historialPromociones.categoriaAnterior.area',
            'historialPromociones.categoriaNueva.area'
        ]);

        // ✅ Estadísticas generales (tu método existente)
        $stats = $this->calcularEstadisticasOptimizadas($trabajador);

        // ✅ AGREGAR: Estadísticas específicas de promociones
        $statsPromociones = [
            'total_cambios' => $trabajador->historialPromociones()->count(),
            'promociones' => $trabajador->historialPromociones()->where('tipo_cambio', 'promocion')->count(),
            'transferencias' => $trabajador->historialPromociones()->where('tipo_cambio', 'transferencia')->count(),
            'aumentos_sueldo' => $trabajador->historialPromociones()->where('tipo_cambio', 'aumento_sueldo')->count(),
            'reclasificaciones' => $trabajador->historialPromociones()->where('tipo_cambio', 'reclasificacion')->count(),
            'ultimo_cambio' => $trabajador->historialPromociones()->latest('fecha_cambio')->first()
        ];

        // ✅ AGREGAR: Historial reciente (últimos 5 cambios)
        $historialReciente = $trabajador->historialPromociones()
            ->with(['categoriaAnterior', 'categoriaNueva'])
            ->latest('fecha_cambio')
            ->limit(5)
            ->get()
            ->map(function ($promocion) {
                // Calcular diferencia de sueldo
                $promocion->diferencia_sueldo = $promocion->sueldo_nuevo - ($promocion->sueldo_anterior ?? 0);
                
                // Determinar color según el tipo de cambio
                $promocion->color_tipo_cambio = match($promocion->tipo_cambio) {
                    'promocion' => 'success',
                    'transferencia' => 'primary',
                    'aumento_sueldo' => 'warning',
                    'reclasificacion' => 'info',
                    'ajuste_salarial' => 'secondary',
                    default => 'light'
                };

                // Texto legible del tipo de cambio
                $promocion->tipo_cambio_texto = match($promocion->tipo_cambio) {
                    'promocion' => 'Promoción',
                    'transferencia' => 'Transferencia',
                    'aumento_sueldo' => 'Aumento de Sueldo',
                    'reclasificacion' => 'Reclasificación',
                    'ajuste_salarial' => 'Ajuste Salarial',
                    default => ucfirst(str_replace('_', ' ', $promocion->tipo_cambio))
                };

                return $promocion;
            });

        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        
        if ($trabajador->fichaTecnica && $trabajador->fichaTecnica->categoria) {
            $categorias = Categoria::where('id_area', $trabajador->fichaTecnica->categoria->id_area)
                                ->orderBy('nombre_categoria')
                                ->get();
        }

        // ✅ CORREGIR: Pasar las variables que necesita la vista
        return view('trabajadores.perfil_trabajador', compact(
            'trabajador', 
            'areas', 
            'categorias', 
            'stats',
            'statsPromociones',    // ← NUEVA VARIABLE
            'historialReciente'    // ← NUEVA VARIABLE
        ));
    }

    /**
     * ✅ NUEVO MÉTODO OPTIMIZADO - usar este en lugar del existente
     */
    private function calcularEstadisticasOptimizadas(Trabajador $trabajador): array
    {
        // Cálculos que antes estaban en el modelo
        $edad = $trabajador->fecha_nacimiento 
            ? \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age 
            : null;

        $antiguedad = \Carbon\Carbon::parse($trabajador->fecha_ingreso)->diffInYears(now());
        
        $antiguedadTexto = match($antiguedad) {
            0 => 'Nuevo',
            1 => '1 año',
            default => "$antiguedad años"
        };

        return [
            // ✅ Calculado en controlador (más eficiente)
            'edad' => $edad,
            'antiguedad_texto' => $antiguedadTexto,
            
            // ✅ Mantener desde modelo/relaciones (por ahora)
            'porcentaje_documentos' => $trabajador->documentos?->porcentaje_completado ?? 0,
            'documentos_faltantes' => $trabajador->documentos 
                ? count($trabajador->documentos->documentos_faltantes) 
                : count(DocumentoTrabajador::TODOS_DOCUMENTOS),
            'documentos_basicos_completos' => $trabajador->documentos?->documentos_basicos_completos ?? false,
            'estado_documentos' => $trabajador->documentos?->estado_texto ?? 'Sin documentos',
            'ultima_actualizacion' => $trabajador->updated_at->diffForHumans(),
            'es_nuevo' => $antiguedad === 0,
            
            // ✅ Conteos optimizados
            'total_promociones' => $trabajador->historialPromociones()->count(),
            'ultimo_cambio' => $trabajador->historialPromociones()->first(),
        ];
    }

    /**
     * Actualizar datos básicos del trabajador
     */
    public function updateDatos(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            // Datos personales
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:-18 years',
            'curp' => ['required', 'string', 'size:18', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'rfc' => ['required', 'string', 'size:13', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => ['nullable', 'email', 'max:55', Rule::unique('trabajadores')->ignore($trabajador->id_trabajador, 'id_trabajador')],
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => 'required|date|before_or_equal:today',
        ], [
            'nombre_trabajador.required' => 'El nombre es obligatorio',
            'ape_pat.required' => 'El apellido paterno es obligatorio',
            'fecha_nacimiento.before' => 'El trabajador debe ser mayor de 18 años',
            'curp.size' => 'El CURP debe tener exactamente 18 caracteres',
            'curp.unique' => 'Este CURP ya está registrado',
            'rfc.size' => 'El RFC debe tener exactamente 13 caracteres',
            'rfc.unique' => 'Este RFC ya está registrado',
            'telefono.size' => 'El teléfono debe tener exactamente 10 dígitos',
            'correo.unique' => 'Este correo ya está registrado',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
        ]);

        DB::beginTransaction();
        
        try {
            // Calcular nueva antigüedad si cambió la fecha de ingreso
            $nuevaAntiguedad = (int) Carbon::parse($validated['fecha_ingreso'])->diffInYears(now());

            $trabajador->update([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'curp' => strtoupper($validated['curp']),
                'rfc' => strtoupper($validated['rfc']),
                'no_nss' => $validated['no_nss'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'antiguedad' => $nuevaAntiguedad,
            ]);

            DB::commit();

            Log::info('Datos personales actualizados', [
                'trabajador_id' => $trabajador->id_trabajador,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->with('success', 'Datos personales actualizados exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al actualizar datos personales', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al actualizar los datos: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar datos laborales (ficha técnica) ✅ MÉTODO MODIFICADO
     */
    public function updateFichaTecnica(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            'motivo_cambio' => 'nullable|string|max:255',
            'tipo_cambio' => 'nullable|in:promocion,transferencia,aumento_sueldo,reclasificacion,ajuste_salarial', // ✅ NUEVO CAMPO
        ], [
            'id_area.required' => 'Debe seleccionar un área',
            'id_categoria.required' => 'Debe seleccionar una categoría',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio',
            'sueldo_diarios.min' => 'El sueldo debe ser mayor a 0',
            'tipo_cambio.in' => 'El tipo de cambio seleccionado no es válido',
        ]);

        // Validar que la categoría pertenezca al área
        $categoria = Categoria::where('id_categoria', $validated['id_categoria'])
                             ->where('id_area', $validated['id_area'])
                             ->first();
                             
        if (!$categoria) {
            return back()->withErrors(['id_categoria' => 'La categoría no pertenece al área seleccionada']);
        }

        DB::beginTransaction();
        
        try {
            // ✅ OBTENER DATOS ANTERIORES PARA EL HISTORIAL
            $datosAnteriores = null;
            if ($trabajador->fichaTecnica) {
                $datosAnteriores = [
                    'id_categoria' => $trabajador->fichaTecnica->id_categoria,
                    'sueldo_diarios' => $trabajador->fichaTecnica->sueldo_diarios,
                    'formacion' => $trabajador->fichaTecnica->formacion,
                    'grado_estudios' => $trabajador->fichaTecnica->grado_estudios,
                ];
            }

            // Actualizar o crear ficha técnica
            if ($trabajador->fichaTecnica) {
                $trabajador->fichaTecnica->update([
                    'id_categoria' => $validated['id_categoria'],
                    'sueldo_diarios' => $validated['sueldo_diarios'],
                    'formacion' => $validated['formacion'],
                    'grado_estudios' => $validated['grado_estudios'],
                ]);
                $fichaTecnica = $trabajador->fichaTecnica;
            } else {
                $fichaTecnica = FichaTecnica::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'id_categoria' => $validated['id_categoria'],
                    'sueldo_diarios' => $validated['sueldo_diarios'],
                    'formacion' => $validated['formacion'],
                    'grado_estudios' => $validated['grado_estudios'],
                ]);
            }

            // ✅ REGISTRAR EN HISTORIAL DE PROMOCIONES
            $usuarioActual = Auth::user()->email ?? 'Sistema';
            
            if ($datosAnteriores === null) {
                // Es la primera vez que se crea la ficha técnica
                HistorialPromocion::registrarInicial($trabajador, $fichaTecnica, $usuarioActual);
            } else {
                // Verificar si hubo cambios significativos
                $huboComboio = $this->verificarCambiosSignificativos($datosAnteriores, $validated);
                
                if ($huboComboio) {
                    // ✅ PREPARAR DATOS PARA EL HISTORIAL
                    $datosHistorial = [
                        'id_trabajador' => $trabajador->id_trabajador,
                        'id_categoria_anterior' => $datosAnteriores['id_categoria'],
                        'id_categoria_nueva' => $validated['id_categoria'],
                        'sueldo_anterior' => $datosAnteriores['sueldo_diarios'],
                        'sueldo_nuevo' => $validated['sueldo_diarios'],
                        'motivo' => $validated['motivo_cambio'] ?? 'Actualización de datos laborales',
                        'usuario_cambio' => $usuarioActual,
                        'datos_adicionales' => [
                            'formacion_anterior' => $datosAnteriores['formacion'],
                            'formacion_nueva' => $validated['formacion'],
                            'grado_estudios_anterior' => $datosAnteriores['grado_estudios'],
                            'grado_estudios_nuevo' => $validated['grado_estudios'],
                        ]
                    ];

                    // ✅ USAR TIPO DE CAMBIO MANUAL O AUTOMÁTICO
                    if (!empty($validated['tipo_cambio'])) {
                        // Usuario seleccionó un tipo específico
                        $datosHistorial['tipo_cambio'] = $validated['tipo_cambio'];
                    }
                    // Si no se especifica tipo, se determinará automáticamente en el modelo

                    HistorialPromocion::registrarCambio($datosHistorial);
                }
            }

            DB::commit();

            Log::info('Ficha técnica actualizada', [
                'trabajador_id' => $trabajador->id_trabajador,
                'categoria_anterior' => $datosAnteriores['id_categoria'] ?? null,
                'categoria_nueva' => $validated['id_categoria'],
                'sueldo_anterior' => $datosAnteriores['sueldo_diarios'] ?? null,
                'sueldo_nuevo' => $validated['sueldo_diarios'],
                'usuario' => $usuarioActual
            ]);

            return back()->with('success', 'Datos laborales actualizados exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al actualizar ficha técnica', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al actualizar los datos laborales: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ NUEVO MÉTODO: Verificar si hubo cambios significativos
     */
    private function verificarCambiosSignificativos(array $datosAnteriores, array $datosNuevos): bool
    {
        // Cambio de categoría
        if ($datosAnteriores['id_categoria'] != $datosNuevos['id_categoria']) {
            return true;
        }
        
        // Cambio de sueldo significativo (más de $0.01)
        if (abs($datosAnteriores['sueldo_diarios'] - $datosNuevos['sueldo_diarios']) > 0.01) {
            return true;
        }
        
        return false;
    }

    /**
     * Subir o actualizar documento
     */
    public function uploadDocument(Request $request, Trabajador $trabajador)
    {
        $tipoDocumento = $request->input('tipo_documento');
        
        // Validar tipo de documento
        if (!array_key_exists($tipoDocumento, DocumentoTrabajador::TODOS_DOCUMENTOS)) {
            return back()->withErrors(['error' => 'Tipo de documento no válido']);
        }

        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'documento.required' => 'Debe seleccionar un archivo',
            'documento.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'documento.max' => 'El archivo no debe superar 2MB',
        ]);

        DB::beginTransaction();
        
        try {
            $archivo = $request->file('documento');
            
            // Verificar que el archivo sea válido
            if (!$archivo->isValid()) {
                throw new \Exception('El archivo no es válido');
            }

            // Buscar o crear registro de documentos
            $documentos = $trabajador->documentos;
            if (!$documentos) {
                $documentos = DocumentoTrabajador::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'porcentaje_completado' => 0.00,
                    'documentos_basicos_completos' => false,
                    'estado' => 'incompleto',
                    'fecha_ultima_actualizacion' => now()
                ]);
            }

            // Eliminar archivo anterior si existe
            if (!empty($documentos->$tipoDocumento)) {
                Storage::disk('public')->delete($documentos->$tipoDocumento);
            }

            // Generar nombre del archivo
            $nombreArchivo = $this->generarNombreArchivo($trabajador, $tipoDocumento, $archivo);
            $directorioDestino = "documentos/trabajadores/{$trabajador->id_trabajador}";
            
            // Crear directorio si no existe
            if (!Storage::disk('public')->exists($directorioDestino)) {
                Storage::disk('public')->makeDirectory($directorioDestino);
            }
            
            // Guardar archivo
            $ruta = $archivo->storeAs($directorioDestino, $nombreArchivo, 'public');
            
            if (!$ruta) {
                throw new \Exception('No se pudo guardar el archivo');
            }

            // Actualizar registro de documentos
            $documentos->$tipoDocumento = $ruta;
            $documentos->fecha_ultima_actualizacion = now();
            $documentos->save();

            // Recalcular porcentaje
            $documentos->calcularPorcentaje(true);

            DB::commit();

            Log::info('Documento actualizado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_documento' => $tipoDocumento,
                'archivo' => $nombreArchivo,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->with('success', 'Documento ' . DocumentoTrabajador::TODOS_DOCUMENTOS[$tipoDocumento] . ' actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al subir documento', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_documento' => $tipoDocumento,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al subir el documento: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar documento
     */
    public function deleteDocument(Request $request, Trabajador $trabajador)
    {
        $tipoDocumento = $request->input('tipo_documento');
        
        if (!array_key_exists($tipoDocumento, DocumentoTrabajador::TODOS_DOCUMENTOS)) {
            return back()->withErrors(['error' => 'Tipo de documento no válido']);
        }

        $documentos = $trabajador->documentos;
        if (!$documentos || empty($documentos->$tipoDocumento)) {
            return back()->withErrors(['error' => 'El documento no existe']);
        }

        DB::beginTransaction();
        
        try {
            // Eliminar archivo físico
            Storage::disk('public')->delete($documentos->$tipoDocumento);
            
            // Limpiar campo en la base de datos
            $documentos->$tipoDocumento = null;
            $documentos->fecha_ultima_actualizacion = now();
            $documentos->save();

            // Recalcular porcentaje
            $documentos->calcularPorcentaje(true);

            DB::commit();

            Log::info('Documento eliminado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_documento' => $tipoDocumento,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            return back()->with('success', 'Documento ' . DocumentoTrabajador::TODOS_DOCUMENTOS[$tipoDocumento] . ' eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al eliminar documento', [
                'trabajador_id' => $trabajador->id_trabajador,
                'tipo_documento' => $tipoDocumento,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al eliminar el documento: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Obtener categorías por área (para AJAX)
     */
    public function getCategoriasPorArea(Area $area)
    {
        $categorias = $area->categorias()
                          ->select('id_categoria', 'nombre_categoria')
                          ->orderBy('nombre_categoria')
                          ->get();

        return response()->json($categorias);
    }

    /**
     * ✅ NUEVA RUTA: Ver historial completo de promociones
     */
    public function verHistorialCompleto(Trabajador $trabajador)
    {
        $historialCompleto = HistorialPromocion::obtenerHistorialTrabajador($trabajador->id_trabajador);
        $estadisticas = HistorialPromocion::obtenerEstadisticas($trabajador->id_trabajador);
        
        return view('trabajadores.historial_promociones', compact(
            'trabajador',
            'historialCompleto',
            'estadisticas'
        ));
    }

    /**
     * Calcular estadísticas específicas del trabajador ✅ MÉTODO ACTUALIZADO
     */
    private function calcularEstadisticasTrabajador(Trabajador $trabajador)
    {
        $stats = [
            'antiguedad_texto' => $trabajador->antiguedad == 0 ? 'Nuevo' : 
                                ($trabajador->antiguedad == 1 ? '1 año' : "{$trabajador->antiguedad} años"),
            'edad' => $trabajador->edad,
            'porcentaje_documentos' => $trabajador->documentos ? $trabajador->documentos->porcentaje_completado : 0,
            'documentos_faltantes' => $trabajador->documentos ? count($trabajador->documentos->documentos_faltantes) : count(DocumentoTrabajador::TODOS_DOCUMENTOS),
            'documentos_basicos_completos' => $trabajador->documentos ? $trabajador->documentos->documentos_basicos_completos : false,
            'estado_documentos' => $trabajador->documentos ? $trabajador->documentos->estado_texto : 'Sin documentos',
            'ultima_actualizacion' => $trabajador->updated_at->diffForHumans(),
            'es_nuevo' => $trabajador->es_nuevo,
            // ✅ NUEVAS ESTADÍSTICAS DE PROMOCIONES
            'total_promociones' => HistorialPromocion::contarPromociones($trabajador->id_trabajador),
            'ultimo_cambio' => HistorialPromocion::obtenerUltimoCambio($trabajador->id_trabajador),
        ];

        return $stats;
    }

    /**
     * Generar nombre único para archivo
     */
    private function generarNombreArchivo(Trabajador $trabajador, string $tipo, $archivo): string
    {
        $extension = $archivo->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $nombre = strtolower(str_replace(' ', '_', $trabajador->nombre_trabajador));
        
        return "{$tipo}_{$nombre}_{$timestamp}.{$extension}";
    }
}