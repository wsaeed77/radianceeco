# Radiance CRM - EC2 Deployment Guide

## 🚀 Quick Deployment (15 minutes)

This guide will help you deploy Radiance CRM on your Ubuntu EC2 instance.

---

## 📋 Prerequisites

- Ubuntu 20.04 or 22.04 EC2 instance
- SSH access to your EC2 instance
- Security Group configured to allow:
  - Port 22 (SSH)
  - Port 80 (HTTP)
  - Port 443 (HTTPS)
- (Optional) A domain name pointed to your EC2 IP

---

## 🎯 Step-by-Step Deployment

### Step 1: Connect to Your EC2 Instance

```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

### Step 2: Prepare the System

```bash
# Download the deployment script
sudo apt-get update
sudo apt-get install -y git
git clone <your-repository-url> /tmp/radiance-deploy
cd /tmp/radiance-deploy

# Or if you have a zip file, upload it and extract:
# scp -i your-key.pem radiance.zip ubuntu@your-ec2-ip:~
# unzip radiance.zip
# cd radiance

# Make deployment scripts executable
chmod +x deploy.sh deploy-app.sh

# Run system setup
sudo ./deploy.sh
```

**You'll be asked for:**
- Domain name (optional - press Enter to use IP address)
- Database name (default: radiance_crm)
- Database username (default: radiance_user)
- Database password (create a strong password)
- Application directory (default: /var/www/radiance)

### Step 3: Upload Your Application

If you haven't already, upload your application code to the server:

```bash
# On your local machine:
# Zip the project (excluding node_modules, vendor, etc.)
zip -r radiance-app.zip . \
  -x "node_modules/*" \
  -x "vendor/*" \
  -x ".git/*" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*"

# Upload to EC2
scp -i your-key.pem radiance-app.zip ubuntu@your-ec2-ip:/tmp/

# On EC2, extract to application directory
sudo unzip /tmp/radiance-app.zip -d /var/www/radiance
```

### Step 4: Deploy the Application

```bash
# Go to application directory
cd /var/www/radiance

# Run application deployment
sudo ./deploy-app.sh
```

**You'll be asked for:**
- Application URL (e.g., https://yourdomain.com or http://your-ec2-ip)
- Database credentials (same as Step 2)
- Whether to seed the database
- Whether to setup SSL (if you have a domain)

### Step 5: Verify Deployment

Visit your application:
- With domain: `https://yourdomain.com`
- Without domain: `http://your-ec2-ip`

---

## 🔐 Security Configuration

### 1. Configure .env File

Important environment variables to set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Generate a secure key
php artisan key:generate

# EPC API Configuration
EPC_API_KEY=your_epc_api_key

# Google Drive (if using)
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

### 2. Set File Permissions

```bash
sudo chown -R www-data:www-data /var/www/radiance
sudo chmod -R 755 /var/www/radiance
sudo chmod -R 775 /var/www/radiance/storage
sudo chmod -R 775 /var/www/radiance/bootstrap/cache
```

### 3. Enable Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

---

## 📊 Post-Deployment Tasks

### Create Admin User

```bash
cd /var/www/radiance
php artisan tinker
```

In tinker:
```php
$user = new App\Models\User();
$user->name = 'Admin User';
$user->email = 'admin@example.com';
$user->password = bcrypt('your-secure-password');
$user->save();
```

### Run Database Seeders (Optional)

```bash
php artisan db:seed
```

---

## 🔄 Updating Your Application

Create an update script for easy deployments:

```bash
cd /var/www/radiance
sudo ./update.sh
```

Or manually:

```bash
cd /var/www/radiance

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart radiance-worker:*
sudo systemctl restart nginx php8.1-fpm
```

---

## 🐛 Troubleshooting

### Check Application Logs

```bash
tail -f /var/www/radiance/storage/logs/laravel.log
```

### Check Nginx Logs

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Check PHP-FPM Status

```bash
sudo systemctl status php8.1-fpm
```

### Check Queue Workers

```bash
sudo supervisorctl status radiance-worker:*
```

### Restart All Services

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo supervisorctl restart radiance-worker:*
```

### Fix Permission Issues

```bash
cd /var/www/radiance
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🔧 Common Issues

### 1. "500 Internal Server Error"

- Check storage permissions
- Check .env file exists and is configured
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Clear caches: `php artisan config:clear && php artisan cache:clear`

### 2. "Database connection error"

- Verify MySQL is running: `sudo systemctl status mysql`
- Check database credentials in .env
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

### 3. "Assets not loading"

- Build assets: `npm run build`
- Clear config: `php artisan config:cache`
- Check public/build directory exists

### 4. "Queue not processing"

- Check worker status: `sudo supervisorctl status radiance-worker:*`
- Restart workers: `sudo supervisorctl restart radiance-worker:*`
- Check logs: `tail -f storage/logs/worker.log`

---

## 📈 Performance Optimization

### Enable OPcache

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Configure PHP-FPM

Edit `/etc/php/8.1/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.1-fpm
```

---

## 🔒 SSL Certificate Setup

If you have a domain:

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Auto-renewal is set up automatically. Test it:

```bash
sudo certbot renew --dry-run
```

---

## 📱 Monitoring

### Set up monitoring (optional)

```bash
# Install monitoring tools
sudo apt-get install -y htop iotop nethogs

# Monitor in real-time
htop           # CPU/Memory
iotop          # Disk I/O
nethogs        # Network
```

---

## 💾 Backup

### Database Backup Script

Create `/usr/local/bin/backup-radiance.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/radiance"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u radiance_user -p radiance_crm > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/radiance/storage

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Add to crontab:
```bash
sudo crontab -e
# Add this line:
0 2 * * * /usr/local/bin/backup-radiance.sh
```

---

## ✅ Deployment Checklist

- [ ] EC2 Security Group configured (ports 22, 80, 443)
- [ ] Domain DNS pointed to EC2 IP (if using domain)
- [ ] System packages installed (deploy.sh)
- [ ] Application code uploaded
- [ ] .env file configured
- [ ] Database migrated
- [ ] Frontend assets built
- [ ] Nginx configured and running
- [ ] SSL certificate installed (if using domain)
- [ ] Queue workers running
- [ ] Cron scheduler configured
- [ ] Admin user created
- [ ] Backup script configured
- [ ] Application accessible and working

---

## 📞 Need Help?

- Check Laravel logs: `/var/www/radiance/storage/logs/laravel.log`
- Check Nginx logs: `/var/log/nginx/error.log`
- Laravel documentation: https://laravel.com/docs
- Ubuntu documentation: https://ubuntu.com/server/docs

---

**Last Updated:** $(date)

