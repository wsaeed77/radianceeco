# ECO4 Calculator - Implementation Status

## ✅ COMPLETED (Backend - 95%)

### 1. Database Layer
- ✅ **5 Database Tables Created**
  - `eco4_partial_scores` (16,564 rows)
  - `eco4_full_scores` (304 rows)
  - `gbis_partial_scores` (4,936 rows)
  - `eco4_calculations` (stores saved calculations)
  - `eco4_measures` (stores selected measures)

### 2. Data Import
- ✅ **21,804 Ofgem Scores Imported**
- ✅ Import Command: `php artisan ofgem:import --fresh`
- ✅ CSV Converter: `php artisan ofgem:convert`

### 3. Models
- ✅ `Eco4PartialScore` - ECO4 partial project scores
- ✅ `Eco4FullScore` - ECO4 full project scores
- ✅ `GbisPartialScore` - GBIS scores
- ✅ `Eco4Calculation` - Saved calculations
- ✅ `Eco4Measure` - Individual measures
- ✅ Relationships added to `Lead` model

### 4. Calculator Service
- ✅ `Eco4CalculatorService` - Complete calculation logic
  - `calculate()` - Main calculation method
  - `calculatePartial()` - Partial project calculations
  - `calculateFullProject()` - Full project calculations
  - `calculateMeasure()` - Individual measure calculation
  - `saveCalculation()` - Save to database
  - `getAvailableMeasures()` - Get measure lists
  - `getMetadata()` - Get dropdown data

### 5. API Endpoints
- ✅ `GET /api/eco4/metadata` - Get dropdowns and options
- ✅ `POST /api/eco4/calculate` - Calculate scores
- ✅ `POST /api/eco4/leads/{lead}/save` - Save calculation
- ✅ `GET /api/eco4/leads/{lead}` - Get lead calculations
- ✅ `DELETE /api/eco4/calculations/{calculation}` - Delete calculation

---

## 🚧 TO DO (Frontend - React Components)

### Required React Components

#### 1. **Main Calculator Page** (`resources/js/Pages/Eco4Calculator.jsx`)
```jsx
// Full-page calculator similar to CoreLogic
// - Scheme selection (GBIS/ECO4)
// - SAP band input
// - Floor area selection
// - Measure selection
// - Results display
```

#### 2. **Calculator Card** (`resources/js/Components/Eco4CalculatorCard.jsx`)
```jsx
// Embeddable calculator card for Lead Show page
// - Compact version
// - Shows on lead detail page
// - Uses EPC data automatically
```

#### 3. **Measure Selection Modal** (`resources/js/Components/Eco4MeasureSelector.jsx`)
```jsx
// Modal to select measures
// - Checkboxes for measures
// - Grouped by category
// - Search/filter
```

#### 4. **Measure Card** (`resources/js/Components/Eco4MeasureCard.jsx`)
```jsx
// Individual measure display (like CoreLogic cards)
// - Measure icon
// - PPS points display
// - ECO Value display
// - Percentage treated input
// - Innovation checkbox
```

#### 5. **Calculation Summary** (`resources/js/Components/Eco4CalculationSummary.jsx`)
```jsx
// Summary of all measures
// - Total ABS
// - Total ECO Value
// - Measure count
// - Save/Export buttons
```

#### 6. **Saved Calculations List** (`resources/js/Components/Eco4CalculationsList.jsx`)
```jsx
// Show saved calculations for a lead
// - List view
// - View/Delete actions
// - Date saved
```

---

## 🎯 Quick Start - Adding Calculator to Lead Page

### Step 1: Test API Endpoints

```bash
# Get metadata
curl http://radiance.local/api/eco4/metadata

# Test calculation
curl -X POST http://radiance.local/api/eco4/calculate \
  -H "Content-Type: application/json" \
  -d '{
    "scheme": "GBIS",
    "starting_sap_band": "High_D",
    "floor_area_band": "0-72",
    "measures": [
      {"type": "CWI_0.040", "percentage_treated": 100}
    ]
  }'
```

### Step 2: Add Calculator Tab to Lead Show Page

Edit `resources/js/Pages/Leads/Show.jsx`:

```jsx
// Add new tab for ECO4 Calculator
<Tab label="ECO4 Calculator">
  <Eco4CalculatorCard lead={lead} />
</Tab>
```

### Step 3: Create Basic Calculator Component

```jsx
// resources/js/Components/Eco4CalculatorCard.jsx
import { useState, useEffect } from 'react';
import axios from 'axios';

export default function Eco4CalculatorCard({ lead }) {
    const [metadata, setMetadata] = useState(null);
    const [result, setResult] = useState(null);
    
    useEffect(() => {
        // Load metadata
        axios.get('/api/eco4/metadata').then(res => {
            setMetadata(res.data);
        });
    }, []);
    
    const calculate = () => {
        axios.post('/api/eco4/calculate', {
            scheme: 'GBIS',
            starting_sap_band: lead.epc_data?.current_energy_rating,
            floor_area_band: '0-72', // From EPC data
            measures: [/* selected measures */]
        }).then(res => {
            setResult(res.data);
        });
    };
    
    return (
        <div>
            {/* Calculator UI */}
        </div>
    );
}
```

---

## 📊 Using EPC Data Automatically

The calculator can pre-populate from EPC data:

```jsx
// Auto-populate from EPC
const sapScore = lead.epc_data?.current_energy_efficiency; // e.g., 68
const sapBand = determineB and(sapScore); // "High_D"
const floorArea = lead.epc_data?.total_floor_area; // e.g., 28
const floorAreaBand = getFloorAreaBand(floorArea); // "0-72"
const heatingSource = lead.epc_data?.main_heating_description; // "Condensing Gas Boiler"
```

---

## 🔄 Calculation Flow

```
1. User selects scheme (GBIS/ECO4)
2. System loads SAP score from EPC data
3. User selects floor area band
4. User selects measures from list
5. System calls POST /api/eco4/calculate
6. Results displayed with PPS and ECO values
7. User can save calculation to lead
```

---

## 📁 File Structure

```
app/
├── Console/Commands/
│   ├── ConvertOfgemFiles.php ✅
│   └── ImportOfgemData.php ✅
├── Http/Controllers/Api/
│   └── Eco4CalculatorController.php ✅
├── Models/
│   ├── Eco4Calculation.php ✅
│   ├── Eco4FullScore.php ✅
│   ├── Eco4Measure.php ✅
│   ├── Eco4PartialScore.php ✅
│   └── GbisPartialScore.php ✅
├── Services/
│   └── Eco4CalculatorService.php ✅
└── ...

database/migrations/
└── 2025_10_12_195545_create_eco4_tables.php ✅

resources/js/
├── Components/ (TO CREATE)
│   ├── Eco4CalculatorCard.jsx 🚧
│   ├── Eco4MeasureSelector.jsx 🚧
│   ├── Eco4MeasureCard.jsx 🚧
│   ├── Eco4CalculationSummary.jsx 🚧
│   └── Eco4CalculationsList.jsx 🚧
└── Pages/ (TO CREATE)
    └── Eco4Calculator.jsx 🚧

storage/ofgem_files/
├── eco4_partial_v6.csv ✅
├── eco4_full_v1.csv ✅
└── gbis_partial_v3.csv ✅
```

---

## 🧪 Testing

### Test Import
```bash
php artisan ofgem:import --fresh
```

### Test Calculator Service
```bash
php artisan tinker
```

```php
$service = new App\Services\Eco4CalculatorService();

$result = $service->calculate([
    'scheme' => 'GBIS',
    'starting_sap_band' => 'High_D',
    'floor_area_band' => '0-72',
    'measures' => [
        ['type' => 'CWI_0.040', 'percentage_treated' => 100]
    ]
]);

print_r($result);
```

---

## 📖 API Documentation

### GET /api/eco4/metadata

Response:
```json
{
  "schemes": ["GBIS", "ECO4"],
  "sap_bands": [...],
  "floor_area_bands": ["0-72", "73-97", "98-199", "200+"],
  "pre_main_heat_sources": [...],
  "measures": {
    "eco4": {...},
    "gbis": {...}
  }
}
```

### POST /api/eco4/calculate

Request:
```json
{
  "scheme": "GBIS",
  "starting_sap_band": "High_D",
  "floor_area_band": "0-72",
  "pps_eco_rate": 21.5,
  "measures": [
    {
      "type": "CWI_0.040",
      "percentage_treated": 100
    }
  ]
}
```

Response:
```json
{
  "success": true,
  "summary": {
    "total_abs": 122.90,
    "total_eco_value": 2638.05
  },
  "measures": [
    {
      "measure_type": "CWI_0.040",
      "abs_value": 122.90,
      "pps_points": 2638.05,
      "eco_value": 2638.05
    }
  ]
}
```

---

## 🎨 UI Design Notes

Match CoreLogic design:
- Green header for calculator section
- Color-coded SAP bands (G=red, F=orange, E=yellow, D=light-green, C=green, B=dark-green, A=blue)
- Card-based measure display
- Orange "Calculate" buttons
- White background cards with shadows

---

## 🚀 Next Steps

1. **Create Eco4CalculatorCard.jsx** - Start here
2. **Add to Lead Show page** - As new tab
3. **Test with real EPC data**
4. **Build full-page calculator** - For standalone use
5. **Add PDF quote generation** - Export feature
6. **Add measure library** - Pre-defined measure templates

---

## 💡 Quick Win

Add a simple "Calculate ECO4" button to lead page that opens a modal with basic calculation. Use the API endpoints already created!

---

**Backend Status: ✅ COMPLETE (95%)**  
**Frontend Status: 🚧 TO BUILD (5%)**  
**Total Progress: ~90% Complete**

The heavy lifting is done - just need the UI now!

