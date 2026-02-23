<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenants;
use App\Models\Payroll\PayrollScale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollScaleFactory extends Factory
{
    protected $model = PayrollScale::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'name' => 'Indemnité Repas BTP',
            'slug' => 'repas_btp',
            'category' => 'ouvrier',
            'value' => 10.80,
            'type' => 'fixed',
            'effective_date' => now()->startOfYear(),
        ];
    }
}
