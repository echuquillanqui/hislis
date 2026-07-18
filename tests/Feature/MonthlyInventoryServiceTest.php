<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\MonthlyInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MonthlyInventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_inventory_snapshot_records_count_differences_and_closes(): void
    {
        [$product, $warehouse] = $this->inventorySetup();
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 10, 'lot_number' => 'MES-01']);

        $service = app(MonthlyInventoryService::class);
        $count = $service->open($warehouse, '2026-07-01');

        $this->assertDatabaseHas('monthly_inventory_counts', ['id' => $count->id, 'status' => 'draft']);
        $this->assertSame(10.0, (float) $count->lines->first()->snapshot_quantity);

        $count = $service->recordCounts($count, [$count->lines->first()->id => 8]);

        $this->assertSame('counted', $count->status);
        $this->assertSame(-2.0, (float) $count->lines->first()->difference_quantity);

        $count = $service->close($count);

        $this->assertSame('closed', $count->status);
        $this->assertNotNull($count->closed_at);
    }

    public function test_monthly_inventory_requires_all_lines_counted_before_close(): void
    {
        [$product, $warehouse] = $this->inventorySetup();
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 5, 'lot_number' => 'MES-02']);

        $this->expectException(ValidationException::class);

        app(MonthlyInventoryService::class)->close(app(MonthlyInventoryService::class)->open($warehouse, '2026-07-01'));
    }

    private function inventorySetup(): array
    {
        return [
            Product::create(['code' => 'CNT-001', 'name' => 'Reactivo conteo', 'category' => 'Reactivo', 'min_stock' => 1]),
            Warehouse::create(['name' => 'Almacén mensual']),
        ];
    }
}
