#!/usr/bin/env bash
# Quick fix script to create the Google credentials symlink in shared storage
# Run this ON THE EC2 SERVER to fix the current deployment

set -e

DEPLOY_PATH="/var/www/radiance"

echo "🔧 Fixing Google Drive credentials symlink..."
echo ""

# Check if the credentials file exists in shared
if [ ! -f "${DEPLOY_PATH}/shared/google-drive-credentials.json" ]; then
    echo "❌ Error: ${DEPLOY_PATH}/shared/google-drive-credentials.json not found"
    echo ""
    echo "Please upload the credentials file first:"
    echo "  scp -i <key> google-drive-credentials.json ubuntu@server:/tmp/"
    echo "  sudo mv /tmp/google-drive-credentials.json ${DEPLOY_PATH}/shared/"
    echo "  sudo chown www-data:www-data ${DEPLOY_PATH}/shared/google-drive-credentials.json"
    echo "  sudo chmod 640 ${DEPLOY_PATH}/shared/google-drive-credentials.json"
    exit 1
fi

echo "✓ Found credentials in shared directory"

# Create the app directory in shared storage if it doesn't exist
echo ""
echo "Creating app directory in shared storage..."
sudo mkdir -p ${DEPLOY_PATH}/shared/storage/app

# Remove old symlink if exists and create new one in shared storage
echo "Creating symlink in shared storage..."
sudo rm -f ${DEPLOY_PATH}/shared/storage/app/google-drive-credentials.json

# Use relative path to avoid "is a directory" issues
cd ${DEPLOY_PATH}/shared/storage/app
sudo ln -sf ../../google-drive-credentials.json google-drive-credentials.json

echo "✓ Symlink created"

# Set proper permissions
echo ""
echo "Setting permissions..."
sudo chown -h www-data:www-data ${DEPLOY_PATH}/shared/storage/app/google-drive-credentials.json
sudo chmod 640 ${DEPLOY_PATH}/shared/google-drive-credentials.json

# Verify it works
echo ""
echo "Verifying setup..."

# Check symlink exists
if [ -L "${DEPLOY_PATH}/shared/storage/app/google-drive-credentials.json" ]; then
    echo "✓ Symlink exists in shared storage"
else
    echo "❌ Symlink creation failed"
    exit 1
fi

# Check it's accessible from current release (if storage is symlinked)
if [ -d "${DEPLOY_PATH}/current" ]; then
    if [ -L "${DEPLOY_PATH}/current/storage/app/google-drive-credentials.json" ]; then
        echo "✓ Credentials accessible from current release"
        
        # Test readability as www-data
        if sudo -u www-data test -r ${DEPLOY_PATH}/current/storage/app/google-drive-credentials.json; then
            echo "✓ File is readable by www-data"
        else
            echo "⚠️  Warning: File may not be readable by www-data"
        fi
    else
        echo "⚠️  Warning: Not accessible from current release"
        echo "   This is normal if storage isn't symlinked yet"
    fi
fi

echo ""
echo "📁 File structure:"
echo "   Source: ${DEPLOY_PATH}/shared/google-drive-credentials.json"
echo "   Symlink: ${DEPLOY_PATH}/shared/storage/app/google-drive-credentials.json"
echo "   Release sees: current/storage/app/google-drive-credentials.json"

echo ""
echo "✅ Fix applied successfully!"
echo ""
echo "The credentials file should now be accessible by your application."
echo "If you still have issues, restart PHP-FPM:"
echo "  sudo systemctl restart php8.1-fpm"

