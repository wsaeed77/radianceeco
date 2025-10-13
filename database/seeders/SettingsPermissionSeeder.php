<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SettingsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create settings permissions
        $permissions = [
            'setting.view' => 'View settings',
            'setting.manage' => 'Manage settings',
        ];

        foreach ($permissions as $name => $description) {
            // Ensure permissions exist explicitly on the web guard
            Permission::findOrCreate($name, 'web');
        }

        // Give admin role all settings permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
            $this->command->info('✓ Settings permissions granted to admin role');
        }

        // Clear Spatie permission cache so changes take effect immediately
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✓ Settings permissions created successfully');
    }
}
