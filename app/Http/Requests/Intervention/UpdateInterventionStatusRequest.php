<?php

namespace App\Http\Requests\Intervention;

use App\Enums\Intervention\InterventionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInterventionStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(InterventionStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('intervention.manage');
    }
}
