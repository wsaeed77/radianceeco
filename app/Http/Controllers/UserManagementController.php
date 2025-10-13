<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\ActivityType;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:user.view')->only(['index', 'show']);
        $this->middleware('permission:user.create')->only(['create', 'store']);
        $this->middleware('permission:user.edit')->only(['edit', 'update']);
        $this->middleware('permission:user.delete')->only(['destroy']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles', 'permissions');

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->role($request->role);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all roles for filter
        $roles = SpatieRole::all()->map(fn($role) => [
            'value' => $role->name,
            'label' => ucfirst($role->name),
        ]);

        // Get all permissions grouped by module
        $allPermissions = $this->getGroupedPermissions();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $allPermissions,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = SpatieRole::all()->map(fn($role) => [
            'value' => $role->name,
            'label' => ucfirst($role->name),
        ]);

        $permissions = $this->getGroupedPermissions();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        // Assign additional permissions if provided
        if (!empty($validated['permissions'])) {
            $user->givePermissionTo($validated['permissions']);
        }

        // Log user creation
        Activity::create([
            'lead_id' => null,
            'user_id' => Auth::id(),
            'type' => ActivityType::USER_CREATED->value,
            'description' => "User '{$user->name}' (ID: {$user->id}) created by " . Auth::user()->name,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions', 'leads');

        return Inertia::render('Users/Show', [
            'user' => $user,
            'userPermissions' => $user->getAllPermissions()->pluck('name'),
            'rolePermissions' => $user->getPermissionsViaRoles()->pluck('name'),
        ]);
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $user->load('roles', 'permissions');

        $roles = SpatieRole::all()->map(fn($role) => [
            'value' => $role->name,
            'label' => ucfirst($role->name),
        ]);

        $permissions = $this->getGroupedPermissions();

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->roles->first()?->name,
                'permissions' => $user->permissions->pluck('name')->toArray(),
                'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ],
            'roles' => $roles,
            'allPermissions' => $permissions,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sync role
        $user->syncRoles([$validated['role']]);

        // Sync additional permissions
        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        // Log user deletion before deleting (lead_id is null for system-wide logs)
        Activity::create([
            'lead_id' => null,
            'user_id' => Auth::id(),
            'type' => ActivityType::USER_DELETED->value,
            'description' => "User '{$user->name}' (ID: {$user->id}) deleted by " . Auth::user()->name,
        ]);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
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
                    'permissions' => [],
                ];
            }
            
            $grouped[$module]['permissions'][] = [
                'name' => $permission->name,
                'label' => str_replace('_', ' ', ucwords($parts[1] ?? $permission->name, '_')),
            ];
        }

        return array_values($grouped);
    }
}

