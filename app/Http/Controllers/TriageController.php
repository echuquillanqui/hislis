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

    private function triageRules(bool $requiresPatientId = false): array
    {
        $rules = [
            'temp' => 'required|numeric|between:30,45',
            'bp' => ['required', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'hr' => 'required|integer|between:20,250',
            'rr' => 'required|integer|between:5,80',
            'weight' => 'required|numeric|between:1,500',
            'height' => 'required|numeric|between:0.3,2.8',
            'spo2' => 'nullable|integer|between:0,100',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($requiresPatientId) {
            $rules['patient_id'] = 'required|exists:patients,id';
        }

        return $rules;
    }

    private function triageValidationMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'between.numeric' => 'El campo :attribute debe estar entre :min y :max.',
            'between.integer' => 'El campo :attribute debe estar entre :min y :max.',
            'regex' => 'La :attribute debe tener el formato correcto (ejemplo: 120/80).',
            'exists' => 'El paciente seleccionado no existe.',
            'max' => 'El campo :attribute no debe superar los :max caracteres.',
        ];
    }

    private function triageValidationAttributes(): array
    {
        return [
            'patient_id' => 'paciente',
            'temp' => 'temperatura',
            'bp' => 'presión arterial',
            'hr' => 'frecuencia cardíaca',
            'rr' => 'frecuencia respiratoria',
            'weight' => 'peso',
            'height' => 'talla',
            'spo2' => 'saturación de oxígeno',
            'notes' => 'observaciones',
        ];
    }

    public function index()
    {
        $pendientes = Triage::with('patient')->latest()->get();

        return view('admin.attentions.index', compact('pendientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->triageRules(true),
            $this->triageValidationMessages(),
            $this->triageValidationAttributes()
        );

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
        $request->validate(
            $this->triageRules(),
            $this->triageValidationMessages(),
            $this->triageValidationAttributes()
        );

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