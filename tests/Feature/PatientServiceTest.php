<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\Patient;
use App\Models\PatientDuplicateRule;
use App\Models\User;
use App\Services\Patients\PatientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PatientServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_service_creates_patient_and_audit_log(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $documentType = $this->createDocumentType();
        $this->createDuplicateRules();

        $patient = app(PatientService::class)->create([
            'document_type_id' => $documentType->id,
            'document_number' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'Torres',
            'birth_date' => '1991-05-20',
            'gender' => 'F',
            'phone' => '999888777',
            'email' => 'ana@example.test',
        ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'dni' => '12345678',
            'document_number' => '12345678',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'patients.created',
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->id,
        ]);
    }

    public function test_patient_service_blocks_configured_duplicate_document(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $documentType = $this->createDocumentType();
        $this->createDuplicateRules();

        Patient::create([
            'document_type_id' => $documentType->id,
            'document_number' => '12345678',
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'Torres',
            'birth_date' => '1991-05-20',
            'gender' => 'F',
            'phone' => '999888777',
            'status' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(PatientService::class)->create([
            'document_type_id' => $documentType->id,
            'document_number' => '12345678',
            'first_name' => 'Ana María',
            'last_name' => 'Torres',
            'birth_date' => '1991-05-20',
            'gender' => 'F',
        ]);
    }

    private function createDocumentType(): DocumentType
    {
        return DocumentType::create([
            'code' => 'DNI',
            'name' => 'Documento Nacional de Identidad',
            'min_length' => 8,
            'max_length' => 8,
            'is_numeric' => true,
            'status' => true,
        ]);
    }

    private function createDuplicateRules(): void
    {
        PatientDuplicateRule::create([
            'code' => 'DOC_TYPE_NUMBER',
            'name' => 'Tipo y número de documento',
            'fields' => ['document_type_id', 'document_number'],
            'is_blocking' => true,
            'status' => true,
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Audit User',
            'username' => 'audit',
            'dni' => '87654321',
            'email' => 'audit@example.test',
            'password' => Hash::make('secret'),
            'status' => true,
        ]);
    }
}
