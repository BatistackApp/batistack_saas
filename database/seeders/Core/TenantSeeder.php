<?php

namespace Database\Seeders\Core;

use App\Models\Core\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::factory()
            ->count(3)
            ->create();
    }
}
