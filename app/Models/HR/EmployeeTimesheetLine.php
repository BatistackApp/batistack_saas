<?php

namespace App\Models\HR;

use App\Models\Chantiers\Chantier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTimesheetLine extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function employeeTimesheet(): BelongsTo
    {
        return $this->belongsTo(EmployeeTimesheet::class);
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    protected function casts(): array
    {
        return [
            'hours_travel' => 'decimal:2',
            'hours_work' => 'decimal:2',
        ];
    }
}
