# Nginx Configuration Fix - Showing Default Welcome Page

## Problem
Your domain is showing the default "Welcome to nginx!" page instead of the Laravel application.

## Cause
Nginx is installed but either:
1. The Laravel application configuration wasn't created
2. The configuration isn't enabled
3. Nginx is pointing to the wrong directory

## Quick Fix

Connect to your EC2 instance and run these commands:

### Step 1: Check if the application directory exists
```bash
ls -la /var/www/radiance
```

**Expected:** You should see your Laravel application files including `public/`, `app/`, `vendor/`, etc.

**If not found:** The deployment didn't complete. Re-run the deployment.

### Step 2: Check nginx configuration
```bash
ls -la /etc/nginx/sites-available/
ls -la /etc/nginx/sites-enabled/
```

**Expected:** You should see a `radiance` file in both directories.

**If missing:** Create the nginx configuration (see below).

### Step 3: Create/Update Nginx Configuration

Create the configuration file:
```bash
sudo nano /etc/nginx/sites-available/radiance
```

Paste this configuration (replace `your-domain.com` with your actual domain):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/radiance/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Save:** Press `Ctrl+X`, then `Y`, then `Enter`

### Step 4: Enable the Site
```bash
# Remove default nginx site
sudo rm /etc/nginx/sites-enabled/default

# Enable Laravel site
sudo ln -sf /etc/nginx/sites-available/radiance /etc/nginx/sites-enabled/

# Test nginx configuration
sudo nginx -t
```

**Expected output:**
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### Step 5: Restart Nginx
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

### Step 6: Check Status
```bash
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
```

Both should show "active (running)" in green.

### Step 7: Set Correct Permissions
```bash
cd /var/www/radiance
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 8: Check Laravel Installation
```bash
cd /var/www/radiance
php artisan --version
```

**Expected:** Should show Laravel version (e.g., "Laravel Framework 10.49.1")

## Verification

Visit your domain in a browser. You should now see:
- Laravel login page, OR
- Laravel welcome page, OR
- Your application's home page

**Not working yet?** Continue to troubleshooting below.

## Troubleshooting

### Still showing nginx default page?

**Check which configuration is active:**
```bash
sudo nginx -T | grep "server_name"
```

**Should show your domain, not `_` (underscore)**

If it shows `_`, the default configuration is still active:
```bash
sudo rm /etc/nginx/sites-enabled/default
sudo systemctl restart nginx
```

### Getting "502 Bad Gateway"?

**Check PHP-FPM:**
```bash
sudo systemctl status php8.1-fpm

# If not running, start it:
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm
```

### Getting "403 Forbidden"?

**Fix permissions:**
```bash
cd /var/www/radiance
sudo chown -R www-data:www-data .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

### Getting "500 Internal Server Error"?

**Check Laravel logs:**
```bash
tail -50 /var/www/radiance/storage/logs/laravel.log
```

**Check nginx error logs:**
```bash
sudo tail -50 /var/nginx/error.log
```

**Common causes:**
1. **Missing .env file:**
   ```bash
   cp /var/www/radiance/.env.example /var/www/radiance/.env
   nano /var/www/radiance/.env
   # Configure your database and app settings
   ```

2. **Missing APP_KEY:**
   ```bash
   cd /var/www/radiance
   php artisan key:generate
   ```

3. **Database not configured:**
   ```bash
   nano /var/www/radiance/.env
   # Update DB_* settings
   php artisan migrate
   ```

### Getting "404 Not Found" on all pages except home?

**Add this to nginx config:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Then restart: `sudo systemctl restart nginx`

## Complete Deployment Script

If you need to re-deploy completely, here's a complete script:

```bash
#!/bin/bash

# Variables
APP_NAME="radiance"
DOMAIN="your-domain.com"
DB_NAME="radiance_db"
DB_USER="radiance_user"
DB_PASS="your-secure-password"

# Install dependencies
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring \
    php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd \
    mysql-server git unzip

# Install Composer
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer

# Clone application (if not done)
sudo mkdir -p /var/www
cd /var/www
# Replace with your git repo or upload files
sudo git clone <your-repo> radiance
# OR upload via SCP/SFTP

# Set permissions
sudo chown -R $USER:www-data /var/www/radiance
cd /var/www/radiance

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
nano .env  # Configure database, APP_URL, etc.

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set final permissions
sudo chown -R www-data:www-data /var/www/radiance
sudo chmod -R 775 storage bootstrap/cache

# Create nginx config
sudo tee /etc/nginx/sites-available/radiance > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root /var/www/radiance/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/radiance /etc/nginx/sites-enabled/

# Test and restart
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

echo "Deployment complete! Visit http://$DOMAIN"
```

## Quick Checklist

- [ ] Application files in `/var/www/radiance`
- [ ] Nginx config created in `/etc/nginx/sites-available/radiance`
- [ ] Symlink exists in `/etc/nginx/sites-enabled/radiance`
- [ ] Default nginx config removed
- [ ] `.env` file configured
- [ ] `APP_KEY` generated
- [ ] Database configured and migrated
- [ ] Permissions set (www-data:www-data)
- [ ] Nginx restarted
- [ ] PHP-FPM running

## DNS Configuration

Make sure your domain's DNS points to your EC2 instance:

1. Go to your domain registrar
2. Add an A record:
   - **Type**: A
   - **Name**: @ (or your domain)
   - **Value**: Your EC2 public IP
   - **TTL**: 3600

3. Optional: Add www subdomain:
   - **Type**: A
   - **Name**: www
   - **Value**: Your EC2 public IP
   - **TTL**: 3600

**DNS can take 5-60 minutes to propagate.**

## Test From Command Line

```bash
# Test nginx is serving the correct site
curl -I http://your-domain.com

# Should show:
# HTTP/1.1 200 OK
# or HTTP/1.1 302 Found (redirect)

# NOT:
# HTTP/1.1 404 Not Found
```

## SSL Certificate (Optional but Recommended)

Once the site is working, add SSL:

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Follow the prompts
# Choose redirect HTTP to HTTPS (option 2)

# Auto-renewal is set up automatically
```

## Need More Help?

**Check logs:**
```bash
# Nginx access log
sudo tail -f /var/log/nginx/access.log

# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Laravel log
tail -f /var/www/radiance/storage/logs/laravel.log

# PHP-FPM log
sudo tail -f /var/log/php8.1-fpm.log
```

**Share these outputs if you need further assistance.**

## Status: Ready to Fix!

Follow the Quick Fix steps above and your application should be running within 5-10 minutes!

