<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\SkillType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], // Ajouté pour bonne pratique
            'type' => ['required', Rule::enum(SkillType::class)],
            'description' => ['nullable', 'string', 'max:1000'], // Ajouté pour bonne pratique
            'requires_expiry' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->hasRole(['tenant_admin']);
    }
}
