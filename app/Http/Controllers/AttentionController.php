<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Patient;
use App\Models\Voucher;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\LabExam;
use App\Models\SpecialityResult;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttentionController extends Controller
{
    /**
     * Monitor Global de Atenciones
     */
    public function index(Request $request)
    {
        $statusFilter = $request->status === 'all' ? 'all' : 'pending';
        // 1. Obtener áreas médicas activas para las columnas del monitor
        $areas = Area::where('areas.status', 1)
            ->where(function ($q) {
                $q->where('areas.slug', 'triaje')
                  ->orWhere(function ($medicalQ) {
                      $medicalQ->where('areas.is_medical', 1)
                          ->whereHas('services', function ($serviceQ) {
                              $serviceQ->where('services.status', 1)
                                  ->where(function ($templateQ) {
                                      $templateQ->whereNotNull('services.template_id')
                                          ->orWhere(function ($inheritQ) {
                                              $inheritQ->whereNull('services.template_id')
                                                  ->whereHas('area', function ($areaQ) {
                                                      $areaQ->whereNotNull('template_id')
                                                          ->orWhereHas('parent', fn ($parentQ) => $parentQ->whereNotNull('template_id'));
                                                  });
                                          });
                                  });
                          });
                  });
            })
            ->get();

        // 2. Manejo de petición AJAX para reactividad del buscador y filtros
        if ($request->ajax()) {
            $fecha = $request->date ?? Carbon::now()->format('Y-m-d');

            // Consulta base: Pacientes que tienen Vouchers (ventas) en la fecha seleccionada
            $query = Patient::whereHas('vouchers', function ($q) use ($fecha) {
                $q->whereDate('created_at', $fecha)
                  ->where('status', 'paid');
            });

            // Filtro dinámico por nombre, apellido o DNI
            if ($request->search) {
                 $query->where(function ($q) use ($request) {
                    $q->where('dni', 'LIKE', "%{$request->search}%")
                      ->orWhere('first_name', 'LIKE', "%{$request->search}%")
                      ->orWhere('last_name', 'LIKE', "%{$request->search}%");
                });
            }

            $patients = $query->with([
                'vouchers' => function ($q) use ($fecha) {
                    $q->whereDate('created_at', $fecha)
                      ->where('status', 'paid')
                      ->with('orderItems.itemable.area.parent', 'orderItems.specialityResult');
                },
                'triages' => function ($q) use ($fecha) {
                    $q->whereDate('created_at', $fecha)->latest();
                }
            ])
            ->get()
                ->map(function ($patient) use ($statusFilter) {
                    $allItems = $patient->vouchers->flatMap->orderItems
                        ->filter(function ($item) use ($statusFilter) {
                            if (!$item->itemable || !($item->itemable instanceof Service)) {
                                return false;
                            }

                            if ($statusFilter !== 'all' && $item->status === 'completed') {
                                return false;
                            }

                            return (bool) $item->itemable->getActiveTemplate();
                        });

                    $patient->medical_orders = $allItems->groupBy(function ($item) {
                        return $item->itemable->area_id;
                    })->map(function ($group) {
                        return $group->map(function ($item) {
                            $template = $item->itemable->getActiveTemplate();
                            return [
                                'order_item_id' => $item->id,
                                'service_name' => $item->itemable->name,
                                'status' => $item->status,
                                'type' => class_basename($item->itemable_type),
                                'template_name' => $template?->name,
                                'template_schema' => $template?->schema ?? [],
                                'speciality_result' => [
                                    'content' => $item->specialityResult?->content ?? [],
                                    'status' => $item->specialityResult?->status,
                                    'pdf_path' => $item->specialityResult?->pdf_path,
                                ],
                            ];
                        })->values();
                    });

                $patient->paid_area_ids = $patient->medical_orders->keys()->map(fn ($id) => (int) $id)->values()->toArray();
                    $patient->area_order_counts = $patient->medical_orders->map(fn ($orders) => $orders->count());
                    $patient->voucher_count = $patient->vouchers->count();

                // Verificación de Triaje (Signos Vitales)
                $patient->is_triaged = $patient->triages->isNotEmpty();
                    $patient->triage_id = $patient->triages->first()?->id;

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
            'observations' => 'nullable|string',
            'template_data' => 'nullable|array',
        ]);

        try {
            $item = OrderItem::findOrFail($request->order_item_id);

            $payload = $request->template_data ?? [];

            $payloadText = $request->filled('observations')
                ? $request->observations
                : json_encode($payload, JSON_UNESCAPED_UNICODE);

            if ($item->itemable_type === Service::class) {
                $result = $item->specialityResult()->updateOrCreate(
                        ['order_item_id' => $item->id],
                        [
                            'content' => $payload,
                            'user_id' => auth()->id(),
                            'status' => 'finalized',
                        ]
                    );

                    $result->update([
                    'pdf_path' => route('attentions.print', $item),
                ]);
                } elseif ($item->itemable_type === LabExam::class) {
                $item->labResult()->updateOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'lab_exam_id' => $item->itemable_id,
                        'result_value' => $payloadText,
                        'is_abnormal' => $request->boolean('is_abnormal', false),
                    ]
               );
            }

            $item->update(['status' => 'completed']);

            return response()->json(['message' => 'Atención registrada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function print(OrderItem $item)
    {
        abort_unless($item->itemable_type === Service::class, 404);

        $result = $item->specialityResult;
        abort_unless($result instanceof SpecialityResult, 404);

        $content = is_array($result->content) ? $result->content : [];

        return view('admin.attentions.print', [
            'item' => $item->load(['voucher.patient', 'itemable']),
            'result' => $result,
            'content' => $content,
        ]);
    }
}