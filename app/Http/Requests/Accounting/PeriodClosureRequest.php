<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class PeriodClosureRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020', 'max:'.(now()->year + 1)],
            'confirm_lock' => ['required', 'accepted'], // Force la confirmation consciente
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('accounting.manage');
    }
}
