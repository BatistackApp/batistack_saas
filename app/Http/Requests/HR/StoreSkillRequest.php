<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'type' => ['required'],
            'description' => ['nullable'],
            'requires_expiry' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->hasRole(['tenant_admin']);
    }
}
