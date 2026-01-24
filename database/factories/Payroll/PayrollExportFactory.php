<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollExport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollExportFactory extends Factory
{
    protected $model = PayrollExport::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'tenant_id' => Tenant::factory(),
            'format' => $this->faker->randomElement(ExportFormat::cases()),
            'year' => $this->faker->year(),
            'month' => $this->faker->month(),
            'file_path' => 'payroll/exports/' . $this->faker->file() . '.xlsx',
            'file_name' => 'payroll_export_' . $this->faker->numerify('##########') . '.csv',
            'file_size' => $this->faker->numberBetween(1000, 50000),
            'payroll_count' => $this->faker->numberBetween(1, 50),
            'exported_at' => now(),
        ];
    }
}
