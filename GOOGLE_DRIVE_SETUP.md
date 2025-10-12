# Google Drive Integration Setup Guide

This guide will help you set up automatic Google Drive uploads for all documents uploaded in the system.

## 📁 Folder Structure

The system will automatically create the following folder structure in Google Drive:

```
Root Folder (optional)
└── Lead_{ID}_{FirstName}_{LastName}/
    ├── Survey Pics/
    │   └── uploaded-documents.pdf
    ├── Floor Plan/
    │   └── floor-plan.jpg
    ├── Benefit Proof/
    │   └── benefit-document.pdf
    ├── Gas Meter/
    ├── EPR Report/
    ├── EPC/
    └── Other/
```

## 🚀 Setup Steps

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Name it something like "Radiance Document Management"

### Step 2: Enable Google Drive API

1. In your Google Cloud project, go to **APIs & Services** > **Library**
2. Search for "Google Drive API"
3. Click on it and press **Enable**

### Step 3: Create Service Account

1. Go to **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **Service Account**
3. Fill in the details:
   - **Service account name**: `radiance-drive-uploader`
   - **Service account ID**: (auto-generated)
   - **Description**: `Service account for uploading documents to Google Drive`
4. Click **Create and Continue**
5. Grant the service account these roles:
   - **Basic** > **Editor** (or)
   - **Cloud Storage** > **Storage Object Admin**
6. Click **Continue** > **Done**

### Step 4: Create and Download Credentials

1. In the **Service Accounts** list, click on the service account you just created
2. Go to the **Keys** tab
3. Click **Add Key** > **Create new key**
4. Choose **JSON** format
5. Click **Create**
6. A JSON file will be downloaded - **KEEP THIS SECURE!**

### Step 5: Install Credentials File

1. Rename the downloaded JSON file to: `google-drive-credentials.json`
2. Place it in: `storage/app/google-drive-credentials.json`
3. **Important**: This file contains sensitive credentials. Never commit it to version control!

### Step 6: Create Root Folder in Google Drive (Optional but Recommended)

1. Go to your [Google Drive](https://drive.google.com)
2. Create a folder named "Radiance Documents" (or any name you prefer)
3. Right-click the folder > **Share**
4. Add the service account email (found in the JSON file as `client_email`)
5. Give it **Editor** permission
6. Click **Share**
7. Get the Folder ID:
   - Open the folder in Google Drive
   - Look at the URL: `https://drive.google.com/drive/folders/FOLDER_ID_HERE`
   - Copy the `FOLDER_ID_HERE` part

### Step 7: Configure Environment Variables

Add these lines to your `.env` file:

```env
# Google Drive Integration
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_ROOT_FOLDER_ID=your_folder_id_from_step_6
```

**Example:**
```env
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_ROOT_FOLDER_ID=1a2b3c4d5e6f7g8h9i0j
```

**Note:** If you skip Step 6, leave `GOOGLE_DRIVE_ROOT_FOLDER_ID` empty, and files will be created in the service account's root drive.

### Step 8: Run Database Migration

Run the migration to add Google Drive fields to the documents table:

```bash
php artisan migrate
```

### Step 9: Test the Integration

1. Go to any lead in your system
2. Upload a document
3. Check your Google Drive - you should see:
   - A folder for the lead (e.g., `Lead_123_John_Doe`)
   - Inside that, a folder for the document type (e.g., `Survey Pics`)
   - The uploaded document inside that folder

## 🔍 Verification

To verify everything is working:

1. **Check Laravel Logs**: `storage/logs/laravel.log`
   - Look for: "Document uploaded to Google Drive"
   - Or errors: "Failed to upload to Google Drive"

2. **Check Database**: Look at the `documents` table
   - Should have `google_drive_file_id` populated
   - Should have `google_drive_web_view_link` populated

3. **Check Google Drive**: 
   - Folders should be created automatically
   - Documents should appear in the correct folders

## 🛠️ Troubleshooting

### Error: "Client secret not found"
- Make sure `storage/app/google-drive-credentials.json` exists
- Check file permissions (should be readable)

### Error: "Insufficient Permission"
- Make sure you shared the root folder with the service account
- Check that the service account has Editor permissions

### Documents upload locally but not to Google Drive
- Check `.env` file: `GOOGLE_DRIVE_ENABLED=true`
- Check Laravel logs for specific errors
- Verify credentials file is in the correct location

### Error: "The file specified does not exist"
- Check `storage/app/google-drive-credentials.json` exists
- Verify the file path in `GoogleDriveService.php`

## 📊 Features

### Automatic Folder Creation
- Lead folders are created automatically on first document upload
- Document type subfolders are created automatically
- Existing folders are reused (no duplicates)

### Folder Naming Convention
- Lead Folder: `Lead_{ID}_{FirstName}_{LastName}`
- Document Type Folders: `Survey Pics`, `Floor Plan`, `Benefit Proof`, etc.

### Document Management
- Documents are uploaded to both local storage and Google Drive
- If Google Drive upload fails, local upload still succeeds
- Deleting a document removes it from both locations
- Google Drive links are stored in the database

### Error Handling
- Graceful fallback if Google Drive is unavailable
- All errors are logged for debugging
- System continues working even if Google Drive fails

## 🔐 Security Notes

1. **Never commit** `google-drive-credentials.json` to version control
2. Add to `.gitignore`:
   ```
   storage/app/google-drive-credentials.json
   ```
3. Keep service account permissions minimal
4. Regularly rotate service account keys
5. Monitor Google Cloud Console for unusual activity

## 🔄 Disabling Google Drive Integration

To temporarily disable Google Drive uploads:

1. Set in `.env`:
   ```env
   GOOGLE_DRIVE_ENABLED=false
   ```

2. Documents will only be stored locally

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify all environment variables are set correctly
3. Ensure service account has proper permissions
4. Test with a small file first

## ✅ Quick Checklist

- [ ] Google Cloud Project created
- [ ] Google Drive API enabled
- [ ] Service Account created
- [ ] Credentials JSON file downloaded
- [ ] Credentials file placed in `storage/app/google-drive-credentials.json`
- [ ] Root folder created in Google Drive (optional)
- [ ] Root folder shared with service account email
- [ ] Folder ID copied
- [ ] Environment variables added to `.env`
- [ ] Migration run (`php artisan migrate`)
- [ ] Test document upload
- [ ] Verify document appears in Google Drive

---

**All done!** Your documents will now automatically sync to Google Drive! 🎉

