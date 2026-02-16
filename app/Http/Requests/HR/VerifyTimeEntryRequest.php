<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\TimeEntryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class VerifyTimeEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(TimeEntryStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'verified_by' => $this->user()->id,
        ]);
    }
}
