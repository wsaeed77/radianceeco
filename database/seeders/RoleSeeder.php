<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => RoleEnum::ADMIN->value]);
        $managerRole = Role::create(['name' => RoleEnum::MANAGER->value]);
        $agentRole = Role::create(['name' => RoleEnum::AGENT->value]);
        $readonlyRole = Role::create(['name' => RoleEnum::READONLY->value]);
        
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
