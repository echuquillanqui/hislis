<?php

namespace App\Http\Controllers;

use App\Models\MonthlyInventoryCount;
use App\Models\Warehouse;
use App\Services\Inventory\MonthlyInventoryService;
use Illuminate\Http\Request;

class MonthlyInventoryCountController extends Controller
{
    public function index()
    {
        return view('admin.inventory.monthly.index', [
            'counts' => MonthlyInventoryCount::with('warehouse')->latest('period_month')->paginate(12),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, MonthlyInventoryService $service)
    {
        $data = $request->validate(['warehouse_id' => ['required', 'exists:warehouses,id'], 'period_month' => ['required', 'date']]);
        $count = $service->open(Warehouse::findOrFail($data['warehouse_id']), $data['period_month'], $request->user()?->id);

        return redirect()->route('monthly-inventory-counts.show', $count)->with('success', 'Inventario mensual preparado con snapshot de saldos.');
    }

    public function show(MonthlyInventoryCount $monthlyInventoryCount)
    {
        return view('admin.inventory.monthly.show', ['count' => $monthlyInventoryCount->load(['warehouse', 'lines.product', 'lines.lot'])]);
    }

    public function update(Request $request, MonthlyInventoryCount $monthlyInventoryCount, MonthlyInventoryService $service)
    {
        $data = $request->validate(['counts' => ['required', 'array'], 'counts.*' => ['nullable', 'numeric', 'min:0']]);
        $service->recordCounts($monthlyInventoryCount->load('lines'), array_filter($data['counts'], fn ($value) => $value !== null));

        return back()->with('success', 'Conteo físico actualizado.');
    }

    public function close(Request $request, MonthlyInventoryCount $monthlyInventoryCount, MonthlyInventoryService $service)
    {
        $service->close($monthlyInventoryCount, $request->user()?->id);

        return back()->with('success', 'Inventario mensual cerrado.');
    }
}
