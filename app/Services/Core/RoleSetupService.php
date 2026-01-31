<?php

namespace App\Services\Core;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSetupService
{
    public function setup()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Module Chantiers
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'projects.manage_budget',
            // Module Stock
            'inventory.view', 'inventory.manage', 'inventory.audit',
            // Module Tiers
            'tiers.view', 'tiers.manage', 'tiers.compliance_validate',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Création des rôles et attribution des permissions

        // Rôle : Conducteur de Travaux
        $ctRole = Role::findOrCreate('project_manager', 'web');
        $ctRole->givePermissionTo(['projects.view', 'projects.edit', 'projects.manage_budget', 'inventory.view']);

        // Rôle : Responsable Logistique
        $logRole = Role::findOrCreate('logistics_manager', 'web');
        $logRole->givePermissionTo(['inventory.view', 'inventory.manage', 'inventory.audit']);
    }
}
