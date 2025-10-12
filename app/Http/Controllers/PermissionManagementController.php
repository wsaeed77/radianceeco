<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:role.view')->only(['index']);
        $this->middleware('permission:role.assign_permissions')->only(['updateRolePermissions']);
    }

    /**
     * Display the permission management page.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'label' => ucfirst($role->name),
                'permissions' => $role->permissions->pluck('name')->toArray(),
                'users_count' => $role->users()->count(),
            ];
        });

        $permissions = $this->getGroupedPermissions();

        return Inertia::render('Permissions/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update permissions for a specific role.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permissions updated for {$role->name} role!");
    }

    /**
     * Get permissions grouped by module.
     */
    private function getGroupedPermissions()
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'name' => ucfirst($module),
                    'key' => $module,
                    'permissions' => [],
                ];
            }
            
            $grouped[$module]['permissions'][] = [
                'name' => $permission->name,
                'label' => str_replace('_', ' ', ucwords($parts[1] ?? $permission->name, '_')),
                'description' => $this->getPermissionDescription($permission->name),
            ];
        }

        return array_values($grouped);
    }

    /**
     * Get human-readable description for a permission.
     */
    private function getPermissionDescription($permissionName)
    {
        $descriptions = [
            'dashboard.view' => 'Access dashboard',
            'dashboard.view_all_stats' => 'View all statistics',
            
            'lead.view_all' => 'View all leads',
            'lead.view_own' => 'View own assigned leads',
            'lead.view_team' => 'View team leads',
            'lead.create' => 'Create new leads',
            'lead.edit_all' => 'Edit all leads',
            'lead.edit_own' => 'Edit own leads',
            'lead.delete' => 'Delete leads',
            'lead.assign' => 'Assign leads to agents',
            'lead.change_status' => 'Change lead status',
            'lead.change_stage' => 'Change lead stage',
            'lead.export' => 'Export leads data',
            'lead.import' => 'Import leads data',
            
            'activity.view_all' => 'View all activities',
            'activity.view_own' => 'View own activities',
            'activity.create' => 'Create activities',
            'activity.edit_own' => 'Edit own activities',
            'activity.edit_all' => 'Edit all activities',
            'activity.delete_own' => 'Delete own activities',
            'activity.delete_all' => 'Delete all activities',
            
            'document.view_all' => 'View all documents',
            'document.view_own' => 'View own documents',
            'document.upload' => 'Upload documents',
            'document.download' => 'Download documents',
            'document.delete_own' => 'Delete own documents',
            'document.delete_all' => 'Delete all documents',
            
            'report.view' => 'View reports',
            'report.view_all_agents' => 'View all agents reports',
            'report.view_own' => 'View own performance',
            'report.export' => 'Export reports',
            
            'user.view' => 'View users list',
            'user.create' => 'Create new users',
            'user.edit' => 'Edit users',
            'user.delete' => 'Delete users',
            'user.change_role' => 'Change user roles',
            'user.manage_permissions' => 'Manage user permissions',
            
            'role.view' => 'View roles',
            'role.create' => 'Create new roles',
            'role.edit' => 'Edit roles',
            'role.delete' => 'Delete roles',
            'role.assign_permissions' => 'Assign permissions to roles',
            
            'permission.view' => 'View permissions',
            'permission.create' => 'Create new permissions',
            'permission.edit' => 'Edit permissions',
            'permission.delete' => 'Delete permissions',
            
            'agent.view' => 'View agents',
            'agent.create' => 'Create agents',
            'agent.edit' => 'Edit agents',
            'agent.delete' => 'Delete agents',
            'agent.assign_leads' => 'Assign leads to agents',
            
            'settings.view' => 'View settings',
            'settings.edit' => 'Edit settings',
            'settings.system' => 'Manage system settings',
            
            'data.import' => 'Import data',
            'data.export' => 'Export data',
            'data.dedupe_view' => 'View duplicates',
            'data.dedupe_run' => 'Run deduplication',
            'data.bulk_edit' => 'Bulk edit records',
            'data.bulk_delete' => 'Bulk delete records',
            
            'system.logs_view' => 'View system logs',
            'system.backup' => 'Create backups',
            'system.restore' => 'Restore from backups',
            'system.maintenance' => 'Maintenance mode',
        ];

        return $descriptions[$permissionName] ?? ucwords(str_replace(['.', '_'], ' ', $permissionName));
    }
}

