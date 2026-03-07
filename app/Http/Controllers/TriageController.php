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
        $pendientes = Triage::with('patient')->latest()->get();

        return view('admin.attentions.index', compact('pendientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'temp' => 'required|numeric',
            'bp' => 'required|string',
            'hr' => 'required|integer',
            'rr' => 'required|integer',
            'weight' => 'required|numeric',
            'height' => 'required|numeric|gt:0',
            'spo2' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $data['bmi'] = round($data['weight'] / ($data['height'] * $data['height']), 2);
        $data['user_id'] = Auth::id();

        $triage = Triage::where('patient_id', $data['patient_id'])
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($triage) {
            $triage->update($data);
        } else {
            $triage = Triage::create($data);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Triaje guardado correctamente.',
                'triage_id' => $triage->id,
            ]);
        }

        return redirect()->route('attentions.index')->with('success', 'Triaje guardado correctamente.');
    }

    /**
     * Muestra la vista de rellenado de datos.
     * Ruta: admin/triage/{id}/edit
     */
    public function edit(string $id)
    {
        $triage = Triage::with('patient')->findOrFail($id);
        
        return view('admin.attentions.triage', compact('triage'));
    }

    /**
     * Actualiza los datos del triaje.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'temp' => 'required|numeric',
            'bp' => 'required|string',
            'hr' => 'required|integer',
            'rr' => 'required|integer',
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
            'temp' => $request->temp,
            'bp' => $request->bp,
            'hr' => $request->hr,
            'rr' => $request->rr,
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $imc,
            'spo2' => $request->spo2,
            'notes' => $request->notes,
        ]);

        // Redirigimos al monitor de atención después de guardar
        return redirect()->route('attentions.index')
            ->with('success', 'Triaje guardado correctamente.');
    }
}