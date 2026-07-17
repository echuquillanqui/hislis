<?php

namespace App\Services\Results;

use App\Models\ExamMethod;
use App\Models\LabOrderItem;
use App\Models\LabResultRecord;
use App\Models\TemplateVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LabResultService
{
    public function enter(LabOrderItem $item, array $content, ?int $userId = null): LabResultRecord
    {
        return DB::transaction(function () use ($item, $content, $userId) {
            [$templateVersion, $method] = $this->contextFor($item);
            $this->validateRequiredFields($templateVersion, $content);

            $record = LabResultRecord::updateOrCreate(
                ['lab_order_item_id' => $item->id],
                [
                    'template_version_id' => $templateVersion?->id,
                    'exam_method_id' => $method?->id,
                    'entered_by' => $userId,
                    'content' => $content,
                    'status' => 'entered',
                    'entered_at' => now(),
                ]
            );

            $record->values()->delete();
            foreach ($this->fields($templateVersion, $content) as $field) {
                $assessment = $this->assess($method, $field, $content[$field->slug] ?? null);
                $record->values()->create(array_merge([
                    'template_field_id' => $field->id,
                    'field_slug' => $field->slug,
                    'field_label' => $field->label,
                    'field_type' => $field->type,
                    'unit' => $field->unit,
                    'value' => $content[$field->slug] ?? null,
                ], $assessment));
            }

            $item->update(['status' => 'processing']);
            return $record->load('values');
        });
    }

    public function validateTechnically(LabResultRecord $record, ?int $userId = null): LabResultRecord
    {
        if ($record->status !== 'entered') {
            throw ValidationException::withMessages(['status' => 'Solo resultados ingresados pueden validarse técnicamente.']);
        }

        $record->update(['status' => 'technical_validated', 'technically_validated_by' => $userId, 'technically_validated_at' => now()]);
        return $record->refresh();
    }

    public function approve(LabResultRecord $record, ?int $userId = null, ?string $notes = null): LabResultRecord
    {
        if ($record->status !== 'technical_validated') {
            throw ValidationException::withMessages(['status' => 'Solo resultados validados técnicamente pueden aprobarse.']);
        }

        $record->update(['status' => 'approved', 'professionally_approved_by' => $userId, 'professionally_approved_at' => now(), 'approval_notes' => $notes]);
        $record->orderItem()->update(['status' => 'completed']);
        return $record->refresh();
    }

    public function correct(LabResultRecord $record, array $content, string $reason, ?int $userId = null): LabResultRecord
    {
        if ($record->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'Solo resultados aprobados pueden corregirse.']);
        }

        return DB::transaction(function () use ($record, $content, $reason, $userId) {
            $from = $record->report_version;
            $record->corrections()->create([
                'corrected_by' => $userId,
                'from_report_version' => $from,
                'to_report_version' => $from + 1,
                'previous_content' => $record->content,
                'new_content' => $content,
                'reason' => $reason,
                'corrected_at' => now(),
            ]);
            $record->update(['content' => $content, 'report_version' => $from + 1, 'status' => 'corrected']);
            $fresh = $this->enter($record->orderItem, $content, $userId);
            $fresh->update(['status' => 'corrected']);
            return $fresh->refresh();
        });
    }

    private function contextFor(LabOrderItem $item): array
    {
        $method = $item->exam?->methods()->where('is_default', true)->first() ?? $item->exam?->methods()->first();
        $templateVersion = $item->exam?->template?->versions()->where('status', 'published')->latest('version_number')->first();
        return [$templateVersion, $method];
    }

    private function fields(?TemplateVersion $templateVersion, array $content)
    {
        return $templateVersion
            ? $templateVersion->sections()->with('fields')->get()->flatMap->fields
            : collect(array_keys($content))->map(fn ($slug) => (object) ['id' => null, 'slug' => $slug, 'label' => $slug, 'type' => 'text', 'unit' => null, 'required' => false]);
    }

    private function validateRequiredFields(?TemplateVersion $templateVersion, array $content): void
    {
        $missing = $this->fields($templateVersion, $content)->filter(fn ($field) => $field->required && blank($content[$field->slug] ?? null))->pluck('label')->all();
        if ($missing) {
            throw ValidationException::withMessages(['content' => 'Campos requeridos faltantes: '.implode(', ', $missing)]);
        }
    }

    private function assess(?ExamMethod $method, object $field, mixed $value): array
    {
        $numeric = is_numeric($value) ? (float) $value : null;
        $range = $method && $numeric !== null ? $method->referenceRanges()->where('status', true)->first() : null;
        $critical = $method && $numeric !== null ? $method->criticalValues()->where('status', true)->first() : null;
        $flag = 'normal';
        if ($range?->min_value !== null && $numeric < (float) $range->min_value) $flag = 'low';
        if ($range?->max_value !== null && $numeric > (float) $range->max_value) $flag = 'high';
        if ($critical?->low_value !== null && $numeric <= (float) $critical->low_value) $flag = 'critical_low';
        if ($critical?->high_value !== null && $numeric >= (float) $critical->high_value) $flag = 'critical_high';

        return [
            'exam_method_reference_range_id' => $range?->id,
            'numeric_value' => $numeric,
            'reference_min' => $range?->min_value,
            'reference_max' => $range?->max_value,
            'reference_text' => $range?->reference_text,
            'flag' => $flag,
            'is_abnormal' => $flag !== 'normal',
            'is_critical' => str_starts_with($flag, 'critical'),
        ];
    }
}
