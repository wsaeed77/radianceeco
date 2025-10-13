#!/usr/bin/env bash
# One-time setup script for EC2 to create shared storage with correct permissions
# Run this ONCE on the server before deploying

set -euo pipefail

DEPLOY_PATH="/var/www/radiance"

echo "Setting up shared storage for Laravel releases..."

# Create shared storage structure
mkdir -p ${DEPLOY_PATH}/shared/storage/app/public
mkdir -p ${DEPLOY_PATH}/shared/storage/framework/cache/data
mkdir -p ${DEPLOY_PATH}/shared/storage/framework/sessions
mkdir -p ${DEPLOY_PATH}/shared/storage/framework/views
mkdir -p ${DEPLOY_PATH}/shared/storage/logs

echo "Setting ownership to www-data..."
sudo chown -R www-data:www-data ${DEPLOY_PATH}/shared/storage

echo "Setting permissions..."
sudo chmod -R 775 ${DEPLOY_PATH}/shared/storage

# If there's existing storage in current, migrate it
if [ -d "${DEPLOY_PATH}/current/storage" ] && [ ! -L "${DEPLOY_PATH}/current/storage" ]; then
    echo "Migrating existing storage data..."
    sudo cp -r ${DEPLOY_PATH}/current/storage/* ${DEPLOY_PATH}/shared/storage/ 2>/dev/null || true
    sudo chown -R www-data:www-data ${DEPLOY_PATH}/shared/storage
fi

echo "✓ Shared storage setup complete!"
echo ""
echo "Structure created:"
ls -la ${DEPLOY_PATH}/shared/storage/
echo ""
echo "You can now deploy. The deploy script will automatically symlink storage to this shared directory."

