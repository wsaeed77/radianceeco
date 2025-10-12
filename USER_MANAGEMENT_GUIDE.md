# User Management & Permissions System

## Overview
A comprehensive Role-Based Access Control (RBAC) system has been implemented to manage users and their permissions across the entire application.

## Features

### 🔐 User Management
- Create, edit, view, and delete users
- Assign roles to users
- Set individual permissions beyond role permissions
- Search and filter users by name, email, or role
- View user permission summaries

### 👥 Role System
Four predefined roles with different permission levels:
- **Admin**: Full system access
- **Manager**: Can manage leads, users (agents), reports
- **Agent**: Can manage own leads and activities
- **Readonly**: View-only access

### ✅ Permission Management
- Granular control over 70+ permissions
- Grouped by modules (Dashboard, Leads, Activities, Documents, Reports, etc.)
- Easy-to-use interface to assign/unassign permissions per role
- Real-time permission updates

### 🎯 Permission Categories

#### Dashboard
- `dashboard.view` - Access dashboard
- `dashboard.view_all_stats` - View all statistics

#### Leads Management
- `lead.view_all` - View all leads
- `lead.view_own` - View own assigned leads
- `lead.view_team` - View team leads
- `lead.create` - Create new leads
- `lead.edit_all` - Edit all leads
- `lead.edit_own` - Edit own leads
- `lead.delete` - Delete leads
- `lead.assign` - Assign leads to agents
- `lead.change_status` - Change lead status
- `lead.change_stage` - Change lead stage
- `lead.export` - Export leads data
- `lead.import` - Import leads data

#### Activities
- `activity.view_all` / `activity.view_own`
- `activity.create`
- `activity.edit_all` / `activity.edit_own`
- `activity.delete_all` / `activity.delete_own`

#### Documents
- `document.view_all` / `document.view_own`
- `document.upload`
- `document.download`
- `document.delete_all` / `document.delete_own`

#### Reports & Analytics
- `report.view` - View reports
- `report.view_all_agents` - View all agents' reports
- `report.view_own` - View own performance
- `report.export` - Export reports

#### User Management
- `user.view` - View users list
- `user.create` - Create new users
- `user.edit` - Edit users
- `user.delete` - Delete users
- `user.change_role` - Change user roles
- `user.manage_permissions` - Manage user permissions

#### Role & Permission Management
- `role.view` - View roles
- `role.assign_permissions` - Assign permissions to roles
- `permission.view` - View permissions

#### Data Operations
- `data.import` / `data.export`
- `data.dedupe_view` / `data.dedupe_run`
- `data.bulk_edit` / `data.bulk_delete`

#### System
- `system.logs_view`
- `system.backup` / `system.restore`
- `system.maintenance`

## Installation & Setup

### Step 1: Run Database Seeders

```bash
# Run the comprehensive permission seeder
php artisan db:seed --class=ComprehensivePermissionSeeder

# Run the updated role seeder
php artisan db:seed --class=UpdatedRoleSeeder
```

### Step 2: Verify Setup

```bash
# List all permissions
php artisan permission:show

# List all roles with their permissions
php artisan permission:show --role=admin
php artisan permission:show --role=manager
php artisan permission:show --role=agent
php artisan permission:show --role=readonly
```

### Step 3: Assign Roles to Existing Users

```bash
# Via Tinker
php artisan tinker

# Assign admin role to a user
$user = User::where('email', 'admin@example.com')->first();
$user->assignRole('admin');

# Or via the UI
# Go to Users > Edit User > Select Role
```

## Usage

### Accessing User Management
Navigate to **Users** from the main navigation menu, or visit `/users`

### Creating a New User
1. Click **New User** button
2. Fill in user details (name, email, password, phone, role)
3. Optionally add extra permissions beyond the role permissions
4. Click **Create User**

### Editing a User
1. Find the user in the list
2. Click **Edit**
3. Update user information or change role
4. Modify permissions as needed
5. Click **Update User**

### Managing Permissions
1. Navigate to **User Management**
2. Click **Manage Permissions** button
3. Select a role from the left panel
4. Check/uncheck permissions for that role
5. Click **Save Changes**

Changes are applied immediately to all users with that role.

## Permission Checking in Code

### In Controllers

```php
// Check if user has permission
if (Auth::user()->can('lead.edit_all')) {
    // User can edit all leads
}

// Using middleware
Route::get('/leads', [LeadController::class, 'index'])
    ->middleware('permission:lead.view_all');

// In controller constructor
public function __construct()
{
    $this->middleware('permission:lead.view')->only(['index', 'show']);
    $this->middleware('permission:lead.create')->only(['create', 'store']);
}
```

### In Blade Templates

```blade
@can('lead.create')
    <a href="{{ route('leads.create') }}">Create Lead</a>
@endcan

@role('admin')
    <p>You are an administrator!</p>
@endrole
```

### In React/Inertia Components

```jsx
// Pass permissions via Inertia share
// In HandleInertiaRequests.php
public function share(Request $request)
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
            'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
        ],
    ]);
}

// In your component
const { auth } = usePage().props;

{auth.permissions.includes('lead.create') && (
    <Button onClick={createLead}>Create Lead</Button>
)}
```

## Default Role Permissions

### Admin
- All permissions (full access)

### Manager
- Dashboard: Full access
- Leads: Full management (view all, create, edit, delete, assign, export, import)
- Activities: Full access
- Documents: Full access
- Reports: View all agents' reports and export
- Users: View and manage agents
- Data: Import, export, dedupe, bulk edit

### Agent
- Dashboard: View
- Leads: View and edit own leads only
- Activities: Manage own activities
- Documents: Upload and view own documents
- Reports: View own performance only

### Readonly
- Dashboard: View
- Leads: View all leads (read-only)
- Activities: View all activities
- Documents: View and download all documents
- Reports: View all reports

## Security Best Practices

1. **Principle of Least Privilege**: Assign only necessary permissions
2. **Regular Audits**: Review user permissions periodically
3. **Role-Based Assignments**: Use roles primarily, individual permissions sparingly
4. **Password Policies**: Enforce strong passwords
5. **Activity Logging**: Monitor permission changes and user actions

## Customization

### Adding New Permissions

1. **Add to Seeder**:
```php
// In ComprehensivePermissionSeeder.php
Permission::firstOrCreate(['name' => 'your_module.your_action']);
```

2. **Run Seeder**:
```bash
php artisan db:seed --class=ComprehensivePermissionSeeder
```

3. **Assign to Roles**:
Update `UpdatedRoleSeeder.php` to include the new permission for appropriate roles.

### Creating Custom Roles

```php
use Spatie\Permission\Models\Role;

$customRole = Role::create(['name' => 'supervisor']);
$customRole->givePermissionTo([
    'lead.view_team',
    'lead.edit_team',
    'activity.view_all',
]);
```

## Troubleshooting

### Permission Denied Errors
- Verify user has the required permission
- Check if permission is assigned to user's role
- Clear permission cache: `php artisan permission:cache-reset`

### Permissions Not Updating
- Clear cache: `php artisan cache:clear`
- Clear permission cache: `php artisan permission:cache-reset`
- Restart queue workers if using queues

### Missing Permissions
- Run seeder: `php artisan db:seed --class=ComprehensivePermissionSeeder`
- Verify in database: Check `permissions` and `role_has_permissions` tables

## API Endpoints

### User Management
- `GET /users` - List users (requires: user.view)
- `POST /users` - Create user (requires: user.create)
- `GET /users/{id}` - View user (requires: user.view)
- `PUT /users/{id}` - Update user (requires: user.edit)
- `DELETE /users/{id}` - Delete user (requires: user.delete)

### Permission Management
- `GET /permissions` - View permissions page (requires: role.view)
- `POST /permissions/roles/{role}` - Update role permissions (requires: role.assign_permissions)

## Support

For issues or questions about the permission system:
1. Check Laravel Spatie Permission documentation
2. Review application logs
3. Contact development team

---

**Version**: 1.0  
**Last Updated**: 2025  
**Package**: spatie/laravel-permission v6.x

