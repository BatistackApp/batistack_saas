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
        $report = $this->route('expense_report');

        // Création : Autorisée pour tous les utilisateurs connectés
        if (! $report) {
            return true;
        }

        // Mise à jour : Propriétaire du rapport + Statut modifiable
        $isOwner = $report->user_id === auth()->id();
        $isEditable = in_array($report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected]);
        $isAdmin = auth()->user()->hasRole('tenant_admin');

        return $isAdmin || ($isOwner && $isEditable);
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé de la note de frais est obligatoire.',
            'label.max' => 'Le libellé ne doit pas dépasser 255 caractères.',
        ];
    }
}
