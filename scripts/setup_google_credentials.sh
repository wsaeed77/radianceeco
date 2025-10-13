#!/usr/bin/env bash
# One-time setup script to upload and configure Google Drive credentials on EC2
# Run this script LOCALLY, not on the server

set -e

echo "🔐 Google Drive Credentials Setup for EC2"
echo "=========================================="
echo ""

# Configuration
SSH_USER="${SSH_USER:-ubuntu}"
SSH_HOST="${SSH_HOST:-leads.radianceeco.co.uk}"
SSH_KEY="${SSH_KEY:-}"
CREDENTIALS_FILE="google-drive-credentials.json"
DEPLOY_PATH="/var/www/radiance"

# Check if credentials file exists locally
if [ ! -f "$CREDENTIALS_FILE" ]; then
    echo "❌ Error: $CREDENTIALS_FILE not found in current directory"
    echo ""
    echo "Please ensure you have the Google Drive credentials JSON file"
    echo "in the current directory before running this script."
    exit 1
fi

# Validate JSON
if ! command -v jq &> /dev/null; then
    echo "⚠️  Warning: jq not installed. Skipping JSON validation."
else
    if ! jq empty "$CREDENTIALS_FILE" 2>/dev/null; then
        echo "❌ Error: $CREDENTIALS_FILE is not valid JSON"
        exit 1
    fi
    echo "✓ Credentials file is valid JSON"
fi

# Prompt for SSH key if not provided
if [ -z "$SSH_KEY" ]; then
    echo ""
    read -p "Enter path to SSH private key (or press Enter for default ~/.ssh/id_rsa): " SSH_KEY
    SSH_KEY="${SSH_KEY:-$HOME/.ssh/id_rsa}"
fi

# Check if SSH key exists
if [ ! -f "$SSH_KEY" ]; then
    echo "❌ Error: SSH key not found at $SSH_KEY"
    exit 1
fi

echo ""
echo "📤 Uploading credentials to EC2..."
echo "   File: $CREDENTIALS_FILE"
echo "   Server: $SSH_USER@$SSH_HOST"
echo "   SSH Key: $SSH_KEY"
echo ""

# Upload to /tmp on server
if ! scp -i "$SSH_KEY" "$CREDENTIALS_FILE" "$SSH_USER@$SSH_HOST:/tmp/google-drive-credentials.json"; then
    echo "❌ Error: Failed to upload file to server"
    exit 1
fi

echo "✓ File uploaded to /tmp on server"

# Move to shared directory and set permissions via SSH
echo ""
echo "🔧 Configuring credentials on server..."

ssh -i "$SSH_KEY" "$SSH_USER@$SSH_HOST" << 'ENDSSH'
set -e

DEPLOY_PATH="/var/www/radiance"

# Ensure shared directory exists
sudo mkdir -p ${DEPLOY_PATH}/shared

# Move file to shared directory
sudo mv /tmp/google-drive-credentials.json ${DEPLOY_PATH}/shared/google-drive-credentials.json

# Set ownership to www-data
sudo chown www-data:www-data ${DEPLOY_PATH}/shared/google-drive-credentials.json

# Set secure permissions (read/write for owner, read for group, none for others)
sudo chmod 640 ${DEPLOY_PATH}/shared/google-drive-credentials.json

# Verify file
echo ""
echo "✓ Credentials file installed:"
ls -lh ${DEPLOY_PATH}/shared/google-drive-credentials.json

# Create symlink in shared storage (so it's available via storage symlink)
echo ""
echo "Creating symlink in shared storage..."
sudo mkdir -p ${DEPLOY_PATH}/shared/storage/app
# Use relative path to avoid symlink issues
cd ${DEPLOY_PATH}/shared/storage/app
sudo ln -sf ../../google-drive-credentials.json google-drive-credentials.json
echo "✓ Symlink created in shared storage"

# If current release exists, verify it's accessible
if [ -d "${DEPLOY_PATH}/current/storage/app" ]; then
    echo ""
    echo "Verifying accessibility from current release..."
    if [ -L "${DEPLOY_PATH}/current/storage/app/google-drive-credentials.json" ]; then
        echo "✓ Credentials accessible from current release"
    else
        echo "⚠️  Note: Deploy to make credentials available in current release"
    fi
fi

echo ""
echo "Verifying setup..."
if sudo -u www-data test -r ${DEPLOY_PATH}/shared/google-drive-credentials.json; then
    echo "✓ File is readable by www-data user"
else
    echo "⚠️  Warning: File may not be readable by www-data"
fi

ENDSSH

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Setup Complete!"
    echo ""
    echo "The Google Drive credentials file has been successfully installed on EC2."
    echo "It is now stored in: $DEPLOY_PATH/shared/google-drive-credentials.json"
    echo ""
    echo "Future deployments will automatically symlink this file into each release."
    echo "You can now deploy your application normally."
    echo ""
    echo "Next steps:"
    echo "  1. git add ."
    echo "  2. git commit -m 'Update deployment with Google credentials support'"
    echo "  3. git push origin main"
    echo ""
else
    echo ""
    echo "❌ Setup failed. Please check the errors above."
    exit 1
fi

