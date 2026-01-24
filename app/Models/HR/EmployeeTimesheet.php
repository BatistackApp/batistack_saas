<?php

namespace App\Models\HR;

use App\Enums\HR\TimesheetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeTimesheet extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EmployeeTimesheetLine::class);
    }

    protected function casts(): array
    {
        return [
            'timesheet_date' => 'date',
            'total_hours_work' => 'decimal:2',
            'total_hours_travel' => 'decimal:2',
            'status' => TimesheetStatus::class,
        ];
    }
}
