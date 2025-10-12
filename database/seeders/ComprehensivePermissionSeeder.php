<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ComprehensivePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions by module
        $permissions = [
            // Dashboard
            'dashboard.view',
            'dashboard.view_all_stats',
            
            // Leads Management
            'lead.view_all',
            'lead.view_own',
            'lead.view_team',
            'lead.create',
            'lead.edit_all',
            'lead.edit_own',
            'lead.delete',
            'lead.assign',
            'lead.change_status',
            'lead.change_stage',
            'lead.export',
            'lead.import',
            
            // Activities
            'activity.view_all',
            'activity.view_own',
            'activity.create',
            'activity.edit_own',
            'activity.edit_all',
            'activity.delete_own',
            'activity.delete_all',
            
            // Documents
            'document.view_all',
            'document.view_own',
            'document.upload',
            'document.download',
            'document.delete_own',
            'document.delete_all',
            
            // Reports & Analytics
            'report.view',
            'report.view_all_agents',
            'report.view_own',
            'report.export',
            
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.change_role',
            'user.manage_permissions',
            
            // Role Management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.assign_permissions',
            
            // Permission Management
            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',
            
            // Agent Management
            'agent.view',
            'agent.create',
            'agent.edit',
            'agent.delete',
            'agent.assign_leads',
            
            // Settings
            'settings.view',
            'settings.edit',
            'settings.system',
            
            // Data Operations
            'data.import',
            'data.export',
            'data.dedupe_view',
            'data.dedupe_run',
            'data.bulk_edit',
            'data.bulk_delete',
            
            // System
            'system.logs_view',
            'system.backup',
            'system.restore',
            'system.maintenance',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

