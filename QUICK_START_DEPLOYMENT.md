# 🚀 Radiance CRM - Quick Start Deployment

## The Fastest Way to Deploy (Copy & Paste)

### Step 1: SSH into Your EC2 Instance
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip-address
```

### Step 2: Run These Commands

```bash
# Update system
sudo apt-get update

# Install git
sudo apt-get install -y git unzip

# Upload your project or clone from git
# Option A: If you have the code on GitHub/GitLab
git clone YOUR_REPOSITORY_URL /tmp/radiance
cd /tmp/radiance

# Option B: If you have a zip file on your computer
# Run this on YOUR LOCAL MACHINE first:
# zip -r radiance.zip . -x "node_modules/*" "vendor/*" ".git/*"
# scp -i your-key.pem radiance.zip ubuntu@your-ec2-ip:/tmp/
# Then on EC2:
# cd /tmp && unzip radiance.zip -d radiance && cd radiance

# Make scripts executable
chmod +x deploy.sh deploy-app.sh update.sh

# Run deployment
sudo ./deploy.sh
```

**When prompted, enter:**
- Domain: `your-domain.com` (or press Enter to use IP)
- Database name: Press Enter (uses: radiance_crm)
- Database user: Press Enter (uses: radiance_user)
- Database password: `YourStrongPassword123!`
- App directory: Press Enter (uses: /var/www/radiance)

```bash
# Copy application files to web directory
sudo cp -r . /var/www/radiance/
cd /var/www/radiance

# Deploy application
sudo ./deploy-app.sh
```

**When prompted, enter:**
- APP_URL: `https://your-domain.com` or `http://your-ec2-ip`
- Database name: `radiance_crm`
- Database user: `radiance_user`
- Database password: `YourStrongPassword123!`
- Seed database: `y` (if you want sample data)
- Setup SSL: `y` (if you have a domain)

### Step 3: Access Your Application

Open browser and visit:
- With domain: `https://your-domain.com`
- Without domain: `http://your-ec2-ip-address`

---

## ⚡ Important: Configure Security Group

In AWS Console, make sure your EC2 Security Group allows:

| Type | Protocol | Port | Source |
|------|----------|------|--------|
| SSH | TCP | 22 | Your IP |
| HTTP | TCP | 80 | 0.0.0.0/0 |
| HTTPS | TCP | 443 | 0.0.0.0/0 |

---

## 🔑 Create Admin User

```bash
cd /var/www/radiance
php artisan tinker
```

Then run:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('Admin123!');
$user->save();
exit
```

---

## 🔄 Future Updates

When you have code changes:

```bash
# SSH into server
ssh -i your-key.pem ubuntu@your-ec2-ip

# Upload new code or git pull
# Then run:
cd /var/www/radiance
sudo ./update.sh
```

---

## 🆘 Quick Troubleshooting

### Can't access the site?
```bash
sudo systemctl status nginx
sudo systemctl restart nginx
```

### 500 Error?
```bash
cd /var/www/radiance
sudo php artisan config:clear
sudo php artisan cache:clear
tail -f storage/logs/laravel.log
```

### Permission errors?
```bash
cd /var/www/radiance
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

---

## 📞 Need More Help?

See full documentation: `DEPLOYMENT_GUIDE.md`

---

**That's it! Your application should be live! 🎉**

