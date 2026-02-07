<?php

namespace App\Http\Requests\GPAO;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeWorkOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity_produced' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('gpao.manage');
    }
}
