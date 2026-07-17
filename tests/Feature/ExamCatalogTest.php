<?php

namespace Tests\Feature;

use App\Models\AnalyticalPrinciple;
use App\Models\Area;
use App\Models\Exam;
use App\Models\ExamMethod;
use App\Models\Manufacturer;
use App\Models\MeasurementUnit;
use App\Models\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_can_be_linked_to_area_sample_method_and_reference_range(): void
    {
        $area = Area::create(['name' => 'Bioquímica', 'slug' => 'bioquimica', 'status' => true, 'is_medical' => true]);
        $sampleType = SampleType::create(['code' => 'SUERO', 'name' => 'Suero', 'status' => true]);
        $unit = MeasurementUnit::create(['code' => 'MG_DL', 'name' => 'Miligramos por decilitro', 'symbol' => 'mg/dL', 'status' => true]);
        $principle = AnalyticalPrinciple::create(['code' => 'COLORIMETRIA', 'name' => 'Colorimetría', 'status' => true]);
        $manufacturer = Manufacturer::create(['code' => 'GENERIC', 'name' => 'Fabricante genérico', 'status' => true]);

        $exam = Exam::create([
            'code' => 'GLU',
            'name' => 'Glucosa',
            'requires_fasting' => true,
            'status' => true,
        ]);

        $exam->areas()->attach($area->id, ['is_primary' => true]);
        $exam->sampleTypes()->attach($sampleType->id, ['is_default' => true]);

        $method = ExamMethod::create([
            'exam_id' => $exam->id,
            'analytical_principle_id' => $principle->id,
            'manufacturer_id' => $manufacturer->id,
            'sample_type_id' => $sampleType->id,
            'measurement_unit_id' => $unit->id,
            'name' => 'Glucosa oxidasa',
            'result_type' => 'decimal',
            'decimals' => 2,
            'is_default' => true,
            'status' => true,
        ]);

        $method->referenceRanges()->create([
            'measurement_unit_id' => $unit->id,
            'min_value' => 70,
            'max_value' => 110,
            'reference_text' => '70 - 110 mg/dL',
            'status' => true,
        ]);

        $this->assertTrue($exam->areas()->whereKey($area->id)->exists());
        $this->assertTrue($exam->sampleTypes()->whereKey($sampleType->id)->exists());
        $this->assertTrue($exam->methods()->where('name', 'Glucosa oxidasa')->exists());
        $this->assertDatabaseHas('exam_method_reference_ranges', [
            'exam_method_id' => $method->id,
            'reference_text' => '70 - 110 mg/dL',
        ]);
    }
}
