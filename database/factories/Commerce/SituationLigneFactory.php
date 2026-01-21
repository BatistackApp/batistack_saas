<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\Situation;
use App\Models\Commerce\SituationLigne;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SituationLigneFactory extends Factory
{
    protected $model = SituationLigne::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'percentage_avancement' => $this->faker->randomFloat(),
            'prix_unitaire' => $this->faker->randomFloat(),
            'tva' => $this->faker->word(),
            'montant_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'situation_id' => Situation::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
