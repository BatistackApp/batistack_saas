<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Core\ModuleSeeder;
use Database\Seeders\Core\PlanModuleSeeder;
use Database\Seeders\Core\PlanSeeder;
use Database\Seeders\Core\TenantSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ModuleCatalogSeeder::class,
        ]);
    }
}
