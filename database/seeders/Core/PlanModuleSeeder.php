<?php

namespace Database\Seeders\Core;

use App\Models\Core\Module;
use App\Models\Core\Plan;
use Illuminate\Database\Seeder;

class PlanModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Modules de base (inclus dans tous les plans)
        $coreModules = ['tiers-crm', 'chantiers', 'articles-stock', 'commerce-facturation', 'comptabilite', 'pointage-rh'];

        // Modules pour Starter (basique)
        $starterModules = $coreModules;

        // Modules pour Professional (tous les modules de base + quelques extras)
        $professionalModules = array_merge($coreModules, ['ged', 'banque', 'notes-frais']);

        // Modules pour Enterprise (tous les modules)
        $enterpriseModules = Module::pluck('slug')->toArray();

        $plans = [
            'plan-starter' => $starterModules,
            'plan-professional' => $professionalModules,
            'plan-enterprise' => $enterpriseModules,
            'plan-starter-yearly' => $starterModules,
            'plan-professional-yearly' => $professionalModules,
            'plan-enterprise-yearly' => $enterpriseModules,
        ];

        foreach ($plans as $planSlug => $moduleSlugs) {
            $plan = Plan::where('slug', $planSlug)->first();

            if ($plan) {
                foreach ($moduleSlugs as $moduleSlug) {
                    $module = Module::where('slug', $moduleSlug)->first();

                    if ($module) {
                        $plan->modules()->syncWithoutDetaching([
                            $module->id => ['is_included' => true],
                        ]);
                    }
                }
            }
        }
    }
}
