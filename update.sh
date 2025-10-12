#!/bin/bash

# Radiance CRM - Update Script
# Use this for deploying updates to your application

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() { echo -e "${GREEN}[✓]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[!]${NC} $1"; }

if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root (use sudo)"
    exit 1
fi

APP_DIR=$(pwd)

print_warning "Starting application update..."
echo

# Put application in maintenance mode
print_status "Enabling maintenance mode..."
php artisan down

# Pull latest changes (if using git)
if [ -d .git ]; then
    print_status "Pulling latest code..."
    sudo -u www-data git pull origin main
fi

# Install/update dependencies
print_status "Updating PHP dependencies..."
composer install --optimize-autoloader --no-dev

print_status "Updating Node.js dependencies..."
npm ci

# Build frontend assets
print_status "Building frontend assets..."
npm run build

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Clear and optimize
print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
print_status "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Restart services
print_status "Restarting services..."
systemctl restart php8.1-fpm
supervisorctl restart radiance-worker:*

# Bring application back online
print_status "Disabling maintenance mode..."
php artisan up

echo
print_status "Update complete!"
echo "Application is now live with the latest changes."

