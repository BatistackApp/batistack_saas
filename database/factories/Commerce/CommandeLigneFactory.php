<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Commande;
use App\Models\Commerce\CommandeLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CommandeLigneFactory extends Factory
{
    protected $model = CommandeLigne::class;

    public function definition(): array
    {
        return [
            'quantite_commande' => $this->faker->randomFloat(),
            'quantite_livre' => $this->faker->randomFloat(),
            'prix_unitaire' => $this->faker->randomFloat(),
            'tva' => $this->faker->word(),
            'montant_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'commande_id' => Commande::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
