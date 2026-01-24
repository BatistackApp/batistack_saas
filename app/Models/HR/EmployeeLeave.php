<?php

namespace App\Models\HR;

use App\Enums\HR\LeaveStatus;
use App\Enums\HR\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeave extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'leave_type' => LeaveType::class,
            'status' => LeaveStatus::class,
        ];
    }

    public function getDurationInDaysAttribute(): int
    {
        return $this->end_date->diffInDays($this->start_date) + 1;
    }
}
