<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\OvertimeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollOvertime extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function payrollSlip(): BelongsTo
    {
        return $this->belongsTo(PayrollSlip::class);
    }

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'type' => OvertimeType::class,
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }
}
