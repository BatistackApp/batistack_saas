<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OuvrageFactory extends Factory
{
    protected $model = Ouvrage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->text(),
            'production_coast' => $this->faker->randomFloat(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
