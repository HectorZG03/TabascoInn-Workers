<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\PermisosLaborales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PermisosLaboralesController extends Controller
{
    // ✅ VALIDACIÓN ACTUALIZADA - PERMITE FECHAS PASADAS, PRESENTES Y FUTURAS
    public function store(Request $request, Trabajador $trabajador)
    {
        if (!$trabajador->puedeAsignarPermiso()) {
            return back()->withErrors(['error' => 'Solo se pueden asignar permisos a trabajadores activos o sin permisos activos.']);
        }

        $tiposBasicos = array_keys(PermisosLaborales::getTiposDisponibles());
        $tiposValidos = array_merge($tiposBasicos, ['OTRO']);

        $validated = $request->validate([
            'tipo_permiso' => ['required', 'string', 'in:' . implode(',', $tiposValidos)],
            'tipo_personalizado' => ['nullable', 'required_if:tipo_permiso,OTRO', 'string', 'min:3', 'max:80'],
            'motivo' => ['required', 'string', 'min:3', 'max:100'],
            'archivo_permiso' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            
            // ✅ FECHAS ACTUALIZADAS - SIN RESTRICCIÓN DE FECHAS PASADAS
            'fecha_inicio' => [
                'required', 
                'date_format:d/m/Y'
            ],
            'fecha_fin' => [
                'required', 
                'date_format:d/m/Y', 
                'after_or_equal:fecha_inicio'
            ],
            
            'observaciones' => ['nullable', 'string', 'max:500'],
            'es_por_horas' => ['nullable', 'boolean'],
            'hora_inicio' => ['nullable', 'required_if:es_por_horas,1', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'required_if:es_por_horas,1', 'date_format:H:i', function ($attribute, $value, $fail) use ($request) {
                if ($request->boolean('es_por_horas') && $request->filled('hora_inicio') && $value <= $request->hora_inicio) {
                    $fail('La hora de fin debe ser posterior a la hora de inicio.');
                }
            }],
        ], [
            'tipo_permiso.required' => 'El tipo de permiso es obligatorio',
            'tipo_permiso.in' => 'El tipo de permiso seleccionado no es válido',
            'tipo_personalizado.required_if' => 'Debe especificar el tipo de permiso cuando selecciona "Otro"',
            'motivo.required' => 'El motivo es obligatorio',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date_format' => 'La fecha de inicio debe tener el formato DD/MM/YYYY',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date_format' => 'La fecha de fin debe tener el formato DD/MM/YYYY',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'hora_inicio.required_if' => 'La hora de inicio es obligatoria para permisos por horas',
            'hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM',
            'hora_fin.required_if' => 'La hora de fin es obligatoria para permisos por horas',
            'hora_fin.date_format' => 'La hora de fin debe tener el formato HH:MM',
            'archivo_permiso.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG o PNG',
            'archivo_permiso.max' => 'El archivo no debe ser mayor a 5MB',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
        ]);

        $fechaInicioCarbon = Carbon::createFromFormat('d/m/Y', $validated['fecha_inicio']);
        $fechaFinCarbon = Carbon::createFromFormat('d/m/Y', $validated['fecha_fin']);

        $tipoFinal = $validated['tipo_permiso'] === 'OTRO'
            ? trim($validated['tipo_personalizado'])
            : $validated['tipo_permiso'];

        $esPorHoras = $request->boolean('es_por_horas');

        // ✅ VALIDACIÓN DE CONFLICTOS ACTUALIZADA - MÁS FLEXIBLE PARA FECHAS PASADAS
        $conflicto = PermisosLaborales::where('id_trabajador', $trabajador->id_trabajador)
            ->where('estatus_permiso', 'activo')
            ->where(function ($q) use ($fechaInicioCarbon, $fechaFinCarbon, $esPorHoras, $request) {
                $q->whereBetween('fecha_inicio', [$fechaInicioCarbon->format('Y-m-d'), $fechaFinCarbon->format('Y-m-d')])
                ->orWhereBetween('fecha_fin', [$fechaInicioCarbon->format('Y-m-d'), $fechaFinCarbon->format('Y-m-d')])
                ->orWhere(function ($sub) use ($fechaInicioCarbon, $fechaFinCarbon) {
                    $sub->where('fecha_inicio', '<=', $fechaInicioCarbon->format('Y-m-d'))
                        ->where('fecha_fin', '>=', $fechaFinCarbon->format('Y-m-d'));
                });

                if ($esPorHoras && $request->filled('hora_inicio') && $request->filled('hora_fin')) {
                    $q->where(function ($h) use ($fechaInicioCarbon, $request) {
                        $h->where('fecha_inicio', $fechaInicioCarbon->format('Y-m-d'))
                        ->where('hora_inicio', '<', $request->hora_fin)
                        ->where('hora_fin', '>', $request->hora_inicio);
                    });
                }
            })
            ->exists();

        if ($conflicto) {
            return back()->withErrors(['fecha_inicio' => 'Ya existe un permiso activo en el rango (fecha y hora) seleccionado.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $permiso = PermisosLaborales::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'tipo_permiso' => $tipoFinal,
                'motivo' => $validated['motivo'],
                'fecha_inicio' => $fechaInicioCarbon->format('Y-m-d'),
                'fecha_fin' => $fechaFinCarbon->format('Y-m-d'),
                'observaciones' => $validated['observaciones'] ?? null,
                'estatus_permiso' => 'activo',
                'es_por_horas' => $esPorHoras,
                'hora_inicio' => $esPorHoras ? $validated['hora_inicio'] : null,
                'hora_fin' => $esPorHoras ? $validated['hora_fin'] : null,
            ]);

            if ($request->hasFile('archivo_permiso')) {
                $archivo = $request->file('archivo_permiso');
                $nombreArchivo = 'permiso_' . $permiso->id_permiso . '.' . $archivo->getClientOriginalExtension();
                $ruta = $archivo->storeAs('permisos', $nombreArchivo, 'public');
                $permiso->update(['ruta_pdf' => $ruta]);
            }

            $trabajador->update(['estatus' => 'permiso']);

            DB::commit();

            $duracionDias = $fechaInicioCarbon->diffInDays($fechaFinCarbon) + 1;
            $mensaje = "Permiso asignado exitosamente a {$trabajador->nombre_completo}";
            if ($validated['tipo_permiso'] === 'OTRO') {
                $mensaje .= " con tipo personalizado: \"{$tipoFinal}\"";
            }
            $mensaje .= " por {$duracionDias} día" . ($duracionDias > 1 ? 's' : '');
            if ($esPorHoras) {
                $mensaje .= " de {$validated['hora_inicio']} a {$validated['hora_fin']}";
            }

            return redirect()->route('trabajadores.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al asignar el permiso: ' . $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        $query = PermisosLaborales::with('trabajador.fichaTecnica.categoria.area');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trabajador', function ($q) use ($search) {
                $q->where('nombre_trabajador', 'like', "%{$search}%")
                  ->orWhere('ape_pat', 'like', "%{$search}%")
                  ->orWhere('ape_mat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_permiso')) {
            $query->where('tipo_permiso', $request->tipo_permiso);
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', 'like', "%{$request->motivo}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            switch ($request->estado) {
                case 'activos':
                    $query->where('estatus_permiso', 'activo');
                    break;
                case 'finalizados':
                    $query->where('estatus_permiso', 'finalizado');
                    break;
                case 'cancelados':
                    $query->where('estatus_permiso', 'cancelado');
                    break;
                case 'vencidos':
                    $query->where('fecha_fin', '<', now())
                          ->where('estatus_permiso', 'activo');
                    break;
            }
        }

        $permisos = $query->orderByDesc('created_at')->paginate(20);

        $estadisticasController = new EstadisticasController();
        $stats = $estadisticasController->obtenerEstadisticasPermisos();

        $tiposBasicos = PermisosLaborales::getTiposDisponibles();

        $tiposPersonalizados = PermisosLaborales::select('tipo_permiso')
            ->whereNotIn('tipo_permiso', array_keys($tiposBasicos))
            ->distinct()
            ->pluck('tipo_permiso')
            ->toArray();

        $tiposPermisos = $tiposBasicos + array_combine($tiposPersonalizados, $tiposPersonalizados);

        $coloresPermiso = [
            'Vacaciones' => 'success',
            'Licencia Médica' => 'danger',
            'Licencia por Maternidad' => 'info',
            'Licencia por Paternidad' => 'info',
            'Permiso Personal' => 'warning',
            'Permiso por Estudios' => 'primary',
            'Permiso por Capacitación' => 'primary',
            'Licencia sin Goce de Sueldo' => 'secondary',
            'Permiso Especial' => 'dark',
            'Permiso por Duelo' => 'dark',
            'Permiso por Matrimonio' => 'success',
            'Incapacidad Temporal' => 'danger',
            'Licencia por Familiar Enfermo' => 'warning',
            'Permiso por Emergencia' => 'danger',
            'Licencia Sindical' => 'info',
        ];

        $iconosPermiso = [
            'Vacaciones' => 'bi-sun',
            'Licencia Médica' => 'bi-heart-pulse',
            'Licencia por Maternidad' => 'bi-person-hearts',
            'Licencia por Paternidad' => 'bi-person-hearts',
            'Permiso Personal' => 'bi-person',
            'Permiso por Estudios' => 'bi-mortarboard',
            'Permiso por Capacitación' => 'bi-book',
            'Licencia sin Goce de Sueldo' => 'bi-dash-circle',
            'Permiso Especial' => 'bi-star',
            'Permiso por Duelo' => 'bi-heart',
            'Permiso por Matrimonio' => 'bi-suit-heart',
            'Incapacidad Temporal' => 'bi-bandaid',
            'Licencia por Familiar Enfermo' => 'bi-person-fill-exclamation',
            'Permiso por Emergencia' => 'bi-exclamation-triangle',
            'Licencia Sindical' => 'bi-people',
        ];

        return view('trabajadores.estatus.permisos_lista', compact(
            'permisos',
            'stats',
            'tiposPermisos',
            'coloresPermiso',
            'iconosPermiso'
        ));
    }

    public function finalizar(PermisosLaborales $permiso)
    {
        $trabajador = $permiso->trabajador;

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors(['error' => 'Solo se pueden finalizar permisos que estén activos']);
        }

        if ($trabajador->estatus !== 'permiso') {
            return back()->withErrors(['error' => 'El trabajador debe estar en estado de permiso']);
        }

        DB::beginTransaction();

        try {
            $permiso->update([
                'fecha_fin' => now()->format('Y-m-d'),
                'estatus_permiso' => 'finalizado',
                'observaciones' => $permiso->observaciones .
                    "\n[FINALIZADO EL " . now()->format('d/m/Y') . " por " . (Auth::user()->email ?? 'Sistema') . "]"
            ]);

            $trabajador->update(['estatus' => 'activo']);

            DB::commit();

            return redirect()->route('permisos.index')->with('success', "Permiso finalizado. {$trabajador->nombre_completo} ha sido reactivado");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al finalizar: ' . $e->getMessage()]);
        }
    }

    // ✅ NUEVO MÉTODO PARA CANCELAR PERMISO CON MOTIVO
    public function cancelar(Request $request, PermisosLaborales $permiso)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'motivo_cancelacion' => 'required|string|min:10|max:500'
        ], [
            'motivo_cancelacion.required' => 'El motivo de cancelación es obligatorio',
            'motivo_cancelacion.min' => 'El motivo debe tener al menos 10 caracteres',
            'motivo_cancelacion.max' => 'El motivo no puede exceder 500 caracteres'
        ]);

        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors(['error' => 'Solo se pueden cancelar permisos que estén activos']);
        }

        $trabajador = $permiso->trabajador;

        DB::beginTransaction();

        try {
            // Usar el método del modelo para cancelar
            $permiso->cancelarPermiso(
                $validated['motivo_cancelacion'], 
                Auth::user()->email ?? 'Sistema'
            );

            // Reactivar al trabajador
            $trabajador->update(['estatus' => 'activo']);

            DB::commit();

            return redirect()->route('permisos.index')->with('success', 
                "Permiso cancelado exitosamente. {$trabajador->nombre_completo} ha sido reactivado");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al cancelar: ' . $e->getMessage()]);
        }
    }

    // ✅ NUEVO MÉTODO PARA ELIMINAR PERMISO DEFINITIVAMENTE  
    public function eliminar(PermisosLaborales $permiso)
    {
        if ($permiso->estatus_permiso !== 'activo') {
            return back()->withErrors(['error' => 'Solo se pueden eliminar permisos que estén activos']);
        }

        $trabajador = $permiso->trabajador;

        DB::beginTransaction();

        try {
            $trabajador->update(['estatus' => 'activo']);
            $permiso->delete();

            DB::commit();

            return redirect()->route('permisos.index')->with('success', 
                "Permiso eliminado definitivamente. {$trabajador->nombre_completo} ha sido reactivado");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function estadisticas()
    {
        $añoActual = Carbon::now()->year;

        $estadisticasController = new EstadisticasController();
        $estadisticasBasicas = $estadisticasController->obtenerEstadisticasPermisos();

        $estadisticas = array_merge($estadisticasBasicas, [
            'por_tipo' => PermisosLaborales::selectRaw('tipo_permiso, COUNT(*) as total')
                ->whereYear('fecha_inicio', $añoActual)
                ->groupBy('tipo_permiso')
                ->orderByDesc('total')
                ->get(),
            'por_motivo' => PermisosLaborales::selectRaw('motivo, COUNT(*) as total')
                ->whereYear('fecha_inicio', $añoActual)
                ->groupBy('motivo')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'por_mes' => PermisosLaborales::selectRaw('MONTH(fecha_inicio) as mes, COUNT(*) as total')
                ->whereYear('fecha_inicio', $añoActual)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get(),
        ]);

        return response()->json($estadisticas);
    }

    public function show(PermisosLaborales $permiso)
    {
        $permiso->load('trabajador.fichaTecnica.categoria.area');

        return view('permisos.show', compact('permiso'));
    }

    public function descargar($id)
    {
        $permiso = PermisosLaborales::findOrFail($id);

        if (!$permiso->ruta_pdf || !Storage::disk('public')->exists($permiso->ruta_pdf)) {
            return back()->with('error', 'El archivo no se encuentra disponible.');
        }

        return Storage::disk('public')->download($permiso->ruta_pdf);
    }

    public function subirArchivo(Request $request, PermisosLaborales $permiso)
    {
        $request->validate([
            'archivo_permiso' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120',
        ]);

        if ($request->hasFile('archivo_permiso')) {
            $archivo = $request->file('archivo_permiso');
            $nombreArchivo = 'permiso_' . $permiso->id_permiso . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('permisos', $nombreArchivo, 'public');
            $permiso->update(['ruta_pdf' => $ruta]);
        }

        return redirect()->back()->with('success', 'Archivo subido correctamente.');
    }
}