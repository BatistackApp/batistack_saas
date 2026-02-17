<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\TimeEntryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class VerifyTimeEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(TimeEntryStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        $user = $this->user();
        $targetStatus = TimeEntryStatus::tryFrom($this->status);

        // Validation N1 : Managers directs
        if ($targetStatus === TimeEntryStatus::Verified) {
            return $user->can('time_entries.verify') || $user->can('payroll.manage');
        }

        // Validation N2 : Administrateurs / Service Paie
        if ($targetStatus === TimeEntryStatus::Approved) {
            return $user->can('payroll.manage');
        }

        // Rejet : Autorisé pour toute personne ayant un droit de validation
        if ($targetStatus === TimeEntryStatus::Rejected) {
            return $user->can('time_entries.verify') || $user->can('payroll.manage');
        }

        return false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()) {
            $this->merge([
                'verified_by' => $this->user()->id,
            ]);
        }
    }
}
