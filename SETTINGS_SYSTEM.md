# Settings System Documentation

## Overview
The PPS ECO Rate and other ECO4 calculator settings are now configurable through a database-backed settings system. This allows you to update rates without modifying code or redeploying the application.

## Features
- ✅ Database-backed settings storage
- ✅ Cached for performance (1 hour cache)
- ✅ Type-safe (string, integer, float, boolean, json)
- ✅ Grouped by category (eco4, general, system, etc.)
- ✅ Command-line interface for quick updates
- ✅ API endpoints for programmatic access
- ✅ Admin UI ready (routes configured)

## Available Settings

### ECO4 Settings
| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `eco4_pps_eco_rate` | 21.0 | float | Rate to calculate ECO value from PPS points (£/£ ABS) |
| `eco4_innovation_multiplier` | 1.0 | float | Multiplier applied to innovative measures |

## How to Update Settings

### Method 1: Command Line (Recommended)

Update the PPS ECO Rate:
```bash
php artisan setting:update eco4_pps_eco_rate 21.5
```

Update the Innovation Multiplier:
```bash
php artisan setting:update eco4_innovation_multiplier 1.2
```

View current value:
```bash
php artisan tinker --execute="echo \App\Models\Setting::get('eco4_pps_eco_rate');"
```

### Method 2: Direct Database

```sql
UPDATE settings 
SET value = '21.5', updated_at = NOW() 
WHERE key = 'eco4_pps_eco_rate';
```

**Note**: After direct database updates, clear the cache:
```bash
php artisan cache:clear
```

### Method 3: Programmatically (PHP)

```php
use App\Models\Setting;

// Get a setting
$rate = Setting::get('eco4_pps_eco_rate', 21.0);

// Update a setting
Setting::set('eco4_pps_eco_rate', 21.5, 'float');

// Get all settings in a group
$eco4Settings = Setting::getByGroup('eco4');
```

### Method 4: API Endpoint

**Get all settings:**
```bash
GET /settings/api
```

**Update a setting** (requires `setting.manage` permission):
```bash
POST /settings/api/update
Content-Type: application/json

{
  "key": "eco4_pps_eco_rate",
  "value": "21.5"
}
```

## How It Works

### 1. Service Layer
The `Eco4CalculatorService` automatically reads settings on each calculation:

```php
// app/Services/Eco4CalculatorService.php
$defaultPpsEcoRate = Setting::get('eco4_pps_eco_rate', 21.0);
$ppsEcoRate = (float)($data['pps_eco_rate'] ?? $defaultPpsEcoRate);
```

### 2. Metadata API
The settings are included in the calculator metadata:

```javascript
// GET /eco4/metadata response
{
  "schemes": ["GBIS", "ECO4"],
  "sap_bands": [...],
  "settings": {
    "pps_eco_rate": 21.0,
    "innovation_multiplier": 1.0
  }
}
```

### 3. Caching
Settings are cached for 1 hour to improve performance. Cache is automatically cleared when a setting is updated.

## Migration Details

### Database Schema
```sql
CREATE TABLE settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key VARCHAR(255) UNIQUE NOT NULL,
  value TEXT,
  type VARCHAR(255) DEFAULT 'string',
  `group` VARCHAR(255) DEFAULT 'general',
  label VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Supported Types
- `string` - Text values
- `integer` - Whole numbers
- `float` - Decimal numbers (used for rates)
- `boolean` - True/false values
- `json` - Complex data structures

## Adding New Settings

### Via Migration
```php
DB::table('settings')->insert([
    'key' => 'eco4_new_setting',
    'value' => '100',
    'type' => 'integer',
    'group' => 'eco4',
    'label' => 'New Setting',
    'description' => 'Description of what this controls',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### Via Code
```php
Setting::set('eco4_new_setting', 100, 'integer');
```

## Admin UI (Future)

Routes are configured for admin UI at `/settings`:
- `GET /settings` - View all settings (requires `setting.manage` permission)
- `PUT /settings/{setting}` - Update a setting

To create the admin page, add `resources/js/Pages/Settings/Index.jsx`.

## Security

- Settings management requires `setting.manage` permission
- All settings are validated before saving
- Cache prevents excessive database queries
- Type casting ensures data integrity

## Testing

Test the settings system:
```bash
# Show current settings
php artisan tinker --execute="print_r(\App\Models\Setting::getByGroup('eco4'));"

# Update and verify
php artisan setting:update eco4_pps_eco_rate 22.0
php artisan test:loft
# Should show new rate in calculations

# Reset
php artisan setting:update eco4_pps_eco_rate 21.0
```

## Impact on Calculations

When you change the PPS ECO Rate:
- **New calculations** will use the new rate immediately
- **Saved calculations** retain the rate at the time they were calculated
- **Metadata API** returns the current rate

Example:
```
Old Rate (21.0): 85.5 ABS × 21.0 = £1,795.50
New Rate (22.0): 85.5 ABS × 22.0 = £1,881.00
```

## Files Changed

### New Files
- `database/migrations/2025_10_12_214058_create_settings_table.php`
- `app/Models/Setting.php`
- `app/Http/Controllers/SettingsController.php`
- `app/Console/Commands/UpdateSetting.php`

### Modified Files
- `app/Services/Eco4CalculatorService.php` - Now reads from settings
- `routes/web.php` - Added settings routes

## Best Practices

1. **Always use the command**: `php artisan setting:update` ensures proper cache clearing
2. **Document changes**: Keep track of when and why rates change
3. **Test after updates**: Run calculator tests after changing rates
4. **Use defaults**: Always provide fallback values in code

## Troubleshooting

**Settings not updating?**
```bash
php artisan cache:clear
php artisan config:clear
```

**Can't find setting?**
```bash
php artisan tinker --execute="\App\Models\Setting::all()->each(fn(\$s) => print \$s->key . PHP_EOL);"
```

**Permission denied?**
Ensure your user has the `setting.manage` permission in the database.

## Future Enhancements

- [ ] Settings history/audit log
- [ ] Settings validation rules
- [ ] Settings import/export
- [ ] UI for managing settings
- [ ] Role-based settings access
- [ ] Settings categories/tabs in UI

## Questions?

For help with the settings system, check:
1. This documentation
2. `app/Models/Setting.php` for all available methods
3. `php artisan help setting:update` for command usage

