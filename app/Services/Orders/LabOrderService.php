<?php

namespace App\Services\Orders;

use App\Models\Exam;
use App\Models\ExamProfile;
use App\Models\LabOrder;
use App\Models\LabOrderSequence;
use App\Models\LabSample;
use App\Models\Tariff;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LabOrderService
{
    public function create(array $data): LabOrder
    {
        return DB::transaction(function () use ($data) {
            $tariff = isset($data['tariff_id']) ? Tariff::find($data['tariff_id']) : null;
            $order = LabOrder::create([
                'branch_id' => $data['branch_id'] ?? null,
                'patient_id' => $data['patient_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'requesting_physician_id' => $data['requesting_physician_id'] ?? null,
                'tariff_id' => $tariff?->id,
                'created_by' => $data['created_by'] ?? null,
                'code' => $this->nextCode($data['branch_id'] ?? null),
                'ordered_at' => $data['ordered_at'] ?? now(),
                'status' => 'registered',
                'clinical_notes' => $data['clinical_notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $this->addOrderable($order, $item, $tariff);
            }

            $subtotal = $order->items()->sum('unit_price');
            $discount = $order->items()->sum('discount');
            $order->update([
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'total' => $order->items()->sum('total'),
                'status' => $order->samples()->where('status', 'pending')->exists() ? 'sample_pending' : 'registered',
            ]);

            return $order->load(['items.samples', 'samples.sampleType']);
        });
    }

    private function addOrderable(LabOrder $order, array $item, ?Tariff $tariff): void
    {
        $type = $item['type'];
        $model = $type === 'exam' ? Exam::findOrFail($item['id']) : ExamProfile::with('exams.sampleTypes')->findOrFail($item['id']);
        $price = $item['price'] ?? $this->priceFor($tariff, $model);
        $discount = (float) ($item['discount'] ?? 0);

        $parent = $order->items()->create([
            'orderable_type' => $model::class,
            'orderable_id' => $model->id,
            'description' => $model->name,
            'unit_price' => $price,
            'discount' => $discount,
            'total' => max(0, $price - $discount),
            'status' => 'sample_pending',
            'exam_id' => $model instanceof Exam ? $model->id : null,
        ]);

        $exams = $model instanceof Exam ? collect([$model->load('sampleTypes')]) : $model->exams;
        foreach ($exams as $exam) {
            $sampleType = $exam->sampleTypes->sortByDesc(fn ($sample) => (bool) $sample->pivot->is_default)->first();
            if (! $sampleType) {
                throw new InvalidArgumentException("El examen {$exam->code} no tiene tipo de muestra configurado.");
            }

            $itemForSample = $model instanceof Exam ? $parent : $order->items()->create([
                'orderable_type' => Exam::class,
                'orderable_id' => $exam->id,
                'parent_profile_id' => $model->id,
                'exam_id' => $exam->id,
                'description' => $exam->name,
                'unit_price' => 0,
                'discount' => 0,
                'total' => 0,
                'status' => 'sample_pending',
            ]);

            $sample = $this->sampleFor($order, $sampleType->id);
            $itemForSample->samples()->syncWithoutDetaching([$sample->id]);
        }
    }

    private function sampleFor(LabOrder $order, int $sampleTypeId): LabSample
    {
        $sample = $order->samples()->firstOrCreate(
            ['sample_type_id' => $sampleTypeId],
            ['barcode' => sprintf('%s-M%02d', $order->code, $order->samples()->count() + 1), 'status' => 'pending']
        );

        $sample->events()->firstOrCreate(
            ['event' => 'created'],
            ['occurred_at' => now(), 'notes' => 'Muestra generada al registrar la orden.']
        );

        return $sample;
    }

    private function priceFor(?Tariff $tariff, Exam|ExamProfile $model): float
    {
        if (! $tariff) {
            return 0.0;
        }

        return (float) optional($tariff->items()
            ->where('tariffable_type', $model::class)
            ->where('tariffable_id', $model->id)
            ->where('status', true)
            ->first())->price;
    }

    private function nextCode(?int $branchId): string
    {
        $year = (int) now()->format('Y');
        $sequence = LabOrderSequence::where('branch_id', $branchId)->where('year', $year)->lockForUpdate()->first();
        if (! $sequence) {
            $sequence = LabOrderSequence::create(['branch_id' => $branchId, 'year' => $year, 'next_number' => 1]);
        }

        $number = $sequence->next_number;
        $sequence->increment('next_number');

        return sprintf('ORD-%d-%06d', $year, $number);
    }
}
