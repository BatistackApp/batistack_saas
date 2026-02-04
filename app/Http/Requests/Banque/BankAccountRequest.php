<?php

namespace App\Http\Requests\Banque;

use App\Enums\Banque\BankAccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:34'], // Validation IBAN simplifiée
            'type' => ['required', Rule::enum(BankAccountType::class)],
            'initial_balance' => ['required', 'numeric'],

            // Champs techniques pour l'agrégation Bridge
            'bridge_id' => ['nullable', 'string', 'max:100'],
            'bridge_item_id' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom usuel du compte est obligatoire.',
            'type.Illuminate\Validation\Rules\Enum' => 'Le type de compte sélectionné est invalide.',
            'initial_balance.numeric' => 'Le solde initial doit être une valeur numérique.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
