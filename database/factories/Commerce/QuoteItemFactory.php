<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Quote;
use App\Models\Commerce\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_price_ht' => $this->faker->randomFloat(),
            'order' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'quote_id' => Quote::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
