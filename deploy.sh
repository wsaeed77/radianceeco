#!/bin/bash

# Radiance CRM - Automated Deployment Script for Ubuntu EC2
# This script will set up everything needed to deploy the application

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    print_error "Please run as root (use sudo)"
    exit 1
fi

print_status "Starting Radiance CRM deployment on Ubuntu..."

# Get deployment information
read -p "Enter your domain name (or press Enter to use IP address): " DOMAIN_NAME
read -p "Enter database name (default: radiance_crm): " DB_NAME
DB_NAME=${DB_NAME:-radiance_crm}
read -p "Enter database username (default: radiance_user): " DB_USER
DB_USER=${DB_USER:-radiance_user}
read -sp "Enter database password: " DB_PASSWORD
echo
read -p "Enter application directory (default: /var/www/radiance): " APP_DIR
APP_DIR=${APP_DIR:-/var/www/radiance}

# Update system
print_status "Updating system packages..."
apt-get update -qq
apt-get upgrade -y -qq

# Install required packages
print_status "Installing required packages..."
apt-get install -y -qq \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    zip \
    nginx \
    supervisor \
    certbot \
    python3-certbot-nginx

# Install PHP 8.1
print_status "Installing PHP 8.1 and extensions..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php8.1-fpm \
    php8.1-cli \
    php8.1-common \
    php8.1-mysql \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-intl

# Install MySQL
print_status "Installing MySQL..."
apt-get install -y -qq mysql-server

# Secure MySQL and create database
print_status "Setting up MySQL database..."
mysql <<MYSQL_SCRIPT
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_PASSWORD}';
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# Install Node.js 18.x
print_status "Installing Node.js 18.x..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y -qq nodejs

# Install Composer
print_status "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create application directory
print_status "Creating application directory..."
mkdir -p $APP_DIR
cd $APP_DIR

print_status "Deployment preparation complete!"
echo
print_warning "Next steps:"
echo "1. Upload your application code to: $APP_DIR"
echo "2. Run: cd $APP_DIR && sudo bash deploy-app.sh"
echo
echo "Database Details:"
echo "  Database: $DB_NAME"
echo "  Username: $DB_USER"
echo "  Password: $DB_PASSWORD"
echo
print_status "System is ready for application deployment!"

