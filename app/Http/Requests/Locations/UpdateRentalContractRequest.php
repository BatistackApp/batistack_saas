<?php

namespace App\Http\Requests\Locations;

use App\Enums\Locations\RentalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalContractRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'start_date_planned' => ['sometimes', 'date'],
            'end_date_planned' => ['nullable', 'date', 'after_or_equal:start_date_planned'],
            'status' => ['sometimes', Rule::enum(RentalStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        $contract = $this->route('rental_contract');

        // Un contrat facturé ne peut plus être modifié au niveau administratif
        return $contract && $contract->status !== RentalStatus::INVOICED;
    }
}
