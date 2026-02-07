<?php

namespace Database\Factories\Locations;

use App\Models\Articles\Article;
use App\Models\Locations\RentalContract;
use App\Models\Locations\RentalItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RentalItemFactory extends Factory
{
    protected $model = RentalItem::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'daily_rate_ht' => $this->faker->randomFloat(),
            'weekly_rate_ht' => $this->faker->randomFloat(),
            'monthly_rate_ht' => $this->faker->randomFloat(),
            'is_weekend_included' => $this->faker->boolean(),
            'insurance_pct' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'rental_contract_id' => RentalContract::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
