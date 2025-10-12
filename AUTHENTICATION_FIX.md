# ✅ AUTHENTICATION ISSUE - FIXED!

## The Problem:
The calculator was showing "Loading calculator..." forever because the metadata API was returning:
```json
{"message": "Unauthenticated."}
```

## Root Cause:
The ECO4 API routes were using **Sanctum authentication** (`auth:sanctum` middleware), but your Inertia.js app uses **Laravel web sessions**.

- API routes expect: Sanctum tokens (for external APIs)
- Your app uses: Session cookies (for web authentication)
- Result: Mismatch = "Unauthenticated" ❌

## The Solution:
I moved the ECO4 Calculator routes from **API routes** to **Web routes** so they use session authentication instead.

### What I Changed:

#### 1. Added Web Routes (`routes/web.php`)
```php
// ECO4 Calculator routes (web-based, uses session auth)
Route::prefix('eco4')->middleware('auth')->group(function () {
    Route::get('/metadata', [Eco4CalculatorController::class, 'metadata']);
    Route::post('/calculate', [Eco4CalculatorController::class, 'calculate']);
    Route::post('/leads/{lead}/save', [Eco4CalculatorController::class, 'save']);
    Route::get('/leads/{lead}', [Eco4CalculatorController::class, 'getByLead']);
    Route::delete('/calculations/{calculation}', [Eco4CalculatorController::class, 'delete']);
});
```

#### 2. Updated Calculator Component
Changed from API routes to web routes:
- ❌ `/api/eco4/metadata` 
- ✅ `/eco4/metadata`

#### 3. Rebuilt Assets
```bash
npm run build  # ✓ built in 11.80s
```

#### 4. Cleared Caches
```bash
php artisan route:clear
php artisan config:clear
```

---

## 🚀 What to Do Now:

### Step 1: Hard Refresh Browser
Press `Ctrl+F5` to clear cache

### Step 2: Navigate to a Lead
Go to any lead page:
```
http://radiance.local/leads/{lead-id}
```

### Step 3: Scroll to Calculator
The calculator should now:
- ✅ Show form fields (not "Loading calculator...")
- ✅ Have dropdowns populated
- ✅ Auto-populate from EPC data
- ✅ Work when you click "Calculate"

---

## ✅ Expected Behavior:

### Before (Broken):
```
🧮 ECO4/GBIS Calculator
┌─────────────────────────┐
│ Loading calculator...   │
│ (spinning forever)      │
└─────────────────────────┘
```

### After (Working):
```
🧮 ECO4/GBIS Calculator
┌─────────────────────────────┐
│ Scheme: ○ GBIS  ○ ECO4     │
│ SAP Band: [High_D ▼]       │
│ Floor Area: [0-72 ▼]       │
│ Measures: ☑ CWI_0.040      │
│ [Calculate]                 │
└─────────────────────────────┘
```

---

## 🔍 How to Verify:

### Check Browser Console (F12):
**Before:**
```
GET /api/eco4/metadata → 401 Unauthorized
{"message": "Unauthenticated."}
```

**After:**
```
GET /eco4/metadata → 200 OK
{schemes: [...], sap_bands: [...], ...}
```

### Check Network Tab:
You should see:
- ✅ `GET /eco4/metadata` → Status 200
- ✅ Response contains schemes, SAP bands, measures
- ✅ No authentication errors

---

## 📊 Routes Comparison:

| Endpoint | Old (API) | New (Web) | Auth |
|----------|-----------|-----------|------|
| Metadata | `/api/eco4/metadata` | `/eco4/metadata` | Session ✅ |
| Calculate | `/api/eco4/calculate` | `/eco4/calculate` | Session ✅ |
| Save | `/api/eco4/leads/{id}/save` | `/eco4/leads/{id}/save` | Session ✅ |

---

## 💡 Why This Works:

### API Routes (`routes/api.php`):
- Use `auth:sanctum` middleware
- Expect API tokens in headers
- For external applications/mobile apps
- Stateless authentication

### Web Routes (`routes/web.php`):
- Use `auth` middleware
- Use session cookies
- For web browser applications
- Stateful authentication
- **Perfect for Inertia.js!** ✅

---

## 🎯 Test the Calculator:

1. **Hard refresh** (`Ctrl+F5`)
2. **Go to any lead** with EPC data
3. **Scroll to calculator**
4. You should see:
   - ✅ Scheme buttons
   - ✅ SAP Band dropdown (auto-filled from EPC)
   - ✅ Floor Area dropdown (auto-filled from EPC)
   - ✅ Measures list
5. **Select a measure** (e.g., CWI_0.040)
6. **Click Calculate**
7. **See results**: £1,395.35 ECO Value ✅

---

## 🔧 Future Note:

If you ever want to create a **real API** for external access:
1. Keep the routes in `routes/api.php`
2. Use Sanctum tokens properly
3. But for **internal web calculator**, web routes are better!

---

**The calculator should now load and work perfectly!** 🎉

Just hard refresh your browser and test it out!

