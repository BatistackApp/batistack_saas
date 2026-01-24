<?php

namespace Database\Factories\Payroll;

use App\Enums\Payroll\PayrollExportFormat;
use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollSettingFactory extends Factory
{
    protected $model = PayrollSetting::class;

    public function definition(): array
    {
        return [
            'default_export_format' => PayrollExportFormat::Generic,
            'social_contribution_rate' => 42.00,
            'auto_validate_payroll' => false,
            'auto_export_payroll' => false,

            'tenant_id' => Tenant::factory(),
        ];
    }
}
