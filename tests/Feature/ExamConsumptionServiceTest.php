<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamConsumableRequirement;
use App\Models\LabConsumptionAttempt;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Consumption\ExamConsumptionService;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamConsumptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumption_is_idempotent_and_integrates_with_inventory(): void
    {
        [$item, $product, $warehouse, $requirement] = $this->consumptionSetup();
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 10, 'lot_number' => 'R1', 'expiry_date' => '2026-01-31']);

        $service = app(ExamConsumptionService::class);
        $first = $service->consume($item, $warehouse, ['idempotency_key' => 'ORD-1-GLU']);
        $second = $service->consume($item, $warehouse, ['idempotency_key' => 'ORD-1-GLU']);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('completed', $first->fresh()->status);
        $this->assertSame(1, LabConsumptionAttempt::count());
        $this->assertDatabaseHas('lab_consumptions', [
            'lab_consumption_attempt_id' => $first->id,
            'exam_consumable_requirement_id' => $requirement->id,
            'actual_quantity' => 2,
            'variance_quantity' => 0,
        ]);
        $this->assertSame(8.0, app(InventoryService::class)->totalAvailable($product, $warehouse));
        $this->assertDatabaseHas('inventory_kardex_entries', ['movement_type' => 'exam_consumption', 'reference_id' => $first->id, 'quantity_out' => 2]);
    }

    public function test_reprocessing_uses_a_new_attempt_and_records_real_variance(): void
    {
        [$item, $product, $warehouse] = $this->consumptionSetup(10);
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 10, 'lot_number' => 'R1']);

        $attempt = app(ExamConsumptionService::class)->consume($item, $warehouse, [
            'idempotency_key' => 'reprocess-1',
            'reprocess_number' => 1,
            'actual_quantities' => [ExamConsumableRequirement::first()->id => 2.1],
        ]);

        $this->assertSame(1, $attempt->reprocess_number);
        $this->assertDatabaseHas('lab_consumptions', ['lab_consumption_attempt_id' => $attempt->id, 'actual_quantity' => 2.1, 'variance_quantity' => 0.1]);
        $this->assertSame(7.9, app(InventoryService::class)->totalAvailable($product, $warehouse));
    }

    public function test_consumption_can_be_reversed(): void
    {
        [$item, $product, $warehouse] = $this->consumptionSetup();
        app(InventoryService::class)->receive($product, $warehouse, ['quantity' => 5, 'lot_number' => 'R1']);
        $attempt = app(ExamConsumptionService::class)->consume($item, $warehouse, ['idempotency_key' => 'consume-reverse']);

        $reverse = app(ExamConsumptionService::class)->reverse($attempt, ['idempotency_key' => 'reverse-1']);

        $this->assertSame('reversed', $attempt->fresh()->status);
        $this->assertTrue((bool) $reverse->consumptions->first()->is_reversal);
        $this->assertSame(5.0, app(InventoryService::class)->totalAvailable($product, $warehouse));
        $this->assertDatabaseHas('inventory_kardex_entries', ['movement_type' => 'exam_consumption_reversal', 'quantity_in' => 2]);
    }

    private function consumptionSetup(float $allowedVariance = 0): array
    {
        $patient = Patient::create(['dni' => uniqid(), 'first_name' => 'Ana', 'last_name' => 'Paz', 'birth_date' => '1990-01-01', 'gender' => 'F']);
        $exam = Exam::create(['code' => uniqid('GLU'), 'name' => 'Glucosa', 'status' => true]);
        $order = LabOrder::create(['patient_id' => $patient->id, 'code' => uniqid('ORD'), 'ordered_at' => now(), 'status' => 'registered']);
        $item = LabOrderItem::create(['lab_order_id' => $order->id, 'orderable_type' => Exam::class, 'orderable_id' => $exam->id, 'exam_id' => $exam->id, 'description' => $exam->name]);
        $product = Product::create(['code' => uniqid('R'), 'name' => 'Reactivo glucosa', 'category' => 'Reactivo', 'min_stock' => 1]);
        $warehouse = Warehouse::create(['name' => uniqid('Almacén')]);
        $requirement = ExamConsumableRequirement::create(['exam_id' => $exam->id, 'product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'estimated_quantity' => 2, 'allowed_variance_percent' => $allowedVariance]);

        return [$item, $product, $warehouse, $requirement];
    }
}
