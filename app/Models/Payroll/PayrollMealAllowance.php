<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollMealAllowance extends Model
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
            'amount' => 'decimal:2',
        ];
    }
}
