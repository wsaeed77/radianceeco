# Google Drive Credentials Deployment Guide

## Overview
This guide explains how to securely deploy the `google-drive-credentials.json` file to your EC2 server without committing it to Git.

## Security Approach
✅ Credentials stored in `/var/www/radiance/shared/` (outside Git)  
✅ Symlinked into each release automatically  
✅ Never committed to version control  
✅ Persists across deployments  

## Setup Steps

### 1. Add to .gitignore (Already Done)
The credentials file is now in `.gitignore` to prevent accidental commits:
```
google-drive-credentials.json
storage/app/google-drive-credentials.json
```

### 2. Upload Credentials to EC2 Server (One-Time Setup)

#### Option A: Using SCP (Recommended)
From your local machine where you have the credentials file:

```bash
# Upload the credentials file to EC2
scp -i "C:\Users\TECHNIFI\Downloads\newapp.pem" \
    google-drive-credentials.json \
    ubuntu@leads.radianceeco.co.uk:/tmp/google-drive-credentials.json

# SSH to EC2
ssh -i <your-ssh-key.pem> ubuntu@leads.radianceeco.co.uk

# Move to shared directory
sudo mv /tmp/google-drive-credentials.json /var/www/radiance/shared/google-drive-credentials.json

# Set proper permissions
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

#### Option B: Using SFTP
1. Connect to EC2 via SFTP client (FileZilla, WinSCP, etc.)
2. Upload `google-drive-credentials.json` to `/tmp/`
3. SSH to EC2 and run:
```bash
sudo mv /tmp/google-drive-credentials.json /var/www/radiance/shared/google-drive-credentials.json
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

#### Option C: Manual Copy-Paste
1. SSH to EC2
2. Create the file:
```bash
sudo nano /var/www/radiance/shared/google-drive-credentials.json
```
3. Paste the JSON content from your local file
4. Save (Ctrl+O, Enter, Ctrl+X)
5. Set permissions:
```bash
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

### 3. Verify Setup

After uploading, verify the file exists with correct permissions:

```bash
# Check file exists
ls -lh /var/www/radiance/shared/google-drive-credentials.json

# Should show:
# -rw-r----- 1 www-data www-data <size> <date> google-drive-credentials.json

# Verify it's valid JSON
cat /var/www/radiance/shared/google-drive-credentials.json | jq . > /dev/null && echo "Valid JSON" || echo "Invalid JSON"
```

### 4. Deploy Application

Once the credentials file is in place, deploy normally:

```bash
# Push to GitHub (auto-deploys via GitHub Actions)
git add .
git commit -m "Update Google credentials deployment setup"
git push origin main
```

The deploy script will automatically:
1. Create `/var/www/radiance/shared/storage/app/` directory
2. Symlink the credentials file from shared to the current release
3. Each new deployment will have access to the credentials

## How It Works

### Deployment Flow
```
1. GitHub Actions builds & syncs code to EC2
   └── Excludes google-drive-credentials.json (in .gitignore)

2. deploy_remote.sh runs on EC2:
   ├── Creates shared storage structure
   ├── Symlinks shared/storage -> release/storage
   └── Symlinks shared/google-drive-credentials.json -> release/storage/app/google-drive-credentials.json

3. Laravel app reads credentials from:
   storage_path('app/google-drive-credentials.json')
   └── Actually reads from shared (via symlink)
```

### Directory Structure
```
/var/www/radiance/
├── shared/
│   ├── .env                                    # Shared environment file
│   ├── google-drive-credentials.json          # Shared Google credentials
│   └── storage/                                # Shared storage (logs, sessions, etc.)
│       └── app/
│           └── google-drive-credentials.json -> ../../google-drive-credentials.json (symlink)
├── releases/
│   └── <commit-sha>/
│       ├── .env -> /var/www/radiance/shared/.env
│       └── storage -> /var/www/radiance/shared/storage
│           └── app/
│               └── google-drive-credentials.json (symlinked via parent)
└── current -> /var/www/radiance/releases/<latest-sha>
```

## Updated Deploy Script

The `scripts/deploy_remote.sh` now includes:

```bash
# Symlink Google Drive credentials from shared if exists
if [ -f ${DEPLOY_PATH}/shared/google-drive-credentials.json ]; then
  ln -sf ${DEPLOY_PATH}/shared/google-drive-credentials.json ${RELEASE_DIR}/storage/app/google-drive-credentials.json
fi
```

## Testing

After deployment, verify the credentials are accessible:

```bash
# SSH to EC2
ssh -i <your-key> ubuntu@leads.radianceeco.co.uk

# Check symlink exists in current release
ls -lh /var/www/radiance/current/storage/app/google-drive-credentials.json

# Should show symlink pointing to shared:
# google-drive-credentials.json -> /var/www/radiance/shared/google-drive-credentials.json

# Test from Laravel
cd /var/www/radiance/current
php artisan tinker

# In tinker:
file_exists(storage_path('app/google-drive-credentials.json'));
# Should return: true
```

## Troubleshooting

### Issue: File not found after deployment

**Check 1: Verify file exists in shared**
```bash
ls -lh /var/www/radiance/shared/google-drive-credentials.json
```

**Check 2: Verify symlink exists**
```bash
ls -lh /var/www/radiance/current/storage/app/google-drive-credentials.json
```

**Check 3: Verify permissions**
```bash
# File should be readable by www-data
sudo -u www-data cat /var/www/radiance/shared/google-drive-credentials.json > /dev/null && echo "OK" || echo "Permission denied"
```

**Fix: Recreate symlink manually**
```bash
ln -sf /var/www/radiance/shared/google-drive-credentials.json \
       /var/www/radiance/current/storage/app/google-drive-credentials.json
```

### Issue: Permission denied errors

```bash
# Fix ownership
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json

# Fix permissions (read-only for www-data)
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

### Issue: Invalid credentials error

```bash
# Verify JSON is valid
cat /var/www/radiance/shared/google-drive-credentials.json | jq .

# Re-upload from local
scp -i <key> google-drive-credentials.json ubuntu@server:/tmp/
ssh -i <key> ubuntu@server
sudo mv /tmp/google-drive-credentials.json /var/www/radiance/shared/
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

## Security Best Practices

✅ **DO**:
- Store credentials in `/var/www/radiance/shared/`
- Set ownership to `www-data:www-data`
- Set permissions to `640` (read/write for owner, read for group, none for others)
- Add to `.gitignore`
- Use secure transfer methods (SCP/SFTP)

❌ **DON'T**:
- Commit credentials to Git
- Store in public directories
- Share via insecure channels (email, chat)
- Use overly permissive permissions (777, 666)
- Hardcode credentials in code

## Backup Recommendations

1. **Keep local copy**: Store a copy of credentials file locally in a secure location
2. **Document service account**: Note which Google account/project the service account belongs to
3. **Access to Google Cloud Console**: Ensure you can regenerate credentials if needed

## Updating Credentials

If you need to update the credentials file:

1. Upload new file to EC2:
```bash
scp -i <key> google-drive-credentials.json ubuntu@server:/tmp/
```

2. Replace on server:
```bash
ssh -i <key> ubuntu@server
sudo mv /tmp/google-drive-credentials.json /var/www/radiance/shared/google-drive-credentials.json
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json
```

3. Restart PHP-FPM (optional, for cached credentials):
```bash
sudo systemctl restart php8.1-fpm
```

No code deployment needed - the symlink ensures all releases use the updated file immediately.

## Summary

This approach ensures:
- ✅ Credentials never in version control
- ✅ Automatic availability in every deployment
- ✅ Single source of truth in shared directory
- ✅ Secure permissions and ownership
- ✅ Easy to update without redeploying code
- ✅ Follows Laravel deployment best practices

Your Google Drive integration is now deployment-ready! 🚀

