#!/bin/bash

# Radiance CRM - Application Deployment Script
# Run this after uploading your application code

set -e

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() { echo -e "${GREEN}[✓]${NC} $1"; }
print_error() { echo -e "${RED}[✗]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[!]${NC} $1"; }

if [ "$EUID" -ne 0 ]; then 
    print_error "Please run as root (use sudo)"
    exit 1
fi

APP_DIR=$(pwd)
print_status "Deploying application from: $APP_DIR"

# Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm ci

# Build frontend assets
print_status "Building frontend assets..."
npm run build

# Set up environment file
if [ ! -f .env ]; then
    print_status "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
    
    read -p "Enter APP_URL (e.g., https://yourdomain.com): " APP_URL
    read -p "Enter database name: " DB_NAME
    read -p "Enter database username: " DB_USER
    read -sp "Enter database password: " DB_PASSWORD
    echo
    
    # Update .env file
    sed -i "s|APP_URL=.*|APP_URL=${APP_URL}|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
fi

# Set permissions
print_status "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed database (if needed)
read -p "Do you want to seed the database? (y/N): " SEED_DB
if [ "$SEED_DB" == "y" ] || [ "$SEED_DB" == "Y" ]; then
    php artisan db:seed --force
fi

# Clear and cache configuration
print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configure Nginx
print_status "Configuring Nginx..."
NGINX_CONF="/etc/nginx/sites-available/radiance"
cat > $NGINX_CONF <<'NGINX_CONFIG'
server {
    listen 80;
    listen [::]:80;
    server_name _;
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
NGINX_CONFIG

# Update server_name if domain provided
if [ ! -z "$APP_URL" ]; then
    DOMAIN=$(echo $APP_URL | sed 's|https\?://||' | sed 's|/.*||')
    sed -i "s|server_name _;|server_name $DOMAIN;|" $NGINX_CONF
fi

# Replace APP_DIR in nginx config
sed -i "s|/var/www/radiance|$APP_DIR|g" $NGINX_CONF

# Enable site
ln -sf $NGINX_CONF /etc/nginx/sites-enabled/radiance
rm -f /etc/nginx/sites-enabled/default

# Test and restart Nginx
nginx -t
systemctl restart nginx
systemctl enable nginx

# Configure Laravel Queue Worker
print_status "Setting up queue worker..."
cat > /etc/supervisor/conf.d/radiance-worker.conf <<SUPERVISOR_CONFIG
[program:radiance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR_CONFIG

supervisorctl reread
supervisorctl update
supervisorctl start radiance-worker:*

# Setup SSL if domain provided
if [ ! -z "$DOMAIN" ] && [ "$DOMAIN" != "_" ]; then
    read -p "Setup SSL certificate with Let's Encrypt? (y/N): " SETUP_SSL
    if [ "$SETUP_SSL" == "y" ] || [ "$SETUP_SSL" == "Y" ]; then
        read -p "Enter email for SSL certificate: " SSL_EMAIL
        certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m $SSL_EMAIL
    fi
fi

# Set up cron for Laravel scheduler
print_status "Setting up Laravel scheduler..."
(crontab -l 2>/dev/null; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -

print_status "Deployment complete!"
echo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  🎉 Radiance CRM is now deployed!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo
if [ ! -z "$APP_URL" ]; then
    echo "  🌐 Application URL: $APP_URL"
else
    PUBLIC_IP=$(curl -s ifconfig.me)
    echo "  🌐 Application URL: http://$PUBLIC_IP"
fi
echo "  📁 Application Directory: $APP_DIR"
echo
print_warning "Important: Make sure your EC2 Security Group allows:"
echo "  - Port 80 (HTTP)"
echo "  - Port 443 (HTTPS)"
echo "  - Port 22 (SSH)"
echo

