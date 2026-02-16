<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'skill_id' => ['required', 'exists:skills,id'],
            'issue_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date', 'after:issue_date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'document_path' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'La preuve documentaire est obligatoire pour enregistrer une habilitation.',
            'expiry_date.after' => 'La date d\'expiration doit être postérieure à la date d\'émission.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->hasRole(['hr_manager', 'tenant_admin']);
    }
}
