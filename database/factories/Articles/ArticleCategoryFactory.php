<?php

namespace Database\Factories\Articles;

use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'tenants_id' => Tenants::factory(),
            'parent_id' => null, // Peut être surchargé pour créer des sous-catégories
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
