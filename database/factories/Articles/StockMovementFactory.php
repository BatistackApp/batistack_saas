<?php

namespace Database\Factories\Articles;

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
            'type' => $this->faker->randomElement(StockMovementType::cases()),
            'quantity' => $this->faker->randomFloat(3, 1, 100),
            'unit_cost_ht' => $this->faker->randomFloat(2, 5, 200),
            'reference' => 'REF-'.strtoupper($this->faker->bothify('??###')),
            'notes' => $this->faker->sentence(),
            'user_id' => User::factory(),
        ];
    }

    public function entry(): static
    {
        return $this->state(fn () => ['type' => StockMovementType::Entry]);
    }

    public function exit(): static
    {
        return $this->state(fn () => [
            'type' => StockMovementType::Exit,
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
        ]);
    }
}
