<?php

namespace App\Services\Microbiology;

use App\Models\LabResultRecord;
use App\Models\MicrobiologyCulture;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MicrobiologyService
{
    public function reportNoGrowth(LabResultRecord $record, array $data): MicrobiologyCulture
    {
        return DB::transaction(function () use ($record, $data) {
            $culture = $record->microbiologyCultures()->create([
                'lab_sample_id' => $data['lab_sample_id'] ?? null,
                'culture_type' => $data['culture_type'],
                'growth_status' => 'no_growth',
                'direct_exam' => $data['direct_exam'] ?? null,
                'notes' => $data['notes'] ?? 'Sin crecimiento bacteriano.',
                'incubated_at' => $data['incubated_at'] ?? null,
                'reported_at' => $data['reported_at'] ?? now(),
            ]);

            $record->update(['content' => $this->contentFor($culture), 'status' => 'entered', 'entered_at' => now()]);
            $record->orderItem()->update(['status' => 'processing']);

            return $culture->load('isolates');
        });
    }

    public function reportPositive(LabResultRecord $record, array $data): MicrobiologyCulture
    {
        if (empty($data['isolates'])) {
            throw ValidationException::withMessages(['isolates' => 'Un cultivo positivo requiere al menos un aislamiento.']);
        }

        return DB::transaction(function () use ($record, $data) {
            $culture = $record->microbiologyCultures()->create([
                'lab_sample_id' => $data['lab_sample_id'] ?? null,
                'culture_type' => $data['culture_type'],
                'growth_status' => 'positive',
                'colony_count' => $data['colony_count'] ?? null,
                'direct_exam' => $data['direct_exam'] ?? null,
                'notes' => $data['notes'] ?? null,
                'incubated_at' => $data['incubated_at'] ?? null,
                'reported_at' => $data['reported_at'] ?? now(),
            ]);

            foreach ($data['isolates'] as $index => $isolateData) {
                $isolate = $culture->isolates()->create([
                    'microorganism_id' => $isolateData['microorganism_id'],
                    'isolate_number' => $isolateData['isolate_number'] ?? $index + 1,
                    'quantity' => $isolateData['quantity'] ?? null,
                    'notes' => $isolateData['notes'] ?? null,
                ]);

                foreach ($isolateData['susceptibilities'] ?? [] as $susceptibility) {
                    $isolate->susceptibilities()->create(Arr::only($susceptibility, ['antibiotic_id', 'interpretation', 'method', 'mic', 'disk_diffusion_mm', 'notes']));
                }
            }

            $culture->load('isolates.microorganism', 'isolates.susceptibilities.antibiotic');
            $record->update(['content' => $this->contentFor($culture), 'status' => 'entered', 'entered_at' => now()]);
            $record->orderItem()->update(['status' => 'processing']);

            return $culture;
        });
    }

    public function contentFor(MicrobiologyCulture $culture): array
    {
        $culture->loadMissing('isolates.microorganism', 'isolates.susceptibilities.antibiotic');

        return [
            'culture_type' => $culture->culture_type,
            'growth_status' => $culture->growth_status,
            'colony_count' => $culture->colony_count,
            'direct_exam' => $culture->direct_exam,
            'notes' => $culture->notes,
            'isolates' => $culture->isolates->map(fn ($isolate) => [
                'number' => $isolate->isolate_number,
                'microorganism' => $isolate->microorganism->name,
                'quantity' => $isolate->quantity,
                'susceptibilities' => $isolate->susceptibilities->map(fn ($susceptibility) => [
                    'antibiotic' => $susceptibility->antibiotic->name,
                    'interpretation' => $susceptibility->interpretation,
                    'method' => $susceptibility->method,
                    'mic' => $susceptibility->mic,
                    'disk_diffusion_mm' => $susceptibility->disk_diffusion_mm,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
