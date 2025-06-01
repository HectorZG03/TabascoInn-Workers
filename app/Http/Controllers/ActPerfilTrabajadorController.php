<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\DocumentoTrabajador;
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
     * Mostrar perfil completo del trabajador
     */
    public function show(Trabajador $trabajador)
    {
        // Cargar todas las relaciones necesarias
        $trabajador->load([
            'fichaTecnica.categoria.area', 
            'documentos', 
            'despido'
        ]);

        // Obtener áreas y categorías para formularios
        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        
        if ($trabajador->fichaTecnica && $trabajador->fichaTecnica->categoria) {
            $categorias = Categoria::where('id_area', $trabajador->fichaTecnica->categoria->id_area)
                                 ->orderBy('nombre_categoria')
                                 ->get();
        }

        // Estados disponibles
        $estados = Trabajador::TODOS_ESTADOS;

        // Estadísticas del trabajador
        $stats = $this->calcularEstadisticasTrabajador($trabajador);

        return view('trabajadores.perfil_trabajador', compact(
            'trabajador', 
            'areas', 
            'categorias', 
            'estados',
            'stats'
        ));
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
     * Actualizar datos laborales (ficha técnica)
     */
    public function updateFichaTecnica(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
        ], [
            'id_area.required' => 'Debe seleccionar un área',
            'id_categoria.required' => 'Debe seleccionar una categoría',
            'sueldo_diarios.required' => 'El sueldo diario es obligatorio',
            'sueldo_diarios.min' => 'El sueldo debe ser mayor a 0',
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
            // Actualizar o crear ficha técnica
            if ($trabajador->fichaTecnica) {
                $trabajador->fichaTecnica->update([
                    'id_categoria' => $validated['id_categoria'],
                    'sueldo_diarios' => $validated['sueldo_diarios'],
                    'formacion' => $validated['formacion'],
                    'grado_estudios' => $validated['grado_estudios'],
                ]);
            } else {
                FichaTecnica::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'id_categoria' => $validated['id_categoria'],
                    'sueldo_diarios' => $validated['sueldo_diarios'],
                    'formacion' => $validated['formacion'],
                    'grado_estudios' => $validated['grado_estudios'],
                ]);
            }

            DB::commit();

            Log::info('Ficha técnica actualizada', [
                'trabajador_id' => $trabajador->id_trabajador,
                'categoria_id' => $validated['id_categoria'],
                'usuario' => Auth::user()->email ?? 'Sistema'
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
     * Cambiar estado del trabajador
     */
    public function updateEstado(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'estatus' => 'required|in:' . implode(',', array_keys(Trabajador::TODOS_ESTADOS)),
            'observaciones' => 'nullable|string|max:500'
        ], [
            'estatus.required' => 'Debe seleccionar un estado',
            'estatus.in' => 'El estado seleccionado no es válido',
        ]);

        DB::beginTransaction();
        
        try {
            $estadoAnterior = $trabajador->estatus;
            
            $trabajador->update([
                'estatus' => $validated['estatus']
            ]);

            // Log del cambio de estado
            Log::info('Estado de trabajador cambiado', [
                'trabajador_id' => $trabajador->id_trabajador,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $validated['estatus'],
                'observaciones' => $validated['observaciones'] ?? null,
                'usuario' => Auth::user()->email ?? 'Sistema'
            ]);

            DB::commit();

            return back()->with('success', "Estado cambiado de '{$trabajador::TODOS_ESTADOS[$estadoAnterior]}' a '{$trabajador->estatus_texto}' exitosamente");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al cambiar estado del trabajador', [
                'trabajador_id' => $trabajador->id_trabajador,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
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
     * Calcular estadísticas específicas del trabajador
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