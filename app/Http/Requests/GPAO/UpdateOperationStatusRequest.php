<?php

namespace App\Http\Requests\GPAO;

use App\Enums\GPAO\OperationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OperationStatus::class)],
            'time_actual_minutes' => [
                'required_if:status,' . OperationStatus::Finished->value,
                'numeric',
                'min:0'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'time_actual_minutes.required_if' => 'Le temps réel de production est obligatoire pour terminer l\'opération.',
        ];
    }

    public function authorize(): bool
    {
        $operation = $this->route('operation');
        // On ne peut modifier une opération que si l'OF n'est pas clôturé
        return $operation && $operation->workOrder->status !== \App\Enums\GPAO\WorkOrderStatus::Completed;
    }
}
