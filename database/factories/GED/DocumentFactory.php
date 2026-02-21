<?php

namespace Database\Factories\GED;

use App\Enums\GED\DocumentStatus;
use App\Enums\GED\DocumentType;
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
            'type' => $this->faker->randomElement(DocumentType::cases()),
            'name' => $this->faker->word(),
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'file_name' => $this->faker->word().'.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1000, 5000000),
            'version' => 1,
            'status' => $this->faker->randomElement(DocumentStatus::cases()),
            'metadata' => ['author' => $this->faker->name()],
        ];
    }
}
