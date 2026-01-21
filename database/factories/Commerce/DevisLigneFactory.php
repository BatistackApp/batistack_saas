<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Devis;
use App\Models\Commerce\DevisLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DevisLigneFactory extends Factory
{
    protected $model = DevisLigne::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'quantite' => $this->faker->randomFloat(),
            'prix_unitaire' => $this->faker->randomFloat(),
            'tva' => $this->faker->word(),
            'montant_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'devis_id' => Devis::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
