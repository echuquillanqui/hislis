<?php

namespace App\Http\Controllers;

use App\Models\{Service, Area, Template};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        // Importante: Eager loading para evitar errores en la vista
        $services = Service::with(['area.parent', 'template', 'area.template'])->get();
        $allAreas = Area::orderBy('name')->get();
        $templates = Template::where('status', true)->get();

        return view('admin.services.index', compact('services', 'allAreas', 'templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'area_id' => 'required|exists:areas,id',
            'template_id' => 'nullable|exists:templates,id',
            'area_parent_id' => 'nullable|exists:areas,id',
            'area_template_id' => 'nullable|exists:templates,id',
        ]);

        DB::transaction(function () use ($data) {
            Service::create([
                'name' => $data['name'],
                'price' => $data['price'],
                'area_id' => $data['area_id'],
                'template_id' => $data['template_id'],
                'status' => true
            ]);

            // Actualizar el área para que la jerarquía funcione
            $area = Area::find($data['area_id']);
            $area->update([
                'parent_id' => $data['area_parent_id'],
                'template_id' => $data['area_template_id']
            ]);
        });

        return redirect()->back()->with('success', 'Servicio creado correctamente.');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'area_id' => 'required|exists:areas,id',
            'template_id' => 'nullable|exists:templates,id',
            'area_parent_id' => 'nullable|exists:areas,id',
            'area_template_id' => 'nullable|exists:templates,id',
        ]);

        DB::transaction(function () use ($data, $service) {
            $service->update([
                'name' => $data['name'],
                'price' => $data['price'],
                'area_id' => $data['area_id'],
                'template_id' => $data['template_id'],
            ]);

            $area = Area::find($data['area_id']);
            $area->update([
                'parent_id' => $data['area_parent_id'],
                'template_id' => $data['area_template_id']
            ]);
        });

        return redirect()->back()->with('success', 'Servicio actualizado.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Servicio eliminado correctamente.');
    }
}