<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'label' => ['required', 'string', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('tenant.settings.edit');
    }
}
