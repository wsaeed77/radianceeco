# Deployment Storage Permission Fix

## Problem
After each GitHub Actions deployment, the application showed permission errors:
```
The stream or file "/var/www/radiance/releases/<SHA>/storage/logs/laravel.log" could not be opened in append mode: Failed to open stream: Permission denied
```

## Root Cause
Each deployment created a new release directory with a fresh `storage` folder that:
1. Had incorrect ownership (owned by `ubuntu` instead of `www-data`)
2. Didn't persist logs/sessions between deployments
3. Required manual permission fixes after every deploy

## Solution
Implemented a **shared persistent storage** approach:

### Changes Made

1. **Updated `scripts/deploy_remote.sh`**:
   - Creates `/var/www/radiance/shared/storage` with proper structure
   - Sets `www-data:www-data` ownership on shared storage
   - Removes release-specific `storage` directory
   - Symlinks each release's `storage` to the shared one

2. **Created `scripts/setup_shared_storage_ec2.sh`**:
   - One-time setup script to initialize shared storage on EC2
   - Migrates existing storage data if present
   - Sets correct permissions and ownership

### How It Works

**Directory Structure:**
```
/var/www/radiance/
├── shared/
│   ├── storage/               # Persistent, shared across all releases
│   │   ├── app/
│   │   ├── framework/
│   │   │   ├── cache/
│   │   │   ├── sessions/
│   │   │   └── views/
│   │   └── logs/
│   └── .env                   # Shared environment file
├── releases/
│   └── <SHA>/
│       ├── storage -> /var/www/radiance/shared/storage  # Symlink
│       └── .env -> /var/www/radiance/shared/.env        # Symlink
└── current -> /var/www/radiance/releases/<latest-SHA>
```

### Deployment Steps

#### One-Time Setup (Run on EC2)
```bash
# SSH to EC2
ssh -i <your-key> ubuntu@<your-server>

# Navigate to project
cd /var/www/radiance

# Make setup script executable
chmod +x scripts/setup_shared_storage_ec2.sh

# Run setup (creates shared storage with correct permissions)
./scripts/setup_shared_storage_ec2.sh
```

#### Every Deployment (Automatic via GitHub Actions)
When you push to `main`:
1. GitHub Actions builds the app
2. Syncs to EC2 as a new release
3. `scripts/deploy_remote.sh` runs automatically:
   - Removes the release's `storage` directory
   - Symlinks to `/var/www/radiance/shared/storage`
   - Sets permissions on `bootstrap/cache`
   - Runs migrations and caches
   - Points `current` to the new release

### Benefits
✅ **No more permission errors** - `www-data` owns shared storage permanently  
✅ **Persistent logs** - Logs survive across deployments  
✅ **Persistent sessions** - Users stay logged in during deployments  
✅ **Persistent uploads** - Files in `storage/app` are preserved  
✅ **Zero downtime** - Old release serves traffic until new one is ready

### Manual Deployment Alternative
If deploying manually (not via GitHub Actions):
```bash
# On EC2
cd /var/www/radiance/current
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build  # (if EC2 has enough resources)
bash scripts/deploy_remote.sh /var/www/radiance $(git rev-parse HEAD)
```

### Troubleshooting

**If you still see permission errors:**
```bash
# Verify shared storage exists and has correct ownership
ls -la /var/www/radiance/shared/storage
# Should show: drwxrwxr-x www-data www-data

# If not, fix it:
sudo chown -R www-data:www-data /var/www/radiance/shared/storage
sudo chmod -R 775 /var/www/radiance/shared/storage

# Verify current release's storage is a symlink
ls -la /var/www/radiance/current/storage
# Should show: storage -> /var/www/radiance/shared/storage

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

**If logs are still failing:**
```bash
# Clear all Laravel caches
cd /var/www/radiance/current
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear

# Restart services
sudo systemctl restart php8.1-fpm nginx
```

### Summary
The shared storage approach is a **deployment best practice** for Laravel applications using release-based deployments (like Capistrano, Deployer, or our custom GitHub Actions setup). It ensures runtime directories persist across releases while keeping code immutable.

