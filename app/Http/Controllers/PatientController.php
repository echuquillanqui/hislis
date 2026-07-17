<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Services\Patients\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query()->with('documentType');

        if ($request->search) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('dni', 'LIKE', "%{$search}%")
                    ->orWhere('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('clinical_history_number', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%");
            });
        }

        $patients = $query->orderBy('id', 'desc')->paginate(10);

        if ($request->ajax()) {
            return response()->json($patients);
        }

        return view('admin.patients.index');
    }

    public function store(StorePatientRequest $request, PatientService $patientService)
    {
        $patientService->create($request->validated());

        return back()->with('success', 'Paciente registrado correctamente.');
    }

    public function update(UpdatePatientRequest $request, Patient $patient, PatientService $patientService)
    {
        $patientService->update($patient, $request->validated());

        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy(Patient $patient)
    {
        if ($patient->triages()->exists() || $patient->appointments()->exists() || $patient->vouchers()->exists()) {
            return back()->with('error', 'No se puede eliminar: El paciente ya cuenta con historial clínico, citas u órdenes.');
        }

        $patient->delete();

        return back()->with('success', 'Registro eliminado correctamente.');
    }
}
