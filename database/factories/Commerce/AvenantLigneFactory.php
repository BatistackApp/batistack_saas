<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Avenant;
use App\Models\Commerce\AvenantLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AvenantLigneFactory extends Factory
{
    protected $model = AvenantLigne::class;

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

            'avenant_id' => Avenant::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
