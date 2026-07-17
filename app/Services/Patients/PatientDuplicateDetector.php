<?php

namespace App\Services\Patients;

use App\Models\Patient;
use App\Models\PatientDuplicateRule;
use Illuminate\Database\Eloquent\Collection;

class PatientDuplicateDetector
{
    public function find(array $data, ?Patient $ignoredPatient = null): Collection
    {
        $rules = PatientDuplicateRule::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $duplicates = new Collection();

        foreach ($rules as $rule) {
            $query = Patient::query();
            $canApply = true;

            foreach ($rule->fields as $field) {
                $value = $this->valueFor($field, $data);

                if ($value === null || $value === '') {
                    $canApply = false;
                    break;
                }

                $query->where($field, $value);
            }

            if (! $canApply) {
                continue;
            }

            if ($ignoredPatient) {
                $query->whereKeyNot($ignoredPatient->id);
            }

            $query->get()->each(function (Patient $patient) use ($duplicates): void {
                if (! $duplicates->contains('id', $patient->id)) {
                    $duplicates->push($patient);
                }
            });
        }

        return $duplicates;
    }

    private function valueFor(string $field, array $data): mixed
    {
        if ($field === 'document_number') {
            return $data['document_number'] ?? $data['dni'] ?? null;
        }

        return $data[$field] ?? null;
    }
}
