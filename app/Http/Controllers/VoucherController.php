<?php

namespace App\Http\Controllers;

use App\Models\{Voucher, OrderItem, LabExam, Service, Patient, LabResult, SpecialityResult};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = Voucher::with(['patient', 'user'])
            ->when($request->search, function($q) use ($request) {
                $q->whereHas('patient', function($p) use ($request) {
                    $p->where('last_name', 'LIKE', "%{$request->search}%")
                      ->orWhere('dni', 'LIKE', "%{$request->search}%");
                });
            })->latest()->paginate(15);
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $exams = LabExam::all()->map(fn($i) => ['id'=>$i->id, 'name'=>$i->name, 'price'=>(float)$i->price, 'type'=>'lab', 'cat'=>'LAB']);
        $services = Service::all()->map(fn($i) => ['id'=>$i->id, 'name'=>$i->name, 'price'=>(float)$i->price, 'type'=>'service', 'cat'=>'CONSULTA']);
        $allItems = $exams->concat($services);

        return view('admin.vouchers.create', compact('allItems'));
    }

    // BUSQUEDA DE PACIENTES - Asegúrate que la ruta en web.php sea /admin/vouchers/search-patients
    public function searchPatients(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '' || strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = Patient::query()
            ->where('dni', 'LIKE', "%{$q}%")
            ->orWhere('last_name', 'LIKE', "%{$q}%")
            ->orWhere('first_name', 'LIKE', "%{$q}%")
            ->limit(10)
            ->get(['id', 'dni', 'first_name', 'last_name']);

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'type' => 'required|in:01,03',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|in:lab,service',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $lastNum = Voucher::where('type', $data['type'])->max('number') ?? 0;
            $voucher = Voucher::create([
                'patient_id' => $data['patient_id'],
                'user_id'    => auth()->id(),
                'type'       => $data['type'],
                'series'     => $data['type'] === '01' ? 'F001' : 'B001',
                'number'     => str_pad((int)$lastNum + 1, 8, '0', STR_PAD_LEFT),
                'total'      => $data['total'],
                'status'     => 'paid'
            ]);

            foreach ($data['items'] as $item) {
                $oi = OrderItem::create([
                    'voucher_id'    => $voucher->id,
                    'itemable_id'   => $item['id'],
                    'itemable_type' => $item['type'] == 'lab' ? LabExam::class : Service::class,
                    'price'         => $item['price'],
                    'status'        => 'pending'
                ]);

                if ($item['type'] == 'lab') {
                    LabResult::create(['order_item_id' => $oi->id, 'lab_exam_id' => $item['id']]);
                } else {
                    $template = Service::findOrFail($item['id'])->getActiveTemplate();
                    SpecialityResult::create([
                        'order_item_id' => $oi->id,
                        'user_id'       => auth()->id(),
                        'content'       => $template ? $template->schema : [],
                        'status'        => 'pending'
                    ]);
                }
            }
            return redirect()->route('vouchers.index')->with('success', 'Orden creada');
        });
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['patient', 'orderItems.itemable']);

        return view('admin.vouchers.ticket', compact('voucher'));
    }

    public function printTicket(Voucher $voucher)
    {
        $voucher->load(['patient', 'orderItems.itemable']);

        return view('admin.vouchers.ticket', compact('voucher'));
    }

    public function edit(Voucher $voucher)
    {
        // Cargamos relaciones para validar en la vista
        $voucher->load(['patient', 'orderItems.itemable', 'orderItems.labResult', 'orderItems.specialityResult']);
        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function destroyItem(OrderItem $item)
    {
        $item->load(['labResult', 'specialityResult']);

        // VALIDACIÓN ANTES DE ELIMINAR
        if ($item->itemable_type == LabExam::class) {
            if ($item->labResult && !is_null($item->labResult->result_value)) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar: El laboratorio ya tiene resultados.'], 422);
            }
        } else {
            if ($item->specialityResult && !empty($item->specialityResult->content)) {
                $filled = array_filter($item->specialityResult->content);
                if (count($filled) > 0) {
                    return response()->json(['success' => false, 'message' => 'No se puede eliminar: La ficha médica ya tiene datos.'], 422);
                }
            }
        }

        $item->delete();
        return response()->json(['success' => true]);
    }
}