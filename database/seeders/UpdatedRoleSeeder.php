<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdatedRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or get roles
        $adminRole = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value]);
        $managerRole = Role::firstOrCreate(['name' => RoleEnum::MANAGER->value]);
        $agentRole = Role::firstOrCreate(['name' => RoleEnum::AGENT->value]);
        $readonlyRole = Role::firstOrCreate(['name' => RoleEnum::READONLY->value]);
        
        // Get all permissions
        $allPermissions = Permission::all();
        
        // ADMIN - Full access to everything
        $adminRole->syncPermissions($allPermissions);
        
        // MANAGER - Can manage leads, users, and view reports
        $managerPermissions = [
            'dashboard.view',
            'dashboard.view_all_stats',
            
            'lead.view_all',
            'lead.create',
            'lead.edit_all',
            'lead.delete',
            'lead.assign',
            'lead.change_status',
            'lead.change_stage',
            'lead.export',
            'lead.import',
            
            'activity.view_all',
            'activity.create',
            'activity.edit_all',
            'activity.delete_all',
            
            'document.view_all',
            'document.upload',
            'document.download',
            'document.delete_all',
            
            'report.view',
            'report.view_all_agents',
            'report.export',
            
            'user.view',
            'agent.view',
            'agent.create',
            'agent.edit',
            'agent.assign_leads',
            
            'data.import',
            'data.export',
            'data.dedupe_view',
            'data.dedupe_run',
            'data.bulk_edit',
        ];
        $managerRole->syncPermissions($managerPermissions);
        
        // AGENT - Can manage own leads and activities
        $agentPermissions = [
            'dashboard.view',
            
            'lead.view_own',
            'lead.create',
            'lead.edit_own',
            'lead.change_status',
            'lead.change_stage',
            
            'activity.view_own',
            'activity.create',
            'activity.edit_own',
            'activity.delete_own',
            
            'document.view_own',
            'document.upload',
            'document.download',
            'document.delete_own',
            
            'report.view',
            'report.view_own',
        ];
        $agentRole->syncPermissions($agentPermissions);
        
        // READONLY - View only access
        $readonlyPermissions = [
            'dashboard.view',
            
            'lead.view_all',
            
            'activity.view_all',
            
            'document.view_all',
            'document.download',
            
            'report.view',
            'report.view_all_agents',
        ];
        $readonlyRole->syncPermissions($readonlyPermissions);
    }
}

