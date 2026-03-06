<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Service;
use App\Models\LabExam;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BundleController extends Controller
{
    /**
     * Muestra el listado de paquetes con sus servicios y exámenes (incluyendo especialidad).
     */
    public function index()
    {
        // Cargamos los paquetes. 
        // El Eager Loading carga: items -> itemable (Service/LabExam) -> specialtyLab (si es examen)
        $bundles = Bundle::with(['items.itemable' => function ($morphTo) {
            $morphTo->morphWith([
                LabExam::class => ['specialtyLab']
            ]);
        }])->latest()->get();
        
        // Datos para los selectores del Modal
        $services = Service::where('status', true)->orderBy('name')->get();
        $labExams = LabExam::with('specialtyLab')->where('status', true)->orderBy('name')->get();

        return view('admin.bundles.index', compact('bundles', 'services', 'labExams'));
    }

    /**
     * Guarda un nuevo paquete y sus componentes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el Paquete
            $bundle = Bundle::create([
                'name'        => $request->name,
                'description' => $request->description,
                'price'       => $request->price,
                'status'      => true
            ]);

            // 2. Guardar los Items (Polimórficos)
            foreach ($request->items as $item) {
                BundleItem::create([
                    'bundle_id'     => $bundle->id,
                    'itemable_id'   => $item['id'],
                    // Si el tipo enviado es 'service' usa el modelo Service, sino LabExam
                    'itemable_type' => $item['type'] === 'service' ? Service::class : LabExam::class,
                ]);
            }

            DB::commit();
            return redirect()->route('bundles.index')->with('success', 'Paquete creado con éxito.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el paquete y sincroniza los items.
     */
    public function update(Request $request, Bundle $bundle)
    {
        $request->validate([
            'name'  => 'required|string',
            'price' => 'required|numeric',
            'items' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $bundle->update($request->only('name', 'description', 'price'));

            // Sincronización: Eliminamos items anteriores y creamos los nuevos
            $bundle->items()->delete();

            foreach ($request->items as $item) {
                BundleItem::create([
                    'bundle_id'     => $bundle->id,
                    'itemable_id'   => $item['id'],
                    'itemable_type' => $item['type'] === 'service' ? Service::class : LabExam::class,
                ]);
            }

            DB::commit();
            return redirect()->route('bundles.index')->with('success', 'Paquete actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar.');
        }
    }

    /**
     * Elimina el paquete (los items se eliminan por cascada en la DB).
     */
    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('bundles.index')->with('success', 'Paquete eliminado.');
    }
}