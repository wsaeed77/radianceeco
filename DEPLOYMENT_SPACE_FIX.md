# Deployment Space Issue - Fix Documentation

## Problem
The EC2 instance was running out of disk space during deployment due to:
1. `vendor.zip` (large file) being included in the repository
2. Large Ofgem CSV files (`eco4_partial_v6.csv`, `gbis_partial_v3.csv`, `ECO4 Full Project Scores Matrix.csv`) being copied to each release
3. Multiple releases storing duplicate copies of these files

## Solution

### 1. Remove Large Files from Git Repository

**Files Removed:**
- `vendor.zip` (~100MB+)
- `storage/ofgem_files/*.csv` (multiple large CSV files)

**Changes Made:**
```bash
# Added to .gitignore
vendor.zip
/storage/ofgem_files/*.csv

# Removed from git tracking
git rm --cached vendor.zip
git rm --cached storage/ofgem_files/*.csv
```

### 2. Move Ofgem Files to Shared Storage

**Why?**
- Ofgem CSV files are static reference data
- They don't change between releases
- Storing them in `shared/storage/ofgem_files/` means they're only stored ONCE
- All releases access the same files via symlink

**Deploy Script Updates:**
- `scripts/deploy_remote.sh` now creates `shared/storage/ofgem_files/` directory
- Files are accessed via the existing storage symlink

### 3. One-Time Server Setup

**Upload Ofgem Files to Server (Do this ONCE):**

```bash
# On your local machine, create temp directory on server
ssh ubuntu@your-ec2-server "mkdir -p ~/ofgem_temp"

# Upload the CSV files
scp storage/ofgem_files/eco4_partial_v6.csv ubuntu@your-ec2-server:~/ofgem_temp/
scp storage/ofgem_files/gbis_partial_v3.csv ubuntu@your-ec2-server:~/ofgem_temp/
scp "storage/ofgem_files/ECO4 Full Project Scores Matrix.csv" ubuntu@your-ec2-server:~/ofgem_temp/

# On the server, move files to shared storage
ssh ubuntu@your-ec2-server
sudo mv ~/ofgem_temp/*.csv /var/www/radiance/shared/storage/ofgem_files/
sudo chown -R www-data:www-data /var/www/radiance/shared/storage/ofgem_files
sudo chmod -R 775 /var/www/radiance/shared/storage/ofgem_files
rm -rf ~/ofgem_temp
```

**Or use the setup script:**
```bash
# On server
bash scripts/setup_ofgem_files.sh
# Follow the instructions it provides
```

## How It Works Now

### Before (❌ Space Wasted):
```
/var/www/radiance/
├── releases/
│   ├── abc123/
│   │   └── storage/ofgem_files/*.csv  (30MB+)
│   ├── def456/
│   │   └── storage/ofgem_files/*.csv  (30MB+)
│   └── ghi789/
│       └── storage/ofgem_files/*.csv  (30MB+)
└── shared/
```
**Total: 90MB+ for 3 releases**

### After (✅ Space Saved):
```
/var/www/radiance/
├── releases/
│   ├── abc123/
│   │   └── storage -> ../../shared/storage (symlink)
│   ├── def456/
│   │   └── storage -> ../../shared/storage (symlink)
│   └── ghi789/
│       └── storage -> ../../shared/storage (symlink)
└── shared/
    └── storage/
        └── ofgem_files/*.csv  (30MB+ ONCE)
```
**Total: 30MB for all releases**

## Application Code

**No changes needed!** The application code still uses:
```php
storage_path('ofgem_files/eco4_partial_v6.csv')
storage_path('ofgem_files/gbis_partial_v3.csv')
storage_path('ofgem_files/ECO4 Full Project Scores Matrix.csv')
```

Because `storage` is symlinked to `shared/storage`, the files are automatically accessed from the shared location.

## Deployment Process

### First Time Setup (After This Update):
1. Push these changes to GitHub
2. Let GitHub Actions deploy (it will create the `shared/storage/ofgem_files/` directory)
3. Upload the CSV files to the server (see commands above)
4. Done! Future deployments will use the shared files

### Future Deployments:
- GitHub Actions builds and deploys as normal
- No CSV files are transferred (they're gitignored)
- All releases use the same shared CSV files
- Disk space is saved

## Space Savings

**Per Release:**
- `vendor.zip`: ~100MB saved
- Ofgem CSV files: ~30MB saved
- **Total per release: ~130MB saved**

**With 5 releases:**
- **Total space saved: ~650MB**

## Verification

**Check if files are in shared storage:**
```bash
ssh ubuntu@your-ec2-server
ls -lh /var/www/radiance/shared/storage/ofgem_files/
```

**Check if current release uses symlink:**
```bash
ls -la /var/www/radiance/current/storage
# Should show: storage -> /var/www/radiance/shared/storage
```

**Test the application:**
```bash
# On server
cd /var/www/radiance/current
php artisan tinker
>>> storage_path('ofgem_files/eco4_partial_v6.csv')
# Should return: "/var/www/radiance/shared/storage/ofgem_files/eco4_partial_v6.csv"
```

## Troubleshooting

### "File not found" errors after deployment:
```bash
# Make sure CSV files are in shared storage
ls /var/www/radiance/shared/storage/ofgem_files/

# If missing, upload them (see setup commands above)
```

### Permission errors:
```bash
sudo chown -R www-data:www-data /var/www/radiance/shared/storage/ofgem_files
sudo chmod -R 775 /var/www/radiance/shared/storage/ofgem_files
```

## Important Notes

1. **CSV files are NOT in git** - They must be uploaded to the server manually (once)
2. **Keep local copies** - Keep the CSV files in your local `storage/ofgem_files/` for development
3. **Backup** - Consider backing up the CSV files from the server periodically
4. **Updates** - If you need to update a CSV file, upload the new version to the server's shared storage

## Files Modified

- `.gitignore` - Added vendor.zip and ofgem CSV files
- `scripts/deploy_remote.sh` - Added ofgem_files directory creation
- `scripts/setup_ofgem_files.sh` - New setup helper script
- `DEPLOYMENT_SPACE_FIX.md` - This documentation

---
**Status:** ✅ Complete - Ready for deployment
**Date:** October 13, 2025

