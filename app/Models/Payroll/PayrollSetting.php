<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollExportFormat;
use App\Models\Core\Tenant;
use App\Observers\Payroll\PayrollSettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PayrollSettingObserver::class])]
class PayrollSetting extends Model
{
    use HasFactory, SoftDeletes;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected function casts(): array
    {
        return [
            'default_export_format' => PayrollExportFormat::class,
            'social_contribution_rate' => 'decimal:2',
            'auto_validate_payroll' => 'boolean',
            'auto_export_payroll' => 'boolean',
            'custom_fields' => 'array',
        ];
    }
}
