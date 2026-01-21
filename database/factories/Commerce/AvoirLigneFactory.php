<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Avoir;
use App\Models\Commerce\AvoirLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AvoirLigneFactory extends Factory
{
    protected $model = AvoirLigne::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'quantite' => $this->faker->randomFloat(),
            'prix_unitaire' => $this->faker->randomFloat(),
            'tva' => $this->faker->word(),
            'montant_ht' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'avoir_id' => Avoir::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
