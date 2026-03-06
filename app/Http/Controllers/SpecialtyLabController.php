<?php

namespace App\Http\Controllers;

use App\Models\SpecialtyLab;
use App\Models\Area;
use Illuminate\Http\Request;

class SpecialtyLabController extends Controller
{
    /**
     * Muestra la lista de especialidades.
     */
    public function index()
    {
        // Traemos todas las especialidades con su área padre
        $specialties = SpecialtyLab::with('area')->orderBy('name')->get();
        
        // Traemos las áreas para el select del modal (puedes filtrar por is_medical si gustas)
        $areas = Area::where('status', true)->get();

        return view('admin.specialty_labs.index', compact('specialties', 'areas'));
    }

    /**
     * Guarda una nueva especialidad (Llamada via AJAX o Form tradicional).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'status' => 'nullable|boolean'
        ]);

        SpecialtyLab::create($data);

        return redirect()->route('specialty_labs.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    /**
     * Actualiza la especialidad.
     */
    public function update(Request $request, SpecialtyLab $specialtyLab)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'status' => 'nullable|boolean'
        ]);

        $specialtyLab->update($data);

        return redirect()->route('specialty_labs.index')
            ->with('success', 'Especialidad actualizada con éxito.');
    }

    /**
     * Elimina la especialidad (Solo si no tiene exámenes asociados).
     */
    public function destroy(SpecialtyLab $specialtyLab)
    {
        // Verificamos si tiene exámenes asociados para evitar errores de integridad
        if ($specialtyLab->labExams()->count() > 0) {
            return redirect()->route('specialty_labs.index')
                ->with('error', 'No se puede eliminar: tiene exámenes asociados.');
        }

        $specialty_lab->delete();

        return redirect()->route('specialty_labs.index')
            ->with('success', 'Especialidad eliminada correctamente.');
    }
}