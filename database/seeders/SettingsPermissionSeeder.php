<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Give admin role all settings permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
            $this->command->info('✓ Settings permissions granted to admin role');
        }

        $this->command->info('✓ Settings permissions created successfully');
    }
}
