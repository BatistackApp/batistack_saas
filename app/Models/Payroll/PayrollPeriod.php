<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use HasFactory, HasTenant;
    protected $fillable = ['tenants_id', 'name', 'start_date', 'end_date', 'status'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => PayrollStatus::class,
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}
