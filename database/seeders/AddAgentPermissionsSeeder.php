<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Enums\Role as RoleEnum;

class AddAgentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create agent management permissions
        Permission::create(['name' => 'agent.view']);
        Permission::create(['name' => 'agent.create']);
        Permission::create(['name' => 'agent.edit']);
        Permission::create(['name' => 'agent.delete']);
        
        // Get roles
        $adminRole = Role::findOrCreate(RoleEnum::ADMIN->value);
        $managerRole = Role::findOrCreate(RoleEnum::MANAGER->value);
        
        // Give permissions to roles
        $adminRole->givePermissionTo([
            'agent.view',
            'agent.create',
            'agent.edit',
            'agent.delete',
        ]);
        
        $managerRole->givePermissionTo([
            'agent.view',
        ]);
    }
}