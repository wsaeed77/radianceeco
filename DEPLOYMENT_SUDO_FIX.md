# Deployment Sudo Fix

## Problem
The deployment script was failing because:
1. It tried to change ownership of files in `shared/storage` that are owned by `www-data`
2. The `ubuntu` user running the deployment doesn't have permission to change ownership of files it doesn't own
3. Commands like `artisan` were being run from the wrong directory

## Solution

### 1. Updated Deploy Script (`scripts/deploy_remote.sh`)
The script now:
- Only sets permissions on `shared/storage` **once** (on first deploy)
- Uses `sudo` for permission changes
- Changes directory to the release before running artisan commands
- Creates `bootstrap/cache` directory if it doesn't exist
- Uses `sudo` for service restarts

### 2. Configure Sudo for Ubuntu User

On the EC2 server, run these commands **once**:

```bash
# Edit sudoers file for ubuntu user
sudo visudo -f /etc/sudoers.d/ubuntu-deploy
```

Add the following content:

```
# Allow ubuntu user to run deployment commands without password
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/chown -R www-data\:www-data *
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/chmod -R 775 *
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.1-fpm
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php8.1-fpm
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
ubuntu ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart nginx
```

Save and exit (`:wq` in vi/vim).

Set proper permissions:
```bash
sudo chmod 0440 /etc/sudoers.d/ubuntu-deploy
```

### 3. Test Deployment

After making these changes, push your code and the deployment should work:

```bash
git add .
git commit -m "Fix deployment permissions"
git push origin main
```

## How It Works

1. **First Deployment**: 
   - Script creates `shared/storage` directories
   - Sets `www-data` ownership using `sudo`
   - Creates a marker file `.permissions_set` in shared storage

2. **Subsequent Deployments**:
   - Skips permission setting on `shared/storage` (marker file exists)
   - Only sets permissions on new release's `bootstrap/cache`
   - Runs artisan commands from the correct directory

3. **Permission Strategy**:
   - `shared/storage`: Owned by `www-data` (web server)
   - Release code: Owned by `ubuntu` (deployment user)
   - `bootstrap/cache` in release: Changed to `www-data` after Laravel caching

## Troubleshooting

### If deployment still fails with sudo errors:

1. Verify sudoers file syntax:
```bash
sudo visudo -c -f /etc/sudoers.d/ubuntu-deploy
```

2. Test sudo access manually:
```bash
sudo chown www-data:www-data /var/www/radiance/shared/storage
sudo systemctl reload php8.1-fpm
```

### If artisan commands fail:

1. Verify `.env` symlink exists:
```bash
ls -la /var/www/radiance/current/.env
```

2. Check PHP version:
```bash
php -v
```

### If storage permissions still cause issues:

Manually reset shared storage ownership once:
```bash
sudo chown -R www-data:www-data /var/www/radiance/shared/storage
sudo chmod -R 775 /var/www/radiance/shared/storage
```

## Notes

- The `.permissions_set` marker file prevents repeated permission changes that would fail
- The script uses `|| true` to continue even if some operations fail
- Services are reloaded (faster) with fallback to restart if reload fails

