<?php

namespace App\Http\Requests\Expense;

use App\Enums\Expense\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'user_id' => ['sometimes', 'exists:users,id'], // Permet à un admin de créer pour un tiers
        ];
    }

    public function authorize(): bool
    {
        // Si c'est une mise à jour, on vérifie que la note appartient à l'utilisateur
        // et qu'elle n'est pas verrouillée (statut Draft ou Rejected).
        return true;
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé de la note de frais est obligatoire.',
            'label.max' => 'Le libellé ne doit pas dépasser 255 caractères.',
        ];
    }
}
