<?php

namespace Database\Factories\GED;

use App\Models\Core\Tenants;
use App\Models\GED\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'user_id' => User::factory(),
            'name' => $this->faker->word().'.pdf',
            'file_path' => 'documents/'.$this->faker->uuid(),
            'file_name' => $this->faker->uuid().'.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 5242880), // 1KB à 5MB
            'version' => 1,
            'metadata' => ['type' => 'contract'],
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
        ];
    }
}
