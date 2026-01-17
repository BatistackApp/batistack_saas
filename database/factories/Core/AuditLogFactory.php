<?php

namespace Database\Factories\Core;

use App\Models\Core\AuditLog;
use App\Models\Core\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'auditable_type' => 'App\\Models\\Tier',
            'auditable_id' => 1,
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'changes' => [
                'name' => $this->faker->company(),
            ],
            'user_id' => User::factory(),
        ];
    }
}
