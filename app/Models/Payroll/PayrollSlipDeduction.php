<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollDeductionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipDeduction extends Model
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
            'type' => PayrollDeductionType::class,
            'amount' => 'decimal:2',
        ];
    }
}
