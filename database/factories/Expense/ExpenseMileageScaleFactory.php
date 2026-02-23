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
            'tenants_id' => Tenants::factory(),
            'vehicle_power' => $this->faker->numberBetween(3, 7),
            'min_km' => 0,
            'max_km' => 5000,
            'rate_per_km' => 0.636,
            'fixed_amount' => 0,
            'active_year' => now()->year,
            'vehicle_type' => 'car',
        ];
    }

    /**
     * État pour le palier intermédiaire (5001 - 20000)
     */
    public function midRange(): self
    {
        return $this->state(fn (array $attributes) => [
            'min_km' => 5001,
            'max_km' => 20000,
            'rate_per_km' => 0.339,
            'fixed_amount' => 1488,
        ]);
    }
}
