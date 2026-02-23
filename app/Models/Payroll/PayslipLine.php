<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayslipLineType;
use App\Observers\Payroll\PayslipLineObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PayslipLineObserver::class])]
class PayslipLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payslip_id', 'label', 'base', 'rate',
        'amount_gain', 'amount_deduction', 'employer_amount',
        'type', 'sort_order', 'is_manual_adjustment',
        'is_taxable', 'is_net_deduction',
    ];

    protected function casts(): array
    {
        return [
            'type' => PayslipLineType::class,
            'base' => 'decimal:2',
            'rate' => 'decimal:4',
            'amount_gain' => 'decimal:2',
            'amount_deduction' => 'decimal:2',
            'employer_amount' => 'decimal:2',
            'is_manual_adjustment' => 'boolean',
            'is_taxable' => 'boolean',
            'is_net_deduction' => 'boolean',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }
}
