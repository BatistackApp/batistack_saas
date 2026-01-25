<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Observers\Payroll\PayrollSlipObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PayrollSlipObserver::class])]
class PayrollSlip extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollSlipLine::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollSlipDeduction::class);
    }

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'year' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'validated_at' => 'datetime',
            'exported_at' => 'datetime',
            'status' => PayrollStatus::class,
        ];
    }
}
