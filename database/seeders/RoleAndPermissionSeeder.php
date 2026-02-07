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
            // Expense
            'tenant.expenses.manage', 'tenant.expenses.validate',
            // Payroll
            'payroll.manage', 'payroll.validate',
            // GPAO
            'gpao.manage',
            // Locations
            'locations.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2.2 Création des rôles standards du BTP

        // ADMIN DU TENANT (Accès total au compte entreprise)
        $admin = Role::findOrCreate('tenant_admin', 'web');
        $admin->givePermissionTo(Permission::all());

        // CONDUCTEUR DE TRAVAUX (Focus opérationnel et budget)
        $manager = Role::findOrCreate('project_manager', 'web');
        $manager->givePermissionTo([
            'projects.view', 'projects.create', 'projects.edit', 'projects.manage_budget',
            'inventory.view', 'tiers.view', 'locations.manage',
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
            'projects.view', 'inventory.view', 'locations.view'
        ]);

        // CHEF D'ATELIER
        $atl = Role::findOrCreate('chef_atelier', 'web');
        $atl->givePermissionTo([
            'projects.view', 'inventory.view', 'gpao.manage', 'locations.view'
        ]);
    }
}
