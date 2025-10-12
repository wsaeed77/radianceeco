# Google Sheets Import Module - Complete Guide

## Overview
A comprehensive lead import system that allows importing leads directly from Google Sheets with an intuitive 4-step wizard interface.

## Features

### ✅ Multi-Step Import Wizard
1. **Select Sheet** - Browse and select from your Google Sheets
2. **Select Tab** - Choose which sheet tab to import from
3. **Map Columns** - Intelligent column mapping with auto-detection
4. **Import** - Review and import with options

### ✅ Smart Features
- **Auto-mapping**: Automatically suggests field mappings based on column names
- **Duplicate detection**: Skip or update duplicates based on email/phone
- **Preview data**: See sample rows before importing
- **Progress tracking**: Visual step-by-step progress indicator
- **Error handling**: Detailed error reporting for failed rows
- **Batch processing**: Handles large sheets efficiently

## How to Access

### Navigation
- Click **"Import"** in the left sidebar (download icon)
- Or navigate directly to: `http://radiance.local/import`

## Using the Import Wizard

### Step 1: Select a Google Sheet

1. The system automatically loads your accessible Google Sheets
2. Each sheet shows:
   - Sheet name
   - Owner
   - Last modified date
3. Click on any sheet to proceed

**What you'll see:**
- All spreadsheets you have access to in Google Drive
- Most recently modified sheets appear first
- Green document icon next to each sheet

### Step 2: Select a Sheet Tab

1. Choose which tab (sheet) contains your lead data
2. Each tab shows:
   - Tab name
   - Number of rows and columns
3. Click on the tab to preview

**Example:**
```
"Leads 2025"
324 rows × 8 columns
```

### Step 3: Map Columns & Configure

#### 3.1 Preview Data
- Shows first 3 rows of your data
- All column headers visible
- Verify data before mapping

#### 3.2 Column Mapping
For each column in your sheet, select which lead field it maps to:

**Available Lead Fields:**
- First Name *
- Last Name *
- Email
- Phone *
- Street Name
- House Number
- City
- Zip Code
- Status
- Source
- Notes

**Auto-mapping:**
The system automatically suggests mappings. For example:
- "First Name" column → First Name field
- "email" column → Email field
- "Phone Number" column → Phone field

**Manual mapping:**
- Select from dropdown for each column
- Choose "Skip this column" to ignore a column
- Required fields are marked with *

#### 3.3 Import Options
- ☑ **Skip duplicates** - Don't import if email/phone exists
- ☐ **Update existing** - Update lead data if found

### Step 4: Review Results

After import completes, you'll see:
- **Imported**: New leads created
- **Updated**: Existing leads updated
- **Skipped**: Duplicates skipped
- **Errors**: Any rows that failed (with reasons)

**Actions:**
- **Import More** - Start another import
- **View Leads** - Go to leads page

## Example Import Flow

### Sample Google Sheet:
```
| First Name | Last Name | Email           | Phone       | City   | Post Code |
|------------|-----------|-----------------|-------------|--------|-----------|
| John       | Smith     | john@email.com  | 07700900123 | London | SW1A 1AA  |
| Jane       | Doe       | jane@email.com  | 07700900456 | London | SW1A 2BB  |
```

### Mapping:
```
First Name   → first_name
Last Name    → last_name
Email        → email
Phone        → phone
City         → city
Post Code    → zip_code
```

### Result:
```
✓ 2 leads imported
✗ 0 errors
⊘ 0 skipped
```

## Technical Details

### API Endpoints

**List Google Sheets:**
```
POST /import/sheets/list
Response: {sheets: [...], next_page_token: "..."}
```

**Get Sheet Info:**
```
POST /import/sheets/info
Body: {spreadsheet_id: "abc123"}
Response: {info: {title: "...", sheets: [...]}}
```

**Preview Sheet:**
```
POST /import/sheets/preview
Body: {spreadsheet_id: "abc123", sheet_name: "Sheet1", limit: 10}
Response: {preview: {...}, lead_fields: [...]}
```

**Import Leads:**
```
POST /import/leads
Body: {
  spreadsheet_id: "abc123",
  sheet_name: "Sheet1",
  mapping: {
    "First Name": "first_name",
    "Email": "email",
    ...
  },
  skip_duplicates: true,
  update_existing: false
}
Response: {
  imported: 10,
  updated: 0,
  skipped: 2,
  errors: []
}
```

### Services

**GoogleSheetsService** (`app/Services/GoogleSheetsService.php`)
- `listSheets()` - List accessible spreadsheets
- `getSheetInfo()` - Get spreadsheet metadata
- `getSheetData()` - Fetch sheet data
- `previewSheet()` - Get preview with limit
- `getBatchData()` - Paginated data retrieval

**ImportController** (`app/Http/Controllers/ImportController.php`)
- Handles all import workflow steps
- Validates data and mappings
- Creates/updates leads
- Error handling and reporting

### Frontend

**Import Page** (`resources/js/Pages/Import/Index.jsx`)
- Multi-step wizard UI
- Real-time validation
- Progress indicators
- Error display
- Responsive design

## Field Mapping Rules

### Required Fields
These fields must be mapped:
- **First Name**
- **Last Name**
- **Phone**

### Optional Fields
- Email (recommended for duplicate detection)
- Street Name, House Number, City, Zip Code
- Status, Source, Notes

### Default Values
If not mapped:
- **Status**: "new"
- **Source**: "import"
- **Assigned To**: Current user

## Duplicate Detection

Duplicates are identified by:
1. **Email** (if provided and not empty)
2. **Phone** (if provided and not empty)

**Behavior:**
- If `skip_duplicates` is checked: Skip the row
- If `update_existing` is checked: Update the existing lead
- If neither: Skip the row

## Error Handling

### Common Errors

**"Failed to load Google Sheets"**
- **Cause**: Invalid/missing Google credentials
- **Solution**: Check `storage/app/google-drive-credentials.json`

**"Failed to preview sheet"**
- **Cause**: Invalid spreadsheet ID or permissions
- **Solution**: Verify you have access to the sheet

**Row import errors:**
- Missing required fields
- Invalid data format
- Database constraints

### Error Display
Errors are shown with:
- Row number (e.g., "Row 5:")
- Specific error message
- Scrollable list if many errors

## Best Practices

### Before Importing

1. **Clean your data** in Google Sheets:
   - Remove empty rows
   - Ensure consistent formatting
   - Validate email formats
   - Check phone number formats

2. **Use clear column headers**:
   - "First Name" instead of "FName"
   - "Email Address" instead of "Mail"
   - This helps auto-mapping

3. **Test with a small sample**:
   - Import 5-10 rows first
   - Verify mapping is correct
   - Then import the full sheet

### During Import

1. **Review the preview carefully**
2. **Verify all required fields are mapped**
3. **Choose appropriate duplicate handling**
4. **Don't close the browser during import**

### After Import

1. **Review the results summary**
2. **Check error messages if any**
3. **Verify leads in the system**
4. **Fix errors and re-import if needed**

## Permissions

The import feature is available to all authenticated users.

**No special permissions required** to:
- View import page
- List Google Sheets
- Import leads

## Setup Requirements

### Google Drive API Setup

1. **Credentials File**:
   ```
   storage/app/google-drive-credentials.json
   ```

2. **Required Scopes**:
   - `Google\Service\Sheets::SPREADSHEETS_READONLY`
   - `Google\Service\Drive::DRIVE_READONLY`

3. **Service Account** (recommended):
   - Create in Google Cloud Console
   - Download JSON credentials
   - Place in storage/app/

### Testing

Test the import with:
```bash
# Test Google Sheets connection
php artisan tinker
```

```php
$service = app(\App\Services\GoogleSheetsService::class);
$sheets = $service->listSheets();
print_r($sheets);
```

## Troubleshooting

### Problem: No sheets appear
**Solution:**
1. Check Google credentials are valid
2. Verify scopes include Drive and Sheets
3. Ensure service account has access to sheets

### Problem: Column mapping doesn't save
**Solution:**
1. Select a value for each column (or "Skip")
2. Ensure at least required fields are mapped
3. Check browser console for errors

### Problem: Import hangs/freezes
**Solution:**
1. Check for very large sheets (>10,000 rows)
2. Try importing in smaller batches
3. Check server logs for errors

### Problem: Duplicates not detected
**Solution:**
1. Ensure Email or Phone is mapped
2. Verify data format matches existing leads
3. Check if skip_duplicates option is enabled

## Files Created/Modified

### New Files:
- `app/Services/GoogleSheetsService.php` - Google Sheets API integration
- `app/Http/Controllers/ImportController.php` - Import workflow controller
- `resources/js/Pages/Import/Index.jsx` - Import wizard UI

### Modified Files:
- `routes/web.php` - Added import routes
- `resources/js/Layouts/AppLayout.jsx` - Added Import menu item

## Future Enhancements

Potential improvements:
- [ ] Schedule automated imports
- [ ] Import history/audit log
- [ ] Custom field mapping templates
- [ ] Excel file upload (in addition to Google Sheets)
- [ ] CSV file import
- [ ] Data validation rules
- [ ] Import preview before commit
- [ ] Rollback functionality
- [ ] Bulk edit after import

## Status: ✅ COMPLETE

The Google Sheets import module is fully functional and ready to use!

**Quick Start:**
1. Click "Import" in sidebar
2. Select your Google Sheet
3. Choose the tab
4. Map columns
5. Import!

