<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->search) {
            $query->where('dni', 'LIKE', "%{$request->search}%")
                  ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('first_name', 'LIKE', "%{$request->search}%");
        }

        $patients = $query->orderBy('id', 'desc')->paginate(10);

        if ($request->ajax()) {
            return response()->json($patients);
        }

        return view('admin.patients.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|unique:patients,dni',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:M,F',
            'phone' => 'nullable|string'
        ]);

        Patient::create($validated);
        return back()->with('success', 'Paciente registrado correctamente.');
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dni' => 'required|unique:patients,dni,' . $patient->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:M,F',
            'phone' => 'nullable|string'
        ]);

        $patient->update($validated);
        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy(Patient $patient)
    {
        // VALIDACIÓN DE INTEGRIDAD
        // Si el paciente tiene registros en tablas hijas, abortamos el borrado.
        if ($patient->triages()->exists() || $patient->appointments()->exists()) {
            return back()->with('error', 'No se puede eliminar: El paciente ya cuenta con historial clínico o citas.');
        }

        $patient->delete();
        return back()->with('success', 'Registro eliminado correctamente.');
    }
}