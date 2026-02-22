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
            'reason' => ['required_if:status,' . InterventionStatus::Cancelled->value, 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => "Un motif d'annulation est obligatoire pour annuler une intervention.",
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('intervention.manage');
    }
}
