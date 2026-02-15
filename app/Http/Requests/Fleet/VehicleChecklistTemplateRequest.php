<?php

namespace App\Http\Requests\Fleet;

use App\Enums\Fleet\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class VehicleChecklistTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'vehicle_type' => ['required', new Enum(VehicleType::class)],
            'is_active'    => ['boolean'],
            // Validation optionnelle des questions si envoyées en même temps (bulk)
            'questions'              => ['nullable', 'array'],
            'questions.*.label'      => ['required_with:questions', 'string'],
            'questions.*.response_type' => ['required_with:questions', 'string', 'in:boolean,text,numeric'],
            'questions.*.is_mandatory'  => ['boolean'],
            'questions.*.sort_order'    => ['integer'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('fleet.manage');
    }
}
