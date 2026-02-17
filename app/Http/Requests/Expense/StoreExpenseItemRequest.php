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
            'expense_report_id'   => ['required', 'exists:expense_reports,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'project_id'          => ['nullable', 'exists:projects,id'],
            'date'                => ['required', 'date', 'before_or_equal:today'],
            'description'         => ['required', 'string', 'max:500'],

            // Types de frais
            'is_mileage'          => ['boolean'],
            'is_fixed_allowance'  => ['boolean'],
            'is_billable'         => ['boolean'],

            // Logique conditionnelle pour les IK
            'distance_km'         => ['required_if:is_mileage,true', 'nullable', 'numeric', 'min:0.1'],
            'vehicle_power'       => ['required_if:is_mileage,true', 'nullable', 'integer', 'min:1'],
            'start_location'      => ['nullable', 'string', 'max:255'],
            'end_location'        => ['nullable', 'string', 'max:255'],

            // Montants financiers (Non requis si c'est de l'IK, car calculé par le service)
            'amount_ttc'          => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'min:0'],
            'tax_rate'            => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'in:0,2.1,5.5,10,20'],

            'receipt'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'distance_km.required_if'   => 'La distance est obligatoire pour les frais kilométriques.',
            'vehicle_power.required_if' => 'La puissance fiscale est requise pour le calcul du barème.',
            'amount_ttc.required_unless' => 'Le montant TTC est obligatoire pour les frais hors kilométriques.',
            'receipt.max'               => 'Le justificatif ne doit pas dépasser 5 Mo.',
        ];
    }

    public function authorize(): bool
    {
        $reportId = $this->input('expense_report_id') ?? $this->route('expense_item')?->expense_report_id;
        $report = ExpenseReport::find($reportId);

        if (!$report) return false;

        $isOwner = $report->user_id === auth()->id();
        $isEditable = in_array($report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected]);

        return $isOwner && $isEditable;
    }
}
