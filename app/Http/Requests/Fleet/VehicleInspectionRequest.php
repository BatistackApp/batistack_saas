<?php

namespace App\Http\Requests\Fleet;

use App\Enums\Fleet\InspectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleInspectionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'type' => ['required', Rule::enum(InspectionType::class)],
            'inspection_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date', 'after:inspection_date'],
            'result' => ['required', 'in:passed,failed,with_reservations'],
            'observations' => ['nullable', 'string'],
            // Pour la GED (Gestion Électronique de Documents)
            'report_file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
