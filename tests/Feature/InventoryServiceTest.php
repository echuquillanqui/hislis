<?php

namespace Tests\Feature;

use App\Models\InventoryKardexEntry;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_entries_update_balances_and_write_immutable_kardex(): void
    {
        [$product, $warehouse] = $this->inventorySetup();

        $entry = app(InventoryService::class)->receive($product, $warehouse, [
            'quantity' => 25,
            'lot_number' => 'LOT-A',
            'expiry_date' => '2026-12-31',
            'reason' => 'Compra inicial',
        ]);

        $this->assertDatabaseHas('inventory_balances', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 25]);
        $this->assertDatabaseHas('inventory_kardex_entries', ['id' => $entry->id, 'movement_type' => 'entry', 'quantity_in' => 25, 'balance_after' => 25]);

        $this->expectException(LogicException::class);
        $entry->update(['reason' => 'Intento de edición']);
    }

    public function test_fefo_issue_consumes_earliest_expiring_lots_first(): void
    {
        [$product, $warehouse] = $this->inventorySetup();
        $service = app(InventoryService::class);
        $service->receive($product, $warehouse, ['quantity' => 10, 'lot_number' => 'LATE', 'expiry_date' => '2027-06-30']);
        $service->receive($product, $warehouse, ['quantity' => 8, 'lot_number' => 'EARLY', 'expiry_date' => '2026-01-31']);

        $entries = $service->issueFefo($product, $warehouse, 12, ['reason' => 'Uso en proceso']);

        $this->assertCount(2, $entries);
        $this->assertSame('EARLY', $entries[0]->lot->lot_number);
        $this->assertSame(8.0, (float) $entries[0]->quantity_out);
        $this->assertSame('LATE', $entries[1]->lot->lot_number);
        $this->assertSame(4.0, (float) $entries[1]->quantity_out);
        $this->assertSame(6.0, $service->totalAvailable($product, $warehouse));
    }

    public function test_negative_stock_is_prevented(): void
    {
        [$product, $warehouse] = $this->inventorySetup();
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 3, 'lot_number' => 'A']);

        $this->expectException(ValidationException::class);
        app(InventoryService::class)->issueFefo($product, $warehouse, 4);
    }

    public function test_transfer_moves_stock_between_warehouses_preserving_lot(): void
    {
        [$product, $origin] = $this->inventorySetup();
        $destination = Warehouse::create(['name' => 'Almacén laboratorio']);
        $service = app(InventoryService::class);
        $service->receive($product, $origin, ['quantity' => 5, 'lot_number' => 'TRF', 'expiry_date' => '2026-08-31']);

        $service->transferFefo($product, $origin, $destination, 2, ['reason' => 'Reposición área']);

        $this->assertSame(3.0, $service->totalAvailable($product, $origin));
        $this->assertSame(2.0, $service->totalAvailable($product, $destination));
        $this->assertDatabaseHas('inventory_kardex_entries', ['warehouse_id' => $origin->id, 'movement_type' => 'transfer_out', 'quantity_out' => 2]);
        $this->assertDatabaseHas('inventory_kardex_entries', ['warehouse_id' => $destination->id, 'movement_type' => 'transfer_in', 'quantity_in' => 2]);
    }

    private function inventorySetup(): array
    {
        return [
            Product::create(['code' => 'GLU-001', 'name' => 'Reactivo glucosa', 'category' => 'Reactivo', 'min_stock' => 1]),
            Warehouse::create(['name' => 'Almacén central']),
        ];
    }
}
