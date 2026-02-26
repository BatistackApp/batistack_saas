<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollScaleCategory;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollContributionTemplate extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'label',
        'code',
        'employee_rate',
        'employer_rate',
        'applicable_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employee_rate' => 'decimal:4',
            'employer_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'applicable_to' => PayrollScaleCategory::class,
        ];
    }
}
