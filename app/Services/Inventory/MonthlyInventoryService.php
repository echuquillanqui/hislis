<?php

namespace App\Services\Inventory;

use App\Models\InventoryBalance;
use App\Models\MonthlyInventoryCount;
use App\Models\Warehouse;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyInventoryService
{
    public function open(Warehouse $warehouse, CarbonInterface|string $month, ?int $userId = null): MonthlyInventoryCount
    {
        return DB::transaction(function () use ($warehouse, $month, $userId) {
            $period = Carbon::parse($month)->startOfMonth()->toDateString();
            $count = MonthlyInventoryCount::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'period_month' => $period],
                ['created_by' => $userId, 'status' => 'draft']
            );

            if ($count->lines()->exists()) {
                return $count->load(['warehouse', 'lines.product', 'lines.lot']);
            }

            InventoryBalance::with(['product', 'lot'])
                ->where('warehouse_id', $warehouse->id)
                ->orderBy('product_id')
                ->chunkById(100, function ($balances) use ($count) {
                    foreach ($balances as $balance) {
                        $count->lines()->create([
                            'product_id' => $balance->product_id,
                            'inventory_lot_id' => $balance->inventory_lot_id,
                            'snapshot_quantity' => $balance->quantity,
                            'counted_quantity' => null,
                            'difference_quantity' => 0,
                            'unit_cost' => 0,
                            'valuation_amount' => 0,
                        ]);
                    }
                });

            return $count->load(['warehouse', 'lines.product', 'lines.lot']);
        });
    }

    public function recordCounts(MonthlyInventoryCount $count, array $lineCounts): MonthlyInventoryCount
    {
        if ($count->status === 'closed') {
            throw ValidationException::withMessages(['status' => 'El inventario mensual ya está cerrado.']);
        }

        return DB::transaction(function () use ($count, $lineCounts) {
            foreach ($count->lines as $line) {
                if (! array_key_exists($line->id, $lineCounts)) {
                    continue;
                }
                $counted = (float) $lineCounts[$line->id];
                $difference = $counted - (float) $line->snapshot_quantity;
                $line->update([
                    'counted_quantity' => $counted,
                    'difference_quantity' => $difference,
                    'valuation_amount' => $difference * (float) $line->unit_cost,
                ]);
            }

            $count->update(['status' => 'counted', 'counted_at' => now()]);

            return $count->refresh()->load(['warehouse', 'lines.product', 'lines.lot']);
        });
    }

    public function close(MonthlyInventoryCount $count, ?int $userId = null): MonthlyInventoryCount
    {
        if ($count->lines()->whereNull('counted_quantity')->exists()) {
            throw ValidationException::withMessages(['lines' => 'Todas las líneas deben tener conteo físico antes del cierre.']);
        }

        $count->update(['status' => 'closed', 'approved_by' => $userId, 'approved_at' => now(), 'closed_at' => now()]);

        return $count->load(['warehouse', 'lines.product', 'lines.lot']);
    }
}
