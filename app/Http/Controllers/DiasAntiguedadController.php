<?php

// app/Http/Controllers/DiasAntiguedadController.php
namespace App\Http\Controllers;

use App\Models\DiaAntiguedad;
use Illuminate\Http\Request;

class DiasAntiguedadController extends Controller
{
    public function index()
    {
        $diasAntiguedad = DiaAntiguedad::orderBy('antiguedad_min')->get();
        return view('users.configuracion.dias_antiguedad', compact('diasAntiguedad'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'antiguedad_min' => 'required|integer|min:0',
            'antiguedad_max' => 'nullable|integer|gt:antiguedad_min',
            'dias' => 'required|integer|min:1',
        ]);

        DiaAntiguedad::create($request->all());
        return redirect()->route('configuracion.dias_antiguedad.index')->with('success', 'Registro creado');
    }

    public function update(Request $request, DiaAntiguedad $diaAntiguedad)
    {
        $request->validate([
            'antiguedad_min' => 'required|integer|min:0',
            'antiguedad_max' => 'nullable|integer|gt:antiguedad_min',
            'dias' => 'required|integer|min:1',
        ]);

        $diaAntiguedad->update($request->all());
        return redirect()->route('configuracion.dias_antiguedad.index')->with('success', 'Registro actualizado');
    }

    public function destroy(DiaAntiguedad $diaAntiguedad)
    {
        $diaAntiguedad->delete();
        return redirect()->route('configuracion.dias_antiguedad.index')->with('success', 'Registro eliminado');
    }
}
