<?php

namespace Tests\Feature;

use App\Models\AnalyticalPrinciple;
use App\Models\Exam;
use App\Models\ExamProfile;
use App\Models\LabOrder;
use App\Models\MeasurementUnit;
use App\Models\Patient;
use App\Models\SampleType;
use App\Models\Tariff;
use App\Models\TariffItem;
use App\Services\Orders\LabOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_calculates_totals_expands_profiles_and_creates_samples(): void
    {
        [$glucose, $hemoglobin, $profile, $tariff] = $this->catalog();
        $patient = Patient::create(['dni' => '12345678', 'first_name' => 'Ana', 'last_name' => 'Paz', 'phone' => '999', 'birth_date' => '1990-01-01', 'gender' => 'F']);

        $order = app(LabOrderService::class)->create([
            'patient_id' => $patient->id,
            'tariff_id' => $tariff->id,
            'items' => [
                ['type' => 'exam', 'id' => $glucose->id],
                ['type' => 'profile', 'id' => $profile->id, 'discount' => 5],
            ],
        ]);

        $this->assertSame('ORD-'.now()->format('Y').'-000001', $order->code);
        $this->assertSame('35.00', $order->subtotal);
        $this->assertSame('5.00', $order->discount_total);
        $this->assertSame('30.00', $order->total);
        $this->assertSame('sample_pending', $order->status);
        $this->assertCount(4, $order->items); // one billable exam, one billable profile, two profile exam children
        $this->assertCount(2, $order->samples);
        $this->assertDatabaseHas('lab_sample_events', ['event' => 'created']);
    }

    public function test_order_codes_are_correlative(): void
    {
        [$glucose, , , $tariff] = $this->catalog();
        $patient = Patient::create(['dni' => '87654321', 'first_name' => 'Luis', 'last_name' => 'Sol', 'phone' => '999', 'birth_date' => '1988-01-01', 'gender' => 'M']);

        app(LabOrderService::class)->create(['patient_id' => $patient->id, 'tariff_id' => $tariff->id, 'items' => [['type' => 'exam', 'id' => $glucose->id]]]);
        $second = app(LabOrderService::class)->create(['patient_id' => $patient->id, 'tariff_id' => $tariff->id, 'items' => [['type' => 'exam', 'id' => $glucose->id]]]);

        $this->assertSame('ORD-'.now()->format('Y').'-000002', $second->code);
        $this->assertSame(2, LabOrder::count());
    }

    private function catalog(): array
    {
        $serum = SampleType::create(['code' => 'SUERO', 'name' => 'Suero', 'status' => true]);
        $blood = SampleType::create(['code' => 'SANGRE', 'name' => 'Sangre total', 'status' => true]);
        MeasurementUnit::create(['code' => 'MG_DL', 'name' => 'mg/dL', 'symbol' => 'mg/dL', 'status' => true]);
        AnalyticalPrinciple::create(['code' => 'COLOR', 'name' => 'Colorimetría', 'status' => true]);

        $glucose = Exam::create(['code' => 'GLU', 'name' => 'Glucosa', 'status' => true]);
        $glucose->sampleTypes()->attach($serum->id, ['is_default' => true]);
        $hemoglobin = Exam::create(['code' => 'HB', 'name' => 'Hemoglobina', 'status' => true]);
        $hemoglobin->sampleTypes()->attach($blood->id, ['is_default' => true]);

        $profile = ExamProfile::create(['code' => 'PB', 'name' => 'Perfil básico', 'status' => true]);
        $profile->exams()->attach([$glucose->id => ['sort_order' => 1], $hemoglobin->id => ['sort_order' => 2]]);

        $tariff = Tariff::create(['code' => 'GENERAL', 'name' => 'General', 'valid_from' => now()->toDateString(), 'status' => true]);
        TariffItem::create(['tariff_id' => $tariff->id, 'tariffable_type' => Exam::class, 'tariffable_id' => $glucose->id, 'price' => 15, 'status' => true]);
        TariffItem::create(['tariff_id' => $tariff->id, 'tariffable_type' => ExamProfile::class, 'tariffable_id' => $profile->id, 'price' => 20, 'status' => true]);

        return [$glucose, $hemoglobin, $profile, $tariff];
    }
}
