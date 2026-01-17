<?php

namespace Database\Factories\Core;

use App\Enums\Core\PlanPriority;
use App\Models\Core\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    private static array $modules = [
        ['name' => 'Tiers (CRM)', 'slug' => 'tiers', 'priority' => 'high'],
        ['name' => 'Chantiers', 'slug' => 'chantiers', 'priority' => 'high'],
        ['name' => 'Articles & Stock', 'slug' => 'articles-stock', 'priority' => 'high'],
        ['name' => 'Commerce / Facturation', 'slug' => 'commerce-facturation', 'priority' => 'high'],
        ['name' => 'Comptabilité', 'slug' => 'comptabilite', 'priority' => 'high'],
        ['name' => 'Pointage / RH', 'slug' => 'pointage-rh', 'priority' => 'high'],
        ['name' => 'GED', 'slug' => 'ged', 'priority' => 'medium'],
        ['name' => 'Banque', 'slug' => 'banque', 'priority' => 'medium'],
        ['name' => 'Notes de Frais', 'slug' => 'notes-frais', 'priority' => 'medium'],
        ['name' => 'Paie', 'slug' => 'paie', 'priority' => 'medium'],
        ['name' => 'GPAO', 'slug' => 'gpao', 'priority' => 'medium'],
        ['name' => 'Flottes', 'slug' => 'flottes', 'priority' => 'low'],
        ['name' => 'Locations', 'slug' => 'locations', 'priority' => 'low'],
        ['name' => 'Interventions', 'slug' => 'interventions', 'priority' => 'low'],
        ['name' => 'Pilotage', 'slug' => 'pilotage', 'priority' => 'low'],
        ['name' => '3D Vision', 'slug' => '3d-vision', 'priority' => 'low'],
    ];

    public function definition(): array
    {
        $module = $this->faker->randomElement(self::$modules);

        return [
            'name' => $module['name'],
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->getDescriptionForModule($module['slug']),
            'priority' => $module['priority'],
            'is_active' => true,
        ];
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => PlanPriority::Critical,
        ]);
    }

    public function haute(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => PlanPriority::High,
        ]);
    }

    public function moyenne(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => PlanPriority::Medium,
        ]);
    }

    public function basse(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => PlanPriority::Low,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    private function getDescriptionForModule(string $slug): string
    {
        $descriptions = [
            'tiers' => 'Gestion des clients, fournisseurs et sous-traitants.',
            'chantiers' => 'Suivi des projets, incluant la gestion des coûts et le suivi budgétaire.',
            'articles-stock' => 'Gestion du catalogue d\'articles, des ouvrages et du stock multi-dépôts.',
            'commerce-facturation' => 'Création de devis, factures, acomptes et suivi des paiements.',
            'comptabilite' => 'Comptabilisation automatique et génération du FEC avec numérotation séquentielle stricte.',
            'pointage-rh' => 'Saisie des heures des employés et calcul du coût de la main-d\'œuvre par chantier.',
            'ged' => 'Gestion électronique des documents avec métadonnées et alertes d\'expiration.',
            'banque' => 'Gestion des comptes, synchronisation des transactions et rapprochement bancaire automatisé.',
            'notes-frais' => 'Gestion des dépenses avec workflow de validation et comptabilisation automatique.',
            'paie' => 'Calcul des fiches de paie avec export configurable.',
            'gpao' => 'Gestion des Ordres de Fabrication, planification et suivi de statut.',
            'flottes' => 'Gestion complète des véhicules, assurances et maintenances.',
            'locations' => 'Gestion des contrats fournisseurs avec support de la périodicité.',
            'interventions' => 'Gestion des interventions forfait ou régie avec facturation client.',
            'pilotage' => 'Service de calcul des KPI pour les tableaux de bord.',
            '3d-vision' => 'Structure backend pour la gestion des maquettes 3D et integration viewer BIM/IFC.',
        ];

        return $descriptions[$slug] ?? $this->faker->sentence();
    }
}
