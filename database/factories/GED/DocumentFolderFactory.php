<?php

namespace Database\Factories\GED;

use App\Models\Core\Tenants;
use App\Models\GED\DocumentFolder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DocumentFolderFactory extends Factory
{
    protected $model = DocumentFolder::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'color' => $this->faker->safeHexColor(),
            'tenants_id' => Tenants::factory(),
        ];
    }
}
