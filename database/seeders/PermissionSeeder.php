<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for leads
        Permission::create(['name' => 'lead.view']);
        Permission::create(['name' => 'lead.create']);
        Permission::create(['name' => 'lead.edit']);
        Permission::create(['name' => 'lead.delete']);
        
        // Create permissions for activities
        Permission::create(['name' => 'activity.view']);
        Permission::create(['name' => 'activity.create']);
        
        // Create permissions for documents
        Permission::create(['name' => 'document.view']);
        Permission::create(['name' => 'document.upload']);
        Permission::create(['name' => 'document.delete']);
        
        // Create permissions for import/export
        Permission::create(['name' => 'import.run']);
        Permission::create(['name' => 'export.run']);
        
        // Create permissions for user management
        Permission::create(['name' => 'user.view']);
        Permission::create(['name' => 'user.create']);
        Permission::create(['name' => 'user.edit']);
        Permission::create(['name' => 'user.delete']);
        
        // Create permissions for role management
        Permission::create(['name' => 'role.view']);
        Permission::create(['name' => 'role.create']);
        Permission::create(['name' => 'role.edit']);
        Permission::create(['name' => 'role.delete']);
        
        // Create permissions for system settings
        Permission::create(['name' => 'settings.view']);
        Permission::create(['name' => 'settings.edit']);
        
        // Create permissions for dedupe management
        Permission::create(['name' => 'dedupe.view']);
        Permission::create(['name' => 'dedupe.run']);
    }
}
