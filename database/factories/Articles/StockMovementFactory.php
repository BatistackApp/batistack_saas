<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_cost_ht' => $this->faker->randomFloat(),
            'reference' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
            'target_warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
        ];
    }
}
