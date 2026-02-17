<?php

namespace App\Http\Requests\GPAO;

use App\Enums\GPAO\WorkOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $workOrder = $this->route('work_order');

        return [
            'priority' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'status' => ['sometimes', Rule::enum(WorkOrderStatus::class)],
            'planned_end_at' => [
                'sometimes',
                'date',
                'after_or_equal:'.($workOrder->planned_start_at ? $workOrder->planned_start_at->toDateString() : 'today'),
            ],
        ];
    }

    public function authorize(): bool
    {
        $workOrder = $this->route('work_order');

        return $workOrder && ! in_array($workOrder->status, [
            WorkOrderStatus::Completed,
            WorkOrderStatus::Cancelled,
        ]);
    }
}
