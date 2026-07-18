<?php

namespace App\Services\Consumption;

use App\Models\ExamConsumableRequirement;
use App\Models\LabConsumption;
use App\Models\LabConsumptionAttempt;
use App\Models\LabOrderItem;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamConsumptionService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function consume(LabOrderItem $item, Warehouse $defaultWarehouse, array $data = []): LabConsumptionAttempt
    {
        return DB::transaction(function () use ($item, $defaultWarehouse, $data) {
            $key = $data['idempotency_key'] ?? "lab-item:{$item->id}:consume";
            $existing = LabConsumptionAttempt::where('idempotency_key', $key)->with('consumptions')->first();
            if ($existing) {
                return $existing;
            }

            $attempt = LabConsumptionAttempt::create([
                'lab_order_item_id' => $item->id,
                'idempotency_key' => $key,
                'status' => 'processing',
                'reprocess_number' => $data['reprocess_number'] ?? 0,
            ]);

            try {
                foreach ($this->requirementsFor($item, (bool) ($data['automatic'] ?? true)) as $requirement) {
                    $warehouse = $requirement->warehouse ?: $defaultWarehouse;
                    $actual = (float) ($data['actual_quantities'][$requirement->id] ?? $requirement->estimated_quantity);
                    $estimated = (float) $requirement->estimated_quantity;
                    $this->assertVariance($requirement, $actual, $estimated);

                    $entries = $this->inventory->issueFefo($requirement->product, $warehouse, $actual, [
                        'movement_type' => 'exam_consumption',
                        'reference_type' => LabConsumptionAttempt::class,
                        'reference_id' => $attempt->id,
                        'reason' => "Consumo por prueba {$item->description}",
                        'user_id' => $data['user_id'] ?? null,
                    ]);

                    LabConsumption::create([
                        'lab_consumption_attempt_id' => $attempt->id,
                        'lab_order_item_id' => $item->id,
                        'exam_consumable_requirement_id' => $requirement->id,
                        'product_id' => $requirement->product_id,
                        'warehouse_id' => $warehouse->id,
                        'estimated_quantity' => $estimated,
                        'actual_quantity' => $actual,
                        'variance_quantity' => $actual - $estimated,
                        'unit_cost' => $this->unitCost($entries, $actual),
                        'total_cost' => 0,
                    ]);
                }

                $attempt->update(['status' => 'completed', 'processed_at' => now()]);
                return $attempt->load('consumptions');
            } catch (\Throwable $exception) {
                $attempt->update(['status' => 'failed', 'failure_reason' => $exception->getMessage()]);
                throw $exception;
            }
        });
    }

    public function reverse(LabConsumptionAttempt $attempt, array $data = []): LabConsumptionAttempt
    {
        return DB::transaction(function () use ($attempt, $data) {
            if ($attempt->status === 'reversed') {
                return $attempt->load('consumptions');
            }

            $reverseAttempt = LabConsumptionAttempt::create([
                'lab_order_item_id' => $attempt->lab_order_item_id,
                'idempotency_key' => $data['idempotency_key'] ?? "attempt:{$attempt->id}:reverse",
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            foreach ($attempt->consumptions()->where('is_reversal', false)->get() as $consumption) {
                $this->inventory->receive($consumption->product, $consumption->warehouse, [
                    'quantity' => (float) $consumption->actual_quantity,
                    'lot_number' => 'REVERSO-CONSUMO',
                    'movement_type' => 'exam_consumption_reversal',
                    'reference_type' => LabConsumptionAttempt::class,
                    'reference_id' => $reverseAttempt->id,
                    'reason' => $data['reason'] ?? 'Reversión de consumo por prueba',
                    'user_id' => $data['user_id'] ?? null,
                ]);
                LabConsumption::create($consumption->only(['lab_order_item_id', 'exam_consumable_requirement_id', 'product_id', 'warehouse_id', 'estimated_quantity', 'actual_quantity', 'unit_cost', 'total_cost']) + [
                    'lab_consumption_attempt_id' => $reverseAttempt->id,
                    'variance_quantity' => -1 * (float) $consumption->variance_quantity,
                    'is_reversal' => true,
                    'reverses_consumption_id' => $consumption->id,
                ]);
            }

            $attempt->update(['status' => 'reversed']);
            return $reverseAttempt->load('consumptions');
        });
    }

    private function requirementsFor(LabOrderItem $item, bool $automatic)
    {
        if (! $item->exam_id) {
            return collect();
        }

        return ExamConsumableRequirement::with(['product', 'warehouse'])
            ->where('exam_id', $item->exam_id)
            ->where('is_active', true)
            ->when($automatic, fn ($query) => $query->where('auto_consume', true))
            ->get();
    }

    private function assertVariance(ExamConsumableRequirement $requirement, float $actual, float $estimated): void
    {
        if ($actual <= 0) {
            throw ValidationException::withMessages(['actual_quantity' => 'El consumo real debe ser mayor que cero.']);
        }
        $allowed = $estimated * ((float) $requirement->allowed_variance_percent / 100);
        if ($allowed > 0 && abs($actual - $estimated) > $allowed) {
            throw ValidationException::withMessages(['actual_quantity' => 'La variación supera el porcentaje permitido.']);
        }
    }

    private function unitCost(array $entries, float $actual): float
    {
        return $actual > 0 ? collect($entries)->sum(fn ($entry) => (float) $entry->quantity_out * 0) / $actual : 0;
    }
}
