<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Articles\OuvrageItem;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OuvrageItemFactory extends Factory
{
    protected $model = OuvrageItem::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomFloat(),
            'unit_of_measure' => $this->faker->word(),
            'waste_percentage' => $this->faker->randomFloat(),
            'sort_order' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'ouvrage_id' => Ouvrage::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
