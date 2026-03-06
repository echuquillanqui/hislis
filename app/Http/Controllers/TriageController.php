<?php

namespace App\Http\Controllers;

use App\Models\Triage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TriageController extends Controller
{
    /**
     * El Monitor general es manejado por AttentionController@index,
     * pero si necesitas un listado específico aquí:
     */
    public function index()
    {
        $pendientes = Triage::with('patient')
            ->where('temp', '0')
            ->latest()
            ->get();
            
        return view('attentions.index', compact('pendientes'));
    }

    /**
     * Muestra la vista de rellenado de datos.
     * Ruta: admin/triage/{id}/edit
     */
    public function edit(string $id)
    {
        $triage = Triage::with('patient')->findOrFail($id);
        
        // Retorna la vista en la carpeta attentions/triage.blade.php
        return view('attentions.triage', compact('triage'));
    }

    /**
     * Actualiza los datos del triaje.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'temp'   => 'required|numeric',
            'bp'     => 'required|string',
            'hr'     => 'required|integer',
            'rr'     => 'required|integer',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
        ]);

        $triage = Triage::findOrFail($id);

        // Cálculo automático de IMC
        $imc = 0;
        if ($request->height > 0) {
            $imc = round($request->weight / ($request->height * $request->height), 2);
        }

        $triage->update([
            'user_id' => Auth::id(),
            'temp'    => $request->temp,
            'bp'      => $request->bp,
            'hr'      => $request->hr,
            'rr'      => $request->rr,
            'weight'  => $request->weight,
            'height'  => $request->height,
            'bmi'     => $imc,
        ]);

        // Redirigimos al monitor de atención después de guardar
        return redirect()->route('attentions.index')
            ->with('success', 'Triaje guardado correctamente.');
    }
}