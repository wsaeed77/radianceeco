<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing roles or create them if they don't exist
        $adminRole = Role::findOrCreate(RoleEnum::ADMIN->value);
        $managerRole = Role::findOrCreate(RoleEnum::MANAGER->value);
        $agentRole = Role::findOrCreate(RoleEnum::AGENT->value);
        $readonlyRole = Role::findOrCreate(RoleEnum::READONLY->value);
        
        // Clear existing permissions
        $adminRole->syncPermissions([]);
        $managerRole->syncPermissions([]);
        $agentRole->syncPermissions([]);
        $readonlyRole->syncPermissions([]);
        
        // Get all permissions
        $permissions = Permission::all();
        
        // Assign all permissions to admin
        $adminRole->givePermissionTo($permissions);
        
        // Assign manager permissions
        $managerRole->givePermissionTo([
            'lead.view',
            'lead.create',
            'lead.edit',
            'activity.view',
            'activity.create',
            'document.view',
            'document.upload',
            'document.delete',
            'import.run',
            'export.run',
            'user.view',
            'dedupe.view',
            'status.view',
            'status.create',
            'status.edit',
            'status.delete',
        ]);
        
        // Assign agent permissions
        $agentRole->givePermissionTo([
            'lead.view',
            'lead.create',
            'lead.edit',
            'activity.view',
            'activity.create',
            'document.view',
            'document.upload',
        ]);
        
        // Assign readonly permissions
        $readonlyRole->givePermissionTo([
            'lead.view',
            'activity.view',
            'document.view',
        ]);
    }
}