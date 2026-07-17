<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patients.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['nullable', 'exists:document_types,id'],
            'document_number' => ['nullable', 'string', 'max:20'],
            'dni' => ['required_without:document_number', 'string', 'max:20', 'unique:patients,dni'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', 'in:M,F,O'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'clinical_history_number' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
