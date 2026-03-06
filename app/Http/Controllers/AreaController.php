<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        // Filtros adaptables
        if ($request->name) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Paginación de 10 registros traducida vía JSON
        $areas = $query->orderBy('id', 'desc')->paginate(10);

        if ($request->ajax()) {
            return response()->json($areas);
        }

        return view('admin.areas.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:areas,name|max:100',
            'is_medical' => 'required|boolean', // Agregamos validación
        ]);

        Area::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_medical' => $request->is_medical, // Guardamos el nuevo campo
            'status' => true
        ]);

        return back()->with('success', 'Área creada con éxito.');
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => 'required|unique:areas,name,' . $area->id,
            'status' => 'required|boolean',
            'is_medical' => 'required|boolean', // Agregamos validación
        ]);

        $area->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
            'is_medical' => $request->is_medical // Actualizamos el nuevo campo
        ]);

        return back()->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        // Validación de integridad: No borrar si hay personal vinculado
        if ($area->users()->exists()) {
            return back()->with('error', 'No se puede eliminar: El área tiene personal asignado.');
        }

        $area->delete();
        return back()->with('success', 'Área eliminada del sistema.');
    }
}