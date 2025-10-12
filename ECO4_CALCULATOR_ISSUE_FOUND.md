# 🔍 ECO4 Calculator - Issue Identified!

## The Problem

Looking at your screenshot vs. the database, I found why Loft and Smart Thermostat show zero:

### ❌ **What's Wrong:**

1. **Loft Insulation** - Not in GBIS database!
   - GBIS has: CWI, EWI, HWI, FRI, Smarttherm, TRV
   - GBIS does NOT have Loft Insulation
   - **Loft is only in ECO4 scheme**

2. **"Smart_Thermostat"** - Wrong name!
   - Database has: `Smarttherm`
   - You're searching for: `Smart_Thermostat`
   - **Names don't match = Zero results**

3. **Missing Pre-main Heating Source**
   - Both `Smarttherm` and `TRV` REQUIRE `pre_main_heating_source`
   - Database has 600+ rows of TRV with different heating sources
   - Without specifying heating source = can't find correct row

### ✅ **Why TRV Works:**
TRV might be working because:
- The calculator might be getting `pre_main_heating_source` from somewhere (EPC data or form)
- Or it's randomly matching ONE of the 600+ TRV rows

---

## 📊 **Database Facts:**

### GBIS Measures Available:
```
- CWI (Cavity Wall Insulation)
- EWI (External Wall Insulation)  
- HWI (Hard Wall Insulation)
- FRI (Flat Roof Insulation)
- Smarttherm (Smart Thermostat)
- TRV (Thermostatic Radiator Valve)
```

### Smarttherm & TRV Need:
```
- Measure Type: Smarttherm or TRV
- Pre-main Heating Source: "Condensing Gas Boiler", "Non Condensing Gas Boiler", etc.
- Floor Area Band: 0-72, 73-97, 98-199, 200+
- Starting Band: High_D, Low_D, High_E, etc.
```

### Example TRV Row:
```
measure_type: TRV
pre_main_heating_source: Condensing Gas Boiler
floor_area_band: 0-72
starting_band: High_D
cost_savings: 17.40
```

When you calculate with PPS ECO Rate 21.5:
- PPS = 17.40 × 21.5 = 374.10
- This matches your screenshot! ✅

---

## 🔧 **How to Fix:**

### Fix 1: Change Scheme to ECO4 for Loft
Loft Insulation doesn't exist in GBIS, only in ECO4:

```javascript
// Option A: Use ECO4 instead of GBIS
scheme: 'ECO4'

// Option B: Remove Loft from GBIS, only show for ECO4
```

### Fix 2: Fix Smarttherm Name
```javascript
// Change from:
{ id: 'Smart_Thermostat', label: 'Smart Thermostat' }

// To:
{ id: 'Smarttherm', label: 'Smart Thermostat' }
```

### Fix 3: Add Pre-main Heating Source Dropdown
The calculator MUST ask for pre-main heating source for Smarttherm and TRV:

```javascript
<select name="pre_main_heat_source">
  <option>Condensing Gas Boiler</option>
  <option>Non Condensing Gas Boiler</option>
  <option>Condensing LPG Boiler</option>
  <option>Non Condensing LPG Boiler</option>
  <option>Condensing Oil Boiler</option>
  <option>Non Condensing Oil Boiler</option>
  <option>Electric Boiler</option>
  <option>Air to Water ASHP</option>
  <option>GSHP</option>
  <option>Solid Fossil Boiler</option>
  <option>DHS CHP</option>
  <option>DHS non-CHP</option>
  <option>Bottled LPG Boiler</option>
</select>
```

---

## 🎯 **Recommended Solution:**

### Option 1: Keep it Simple (GBIS only, no Loft)
```javascript
// Remove Loft Insulation from measure list
// Only offer:
- Smart Thermostat (Smarttherm)
- TRV

// Add one pre-main heating source dropdown for all measures
```

### Option 2: Switch to ECO4 (includes Loft)
```javascript
// Change scheme from GBIS to ECO4
scheme: 'ECO4'

// Keep all 3 measures:
- Loft Insulation
- Smart Thermostat  
- TRV
```

### Option 3: Mixed Approach
```javascript
// Let user choose scheme first
// If GBIS selected: Show only Smarttherm, TRV
// If ECO4 selected: Show all 3 including Loft
```

---

## 📝 **Next Steps:**

1. **Decide on scheme**: GBIS or ECO4?
2. **Fix measure names**: `Smarttherm` not `Smart_Thermostat`
3. **Add heating source dropdown**: Required for lookup
4. **Test calculation**: Should return correct values

---

## 💡 **Quick Test:**

To verify, try this manual test:

```php
php artisan tinker
```

```php
// Test Smarttherm lookup
$result = \App\Models\GbisPartialScore::where('measure_type', 'Smarttherm')
    ->where('pre_main_heating_source', 'Condensing Gas Boiler')
    ->where('floor_area_band', '0-72')
    ->where('starting_band', 'High_D')
    ->first();
    
echo "Cost Savings: " . $result->cost_savings . "\n";
echo "PPS (×21.5): " . ($result->cost_savings * 21.5) . "\n";
```

This should give you the correct PPS value!

---

**Which option would you like me to implement?**
1. Remove Loft, keep GBIS with Smarttherm + TRV
2. Switch to ECO4 scheme (all 3 measures)
3. Let user choose scheme dynamically

