#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH=${1:?"DEPLOY_PATH required"}
RELEASE=${2:?"RELEASE SHA required"}

CURRENT=${DEPLOY_PATH}/current
RELEASE_DIR=${DEPLOY_PATH}/releases/${RELEASE}

# Ensure shared storage directory exists with proper structure
mkdir -p ${DEPLOY_PATH}/shared/storage/app/public
mkdir -p ${DEPLOY_PATH}/shared/storage/framework/{cache,sessions,views}
mkdir -p ${DEPLOY_PATH}/shared/storage/logs
mkdir -p ${DEPLOY_PATH}/shared/storage/ofgem_files

# Symlink Google Drive credentials into shared storage if exists (only if not already there)
if [ -f ${DEPLOY_PATH}/shared/google-drive-credentials.json ]; then
  if [ ! -L ${DEPLOY_PATH}/shared/storage/app/google-drive-credentials.json ]; then
    # Use relative path for symlink to avoid "is a directory" issues
    cd ${DEPLOY_PATH}/shared/storage/app && ln -sf ../../google-drive-credentials.json google-drive-credentials.json
  fi
fi

# Only set permissions on shared storage if we can (first deploy or if we own it)
# Skip permission errors - www-data will create files as needed
if [ ! -f ${DEPLOY_PATH}/shared/storage/.permissions_set ]; then
  sudo chown -R www-data:www-data ${DEPLOY_PATH}/shared/storage 2>/dev/null || true
  sudo chmod -R 775 ${DEPLOY_PATH}/shared/storage 2>/dev/null || true
  touch ${DEPLOY_PATH}/shared/storage/.permissions_set || true
fi

# Go to release directory
cd ${RELEASE_DIR}

# Remove release storage and symlink to shared storage
rm -rf ${RELEASE_DIR}/storage
ln -sf ${DEPLOY_PATH}/shared/storage ${RELEASE_DIR}/storage

# Symlink .env from shared if exists
if [ -f ${DEPLOY_PATH}/shared/.env ]; then
  ln -sf ${DEPLOY_PATH}/shared/.env ${RELEASE_DIR}/.env
fi

# Create bootstrap/cache if it doesn't exist
mkdir -p ${RELEASE_DIR}/bootstrap/cache

# Set permissions for bootstrap/cache (this is in the release, so ubuntu owns it)
chmod -R 775 ${RELEASE_DIR}/bootstrap/cache

# Optimize Laravel (composer already installed on CI)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan migrate --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Point current to new release
ln -sfn ${RELEASE_DIR} ${CURRENT}

# Fix permissions for bootstrap/cache in release (let www-data write to it)
sudo chown -R www-data:www-data ${RELEASE_DIR}/bootstrap/cache || true
sudo chmod -R 775 ${RELEASE_DIR}/bootstrap/cache || true

# Restart services
sudo systemctl reload php8.1-fpm || sudo systemctl restart php8.1-fpm || true
sudo systemctl reload nginx || sudo systemctl restart nginx || true

echo "Deploy complete: ${RELEASE}"


