<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use App\Models\DocumentType;
use App\Models\PatientDuplicateRule;
use App\Models\PhysicianSpecialty;
use Illuminate\Database\Seeder;

class LisPatientCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDocumentTypes();
        $this->seedDuplicateRules();
        $this->seedPhysicianSpecialties();
        $this->seedCustomerTypes();
    }

    private function seedDocumentTypes(): void
    {
        $items = [
            ['DNI', 'Documento Nacional de Identidad', 8, 8, true],
            ['CE', 'Carné de Extranjería', 6, 12, false],
            ['PASAPORTE', 'Pasaporte', 6, 20, false],
            ['RUC', 'Registro Único de Contribuyentes', 11, 11, true],
            ['SIN_DOCUMENTO', 'Sin documento', null, null, false],
        ];

        foreach ($items as $index => [$code, $name, $min, $max, $numeric]) {
            DocumentType::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'min_length' => $min,
                    'max_length' => $max,
                    'is_numeric' => $numeric,
                    'status' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedDuplicateRules(): void
    {
        $items = [
            ['DOC_TYPE_NUMBER', 'Tipo y número de documento', ['document_type_id', 'document_number'], true],
            ['NAME_BIRTH_DATE', 'Nombres, apellidos y fecha de nacimiento', ['first_name', 'last_name', 'birth_date'], false],
        ];

        foreach ($items as $index => [$code, $name, $fields, $blocking]) {
            PatientDuplicateRule::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'fields' => $fields,
                    'is_blocking' => $blocking,
                    'status' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedPhysicianSpecialties(): void
    {
        $items = [
            ['MEDICINA_GENERAL', 'Medicina general'],
            ['GINECOLOGIA', 'Ginecología'],
            ['PEDIATRIA', 'Pediatría'],
            ['MEDICINA_INTERNA', 'Medicina interna'],
            ['INFECTOLOGIA', 'Infectología'],
            ['ENDOCRINOLOGIA', 'Endocrinología'],
            ['OTRA', 'Otra'],
        ];

        foreach ($items as $index => [$code, $name]) {
            PhysicianSpecialty::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'status' => true, 'sort_order' => $index + 1]
            );
        }
    }

    private function seedCustomerTypes(): void
    {
        $items = [
            ['PARTICULAR', 'Paciente particular', false, false],
            ['EMPRESA', 'Empresa', true, true],
            ['CLINICA', 'Clínica', true, true],
            ['CONVENIO', 'Convenio', false, true],
            ['ASEGURADORA', 'Aseguradora', true, true],
        ];

        foreach ($items as $index => [$code, $name, $requiresRuc, $allowsCredit]) {
            CustomerType::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'requires_ruc' => $requiresRuc,
                    'allows_credit' => $allowsCredit,
                    'status' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
