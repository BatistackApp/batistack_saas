<?php

namespace Database\Seeders\Core;

use App\Enums\Core\BillingPeriod;
use App\Models\Core\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Plan Starter',
                'slug' => 'plan-starter',
                'description' => 'Plan d\'entrée de gamme idéal pour les petites entreprises.',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Professional',
                'slug' => 'plan-professional',
                'description' => 'Plan complet avec tous les modules essentiels.',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Enterprise',
                'slug' => 'plan-enterprise',
                'description' => 'Plan premium avec support prioritaire et modules avancés.',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Starter Annuel',
                'slug' => 'plan-starter-yearly',
                'description' => 'Plan d\'entrée de gamme avec facturation annuelle (2 mois offerts).',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Professional Annuel',
                'slug' => 'plan-professional-yearly',
                'description' => 'Plan complet avec facturation annuelle (2 mois offerts).',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Enterprise Annuel',
                'slug' => 'plan-enterprise-yearly',
                'description' => 'Plan premium avec facturation annuelle (2 mois offerts).',
                'monthly_price' => 29.99,
                'yearly_price' => 29.99,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
