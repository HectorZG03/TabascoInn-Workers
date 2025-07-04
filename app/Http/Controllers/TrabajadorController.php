<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Trabajador;
use App\Models\FichaTecnica;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrabajadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Trabajador::select([
                'trabajadores.*',
                DB::raw('COALESCE(TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()), 0) as antiguedad_calculada')
            ])
            ->with(['fichaTecnica.categoria.area'])
            ->where('estatus', '!=', 'inactivo');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('area')) {
            $query->whereHas('fichaTecnica.categoria.area', fn($q) => $q->where('id_area', $request->area));
        }

        if ($request->filled('categoria')) {
            $query->whereHas('fichaTecnica.categoria', fn($q) => $q->where('id_categoria', $request->categoria));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => 
                $q->where('nombre_trabajador', 'LIKE', "%{$search}%")
                  ->orWhere('ape_pat', 'LIKE', "%{$search}%")
                  ->orWhere('ape_mat', 'LIKE', "%{$search}%")
                  ->orWhere('curp', 'LIKE', "%{$search}%")
                  ->orWhere('rfc', 'LIKE', "%{$search}%")
            );
        }

        $trabajadores = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        foreach ($trabajadores as $trabajador) {
            $trabajador->antiguedad_calculada = (int) ($trabajador->antiguedad_calculada ?? 0);
            $trabajador->antiguedad_texto = $this->calcularAntiguedadTexto($trabajador->antiguedad_calculada);
        }

        $estadisticasController = new EstadisticasController();
        $stats = $estadisticasController->obtenerEstadisticasTrabajadores();

        $areas = Area::orderBy('nombre_area')->get();
        $categorias = collect();
        $estados = Trabajador::TODOS_ESTADOS;

        return view('trabajadores.lista_trabajadores', compact('trabajadores', 'areas', 'categorias', 'stats', 'estados'));
    }

    public function create()
    {
        $areas = Area::orderBy('nombre_area')->get();
        return view('trabajadores.crear_trabajador', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_trabajador' => 'required|string|max:50',
            'ape_pat' => 'required|string|max:50',
            'ape_mat' => 'nullable|string|max:50',
            'fecha_nacimiento' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/', fn($attr, $val, $fail) => $this->validarFechaNacimiento($val, $fail)],
            'lugar_nacimiento' => 'nullable|string|max:100',
            'estado_actual' => 'nullable|string|max:50',
            'ciudad_actual' => 'nullable|string|max:50',
            'curp' => 'required|string|size:18|unique:trabajadores,curp',
            'rfc' => 'required|string|size:13|unique:trabajadores,rfc',
            'no_nss' => 'nullable|string|max:11',
            'telefono' => 'required|string|size:10',
            'correo' => 'nullable|email|max:55|unique:trabajadores,correo',
            'direccion' => 'nullable|string|max:255',
            'fecha_ingreso' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/', fn($attr, $val, $fail) => $this->validarFechaIngreso($val, $fail)],
            'id_area' => 'required|exists:area,id_area',
            'id_categoria' => 'required|exists:categoria,id_categoria',
            'sueldo_diarios' => 'required|numeric|min:0.01|max:99999.99',
            'formacion' => 'nullable|string|max:50',
            'grado_estudios' => 'nullable|string|max:50',
            'hora_entrada' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'hora_salida' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/', fn($attr, $val, $fail) => $this->validarHorario($val, $request->hora_entrada, $fail)],
            'dias_laborables' => 'required|array|min:1|max:7',
            'dias_laborables.*' => 'string|in:' . implode(',', array_keys(FichaTecnica::DIAS_SEMANA)),
            'beneficiario_nombre' => 'nullable|string|max:150',
            'beneficiario_parentesco' => 'nullable|string|in:' . implode(',', array_keys(FichaTecnica::PARENTESCOS_BENEFICIARIO)),
            'estatus' => 'required|in:activo,prueba',
            'fecha_inicio_contrato' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/', fn($attr, $val, $fail) => $this->validarFechaInicioContrato($val, $fail)],
            'fecha_fin_contrato' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/', fn($attr, $val, $fail) => $this->validarFechaFinContrato($val, $request->fecha_inicio_contrato, $fail)],
            'contacto_nombre_completo' => 'nullable|string|max:150',
            'contacto_parentesco' => 'nullable|string|max:50',
            'contacto_telefono_principal' => 'nullable|string|size:10',
            'contacto_telefono_secundario' => 'nullable|string|size:10',
            'contacto_direccion' => 'nullable|string|max:500',
        ]);

        // Validar relación área-categoría
        if (!Categoria::where('id_categoria', $validated['id_categoria'])->where('id_area', $validated['id_area'])->exists()) {
            return back()->withErrors(['id_categoria' => 'La categoría no pertenece al área seleccionada'])->withInput();
        }

        // Validar días laborables únicos
        if (count($validated['dias_laborables']) !== count(array_unique($validated['dias_laborables']))) {
            return back()->withErrors(['dias_laborables' => 'No puede seleccionar el mismo día más de una vez'])->withInput();
        }

        // Convertir fechas
        $fechaNacimiento = $this->convertirFechaACarbon($validated['fecha_nacimiento']);
        $fechaIngreso = $this->convertirFechaACarbon($validated['fecha_ingreso']);
        $fechaInicioContrato = $this->convertirFechaACarbon($validated['fecha_inicio_contrato']);
        $fechaFinContrato = $this->convertirFechaACarbon($validated['fecha_fin_contrato']);

        // Calcular tipo duración contrato
        $diasTotales = $fechaInicioContrato->diffInDays($fechaFinContrato);
        $tipoDuracion = $diasTotales > 30 ? 'meses' : 'dias';

        DB::beginTransaction();
        try {
            $trabajador = Trabajador::create([
                'nombre_trabajador' => $validated['nombre_trabajador'],
                'ape_pat' => $validated['ape_pat'],
                'ape_mat' => $validated['ape_mat'],
                'fecha_nacimiento' => $fechaNacimiento->format('Y-m-d'),
                'lugar_nacimiento' => $validated['lugar_nacimiento'],
                'estado_actual' => $validated['estado_actual'],
                'ciudad_actual' => $validated['ciudad_actual'],
                'curp' => strtoupper($validated['curp']),
                'rfc' => strtoupper($validated['rfc']),
                'no_nss' => $validated['no_nss'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'fecha_ingreso' => $fechaIngreso->format('Y-m-d'),
                'antiguedad' => $fechaIngreso->diffInYears(now()),
                'estatus' => $validated['estatus'],
            ]);

            // Horarios
            $entrada = Carbon::parse($validated['hora_entrada']);
            $salida = Carbon::parse($validated['hora_salida']);
            if ($salida->lte($entrada)) $salida->addDay();

            $horasCalculadas = round($entrada->diffInMinutes($salida) / 60, 2);
            $diasDescanso = FichaTecnica::calcularDiasDescanso($validated['dias_laborables']);
            $horasSemanales = round($horasCalculadas * count($validated['dias_laborables']), 2);

            $horaEntradaStr = $entrada->format('H:i');
            $horaSalidaOriginal = Carbon::parse($validated['hora_salida'])->format('H:i');

            $turnoCalculado = 'mixto';
            if ($horaEntradaStr >= FichaTecnica::HORARIO_DIURNO_INICIO && $horaSalidaOriginal <= FichaTecnica::HORARIO_DIURNO_FIN) {
                $turnoCalculado = 'diurno';
            } elseif ($horaEntradaStr >= FichaTecnica::HORARIO_NOCTURNO_INICIO || $horaSalidaOriginal <= FichaTecnica::HORARIO_NOCTURNO_FIN) {
                $turnoCalculado = 'nocturno';
            }

            FichaTecnica::create([
                'id_trabajador' => $trabajador->id_trabajador,
                'id_categoria' => $validated['id_categoria'],
                'sueldo_diarios' => $validated['sueldo_diarios'],
                'formacion' => $validated['formacion'],
                'grado_estudios' => $validated['grado_estudios'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'horas_trabajo' => $horasCalculadas,
                'turno' => $turnoCalculado,
                'dias_laborables' => $validated['dias_laborables'],
                'dias_descanso' => $diasDescanso,
                'horas_semanales' => $horasSemanales,
                'beneficiario_nombre' => $validated['beneficiario_nombre'],
                'beneficiario_parentesco' => $validated['beneficiario_parentesco'],
            ]);

            if ($request->filled('contacto_nombre_completo')) {
                ContactoEmergencia::create([
                    'id_trabajador' => $trabajador->id_trabajador,
                    'nombre_completo' => trim($validated['contacto_nombre_completo']),
                    'parentesco' => $validated['contacto_parentesco'],
                    'telefono_principal' => $validated['contacto_telefono_principal'],
                    'telefono_secundario' => $validated['contacto_telefono_secundario'],
                    'direccion' => $validated['contacto_direccion'],
                ]);
            }

            // Generar contrato (dejo igual)
            $contratoController = new ContratoController();
            $contratoController->generarDefinitivo($trabajador, [
                'fecha_inicio_contrato' => $fechaInicioContrato->format('Y-m-d'),
                'fecha_fin_contrato' => $fechaFinContrato->format('Y-m-d'),
                'tipo_duracion' => $tipoDuracion,
            ]);
            $contratoController->limpiarArchivosTemporales();

            DB::commit();

            return redirect()->route('trabajadores.index')->with('success', 'Trabajador creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al crear el trabajador: ' . $e->getMessage()])->withInput();
        }
    }

    private function validarFechaNacimiento($fecha, $fail)
    {
        if (!$this->validarFechaPersonalizada($fecha)) {
            return $fail('La fecha de nacimiento no es válida.');
        }
        $fechaNacimiento = $this->convertirFechaACarbon($fecha);
        if ($fechaNacimiento && $fechaNacimiento->gt(now()->subYears(18))) {
            $fail('El trabajador debe ser mayor de 18 años.');
        }
    }

    private function validarFechaIngreso($fecha, $fail)
    {
        if (!$this->validarFechaPersonalizada($fecha)) {
            return $fail('La fecha de ingreso no es válida.');
        }
        $fechaIngreso = $this->convertirFechaACarbon($fecha);
        if ($fechaIngreso && $fechaIngreso->gt(now())) {
            $fail('La fecha de ingreso no puede ser futura.');
        }
    }

    private function validarHorario($horaSalida, $horaEntrada, $fail)
    {
        if ($horaEntrada && $horaSalida) {
            $entrada = Carbon::parse($horaEntrada);
            $salida = Carbon::parse($horaSalida);
            if ($salida->lte($entrada)) {
                $salida->addDay();
            }
            $horas = $entrada->diffInMinutes($salida) / 60;
            if ($horas < 1 || $horas > 16) {
                $fail('El horario debe estar entre 1 y 16 horas. Calculado: ' . round($horas, 2) . ' horas.');
            }
        }
    }

    private function validarFechaInicioContrato($fecha, $fail)
    {
        if (!$this->validarFechaPersonalizada($fecha)) {
            return $fail('La fecha de inicio del contrato no es válida.');
        }
        $fechaInicio = $this->convertirFechaACarbon($fecha);
        if ($fechaInicio && $fechaInicio->lt(now()->startOfDay())) {
            $fail('La fecha de inicio del contrato no puede ser anterior a hoy.');
        }
    }

    private function validarFechaFinContrato($fechaFin, $fechaInicioStr, $fail)
    {
        if (!$this->validarFechaPersonalizada($fechaFin)) {
            return $fail('La fecha de fin del contrato no es válida.');
        }
        if ($fechaInicioStr) {
            $fechaInicio = $this->convertirFechaACarbon($fechaInicioStr);
            $fechaFin = $this->convertirFechaACarbon($fechaFin);
            if ($fechaInicio && $fechaFin && $fechaFin->lte($fechaInicio)) {
                $fail('La fecha de fin debe ser posterior a la fecha de inicio.');
            }
        }
    }

    private function validarFechaPersonalizada($fecha)
    {
        if (!$fecha) return false;
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $m)) return false;
        return checkdate((int)$m[2], (int)$m[1], (int)$m[3]);
    }

    private function convertirFechaACarbon($fecha)
    {
        if (!$this->validarFechaPersonalizada($fecha)) return null;
        [$d, $m, $y] = explode('/', $fecha);
        try {
            return Carbon::create((int)$y, (int)$m, (int)$d);
        } catch (\Exception) {
            return null;
        }
    }

    public function show(Trabajador $trabajador)
    {
        $trabajador->load(['fichaTecnica.categoria.area', 'contactosEmergencia']);
        return redirect()->route('trabajadores.perfil.show', $trabajador);
    }

    public function getCategoriasPorArea(Area $area)
    {
        $categorias = $area->categorias()->select('id_categoria', 'nombre_categoria')->orderBy('nombre_categoria')->get();
        return response()->json($categorias);
    }

    private function calcularDuracionTexto($inicio, $fin, $tipo)
    {
        if ($tipo === 'dias') {
            $dias = $inicio->diffInDays($fin);
            return $dias . ' ' . ($dias === 1 ? 'día' : 'días');
        }
        $meses = $inicio->diffInMonths($fin);
        if ($inicio->copy()->addMonths($meses)->lt($fin)) $meses++;
        return $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
    }

    private function calcularAntiguedadTexto(int $antiguedad): string
    {
        return match ($antiguedad) {
            0 => 'Nuevo',
            1 => '1 año',
            default => "$antiguedad años",
        };
    }
}
