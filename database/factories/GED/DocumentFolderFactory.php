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
            'name' => $this->faker->name(),
            'color' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
