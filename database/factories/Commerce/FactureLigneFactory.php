<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Facture;
use App\Models\Commerce\FactureLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FactureLigneFactory extends Factory
{
    protected $model = FactureLigne::class;

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

            'facture_id' => Facture::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
