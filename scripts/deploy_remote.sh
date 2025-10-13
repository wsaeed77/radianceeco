#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH=${1:?"DEPLOY_PATH required"}
RELEASE=${2:?"RELEASE SHA required"}

CURRENT=${DEPLOY_PATH}/current
RELEASE_DIR=${DEPLOY_PATH}/releases/${RELEASE}

cd ${RELEASE_DIR}

# Ensure shared storage directory exists with proper structure
mkdir -p ${DEPLOY_PATH}/shared/storage/app/public
mkdir -p ${DEPLOY_PATH}/shared/storage/framework/{cache,sessions,views}
mkdir -p ${DEPLOY_PATH}/shared/storage/logs

# Set permissions on shared storage (once)
chown -R www-data:www-data ${DEPLOY_PATH}/shared/storage || true
chmod -R 775 ${DEPLOY_PATH}/shared/storage || true

# Remove release storage and symlink to shared storage
rm -rf ${RELEASE_DIR}/storage
ln -sf ${DEPLOY_PATH}/shared/storage ${RELEASE_DIR}/storage

# Symlink .env from shared if exists
if [ -f ${DEPLOY_PATH}/shared/.env ]; then
  ln -sf ${DEPLOY_PATH}/shared/.env ${RELEASE_DIR}/.env
fi

# Symlink Google Drive credentials from shared if exists
if [ -f ${DEPLOY_PATH}/shared/google-drive-credentials.json ]; then
  ln -sf ${DEPLOY_PATH}/shared/google-drive-credentials.json ${RELEASE_DIR}/storage/app/google-drive-credentials.json
fi

# Set permissions for bootstrap/cache
chmod -R 775 bootstrap/cache || true

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

# Fix permissions for bootstrap/cache in release
chown -R www-data:www-data ${RELEASE_DIR}/bootstrap/cache || true

# Restart services
systemctl reload php8.1-fpm || systemctl restart php8.1-fpm || true
systemctl reload nginx || systemctl restart nginx || true

echo "Deploy complete: ${RELEASE}"


