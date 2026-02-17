<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset du cache des rôles et permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2.1 Définition des permissions granulaires par module
        $permissions = [
            // Chantiers
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_budget',
            // Stock
            'inventory.view', 'inventory.manage', 'inventory.audit',
            // Tiers
            'tiers.view', 'tiers.manage', 'tiers.compliance_validate',
            // Administration Tenant
            'tenant.users.manage', 'tenant.settings.edit',
            'payroll.manage', 'payroll.validate',
            'employee.manage', // Gestion des fiches salariés
            // Expense
            'tenant.expenses.manage', 'tenant.expenses.validate',
            // MODULE ABSENCES (Nouveau)
            'absences.view_own',   // Voir ses propres congés (Collaborateur)
            'absences.create',     // Soumettre une demande (Collaborateur)
            'absences.view_team',  // Voir le calendrier d'équipe (Manager)
            'absences.validate',   // Valider/Refuser (Manager)
            'absences.manage_all', // Paramétrage et ajustement des soldes (RH)
            'absences.export',     // Export vers la paie (RH)
            // HR
            'time_entries.verify',
            // GPAO
            'gpao.manage',
            // Locations
            'locations.manage', 'locations.view',
            // Flottes
            'fleet.manage', 'fleet.view',
            // Kpi
            'pilotage.view', 'pilotage.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2.2 Création des rôles standards du BTP

        // ADMIN DU TENANT (Accès total au compte entreprise)
        $admin = Role::findOrCreate('tenant_admin', 'web');
        $admin->givePermissionTo(Permission::all());

        // --- RESPONSABLE RH (Persona 3) ---
        $hr = Role::findOrCreate('hr_manager', 'web');
        $hr->syncPermissions([
            'absences.view_own', 'absences.create', 'absences.view_team',
            'absences.manage_all', 'absences.export',
            'employee.manage', 'payroll.manage', 'payroll.validate',
            'tenant.users.manage', 'time_entries.verify',
        ]);

        // CONDUCTEUR DE TRAVAUX (Focus opérationnel et budget)
        // --- CONDUCTEUR DE TRAVAUX / MANAGER (Persona 2) ---
        $manager = Role::findOrCreate('project_manager', 'web');
        $manager->syncPermissions([
            'projects.view', 'projects.create', 'projects.edit', 'projects.manage_budget',
            'inventory.view', 'tiers.view', 'pilotage.view',
            'absences.view_own', 'absences.create', 'absences.view_team', 'absences.validate',
            'time_entries.verify',
        ]);

        // RESPONSABLE LOGISTIQUE (Focus Stock)
        $logistics = Role::findOrCreate('logistics_manager', 'web');
        $logistics->givePermissionTo([
            'inventory.view', 'inventory.manage', 'inventory.audit',
            'projects.view', 'tiers.view', 'gpao.manage', 'locations.manage',
        ]);

        // CHEF DE CHANTIER (Consultation et saisie terrain)
        $foreman = Role::findOrCreate('foreman', 'web');
        $foreman->givePermissionTo([
            'projects.view', 'inventory.view', 'locations.view',
            'absences.view_own', 'absences.create', 'absences.view_team', 'absences.validate',
            'time_entries.verify',
        ]);

        // CHEF D'ATELIER
        $atl = Role::findOrCreate('chef_atelier', 'web');
        $atl->givePermissionTo([
            'projects.view', 'inventory.view', 'gpao.manage', 'locations.view',
            'absences.view_own', 'absences.create', 'absences.view_team', 'absences.validate',
            'time_entries.verify',
        ]);

        // --- COLLABORATEUR / OUVRIER (Persona 1) ---
        $employee = Role::findOrCreate('employee', 'web');
        $employee->syncPermissions([
            'absences.view_own',
            'absences.create',
            'projects.view',
        ]);
    }
}
