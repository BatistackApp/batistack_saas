<?php

namespace App\Models\GPAO;

use App\Enums\GPAO\OperationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderOperation extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_id', 'work_center_id', 'sequence', 'label',
        'time_planned_minutes', 'time_actual_minutes', 'status'
    ];

    protected function casts(): array
    {
        return [
            'status' => OperationStatus::class,
            'time_planned_minutes' => 'decimal:2',
            'time_actual_minutes' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
