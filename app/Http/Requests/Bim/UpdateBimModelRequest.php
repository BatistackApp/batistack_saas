<?php

namespace App\Http\Requests\Bim;

use App\Enums\Bim\BimModelStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBimModelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(BimModelStatus::class)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('bim.manage');
    }
}
