<?php

namespace App\Services\Patients;

use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientService
{
    public function __construct(
        private readonly PatientDuplicateDetector $duplicateDetector,
        private readonly AuditLogger $auditLogger
    ) {
    }

    public function create(array $data): Patient
    {
        $data = $this->normalize($data);
        $duplicates = $this->duplicateDetector->find($data);

        if ($duplicates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dni' => 'Ya existe un paciente con datos coincidentes.',
            ]);
        }

        return DB::transaction(function () use ($data): Patient {
            $patient = Patient::create($data);

            $this->auditLogger->log(
                action: 'patients.created',
                auditable: $patient,
                newValues: Arr::except($patient->fresh()->toArray(), ['created_at', 'updated_at'])
            );

            return $patient;
        });
    }

    public function update(Patient $patient, array $data): Patient
    {
        $data = $this->normalize($data);
        $duplicates = $this->duplicateDetector->find($data, $patient);

        if ($duplicates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dni' => 'Ya existe otro paciente con datos coincidentes.',
            ]);
        }

        return DB::transaction(function () use ($patient, $data): Patient {
            $oldValues = Arr::except($patient->toArray(), ['created_at', 'updated_at']);

            $patient->update($data);

            $this->auditLogger->log(
                action: 'patients.updated',
                auditable: $patient,
                oldValues: $oldValues,
                newValues: Arr::except($patient->fresh()->toArray(), ['created_at', 'updated_at'])
            );

            return $patient;
        });
    }

    private function normalize(array $data): array
    {
        $documentNumber = $data['document_number'] ?? $data['dni'] ?? null;

        $data['document_number'] = $documentNumber;
        $data['dni'] = $data['dni'] ?? $documentNumber;
        $data['status'] = $data['status'] ?? true;

        return $data;
    }
}
