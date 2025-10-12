# ECO4 Calculator - Loft Insulation Fix

## Issue Found
The Loft Insulation measure was incorrectly configured in the calculator. Initial implementation had:
- Wrong measure ID (`Loft_Insulation_0.16`)
- Incorrect scheme (ECO4 instead of GBIS)
- Missing measure type/variant selection

## Correct Implementation

### Database Structure
Loft Insulation in GBIS has **measure variants** instead of pre-main heating source:
- `LI_lessequal100` - For properties with ≤100mm existing insulation
- `LI_greater100` - For properties with >100mm existing insulation

### Frontend Changes
1. **Added Loft Measure Type State**
   ```jsx
   const [loftMeasureType, setLoftMeasureType] = useState('LI_lessequal100');
   ```

2. **Conditional Dropdown for Loft Insulation**
   - Only shows when Loft Insulation is selected
   - Allows user to select between the two variants
   - Updates the measure ID in `selectedMeasures` when changed

3. **Conditional Pre-main Heating Source**
   - Only shows for Smart Thermostat and TRV
   - Not required for Loft Insulation

4. **Updated Measure List**
   ```jsx
   { id: 'LI_lessequal100', label: 'Loft Insulation', scheme: 'GBIS', type: 'loft' },
   { id: 'Smarttherm', label: 'Smart Thermostat', scheme: 'GBIS', type: 'heating' },
   { id: 'TRV', label: 'TRV', scheme: 'GBIS', type: 'heating' },
   ```

5. **Smart Selection Logic**
   - When checking Loft, uses current `loftMeasureType`
   - When unchecking Loft, removes all `LI_*` measures
   - Checks for any `LI_*` measure when determining if selected

## How It Works Now

1. User selects "Loft Insulation" checkbox
2. A new dropdown appears: "Loft Insulation Measure Type"
3. User selects either:
   - `LI_lessequal100 (≤100mm)` - Default
   - `LI_greater100 (>100mm)`
4. The selected variant is used in the calculation
5. Pre-main heating source is only required if Smart Thermostat or TRV is selected

## Testing
To test Loft Insulation calculations:

```bash
php artisan tinker
```

```php
$service = app(\App\Services\Eco4CalculatorService::class);
$result = $service->calculate([
    'scheme' => 'GBIS',
    'starting_sap_band' => 'D',
    'floor_area_band' => '0–72',
    'measures' => [
        ['measure_type' => 'LI_lessequal100']
    ]
]);
print_r($result);
```

## Files Changed
- `resources/js/Components/Eco4CalculatorCard.jsx`
  - Added `loftMeasureType` state
  - Added conditional Loft Measure Type dropdown
  - Made Pre-main Heating Source conditional (only for heating measures)
  - Updated measure list with correct GBIS IDs
  - Updated selection/unselection logic for Loft variants

## Database Verification
Confirmed Loft Insulation exists in GBIS:
```sql
SELECT DISTINCT measure_type FROM gbis_partial_scores WHERE measure_type LIKE 'LI_%';
```
Result:
- `LI_greater100`
- `LI_lessequal100`

## Status
✅ **FIXED** - Loft Insulation now correctly uses GBIS scheme with measure type variants.

## Next Steps
- Test all three measures together
- Verify calculations match CoreLogic calculator
- Add validation to ensure required fields are filled before calculating

