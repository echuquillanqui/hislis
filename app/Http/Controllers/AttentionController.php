<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Patient;
use App\Models\Voucher;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\LabExam;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttentionController extends Controller
{
    /**
     * Monitor Global de Atenciones
     */
    public function index(Request $request)
    {
        // 1. Obtener áreas médicas activas para las columnas del monitor
        $areas = Area::where('status', 1)
            ->where('is_medical', 1)
            ->get();

        // 2. Manejo de petición AJAX para reactividad del buscador y filtros
        if ($request->ajax()) {
            $fecha = $request->date ?? Carbon::now()->format('Y-m-d');

            // Consulta base: Pacientes que tienen Vouchers (ventas) en la fecha seleccionada
            $query = Patient::whereHas('vouchers', function($q) use ($fecha) {
                $q->whereDate('created_at', $fecha);
            });

            // Filtro dinámico por nombre, apellido o DNI
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('dni', 'LIKE', "%{$request->search}%")
                      ->orWhere('first_name', 'LIKE', "%{$request->search}%")
                      ->orWhere('last_name', 'LIKE', "%{$request->search}%");
                });
            }

            $patients = $query->with([
                'vouchers' => function($q) use ($fecha) {
                    $q->whereDate('created_at', $fecha);
                },
                'triages' => function($q) use ($fecha) {
                    $q->whereDate('created_at', $fecha);
                }
            ])
            ->get()
            ->map(function($patient) {
                // Aplanamos todos los items de todos los vouchers del día
                $allItems = $patient->vouchers->flatMap->orderItems
                    ->filter(fn ($item) => $item->itemable);

                // Estructuramos las órdenes médicas agrupadas por ID de Área
                $patient->medical_orders = $allItems->groupBy(function($item) {
                    return $item->itemable->area_id; // Obtenemos el área desde el servicio/examen
                })->map(function($group) {
                    return $group->map(function($item) {
                        $template = null;

                        if ($item->itemable instanceof Service) {
                            $template = $item->itemable->getActiveTemplate();
                        }
                        return [
                            'order_item_id' => $item->id,
                            'service_name'  => $item->itemable->name,
                            'status'        => $item->status,
                            'type'          => class_basename($item->itemable_type), // Service o LabExam
                            'template_name' => $template?->name,
                            'template_schema' => $template?->schema ?? []
                        ];
                    });
                });

                // IDs de áreas pagadas para habilitar botones en la vista
                $patient->paid_area_ids = $patient->medical_orders->keys()->toArray();

                // Resumen rápido por área para el monitor
                $patient->area_order_counts = $patient->medical_orders
                    ->map(fn ($orders) => $orders->count());

                $patient->voucher_count = $patient->vouchers->count();

                // Verificación de Triaje (Signos Vitales)
                $patient->is_triaged = $patient->triages->isNotEmpty();

                return $patient;
             })
            ->filter(fn ($patient) => $patient->medical_orders->isNotEmpty() || $patient->is_triaged)
            ->values();

            return response()->json([
                'patients' => $patients
            ]);
        }

        return view('admin.attentions.index', compact('areas'));
    }

    /**
     * Almacenar la atención médica (Resultados y Receta)
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'observations'  => 'required|string',
        ]);

        try {
            $item = OrderItem::findOrFail($request->order_item_id);

            // Si es un servicio de especialidad (Ginecología, Cardio, etc.)
            if ($item->itemable_type === Service::class) {
                $item->specialityResult()->updateOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'result_text' => $request->observations,
                        'user_id'     => auth()->id(), // Médico que atiende
                        'created_at'  => now()
                    ]
                );
            } 
            // Si es Laboratorio
            elseif ($item->itemable_type === LabExam::class) {
                // Aquí se guardaría en LabResult según tu modelo
                $item->labResults()->create([
                    'lab_exam_id'  => $item->itemable_id,
                    'result_value' => $request->observations,
                    'is_abnormal'  => $request->is_abnormal ?? false
                ]);
            }

            // Actualizar estado del item a completado
            $item->update(['status' => 'completed']);

            return response()->json(['message' => 'Atención registrada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}