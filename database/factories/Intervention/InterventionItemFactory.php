<?php

namespace Database\Factories\Intervention;

use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\Ouvrage;
use App\Models\Intervention\Intervention;
use App\Models\Intervention\InterventionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InterventionItemFactory extends Factory
{
    protected $model = InterventionItem::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_price_ht' => $this->faker->randomFloat(),
            'unit_cost_ht' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'total_ht' => $this->faker->randomFloat(),
            'is_billable' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'intervention_id' => Intervention::factory(),
            'article_id' => Article::factory(),
            'ouvrage_id' => Ouvrage::factory(),
            'article_serial_number_id' => ArticleSerialNumber::factory(),
        ];
    }
}
