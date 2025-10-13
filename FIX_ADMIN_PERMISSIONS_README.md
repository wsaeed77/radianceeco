# Fix Admin Permissions - Quick Guide

## Problem
Getting "403 - USER DOES NOT HAVE THE RIGHT PERMISSIONS" when trying to access Settings, Permissions, or other admin pages.

## Solution
Use one of these fix scripts to assign all permissions to admin role.

---

## Method 1: Bash Script (Recommended)

### Step 1: Upload the script to your server
From your local machine:
```bash
scp fix-admin-permissions.sh ubuntu@your-ec2-ip:/home/ubuntu/
```

### Step 2: SSH into your server
```bash
ssh ubuntu@your-ec2-ip
```

### Step 3: Move script to application directory
```bash
sudo mv fix-admin-permissions.sh /var/www/radiance/
cd /var/www/radiance
```

### Step 4: Make it executable
```bash
chmod +x fix-admin-permissions.sh
```

### Step 5: Run it
```bash
sudo ./fix-admin-permissions.sh
```

**Expected output:**
```
======================================
🔧 Fixing Admin Permissions
======================================

📦 Step 1: Running Permission Seeders...
✓ Seeders completed

👤 Step 2: Assigning All Permissions to Admin Role...
✓ Admin role has 45 permissions
✓ Admin role assigned to: admin@radianceeco.com

🧹 Step 3: Clearing Caches...
✓ Caches cleared

🚀 Step 4: Rebuilding Caches...
✓ Caches rebuilt

🔐 Step 5: Fixing Storage Permissions...
✓ Permissions fixed

♻️  Step 6: Restarting Services...
✓ Services restarted

======================================
✅ Admin Permissions Fixed!
======================================
```

---

## Method 2: PHP Script (Alternative)

### Step 1: Upload the PHP script
```bash
scp fix-admin-permissions.php ubuntu@your-ec2-ip:/home/ubuntu/
```

### Step 2: SSH into your server
```bash
ssh ubuntu@your-ec2-ip
```

### Step 3: Move script to application directory
```bash
sudo mv fix-admin-permissions.php /var/www/radiance/
cd /var/www/radiance
```

### Step 4: Run it
```bash
php fix-admin-permissions.php
```

### Step 5: Clear caches
```bash
php artisan cache:clear
php artisan config:cache
```

---

## Method 3: Manual Commands (If Scripts Don't Work)

SSH into your server and run these commands:

```bash
cd /var/www/radiance

# Run seeders
php artisan db:seed --class=SettingsPermissionSeeder

# Assign permissions via tinker
php artisan tinker
```

In tinker, paste this:
```php
$adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
$allPermissions = \Spatie\Permission\Models\Permission::all();
$adminRole->syncPermissions($allPermissions);
$user = \App\Models\User::where('email', 'admin@radianceeco.com')->first();
$user->assignRole($adminRole);
echo "Done!\n";
exit;
```

Then clear cache:
```bash
php artisan cache:clear
php artisan config:cache
sudo systemctl restart php8.1-fpm nginx
```

---

## After Running the Fix

### Step 1: Logout
- Go to https://leads.radianceeco.co.uk
- Click your profile/logout

### Step 2: Clear Browser Cache
- Press `Ctrl+Shift+Delete` (Windows/Linux)
- Or `Cmd+Shift+Delete` (Mac)
- Clear cookies and cache

### Step 3: Login Again
- Email: `admin@radianceeco.com`
- Password: Your admin password

### Step 4: Test Access
Try accessing these pages:
- ✅ `/settings` - Settings page
- ✅ `/permissions` - Permission management
- ✅ `/users` - User management
- ✅ `/import` - Import leads

All should work now!

---

## Verify Permissions

Check if admin has permissions:

```bash
cd /var/www/radiance
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@radianceeco.com')->first();
echo "User: " . $user->email . "\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Permissions: " . $user->getAllPermissions()->count() . "\n";
exit;
```

Should show:
```
User: admin@radianceeco.com
Roles: admin
Permissions: 45
```

---

## Troubleshooting

### Still getting 403 error?

**1. Check if user has admin role:**
```bash
php artisan tinker --execute="\$user = \App\Models\User::first(); echo 'Roles: ' . \$user->roles->pluck('name')->implode(', ') . PHP_EOL;"
```

**2. Check if admin role has permissions:**
```bash
php artisan tinker --execute="\$role = \Spatie\Permission\Models\Role::where('name', 'admin')->first(); echo 'Permissions: ' . \$role->permissions->count() . PHP_EOL;"
```

**3. Force clear all caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
rm -rf storage/framework/cache/data/*
php artisan config:cache
sudo systemctl restart php8.1-fpm nginx
```

**4. Check Laravel log for errors:**
```bash
tail -50 /var/www/radiance/storage/logs/laravel.log
```

### Permission denied on script execution?

```bash
chmod +x fix-admin-permissions.sh
sudo chown ubuntu:ubuntu fix-admin-permissions.sh
```

### Script not found?

Make sure you're in the right directory:
```bash
cd /var/www/radiance
ls -la fix-admin-permissions.*
```

---

## What These Scripts Do

1. **Run all permission seeders** - Creates roles and permissions
2. **Assign all permissions to admin role** - Admin gets full access
3. **Assign admin role to user** - Your user gets the admin role
4. **Clear caches** - Remove old cached permissions
5. **Fix file permissions** - Ensure Laravel can write to storage
6. **Restart services** - Apply all changes

---

## Need Help?

If you're still having issues:

1. **Check the logs:**
   ```bash
   tail -50 /var/www/radiance/storage/logs/laravel.log
   ```

2. **Verify database connection:**
   ```bash
   php artisan tinker --execute="echo 'DB works: ' . (\DB::connection()->getPdo() ? 'YES' : 'NO') . PHP_EOL;"
   ```

3. **List all users:**
   ```bash
   php artisan tinker --execute="\App\Models\User::all()->each(function(\$u) { echo \$u->email . ' - ' . \$u->roles->pluck('name')->implode(',') . PHP_EOL; });"
   ```

---

## Summary

**Quickest fix (as root on EC2):**
```bash
cd /var/www/radiance
wget https://your-server/fix-admin-permissions.sh
chmod +x fix-admin-permissions.sh
sudo ./fix-admin-permissions.sh
```

Then logout and login again. Done! ✅

