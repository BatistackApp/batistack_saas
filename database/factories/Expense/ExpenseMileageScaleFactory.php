<?php

namespace Database\Factories\Expense;

use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseMileageScale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExpenseMileageScaleFactory extends Factory
{
    protected $model = ExpenseMileageScale::class;

    public function definition(): array
    {
        return [
            'vehicle_power' => $this->faker->randomNumber(),
            'rate_per_km' => $this->faker->randomFloat(),
            'active_year' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
