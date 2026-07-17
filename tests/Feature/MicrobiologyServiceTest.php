<?php

namespace Tests\Feature;

use App\Models\Antibiotic;
use App\Models\Exam;
use App\Models\LabResultRecord;
use App\Models\Microorganism;
use App\Models\Patient;
use App\Models\SampleType;
use App\Services\Microbiology\MicrobiologyService;
use App\Services\Orders\LabOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MicrobiologyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_growth_culture_updates_result_content(): void
    {
        $record = $this->microbiologyRecord();

        $culture = app(MicrobiologyService::class)->reportNoGrowth($record, [
            'culture_type' => 'Urocultivo',
            'direct_exam' => 'No se observan bacterias.',
        ]);

        $this->assertSame('no_growth', $culture->growth_status);
        $this->assertDatabaseHas('microbiology_cultures', [
            'lab_result_record_id' => $record->id,
            'culture_type' => 'Urocultivo',
            'growth_status' => 'no_growth',
        ]);
        $this->assertSame('no_growth', $record->refresh()->content['growth_status']);
    }

    public function test_positive_culture_stores_isolates_and_antibiogram(): void
    {
        $record = $this->microbiologyRecord();
        $microorganism = Microorganism::create(['code' => 'ECOLI', 'name' => 'Escherichia coli', 'gram_stain' => 'negative', 'status' => true]);
        $ampicillin = Antibiotic::create(['code' => 'AMP', 'name' => 'Ampicilina', 'family' => 'Penicilinas', 'status' => true]);
        $amikacin = Antibiotic::create(['code' => 'AMK', 'name' => 'Amikacina', 'family' => 'Aminoglucósidos', 'status' => true]);

        $culture = app(MicrobiologyService::class)->reportPositive($record, [
            'culture_type' => 'Urocultivo',
            'colony_count' => '>100,000 UFC/mL',
            'isolates' => [[
                'microorganism_id' => $microorganism->id,
                'quantity' => 'Abundante',
                'susceptibilities' => [
                    ['antibiotic_id' => $ampicillin->id, 'interpretation' => 'R', 'method' => 'disk_diffusion', 'disk_diffusion_mm' => 10],
                    ['antibiotic_id' => $amikacin->id, 'interpretation' => 'S', 'method' => 'mic', 'mic' => 4],
                ],
            ]],
        ]);

        $this->assertSame('positive', $culture->growth_status);
        $this->assertDatabaseHas('microbiology_isolates', ['microbiology_culture_id' => $culture->id, 'microorganism_id' => $microorganism->id]);
        $this->assertDatabaseHas('antibiotic_susceptibilities', ['interpretation' => 'R', 'antibiotic_id' => $ampicillin->id]);
        $this->assertSame('Escherichia coli', $record->refresh()->content['isolates'][0]['microorganism']);
    }

    public function test_positive_culture_requires_isolates(): void
    {
        $this->expectException(ValidationException::class);

        app(MicrobiologyService::class)->reportPositive($this->microbiologyRecord(), ['culture_type' => 'Hemocultivo', 'isolates' => []]);
    }

    private function microbiologyRecord(): LabResultRecord
    {
        $sample = SampleType::create(['code' => 'URI', 'name' => 'Orina', 'status' => true]);
        $exam = Exam::create(['code' => 'UROC', 'name' => 'Urocultivo', 'status' => true]);
        $exam->sampleTypes()->attach($sample->id, ['is_default' => true]);
        $patient = Patient::create(['dni' => '87654321', 'first_name' => 'Luis', 'last_name' => 'Rojas', 'phone' => '988', 'birth_date' => '1985-05-10', 'gender' => 'M']);
        $order = app(LabOrderService::class)->create(['patient_id' => $patient->id, 'items' => [['type' => 'exam', 'id' => $exam->id]]]);

        return LabResultRecord::create(['lab_order_item_id' => $order->items->first()->id, 'content' => [], 'status' => 'draft']);
    }
}
