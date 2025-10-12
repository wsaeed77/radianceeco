# EPC API Key Format Guide

## Important: API Key Format

The UK Government EPC API key should be in one of these formats:

### Format 1: Email Format (Most Common)
```
your-email@example.com:api-key-string
```

Example:
```
john.doe@example.com:abc123def456ghi789
```

### Format 2: Plain API Key
```
your-api-key-string-here
```

## How to Find Your API Key

1. Login to: https://epc.opendatacommunities.org/
2. Click on your username (top right)
3. Navigate to "API Access" or "Account Settings"
4. Copy the **entire API key** exactly as shown

## Adding to .env File

Open your `.env` file and add:

```env
EPC_API_KEY=your-email@example.com:your-api-key-here
```

**Important:** 
- Do NOT add quotes around the key
- Copy the ENTIRE key including the colon if present
- Make sure there are no spaces

### Example .env Entry
```env
EPC_API_KEY=john.doe@example.com:abc123def456
```

## Testing Your API Key

You can test your API key directly with this curl command:

```bash
curl -u "your-api-key-here:" \
  "https://epc.opendatacommunities.org/api/v1/domestic/search?postcode=SW1A1AA"
```

Or with PowerShell:
```powershell
$headers = @{
    "Accept" = "application/json"
    "Authorization" = "Basic " + [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("your-api-key-here:"))
}
Invoke-RestMethod -Uri "https://epc.opendatacommunities.org/api/v1/domestic/search?postcode=SW1A1AA" -Headers $headers
```

## Common Issues

### Issue 1: Still Getting 401 Error
**Solution:** Make sure you copied the entire API key including any colons

### Issue 2: API Key with Spaces
**Solution:** Remove any spaces from the API key in .env

### Issue 3: Quotes Around Key
**Solution:** In .env, do NOT use quotes:
```env
# ❌ Wrong
EPC_API_KEY="my-key-here"

# ✅ Correct
EPC_API_KEY=my-key-here
```

## After Adding Key

Always run:
```bash
php artisan config:clear
```

This ensures Laravel loads the new configuration.

---

**Need Help?** Check the logs at `storage/logs/laravel.log` for detailed error messages.

