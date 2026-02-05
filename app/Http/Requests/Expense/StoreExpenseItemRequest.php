<?php

namespace App\Http\Requests\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\Expense\ExpenseReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'expense_report_id' => ['required', 'exists:expense_reports,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:500'],
            'amount_ttc' => ['required', 'numeric', 'min:0.01'],
            'tax_rate' => ['required', 'numeric', 'in:0,2.1,5.5,10,20'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB max
            'metadata' => ['nullable', 'array'],
            'metadata.distance' => ['required_if:requires_distance,true', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_ttc.min' => 'Le montant doit être supérieur à zéro.',
            'tax_rate.in' => 'Le taux de TVA sélectionné n\'est pas valide.',
            'date.before_or_equal' => 'La date du frais ne peut pas être dans le futur.',
            'receipt.max' => 'Le justificatif est trop lourd (maximum 5 Mo).',
            'metadata.distance.required_if' => 'La distance est obligatoire pour cette catégorie de frais.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
