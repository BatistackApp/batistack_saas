<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\HR\Employee;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id', 'payroll_period_id', 'employee_id',
        'gross_amount', 'net_social_amount', 'net_to_pay',
        'pas_rate', 'pas_amount', 'status', 'metadata',
        'tenants_id',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class)->orderBy('sort_order');
    }

    protected function casts(): array
    {
        return [
            'status' => PayrollStatus::class,
            'metadata' => 'array', // Stocke Niveau, Coefficient, Ancienneté à l'instant T
            'gross_amount' => 'decimal:2',
            'net_to_pay' => 'decimal:2',
            'pas_rate' => 'float',
        ];
    }
}
