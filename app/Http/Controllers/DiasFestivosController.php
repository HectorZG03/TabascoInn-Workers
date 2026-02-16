<?php

namespace App\Http\Controllers;

use App\Models\DiaFestivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DiasFestivosController extends Controller
{
    /**
     * Mostrar la vista de gestión de días festivos
     */
    public function index(Request $request)
    {
        $añoActual = $request->get('año', Carbon::now()->year);
        
        // Obtener años disponibles (últimos 5 años y próximos 5 años)
        $añosDisponibles = [];
        for ($i = -5; $i <= 5; $i++) {
            $añosDisponibles[] = Carbon::now()->year + $i;
        }
        
        // Obtener días festivos del año seleccionado
        $diasFestivos = DiaFestivo::delAño($añoActual)
            ->ordenados()
            ->with('creadoPor:id,nombre')
            ->get();
        
        // Estadísticas
        $estadisticas = [
            'total_dias' => $diasFestivos->count(),
            'dias_oficiales' => $diasFestivos->where('es_oficial', true)->count(),
            'dias_adicionales' => $diasFestivos->where('es_oficial', false)->count(),
        ];
        
        return view('users.configuracion.dias_festivos', compact(
            'diasFestivos',
            'añoActual',
            'añosDisponibles',
            'estadisticas'
        ));
    }

    /**
     * Almacenar un nuevo día festivo
     * ✅ ACTUALIZADO: Acepta fechas en formato DD/MM/YYYY
     */
    public function store(Request $request)
    {
        $request->validate([
            'año' => 'required|integer|min:2000|max:2050',
            'fecha' => ['required', 'date_format:d/m/Y'], // ✅ CAMBIADO: Formato DD/MM/YYYY
            'nombre' => 'required|string|max:100',
            'es_oficial' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date_format' => 'La fecha debe tener el formato DD/MM/YYYY',
            'nombre.required' => 'El nombre del día festivo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ]);
        
        // ✅ ACTUALIZADO: Convertir fecha DD/MM/YYYY a Carbon
        $fechaCarbon = Carbon::createFromFormat('d/m/Y', $request->fecha);
        $año = $fechaCarbon->year;
        
        // Validar que el año de la fecha coincida con el año seleccionado
        if ($año != $request->año) {
            return back()->with('error', 'El año de la fecha debe coincidir con el año seleccionado');
        }
        
        $existe = DiaFestivo::where('año', $año)
            ->whereDate('fecha', $fechaCarbon)
            ->exists();
        
        if ($existe) {
            return back()->with('error', 'Ya existe un día festivo registrado para esa fecha');
        }
        
        DiaFestivo::create([
            'año' => $año,
            'fecha' => $fechaCarbon,
            'nombre' => $request->nombre,
            'es_oficial' => $request->es_oficial ?? true,
            'observaciones' => $request->observaciones,
            'creado_por' => Auth::id()
        ]);
        
        return redirect()->route('configuracion.dias_festivos.index', ['año' => $año])
            ->with('success', 'Día festivo registrado correctamente');
    }

    /**
     * Actualizar un día festivo
     * ✅ ACTUALIZADO: Acepta fechas en formato DD/MM/YYYY
     */
    public function update(Request $request, DiaFestivo $diaFestivo)
    {
        $request->validate([
            'fecha' => ['required', 'date_format:d/m/Y'], // ✅ CAMBIADO: Formato DD/MM/YYYY
            'nombre' => 'required|string|max:100',
            'es_oficial' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date_format' => 'La fecha debe tener el formato DD/MM/YYYY',
            'nombre.required' => 'El nombre del día festivo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ]);
        
        // ✅ ACTUALIZADO: Convertir fecha DD/MM/YYYY a Carbon
        $fechaCarbon = Carbon::createFromFormat('d/m/Y', $request->fecha);
        $año = $fechaCarbon->year;
        
        // Validar que el año de la nueva fecha coincida con el año actual del día festivo
        if ($año != $diaFestivo->año) {
            return back()->with('error', 'El año de la fecha debe coincidir con el año del día festivo');
        }
        
        // Verificar que no exista otro día festivo en esa fecha (excluyendo el actual)
        $existe = DiaFestivo::where('año', $año)
            ->whereDate('fecha', $fechaCarbon)
            ->where('id', '!=', $diaFestivo->id)
            ->exists();
        
        if ($existe) {
            return back()->with('error', 'Ya existe otro día festivo registrado para esa fecha');
        }
        
        $diaFestivo->update([
            'fecha' => $fechaCarbon,
            'nombre' => $request->nombre,
            'es_oficial' => $request->es_oficial ?? true,
            'observaciones' => $request->observaciones
        ]);
        
        return redirect()->route('configuracion.dias_festivos.index', ['año' => $diaFestivo->año])
            ->with('success', 'Día festivo actualizado correctamente');
    }

    /**
     * Eliminar un día festivo
     */
    public function destroy(DiaFestivo $diaFestivo)
    {
        $año = $diaFestivo->año;
        $diaFestivo->delete();
        
        return redirect()->route('configuracion.dias_festivos.index', ['año' => $año])
            ->with('success', 'Día festivo eliminado correctamente');
    }

    /**
     * Generar días festivos oficiales para un año
     */
    public function generarOficiales(Request $request)
    {
        $request->validate([
            'año' => 'required|integer|min:2000|max:2050'
        ]);
        
        $año = $request->año;
        $creados = DiaFestivo::generarDiasFestivosOficiales($año, Auth::id());
        
        if ($creados > 0) {
            return redirect()->route('configuracion.dias_festivos.index', ['año' => $año])
                ->with('success', "Se generaron {$creados} días festivos oficiales para el año {$año}");
        } else {
            return redirect()->route('configuracion.dias_festivos.index', ['año' => $año])
                ->with('info', "Los días festivos oficiales ya estaban registrados para el año {$año}");
        }
    }

    /**
     * Copiar días festivos de un año a otro
     */
    public function copiarAño(Request $request)
    {
        $request->validate([
            'año_origen' => 'required|integer|min:2000|max:2050',
            'año_destino' => 'required|integer|min:2000|max:2050|different:año_origen'
        ]);
        
        $copiados = DiaFestivo::copiarDiasFestivosDeAño(
            $request->año_origen,
            $request->año_destino,
            Auth::id()
        );
        
        if ($copiados > 0) {
            return redirect()->route('configuracion.dias_festivos.index', ['año' => $request->año_destino])
                ->with('success', "Se copiaron {$copiados} días festivos del año {$request->año_origen} al {$request->año_destino}");
        } else {
            return redirect()->route('configuracion.dias_festivos.index', ['año' => $request->año_destino])
                ->with('info', "No se copiaron días festivos. Es posible que ya existan en el año destino");
        }
    }

    /**
     * API: Obtener días festivos de un año (para AJAX)
     */
    public function obtenerPorAño(Request $request)
    {
        $año = $request->get('año', Carbon::now()->year);
        
        $diasFestivos = DiaFestivo::delAño($año)
            ->ordenados()
            ->get()
            ->map(function ($dia) {
                return [
                    'id' => $dia->id,
                    'fecha' => $dia->fecha->format('Y-m-d'),
                    'fecha_formateada' => $dia->fecha_formateada,
                    'nombre' => $dia->nombre,
                    'dia_semana' => $dia->dia_semana,
                    'es_oficial' => $dia->es_oficial,
                    'observaciones' => $dia->observaciones
                ];
            });
        
        return response()->json([
            'success' => true,
            'dias_festivos' => $diasFestivos,
            'total' => $diasFestivos->count()
        ]);
    }

    /**
     * API: Calcular fecha de reintegro considerando días festivos y descansos
     */
    public function calcularFechaReintegro(Request $request)
    {
        $request->validate([
            'fecha_fin' => 'required|date',
            'dias_descanso' => 'nullable|array'
        ]);
        
        $fechaFin = Carbon::parse($request->fecha_fin);
        $diasDescanso = $request->dias_descanso ?? [];
        
        // La fecha de reintegro inicial es el día siguiente a la fecha fin
        $fechaReintegro = $fechaFin->copy()->addDay();
        
        // Calcular el siguiente día hábil
        $fechaReintegroHabil = DiaFestivo::siguienteDiaHabil($fechaReintegro, $diasDescanso);
        
        // Obtener información sobre los días saltados
        $diasSaltados = [];
        $fechaTemporal = $fechaReintegro->copy();
        
        while ($fechaTemporal->lt($fechaReintegroHabil)) {
            $razon = [];
            
            // Verificar si es día de descanso
            $diasSemanaMap = [
                'domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3,
                'jueves' => 4, 'viernes' => 5, 'sabado' => 6
            ];
            
            $diasDescansoNumeros = array_map(function($dia) use ($diasSemanaMap) {
                return $diasSemanaMap[strtolower($dia)] ?? null;
            }, $diasDescanso);
            
            if (in_array($fechaTemporal->dayOfWeek, $diasDescansoNumeros)) {
                $razon[] = 'Día de descanso';
            }
            
            // Verificar si es día festivo
            if (DiaFestivo::esDiaFestivo($fechaTemporal)) {
                $diaFestivo = DiaFestivo::whereDate('fecha', $fechaTemporal)->first();
                $razon[] = "Día festivo: " . ($diaFestivo->nombre ?? 'Festivo');
            }
            
            if (!empty($razon)) {
                $diasSaltados[] = [
                    'fecha' => $fechaTemporal->format('d/m/Y'),
                    'razon' => implode(' y ', $razon)
                ];
            }
            
            $fechaTemporal->addDay();
        }
        
        return response()->json([
            'success' => true,
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'fecha_reintegro_inicial' => $fechaReintegro->format('Y-m-d'),
            'fecha_reintegro_habil' => $fechaReintegroHabil->format('Y-m-d'),
            'fecha_reintegro_formateada' => $fechaReintegroHabil->format('d/m/Y'),
            'dias_saltados' => $diasSaltados,
            'total_dias_saltados' => count($diasSaltados)
        ]);
    }
}