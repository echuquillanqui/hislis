<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamMethod;
use App\Models\ExamMethodCriticalValue;
use App\Models\ExamMethodReferenceRange;
use App\Models\LabOrderItem;
use App\Models\LabResultRecord;
use App\Models\MeasurementUnit;
use App\Models\Patient;
use App\Models\SampleType;
use App\Services\Orders\LabOrderService;
use App\Services\Results\LabResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LabResultServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_entry_stores_json_and_normalized_values_with_flags(): void
    {
        [$item] = $this->orderItemWithRanges();

        $record = app(LabResultService::class)->enter($item, ['glucose' => '250']);

        $this->assertSame('entered', $record->status);
        $this->assertSame(['glucose' => '250'], $record->content);
        $this->assertDatabaseHas('lab_result_values', [
            'lab_result_record_id' => $record->id,
            'field_slug' => 'glucose',
            'flag' => 'critical_high',
            'is_abnormal' => true,
            'is_critical' => true,
        ]);
    }

    public function test_result_validation_approval_and_correction_are_audited(): void
    {
        [$item] = $this->orderItemWithRanges();
        $service = app(LabResultService::class);
        $record = $service->enter($item, ['glucose' => '100']);

        $validated = $service->validateTechnically($record);
        $approved = $service->approve($validated, notes: 'Conforme');
        $corrected = $service->correct($approved, ['glucose' => '105'], 'Error de transcripción');

        $this->assertSame('corrected', $corrected->status);
        $this->assertSame(2, $corrected->report_version);
        $this->assertDatabaseHas('lab_result_corrections', [
            'lab_result_record_id' => $record->id,
            'from_report_version' => 1,
            'to_report_version' => 2,
            'reason' => 'Error de transcripción',
        ]);
    }

    public function test_approval_requires_technical_validation(): void
    {
        [$item] = $this->orderItemWithRanges();
        $record = app(LabResultService::class)->enter($item, ['glucose' => '100']);

        $this->expectException(ValidationException::class);
        app(LabResultService::class)->approve($record);
    }

    private function orderItemWithRanges(): array
    {
        $unit = MeasurementUnit::create(['code' => 'MGDL', 'name' => 'Miligramos por decilitro', 'symbol' => 'mg/dL', 'status' => true]);
        $sample = SampleType::create(['code' => 'SER', 'name' => 'Suero', 'status' => true]);
        $exam = Exam::create(['code' => 'GLU', 'name' => 'Glucosa', 'status' => true]);
        $exam->sampleTypes()->attach($sample->id, ['is_default' => true]);
        $method = ExamMethod::create(['exam_id' => $exam->id, 'measurement_unit_id' => $unit->id, 'name' => 'Hexoquinasa', 'is_default' => true, 'status' => true]);
        ExamMethodReferenceRange::create(['exam_method_id' => $method->id, 'measurement_unit_id' => $unit->id, 'min_value' => 70, 'max_value' => 110, 'status' => true]);
        ExamMethodCriticalValue::create(['exam_method_id' => $method->id, 'low_value' => 40, 'high_value' => 200, 'status' => true]);
        $patient = Patient::create(['dni' => '12345678', 'first_name' => 'Ana', 'last_name' => 'Paz', 'phone' => '999', 'birth_date' => '1990-01-01', 'gender' => 'F']);
        $order = app(LabOrderService::class)->create(['patient_id' => $patient->id, 'items' => [['type' => 'exam', 'id' => $exam->id]]]);

        return [$order->items->first(), $method];
    }
}
