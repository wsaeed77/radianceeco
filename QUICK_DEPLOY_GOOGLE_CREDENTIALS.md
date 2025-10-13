# Quick Setup: Google Drive Credentials for EC2

## 🔧 Fix Current Deployment (Run This First!)

If you just deployed and got the error "google-drive-credentials.json does not exist":

```bash
# SSH to EC2
ssh -i "C:\Users\TECHNIFI\Downloads\newapp.pem" ubuntu@leads.radianceeco.co.uk

# Run the fix script
cd /var/www/radiance
bash scripts/fix_google_credentials_now.sh
```

This will fix the current deployment immediately!

---

## 🚀 Quick Start (For Fresh Setup)

### Step 1: Make setup script executable (on your local machine)
```bash
chmod +x scripts/setup_google_credentials.sh
```

### Step 2: Run the setup script
```bash
# Make sure google-drive-credentials.json is in your project root
./scripts/setup_google_credentials.sh
```

The script will:
- ✅ Upload your credentials to EC2
- ✅ Place them in the shared directory
- ✅ Set correct permissions
- ✅ Create symlinks automatically
- ✅ Verify everything works

### Step 3: Deploy normally
```bash
git add .
git commit -m "Add document folders and Google credentials deployment"
git push origin main
```

Done! 🎉

---

## 📋 Manual Setup (Alternative)

If you prefer manual setup:

### 1. Upload file to EC2
```bash
scp -i <your-key.pem> \
    google-drive-credentials.json \
    ubuntu@leads.radianceeco.co.uk:/tmp/
```

### 2. SSH and configure
```bash
ssh -i <your-key.pem> ubuntu@leads.radianceeco.co.uk

# Move to shared directory
sudo mv /tmp/google-drive-credentials.json /var/www/radiance/shared/

# Set permissions
sudo chown www-data:www-data /var/www/radiance/shared/google-drive-credentials.json
sudo chmod 640 /var/www/radiance/shared/google-drive-credentials.json

# Verify
ls -lh /var/www/radiance/shared/google-drive-credentials.json
```

### 3. Deploy
```bash
git push origin main
```

---

## ✅ Verify Setup

After deployment, check that everything works:

```bash
# SSH to EC2
ssh -i <your-key> ubuntu@leads.radianceeco.co.uk

# Check symlink exists
ls -lh /var/www/radiance/current/storage/app/google-drive-credentials.json

# Test from Laravel
cd /var/www/radiance/current
php artisan tinker
# In tinker: file_exists(storage_path('app/google-drive-credentials.json'));
# Should return: true
```

---

## 🔒 Security Notes

- ✅ File is in `.gitignore` - never committed to Git
- ✅ Stored in shared directory - persists across deployments
- ✅ Permissions: `640` - only www-data can read
- ✅ Auto-symlinked by deploy script

---

## 📚 Need More Details?

See `GOOGLE_CREDENTIALS_DEPLOYMENT.md` for comprehensive documentation including:
- How it works under the hood
- Troubleshooting guide
- Security best practices
- Updating credentials
- Backup recommendations

