<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll\PayrollPeriod;
use Illuminate\Foundation\Http\FormRequest;

class PayrollPeriodRequest extends FormRequest
{
    public function rules(): array
    {
        $periodId = $this->route('payroll_period')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                // On s'assure que les dates ne se chevauchent pas avec une autre période du même tenant
                function ($attribute, $value, $fail) use ($periodId) {
                    $overlap = PayrollPeriod::where('tenants_id', auth()->user()->tenants_id)
                        ->where(function ($query) use ($value) {
                            $query->where('start_date', '<=', $value)
                                ->where('end_date', '>=', $this->start_date);
                        })
                        ->when($periodId, fn ($q) => $q->where('id', '!=', $periodId))
                        ->exists();

                    if ($overlap) {
                        $fail('Cette période chevauche une période de paie existante.');
                    }
                },
            ],
        ];
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
        return true;
    }
}
