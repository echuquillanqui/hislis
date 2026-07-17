<?php

namespace App\Services\Inventory;

use App\Models\InventoryBalance;
use App\Models\InventoryKardexEntry;
use App\Models\InventoryLot;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receive(Product $product, Warehouse $warehouse, array $data): InventoryKardexEntry
    {
        return DB::transaction(function () use ($product, $warehouse, $data) {
            $quantity = $this->positiveQuantity($data['quantity'] ?? 0);
            $lot = $this->lotFor($product, $data);
            $balance = $this->balanceFor($product, $warehouse, $lot);
            $balance->increment('quantity', $quantity);
            $balance->refresh();

            return $this->kardex($product, $warehouse, $lot, 'entry', $quantity, 0, (float) $balance->quantity, $data);
        });
    }

    public function issueFefo(Product $product, Warehouse $warehouse, float $quantity, array $data = []): array
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $data) {
            $remaining = $this->positiveQuantity($quantity);
            $available = $this->totalAvailable($product, $warehouse);

            if ($available < $remaining) {
                throw ValidationException::withMessages(['quantity' => 'La salida excede el saldo disponible.']);
            }

            $entries = [];
            $balances = InventoryBalance::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('quantity', '>', 0)
                ->with('lot')
                ->join('inventory_lots', 'inventory_balances.inventory_lot_id', '=', 'inventory_lots.id')
                ->orderByRaw('inventory_lots.expiry_date IS NULL')
                ->orderBy('inventory_lots.expiry_date')
                ->orderBy('inventory_lots.received_at')
                ->select('inventory_balances.*')
                ->lockForUpdate()
                ->get();

            foreach ($balances as $balance) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (float) $balance->quantity);
                $balance->decrement('quantity', $take);
                $balance->refresh();
                $entries[] = $this->kardex($product, $warehouse, $balance->lot, 'exit', 0, $take, (float) $balance->quantity, $data);
                $remaining -= $take;
            }

            return $entries;
        });
    }

    public function transferFefo(Product $product, Warehouse $from, Warehouse $to, float $quantity, array $data = []): array
    {
        return DB::transaction(function () use ($product, $from, $to, $quantity, $data) {
            $outs = $this->issueFefo($product, $from, $quantity, [...$data, 'movement_type' => 'transfer_out']);
            $ins = [];
            foreach ($outs as $out) {
                $ins[] = $this->receive($product, $to, [
                    ...$data,
                    'movement_type' => 'transfer_in',
                    'quantity' => (float) $out->quantity_out,
                    'lot_number' => $out->lot->lot_number,
                    'expiry_date' => optional($out->lot->expiry_date)->toDateString(),
                    'received_at' => optional($out->lot->received_at)->toDateString(),
                ]);
            }
            return ['out' => $outs, 'in' => $ins];
        });
    }

    public function totalAvailable(Product $product, Warehouse $warehouse): float
    {
        return (float) InventoryBalance::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->sum('quantity');
    }

    private function lotFor(Product $product, array $data): InventoryLot
    {
        return InventoryLot::firstOrCreate(
            ['product_id' => $product->id, 'lot_number' => $data['lot_number'] ?? 'SIN-LOTE'],
            ['expiry_date' => $data['expiry_date'] ?? null, 'received_at' => $data['received_at'] ?? now()->toDateString(), 'status' => 'available']
        );
    }

    private function balanceFor(Product $product, Warehouse $warehouse, InventoryLot $lot): InventoryBalance
    {
        return InventoryBalance::firstOrCreate(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'inventory_lot_id' => $lot->id], ['quantity' => 0]);
    }

    private function kardex(Product $product, Warehouse $warehouse, ?InventoryLot $lot, string $defaultType, float $in, float $out, float $balanceAfter, array $data): InventoryKardexEntry
    {
        return InventoryKardexEntry::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'inventory_lot_id' => $lot?->id,
            'user_id' => $data['user_id'] ?? null,
            'movement_type' => $data['movement_type'] ?? $defaultType,
            'quantity_in' => $in,
            'quantity_out' => $out,
            'balance_after' => $balanceAfter,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);
    }

    private function positiveQuantity(float|int|string $quantity): float
    {
        if ((float) $quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad debe ser mayor que cero.']);
        }

        return (float) $quantity;
    }
}
