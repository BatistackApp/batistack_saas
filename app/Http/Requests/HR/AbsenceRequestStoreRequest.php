<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\AbsenceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AbsenceRequestStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', new Enum(AbsenceType::class)],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'reason' => ['nullable', 'string', 'max:500'],
            'justification_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'starts_at.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'ends_at.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'justification_file.max' => 'Le fichier ne doit pas dépasser 2 Mo.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('absences.create');
    }
}
