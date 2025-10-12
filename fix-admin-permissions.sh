#!/bin/bash

# Fix Admin Permissions Script for Radiance Eco
# Run this on your EC2 server

set -e

echo "======================================"
echo "🔧 Fixing Admin Permissions"
echo "======================================"

cd /var/www/radiance

echo ""
echo "📦 Step 1: Running Permission Seeders..."
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UpdateRolePermissionsSeeder
php artisan db:seed --class=AddAgentPermissionsSeeder
php artisan db:seed --class=SettingsPermissionSeeder

echo ""
echo "👤 Step 2: Assigning All Permissions to Admin Role..."

php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get admin role
\$adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();

if (!\$adminRole) {
    echo '❌ Admin role not found! Creating it...' . PHP_EOL;
    \$adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
}

// Get all permissions
\$allPermissions = \Spatie\Permission\Models\Permission::all();

// Assign all permissions to admin role
\$adminRole->syncPermissions(\$allPermissions);

echo '✓ Admin role has ' . \$adminRole->permissions->count() . ' permissions' . PHP_EOL;

// Assign admin role to user
\$user = \App\Models\User::where('email', 'admin@radianceeco.com')->first();

if (\$user) {
    \$user->assignRole(\$adminRole);
    echo '✓ Admin role assigned to: ' . \$user->email . PHP_EOL;
} else {
    echo '⚠ User admin@radianceeco.com not found' . PHP_EOL;
}
"

echo ""
echo "🧹 Step 3: Clearing Caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "🚀 Step 4: Rebuilding Caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "🔐 Step 5: Fixing Storage Permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo ""
echo "♻️  Step 6: Restarting Services..."
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

echo ""
echo "======================================"
echo "✅ Admin Permissions Fixed!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Logout from https://leads.radianceeco.co.uk"
echo "2. Login again with: admin@radianceeco.com"
echo "3. Access /settings - should work now!"
echo ""

