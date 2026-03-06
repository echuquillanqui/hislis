<?php

namespace App\Http\Controllers;

use App\Models\LabExam;
use App\Models\SpecialtyLab;
use Illuminate\Http\Request;

class LabExamController extends Controller
{
    public function index()
    {
        // Cargamos especialidades con sus exámenes, área y ordenamos
        $specialties = SpecialtyLab::with(['labExams', 'area'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.lab_exams.index', compact('specialties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'specialty_lab_id' => 'required|exists:specialty_labs,id',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'unit'             => 'nullable|string|max:50',
            'input_type'       => 'required|in:number,text,textarea,select,radio',
            'input_options'    => 'nullable|string', // JSON string desde Alpine
            'min_ref'          => 'nullable|numeric',
            'max_ref'          => 'nullable|numeric',
        ]);

        // Limpieza de opciones si el tipo no es de selección
        if (!in_array($data['input_type'], ['select', 'radio'])) {
            $data['input_options'] = null;
        }

        LabExam::create($data);

        return redirect()->route('lab_exams.index')
            ->with('success', 'Examen creado exitosamente.');
    }

    public function update(Request $request, LabExam $labExam)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'unit'             => 'nullable|string|max:50',
            'input_type'       => 'required|in:number,text,textarea,select,radio',
            'input_options'    => 'nullable|string',
            'min_ref'          => 'nullable|numeric',
            'max_ref'          => 'nullable|numeric',
        ]);

        if (!in_array($data['input_type'], ['select', 'radio'])) {
            $data['input_options'] = null;
        }

        $labExam->update($data);

        return redirect()->route('lab_exams.index')
            ->with('success', 'Examen actualizado correctamente.');
    }

    public function destroy(LabExam $labExam)
    {
        $labExam->delete();
        return redirect()->route('lab_exams.index')
            ->with('success', 'Examen eliminado del catálogo.');
    }
}