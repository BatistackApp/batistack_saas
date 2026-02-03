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
            'quote_id' => Quote::factory(),
            'article_id' => Article::factory(),
            'label' => $this->faker->sentence(3),
            'quantity' => $this->faker->randomFloat(3, 1, 50),
            'unit_price_ht' => $this->faker->randomFloat(2, 10, 1000),
            'order' => 0,
        ];
    }
}
