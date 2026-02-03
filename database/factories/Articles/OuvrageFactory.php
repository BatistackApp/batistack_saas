<?php

namespace Database\Factories\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Models\Articles\Ouvrage;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OuvrageFactory extends Factory
{
    protected $model = Ouvrage::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'sku' => 'OUV-' . strtoupper($this->faker->unique()->bothify('??-####')),
            'name' => $this->faker->randomElement([
                'Mur parpaing 20cm au m²',
                'Pose de cloison placostil 72/48',
                'Installation tableau électrique 2 rangées',
                'Pose carrelage 60x60 grès cérame',
                'Enduit monocouche projeté'
            ]),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement([
                ArticleUnit::SquareMeter,
                ArticleUnit::Meter,
                ArticleUnit::Unit
            ]),
            'is_active' => true,
        ];
    }
}
