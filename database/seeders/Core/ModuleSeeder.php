<?php

namespace Database\Seeders\Core;

use App\Enums\Core\PlanPriority;
use App\Models\Core\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Tiers (CRM)',
                'slug' => 'tiers-crm',
                'description' => 'Gestion des clients, fournisseurs et sous-traitants.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'Chantiers',
                'slug' => 'chantiers',
                'description' => 'Suivi des projets, incluant la gestion des coûts et le suivi budgétaire.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'Articles & Stock',
                'slug' => 'articles-stock',
                'description' => 'Gestion du catalogue d\'articles, des ouvrages et du stock multi-dépôts.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'Commerce / Facturation',
                'slug' => 'commerce-facturation',
                'description' => 'Création de devis, factures, acomptes et suivi des paiements.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'Comptabilité',
                'slug' => 'comptabilite',
                'description' => 'Comptabilisation automatique et génération du FEC.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'Pointage / RH',
                'slug' => 'pointage-rh',
                'description' => 'Saisie des heures des employés et calcul du coût de la main-d\'œuvre.',
                'priority' => PlanPriority::High,
                'is_active' => true,
            ],
            [
                'name' => 'GED',
                'slug' => 'ged',
                'description' => 'Gestion électronique des documents avec métadonnées et alertes.',
                'priority' => PlanPriority::Medium,
                'is_active' => false,
            ],
            [
                'name' => 'Banque',
                'slug' => 'banque',
                'description' => 'Gestion des comptes, synchronisation des transactions et rapprochement bancaire.',
                'priority' => PlanPriority::Medium,
                'is_active' => false,
            ],
            [
                'name' => 'Notes de Frais',
                'slug' => 'notes-frais',
                'description' => 'Gestion des dépenses avec workflow de validation.',
                'priority' => PlanPriority::Medium,
                'is_active' => false,
            ],
            [
                'name' => 'Paie',
                'slug' => 'paie',
                'description' => 'Calcul des fiches de paie et export CSV configurable.',
                'priority' => PlanPriority::Medium,
                'is_active' => false,
            ],
            [
                'name' => 'GPAO',
                'slug' => 'gpao',
                'description' => 'Gestion des Ordres de Fabrication et planification.',
                'priority' => PlanPriority::Medium,
                'is_active' => false,
            ],
            [
                'name' => 'Flottes',
                'slug' => 'flottes',
                'description' => 'Gestion complète des véhicules, assurances et maintenances.',
                'priority' => PlanPriority::Low,
                'is_active' => false,
            ],
            [
                'name' => 'Locations',
                'slug' => 'locations',
                'description' => 'Gestion des contrats fournisseurs avec support de la périodicité.',
                'priority' => PlanPriority::Low,
                'is_active' => false,
            ],
            [
                'name' => 'Interventions',
                'slug' => 'interventions',
                'description' => 'Gestion des interventions forfait ou régie avec facturation client.',
                'priority' => PlanPriority::Low,
                'is_active' => false,
            ],
            [
                'name' => 'Pilotage',
                'slug' => 'pilotage',
                'description' => 'Service de calcul des KPI pour les tableaux de bord.',
                'priority' => PlanPriority::Low,
                'is_active' => false,
            ],
            [
                'name' => '3D Vision',
                'slug' => '3d-vision',
                'description' => 'Structure Backend pour la gestion des maquettes 3D.',
                'priority' => PlanPriority::Low,
                'is_active' => false,
            ], ];

        foreach ($modules as $module) {
            Module::firstOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
