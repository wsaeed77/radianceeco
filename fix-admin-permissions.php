<?php

/**
 * Fix Admin Permissions Script for Radiance Eco
 * Run this with: php fix-admin-permissions.php
 */

echo "\n====================================\n";
echo "🔧 Fixing Admin Permissions\n";
echo "====================================\n\n";

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get or create admin role
echo "📋 Step 1: Getting admin role...\n";
$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
echo "✓ Admin role found/created\n\n";

// Get all permissions
echo "🔑 Step 2: Getting all permissions...\n";
$allPermissions = \Spatie\Permission\Models\Permission::all();
echo "✓ Found " . $allPermissions->count() . " permissions\n\n";

// Assign all permissions to admin role
echo "🎯 Step 3: Assigning permissions to admin role...\n";
$adminRole->syncPermissions($allPermissions);
echo "✓ Admin role now has " . $adminRole->permissions->count() . " permissions\n\n";

// Find admin user
echo "👤 Step 4: Finding admin user...\n";
$adminEmails = [
    'admin@radianceeco.com',
    'admin@radianceeco.co.uk',
];

$adminUsers = \App\Models\User::whereIn('email', $adminEmails)->get();

if ($adminUsers->isEmpty()) {
    // Create admin user if not found
    echo "⚠ No admin user found. Creating one...\n";
    $password = 'Admin@123!';
    $user = \App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'admin@radianceeco.com',
        'password' => bcrypt($password),
        'role' => 'admin',
    ]);
    $user->assignRole($adminRole);
    echo "✓ Admin user created:\n";
    echo "   Email: admin@radianceeco.com\n";
    echo "   Password: {$password}\n";
    echo "   ⚠ CHANGE THIS PASSWORD AFTER LOGIN!\n\n";
} else {
    // Assign admin role to existing users
    foreach ($adminUsers as $user) {
        $user->assignRole($adminRole);
        echo "✓ Admin role assigned to: {$user->email}\n";
    }
    echo "\n";
}

// Verify permissions
echo "🔍 Step 5: Verifying permissions...\n";
$samplePermissions = [
    'lead.view',
    'lead.create',
    'lead.edit',
    'lead.delete',
    'user.view',
    'user.create',
    'setting.view',
    'setting.manage',
];

foreach ($samplePermissions as $permission) {
    $exists = $adminRole->hasPermissionTo($permission);
    $icon = $exists ? '✓' : '✗';
    echo "  {$icon} {$permission}\n";
}

echo "\n====================================\n";
echo "✅ Admin Permissions Fixed!\n";
echo "====================================\n\n";

echo "Next steps:\n";
echo "1. Clear cache: php artisan cache:clear\n";
echo "2. Logout from the website\n";
echo "3. Login again\n";
echo "4. Access /settings - should work now!\n\n";

echo "Admin Details:\n";
foreach ($adminUsers as $user) {
    echo "  Email: {$user->email}\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "  Permissions: " . $user->getAllPermissions()->count() . "\n";
}

echo "\n";

