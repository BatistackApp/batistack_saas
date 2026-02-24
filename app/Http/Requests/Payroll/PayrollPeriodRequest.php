<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll\PayrollPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PayrollPeriodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    /**
     * Utilisation de la méthode after() de Laravel 12 pour les règles métiers complexes.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $periodId = $this->route('payroll_period')?->id;

            $overlap = PayrollPeriod::where('tenants_id', auth()->user()->tenants_id)
                ->where(function ($query) {
                    $query->where('start_date', '<=', $this->end_date)
                        ->where('end_date', '>=', $this->start_date);
                })
                ->when($periodId, fn ($q) => $q->where('id', '!=', $periodId))
                ->exists();

            if ($overlap) {
                $validator->errors()->add('start_date', 'Cette période chevauche une période de paie existante.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'La date de fin doit être postérieure à la date de début.',
            'name.required' => 'Le nom de la période (ex: Janvier 2026) est obligatoire.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('payroll.manage');
    }
}
