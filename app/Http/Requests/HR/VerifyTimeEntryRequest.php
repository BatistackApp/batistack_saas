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
            'verified_by' => ['required', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
