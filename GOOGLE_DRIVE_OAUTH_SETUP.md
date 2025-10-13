# Google Drive OAuth Setup Guide

## Overview
This guide will help you set up OAuth authentication for Google Drive, allowing file uploads to regular Google Drive folders (no Workspace needed).

## Step 1: Create OAuth Credentials in Google Cloud Console

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Select your project: `radiance-portal` (or create a new one)

2. **Enable Google Drive API**
   - Go to "APIs & Services" → "Library"
   - Search for "Google Drive API"
   - Click "Enable"

3. **Create OAuth 2.0 Credentials**
   - Go to "APIs & Services" → "Credentials"
   - Click "+ CREATE CREDENTIALS" → "OAuth client ID"
   - If prompted, configure OAuth consent screen first:
     - User Type: **External**
     - App name: `Radiance Lead Management`
     - User support email: Your email
     - Developer contact: Your email
     - Add scope: `https://www.googleapis.com/auth/drive.file`
     - Save and continue

4. **Configure OAuth Client**
   - Application type: **Web application**
   - Name: `Radiance OAuth Client`
   - Authorized redirect URIs:
     - `http://localhost:8000/google/callback` (for local dev)
     - `https://leads.radianceeco.co.uk/google/callback` (for production)
   - Click "Create"

5. **Download Credentials**
   - Click the download icon (⬇️) next to your OAuth client
   - Save as `google-oauth-credentials.json`
   - Copy to `storage/app/google-oauth-credentials.json`

## Step 2: First-Time OAuth Authorization

Run this command to authorize your Google account:

```bash
php artisan google:authorize
```

This will:
1. Generate an authorization URL
2. Open it in your browser
3. Ask you to sign in with your Google account
4. Request permission to access Google Drive
5. Save the refresh token for future use

## Step 3: Update .env

```env
# Google Drive OAuth Configuration
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_AUTH_TYPE=oauth
GOOGLE_DRIVE_ROOT_FOLDER_ID=your-folder-id-here
```

## Step 4: Test Upload

```bash
php artisan test:google-drive --upload-test
```

## How It Works

### OAuth vs Service Account

**Service Account (Old - Doesn't Work)**
- ❌ Can't upload to regular folders
- ❌ Needs Google Workspace
- ✓ No user interaction needed

**OAuth (New - Works!)**
- ✓ Works with regular Google Drive
- ✓ No Workspace needed
- ✓ Uses YOUR Google Drive storage
- ⚠️ Requires one-time authorization

### File Storage

With OAuth, files will be stored in:
```
Your Google Drive/
└── Radiance Leads/ (root folder)
    ├── Lead_123_John_Doe/
    │   ├── Benefit Proof/
    │   │   └── document1.pdf
    │   └── EPC/
    │       └── epc-cert.pdf
    └── Lead_456_Jane_Smith/
        └── Floor Plan/
            └── floor-plan.jpg
```

## Troubleshooting

### "Invalid Grant" Error
- Re-run: `php artisan google:authorize`
- Your refresh token expired

### "Redirect URI Mismatch"
- Make sure the redirect URI in Google Console matches exactly
- Check HTTP vs HTTPS
- Check trailing slashes

### "Access Denied"
- Make sure you granted Drive permissions during OAuth
- Re-authorize with: `php artisan google:authorize --force`

## Security Notes

- OAuth tokens are stored in `storage/app/google-oauth-token.json`
- This file should NOT be committed to Git (already in .gitignore)
- For production, upload this file to EC2 in `/var/www/radiance/shared/`
- The deploy script will symlink it automatically

## Migration from Service Account

If you were using service account before:

1. The old credentials file will be ignored
2. OAuth will take over automatically
3. Existing local files are unaffected
4. New uploads will go to your Google Drive

## Next Steps

After setup:
1. Upload a test document through the UI
2. Check your Google Drive for the file
3. Share the root folder with team members if needed

