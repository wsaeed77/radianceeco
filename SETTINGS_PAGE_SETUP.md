# Settings Page Setup - Complete! ✅

## What Was Done

### 1. **Created Settings Permission**
- Added `setting.view` and `setting.manage` permissions
- Granted both permissions to the `admin` role
- Seeded via: `SettingsPermissionSeeder`

### 2. **Created Settings Page**
- Built React/Inertia page at `resources/js/Pages/Settings/Index.jsx`
- Clean, professional UI matching the rest of the application
- Real-time updates with visual feedback
- Shows all ECO4 calculator settings

### 3. **Updated Navigation**
- Settings link now points to actual page (was `#`)
- Available in sidebar under Settings icon
- Only visible to users with appropriate permissions

### 4. **Routes Configured**
```
GET  /settings                   → Settings page (requires setting.manage)
PUT  /settings/{setting}         → Update a setting (requires setting.manage)
GET  /settings/api              → Get all settings (requires auth)
POST /settings/api/update       → API update (requires setting.manage)
```

## How to Access

### As Admin User:
1. **Navigate to Settings:**
   - Click "Settings" in the left sidebar
   - Or go directly to: `http://radiance.local/settings`

2. **Update Settings:**
   - Change the value in the input field
   - Click "Update" button
   - See success message
   - Changes take effect immediately

### Current Settings:
| Setting | Default | Description |
|---------|---------|-------------|
| **PPS ECO Rate** | 21.0 | Rate to calculate ECO value from PPS points |
| **Innovation Multiplier** | 1.0 | Multiplier applied to innovative measures |

## Features

✅ **Real-time Updates** - Changes save and apply immediately  
✅ **Visual Feedback** - Success messages and loading states  
✅ **Type-safe Inputs** - Proper number inputs with step values  
✅ **Permission-based** - Only admins can access/modify  
✅ **Responsive Design** - Works on mobile and desktop  
✅ **Help Text** - Clear descriptions for each setting  
✅ **Cache Clearing** - Automatic cache invalidation on update  

## Permissions

**Who can access:**
- Users with `setting.manage` permission (admins by default)

**To grant permission to other roles:**
```bash
php artisan tinker
```
```php
$role = Spatie\Permission\Models\Role::where('name', 'manager')->first();
$role->givePermissionTo('setting.manage');
```

## Alternative Update Methods

### Command Line:
```bash
php artisan setting:update eco4_pps_eco_rate 22.0
```

### Programmatically:
```php
use App\Models\Setting;
Setting::set('eco4_pps_eco_rate', 22.0, 'float');
```

### API:
```bash
POST /settings/api/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "key": "eco4_pps_eco_rate",
  "value": "22.0"
}
```

## Troubleshooting

### "Settings page doesn't open"
**Solution:** Ensure you're logged in as admin user

### "403 Forbidden"
**Solution:** Run permission seeder:
```bash
php artisan db:seed --class=SettingsPermissionSeeder
```

### "Settings not updating"
**Solution:** Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
```

### "Route not found"
**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan route:cache
```

## Testing

1. **Navigate to Settings:**
   ```
   http://radiance.local/settings
   ```

2. **Update PPS ECO Rate:**
   - Change value from 21.0 to 22.0
   - Click "Update"
   - Verify success message

3. **Test Calculator:**
   - Go to a lead with EPC data
   - Open ECO4 calculator
   - Calculate measures
   - Verify new rate is used

4. **Verify via CLI:**
   ```bash
   php artisan tinker --execute="echo Setting::get('eco4_pps_eco_rate');"
   # Should output: 22
   ```

## Files Created/Modified

### New Files:
- `database/migrations/2025_10_12_214058_create_settings_table.php`
- `app/Models/Setting.php`
- `app/Http/Controllers/SettingsController.php`
- `app/Console/Commands/UpdateSetting.php`
- `database/seeders/SettingsPermissionSeeder.php`
- `resources/js/Pages/Settings/Index.jsx`

### Modified Files:
- `app/Services/Eco4CalculatorService.php` - Reads from settings
- `routes/web.php` - Added settings routes
- `resources/js/Layouts/AppLayout.jsx` - Updated settings link

## UI Preview

```
╔═══════════════════════════════════════════════════════╗
║  Settings                                              ║
║  Configure system settings and calculator parameters   ║
╠═══════════════════════════════════════════════════════╣
║                                                         ║
║  ECO4 Calculator Settings                              ║
║  ┌─────────────────────────────────────────────────┐  ║
║  │ PPS ECO Rate                           │ 21.0  │  ║
║  │ Rate to calculate ECO value...         │[Update]│  ║
║  ├─────────────────────────────────────────────────┤  ║
║  │ Innovation Multiplier                  │ 1.0   │  ║
║  │ Multiplier applied to innovative...    │[Update]│  ║
║  └─────────────────────────────────────────────────┘  ║
║                                                         ║
║  📝 Note                                               ║
║  • Changes take effect immediately                     ║
║  • Settings are cached for performance                 ║
║  • CLI: php artisan setting:update [key] [value]       ║
╚═══════════════════════════════════════════════════════╝
```

## Status: ✅ COMPLETE

The settings page is now fully functional and accessible to admin users!

