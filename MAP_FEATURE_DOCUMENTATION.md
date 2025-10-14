# 🗺️ Leads Map Feature - Complete Documentation

## Overview
Interactive map feature showing all leads with their geographic locations using **Leaflet + OpenStreetMap** (100% free, no API keys required).

## ✅ Features Implemented

### 1. **Interactive Map**
- 📍 Shows all leads as markers on an interactive map
- 🎨 Color-coded markers by status
- 🔍 Zoom, pan, and full-screen controls
- 📱 Fully responsive (mobile-friendly)

### 2. **Marker Clustering**
- Groups nearby markers when zoomed out
- Shows count in cluster (e.g., "42")
- Expands on click
- Improves performance with many leads

### 3. **Smart Filters**
- **Search**: Name, postcode, address
- **Status**: Filter by lead status
- **Team**: Filter by assigned team
- **Source**: Filter by lead source
- Toggle filters sidebar on/off

### 4. **Lead Popups**
Click any marker to see:
- Lead name
- Full address
- Phone number
- Email
- Status & Team
- "View Details" link to lead page

### 5. **Auto-Geocoding**
- New leads are automatically geocoded (after response)
- Converts addresses to coordinates
- Uses Nominatim (OpenStreetMap) - FREE
- Respects rate limits (1 request/second)

### 6. **Legend**
- 🟢 Green = New
- 🔵 Blue = In Progress
- 🟡 Yellow = Hold
- 🔴 Red = Not Possible
- ⚫ Gray = Completed

## 📁 Files Created/Modified

### Backend
1. ✅ `database/migrations/2025_10_14_115715_add_coordinates_to_leads_table.php`
2. ✅ `app/Services/GeocodingService.php`
3. ✅ `app/Console/Commands/GeocodeLeads.php`
4. ✅ `app/Http/Controllers/MapController.php`
5. ✅ `app/Models/Lead.php` (updated)
6. ✅ `app/Http/Controllers/LeadViewController.php` (updated)
7. ✅ `routes/web.php` (updated)

### Frontend
1. ✅ `resources/js/Pages/Map/Index.jsx`
2. ✅ `resources/js/Layouts/AppLayout.jsx` (updated)
3. ✅ Leaflet packages installed

## 🚀 Usage

### Accessing the Map
1. Navigate to **Map** in the sidebar
2. Or visit: `https://leads.radianceeco.co.uk/map`

### Geocoding Existing Leads
Run this command to geocode all existing leads:

```bash
php artisan leads:geocode
```

**Options:**
```bash
# Force re-geocode all leads (even if already geocoded)
php artisan leads:geocode --force
```

**Note:** The command respects Nominatim's rate limit (1 request/second), so geocoding 100 leads takes ~100 seconds.

### Automatic Geocoding
- **New leads**: Automatically geocoded after creation (background job)
- **Updated leads**: Re-geocode manually if address changes
- **Imported leads**: Run `php artisan leads:geocode` after import

## 🎯 How It Works

### 1. Address → Coordinates
```
Address: "123 Main St, London, SW1A 1AA, UK"
         ↓ (Geocoding Service)
Coordinates: 51.5074, -0.1278
```

### 2. Database Storage
```sql
leads table:
- latitude: 51.5074
- longitude: -0.1278
- geocoded_at: 2025-10-14 12:00:00
```

### 3. Map Display
```
Frontend fetches leads → Filters applied → Markers rendered → Clustering applied
```

## 🔧 Technical Details

### Geocoding Service
- **Provider**: Nominatim (OpenStreetMap)
- **Cost**: FREE
- **Rate Limit**: 1 request/second
- **Accuracy**: Good for UK postcodes
- **Caching**: Results cached for 30 days
- **Fallback**: Returns null if geocoding fails

### API Endpoints
```
GET /map              → Map page (Inertia)
GET /map/leads        → Get leads data (JSON)
  Query params:
  - status: Filter by status
  - stage: Filter by team
  - source: Filter by source
  - search: Search term
```

### Marker Colors
```javascript
const colors = {
    'new': '#10b981',           // green
    'hold': '#f59e0b',          // yellow
    'not_possible': '#ef4444',  // red
    'property_installed': '#6b7280', // gray
    'unknown': '#9ca3af',       // light gray
    default: '#3b82f6'          // blue
};
```

## 📊 Performance

### Optimization Techniques
1. **Marker Clustering**: Groups nearby markers
2. **Lazy Loading**: Only geocoded leads are fetched
3. **API Caching**: Geocoding results cached
4. **Efficient Queries**: Only necessary fields fetched
5. **Background Jobs**: Geocoding doesn't block UI

### Scalability
- ✅ Handles 1,000+ leads smoothly
- ✅ Clustering prevents marker overload
- ✅ Filters reduce visible markers
- ✅ Minimal database queries

## 🐛 Troubleshooting

### No Leads Showing on Map
**Problem**: Map is empty

**Solutions:**
1. Check if leads have coordinates:
   ```sql
   SELECT COUNT(*) FROM leads WHERE latitude IS NOT NULL;
   ```

2. Run geocoding command:
   ```bash
   php artisan leads:geocode
   ```

3. Check if leads pass filters (clear all filters)

### Geocoding Fails
**Problem**: Leads not getting coordinates

**Possible Causes:**
1. **Invalid Address**: Check address format
2. **Rate Limit**: Wait 1 second between requests
3. **Network Issue**: Check internet connection
4. **API Down**: Nominatim service temporarily unavailable

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Retry geocoding
php artisan leads:geocode --force
```

### Map Not Loading
**Problem**: Map shows loading spinner forever

**Solutions:**
1. Check browser console for errors
2. Verify Leaflet CSS is loaded
3. Clear browser cache
4. Check network tab for API errors

## 🔐 Permissions
Map requires `lead.view` permission (same as Leads page).

## 💡 Future Enhancements (Optional)

### Easy to Add:
1. **Heat Map**: Show density of leads
2. **Draw Tools**: Draw radius/polygon on map
3. **Route Planning**: Plan route between multiple leads
4. **Export**: Export visible leads to CSV
5. **Custom Markers**: Different icons for different teams
6. **Street View**: Integrate Google Street View
7. **Distance Calculator**: Calculate distance between leads
8. **Batch Actions**: Select multiple markers for bulk actions

### Advanced:
1. **Real-time Updates**: WebSocket for live marker updates
2. **Historical Data**: Show lead movement over time
3. **Territory Management**: Assign areas to agents
4. **Offline Mode**: Cache map tiles for offline use

## 📱 Mobile Support
- ✅ Responsive design
- ✅ Touch-friendly controls
- ✅ Collapsible filters sidebar
- ✅ Optimized for small screens

## 🌍 Map Controls
- **Zoom**: Mouse wheel or +/- buttons
- **Pan**: Click and drag
- **Marker Click**: Show lead popup
- **Cluster Click**: Zoom to expand
- **Full Screen**: Browser full-screen mode

## 📈 Analytics Potential
The map can be extended to show:
- Lead density by area
- Conversion rates by region
- Team coverage areas
- Response time by distance
- Revenue by postcode

## ✅ Testing Checklist

- [x] Database migration runs successfully
- [x] Geocoding service converts addresses to coordinates
- [x] Artisan command geocodes existing leads
- [x] New leads auto-geocode on creation
- [x] Map page loads without errors
- [x] Markers appear on map
- [x] Marker clustering works
- [x] Filters work correctly
- [x] Popups show lead details
- [x] "View Details" link works
- [x] Legend displays correctly
- [x] Mobile responsive
- [x] Navigation link added
- [x] Frontend builds successfully

## 🎉 Status: COMPLETE

All features implemented and tested!

---
**Implementation Date:** October 14, 2025  
**Technology:** Leaflet + OpenStreetMap (100% Free)  
**Status:** Production Ready ✅

