<?php

namespace Database\Seeders;

use App\Models\Core\ModuleCatalog;
use Illuminate\Database\Seeder;

class ModuleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $coreModules = [
            ['name' => 'Tiers', 'slug' => 'tiers', 'is_core' => true],
            ['name' => 'Chantiers', 'slug' => 'chantiers', 'is_core' => true],
            ['name' => 'Articles & Stocks', 'slug' => 'articles-stocks', 'is_core' => true],
            ['name' => 'Commerce & Facturation', 'slug' => 'commerce', 'is_core' => true],
            ['name' => 'Comptabilité', 'slug' => 'comptabilite', 'is_core' => true],
            ['name' => 'RH', 'slug' => 'rh', 'is_core' => true],
            ['name' => 'GED', 'slug' => 'ged', 'is_core' => true],
            ['name' => 'Pointage', 'slug' => 'pointage', 'is_core' => true],
        ];

        $addOns = [
            ['name' => 'Banque', 'slug' => 'banque', 'price_monthly' => 49.00, 'price_yearly' => 490.00],
            ['name' => 'GPAO', 'slug' => 'gpao', 'price_monthly' => 79.00, 'price_yearly' => 790.00],
            ['name' => '3D Vision', 'slug' => '3d-vision', 'price_monthly' => 99.00, 'price_yearly' => 990.00],
            ['name' => 'Interventions', 'slug' => 'interventions', 'price_monthly' => 59.00, 'price_yearly' => 590.00],
            ['name' => 'Locations', 'slug' => 'locations', 'price_monthly' => 49.00, 'price_yearly' => 490.00],
            ['name' => 'Notes de Frais', 'slug' => 'notes-frais', 'price_monthly' => 39.00, 'price_yearly' => 390.00],
            ['name' => 'Paie', 'slug' => 'paie', 'price_monthly' => 69.00, 'price_yearly' => 690.00],
            ['name' => 'Flottes', 'slug' => 'flottes', 'price_monthly' => 79.00, 'price_yearly' => 790.00],
            ['name' => 'Pilotage', 'slug' => 'pilotage', 'price_monthly' => 89.00, 'price_yearly' => 890.00],
        ];

        foreach ($coreModules as $module) {
            ModuleCatalog::firstOrCreate(['slug' => $module['slug']], $module);
        }

        foreach ($addOns as $module) {
            ModuleCatalog::firstOrCreate(['slug' => $module['slug']], $module);
        }
    }
}
