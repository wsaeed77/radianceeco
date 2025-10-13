# Quick Fix: Deployment Space Issue

## 🚨 Problem
Deployment failing with "No space left on device" error.

## ✅ Solution Applied

### Changes Made:
1. ✅ Removed `vendor.zip` from git
2. ✅ Removed large CSV files from git
3. ✅ Updated deploy script to use shared storage for CSV files

### What You Need to Do NOW:

#### Step 1: Upload CSV Files to Server (ONE TIME ONLY)

**On your local machine:**
```bash
# Create temp directory on server
ssh ubuntu@leads.radianceeco.co.uk "mkdir -p ~/ofgem_temp"

# Upload CSV files (from your local project root)
scp storage/ofgem_files/eco4_partial_v6.csv ubuntu@leads.radianceeco.co.uk:~/ofgem_temp/
scp storage/ofgem_files/gbis_partial_v3.csv ubuntu@leads.radianceeco.co.uk:~/ofgem_temp/
scp "storage/ofgem_files/ECO4 Full Project Scores Matrix.csv" ubuntu@leads.radianceeco.co.uk:~/ofgem_temp/
```

**On the server:**
```bash
ssh ubuntu@leads.radianceeco.co.uk

# Move files to shared storage
sudo mv ~/ofgem_temp/*.csv /var/www/radiance/shared/storage/ofgem_files/

# Set permissions
sudo chown -R www-data:www-data /var/www/radiance/shared/storage/ofgem_files
sudo chmod -R 775 /var/www/radiance/shared/storage/ofgem_files

# Clean up
rm -rf ~/ofgem_temp

# Verify files are there
ls -lh /var/www/radiance/shared/storage/ofgem_files/
```

#### Step 2: Push Changes & Deploy
```bash
# Commit and push (already done in this session)
git add .
git commit -m "Fix deployment space issue - remove large files from git"
git push origin main

# GitHub Actions will deploy automatically
# This time it should succeed!
```

## 📊 Space Saved
- **Per release:** ~130MB
- **With 5 releases:** ~650MB saved!

## ✅ Verify It Works
After deployment completes:
```bash
ssh ubuntu@leads.radianceeco.co.uk
ls -la /var/www/radiance/current/storage
# Should show: storage -> /var/www/radiance/shared/storage

ls -lh /var/www/radiance/shared/storage/ofgem_files/
# Should show all 3 CSV files
```

## 🔄 Future Deployments
- CSV files stay in shared storage
- All releases use the same files
- No more space issues!

---
**Status:** Ready to deploy after uploading CSV files

