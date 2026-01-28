<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollTravelAllowanceSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollTravelAllowanceSettingFactory extends Factory
{
    protected $model = PayrollTravelAllowanceSetting::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'type' => $this->faker->word(),
            'rate_per_km' => $this->faker->randomFloat(),
            'forfeit_amount' => $this->faker->randomFloat(),
            'max_amount_per_day' => $this->faker->randomFloat(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
        ];
    }
}
