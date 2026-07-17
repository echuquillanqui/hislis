<?php

namespace Database\Seeders;

use App\Models\AnalyticalPrinciple;
use App\Models\Area;
use App\Models\Equipment;
use App\Models\Exam;
use App\Models\ExamMethod;
use App\Models\ExamMethodCriticalValue;
use App\Models\ExamMethodReferenceRange;
use App\Models\ExamProfile;
use App\Models\Manufacturer;
use App\Models\MeasurementUnit;
use App\Models\SampleType;
use App\Models\Tariff;
use App\Models\TariffItem;
use Illuminate\Database\Seeder;

class LisExamCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturer = Manufacturer::updateOrCreate(
            ['code' => 'GENERIC'],
            ['name' => 'Fabricante genérico', 'status' => true]
        );

        $equipment = Equipment::updateOrCreate(
            ['code' => 'EQ-GENERIC-001'],
            [
                'manufacturer_id' => $manufacturer->id,
                'name' => 'Equipo genérico de laboratorio',
                'model' => 'GEN-001',
                'status' => true,
            ]
        );

        $bioquimica = Area::where('slug', 'bioquimica')->first() ?? Area::where('slug', 'lab')->first();
        $hematologia = Area::where('slug', 'hematologia')->first() ?? $bioquimica;
        $suero = SampleType::where('code', 'SUERO')->first();
        $sangre = SampleType::where('code', 'SANGRE')->first();
        $mgDl = MeasurementUnit::where('code', 'MG_DL')->first();
        $gDl = MeasurementUnit::where('code', 'G_DL')->first();
        $colorimetria = AnalyticalPrinciple::where('code', 'COLORIMETRIA')->first();
        $microscopia = AnalyticalPrinciple::where('code', 'MICROSCOPIA')->first();

        $glucose = Exam::updateOrCreate(
            ['code' => 'GLU'],
            [
                'name' => 'Glucosa',
                'description' => 'Glucosa en suero',
                'default_turnaround_minutes' => 120,
                'requires_fasting' => true,
                'status' => true,
            ]
        );

        if ($bioquimica) {
            $glucose->areas()->syncWithoutDetaching([$bioquimica->id => ['is_primary' => true]]);
        }

        if ($suero) {
            $glucose->sampleTypes()->syncWithoutDetaching([$suero->id => ['is_default' => true]]);
        }

        $glucoseMethod = ExamMethod::updateOrCreate(
            ['exam_id' => $glucose->id, 'name' => 'Glucosa oxidasa'],
            [
                'analytical_principle_id' => $colorimetria?->id,
                'equipment_id' => $equipment->id,
                'manufacturer_id' => $manufacturer->id,
                'sample_type_id' => $suero?->id,
                'measurement_unit_id' => $mgDl?->id,
                'result_type' => 'decimal',
                'decimals' => 2,
                'analytical_range' => '0-600',
                'is_default' => true,
                'show_on_report' => true,
                'status' => true,
            ]
        );

        ExamMethodReferenceRange::updateOrCreate(
            ['exam_method_id' => $glucoseMethod->id, 'sex' => null, 'min_age_days' => 6570],
            [
                'measurement_unit_id' => $mgDl?->id,
                'max_age_days' => null,
                'min_value' => 70,
                'max_value' => 110,
                'reference_text' => '70 - 110 mg/dL',
                'status' => true,
            ]
        );

        ExamMethodCriticalValue::updateOrCreate(
            ['exam_method_id' => $glucoseMethod->id],
            [
                'low_value' => 40,
                'high_value' => 400,
                'message' => 'Valor crítico de glucosa; notificar al responsable clínico.',
                'requires_notification' => true,
                'status' => true,
            ]
        );

        $hemoglobin = Exam::updateOrCreate(
            ['code' => 'HB'],
            [
                'name' => 'Hemoglobina',
                'description' => 'Hemoglobina en sangre total',
                'default_turnaround_minutes' => 120,
                'requires_fasting' => false,
                'status' => true,
            ]
        );

        if ($hematologia) {
            $hemoglobin->areas()->syncWithoutDetaching([$hematologia->id => ['is_primary' => true]]);
        }

        if ($sangre) {
            $hemoglobin->sampleTypes()->syncWithoutDetaching([$sangre->id => ['is_default' => true]]);
        }

        ExamMethod::updateOrCreate(
            ['exam_id' => $hemoglobin->id, 'name' => 'Cianometahemoglobina'],
            [
                'analytical_principle_id' => $microscopia?->id,
                'equipment_id' => $equipment->id,
                'manufacturer_id' => $manufacturer->id,
                'sample_type_id' => $sangre?->id,
                'measurement_unit_id' => $gDl?->id,
                'result_type' => 'decimal',
                'decimals' => 2,
                'is_default' => true,
                'show_on_report' => true,
                'status' => true,
            ]
        );

        $profile = ExamProfile::updateOrCreate(
            ['code' => 'PERFIL_BASICO'],
            ['name' => 'Perfil básico', 'description' => 'Perfil básico inicial de laboratorio', 'status' => true]
        );

        $profile->exams()->syncWithoutDetaching([
            $glucose->id => ['sort_order' => 1],
            $hemoglobin->id => ['sort_order' => 2],
        ]);

        $tariff = Tariff::updateOrCreate(
            ['code' => 'GENERAL'],
            [
                'name' => 'Tarifa general',
                'currency' => 'PEN',
                'valid_from' => now()->toDateString(),
                'valid_to' => null,
                'status' => true,
            ]
        );

        TariffItem::updateOrCreate(
            ['tariff_id' => $tariff->id, 'tariffable_id' => $glucose->id, 'tariffable_type' => Exam::class],
            ['price' => 15.00, 'status' => true]
        );

        TariffItem::updateOrCreate(
            ['tariff_id' => $tariff->id, 'tariffable_id' => $hemoglobin->id, 'tariffable_type' => Exam::class],
            ['price' => 12.00, 'status' => true]
        );

        TariffItem::updateOrCreate(
            ['tariff_id' => $tariff->id, 'tariffable_id' => $profile->id, 'tariffable_type' => ExamProfile::class],
            ['price' => 25.00, 'status' => true]
        );
    }
}
