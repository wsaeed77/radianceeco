# 🗺️ Map Feature - Quick Start Guide

## ✅ What's Been Done
- ✅ Database migration (latitude, longitude, geocoded_at columns)
- ✅ Geocoding service (FREE - OpenStreetMap/Nominatim)
- ✅ Auto-geocoding on lead creation
- ✅ Map page with filters and clustering
- ✅ Navigation link added
- ✅ Frontend built successfully

## 🚀 Next Steps

### 1. Geocode Existing Leads (ONE TIME)
```bash
# On local
php artisan leads:geocode

# This will:
# - Find all leads without coordinates
# - Convert addresses to lat/lng
# - Take ~1 second per lead (rate limit)
# - Show progress bar
```

### 2. Access the Map
1. Click **"Map"** in the sidebar
2. Or visit: `http://radiance.local/map`

### 3. Use the Map
- **View leads**: Markers show on map
- **Click marker**: See lead details popup
- **Use filters**: Filter by status/team/source
- **Search**: Search by name or postcode
- **Zoom**: Scroll or use +/- buttons

## 🎨 Marker Colors
- 🟢 **Green** = New
- 🔵 **Blue** = In Progress  
- 🟡 **Yellow** = Hold
- 🔴 **Red** = Not Possible
- ⚫ **Gray** = Completed

## 📋 Features
✅ Interactive map with zoom/pan
✅ Marker clustering (groups nearby leads)
✅ Color-coded by status
✅ Click popup with lead details
✅ Filters (status, team, source, search)
✅ Auto-geocoding for new leads
✅ Mobile responsive

## 🔧 Commands

### Geocode All Leads
```bash
php artisan leads:geocode
```

### Force Re-geocode All
```bash
php artisan leads:geocode --force
```

### Check Geocoded Leads
```bash
php artisan tinker
>>> \App\Models\Lead::whereNotNull('latitude')->count()
```

## 📊 What Happens Automatically
1. **New Lead Created** → Geocoded in background (after page loads)
2. **Lead Address Updated** → Manual re-geocode needed
3. **Leads Imported** → Run `php artisan leads:geocode`

## 💰 Cost
**$0.00** - Completely FREE!
- No API keys needed
- No usage limits
- No billing account required

## 🌍 Technology
- **Map**: Leaflet + OpenStreetMap
- **Geocoding**: Nominatim (OpenStreetMap)
- **Clustering**: React Leaflet Cluster
- **100% Open Source**

## 📱 Mobile Support
✅ Works on all devices
✅ Touch-friendly
✅ Responsive filters

## 🎯 Tips
- **First time**: Run geocoding command to populate coordinates
- **Filters**: Use filters to reduce markers on map
- **Clustering**: Zoom in to see individual markers
- **Performance**: Map handles 1000+ leads easily

## 🐛 Troubleshooting

### Map is Empty
```bash
# Check if leads have coordinates
php artisan tinker
>>> \App\Models\Lead::whereNotNull('latitude')->count()

# If 0, run geocoding
php artisan leads:geocode
```

### Geocoding Slow
- Normal! Rate limited to 1 request/second
- 100 leads = ~100 seconds
- Runs in background for new leads

### Map Not Loading
1. Clear browser cache
2. Check browser console for errors
3. Verify `npm run build` completed successfully

## 🚢 Deployment

### Local (Already Done)
✅ Migration run
✅ Frontend built
✅ Ready to use!

### Server (EC2)
Will deploy automatically via GitHub Actions:
1. Push changes to GitHub
2. GitHub Actions will:
   - Run migration
   - Build frontend
   - Deploy to server
3. On server, run once:
   ```bash
   php artisan leads:geocode
   ```

## 📖 Full Documentation
See `MAP_FEATURE_DOCUMENTATION.md` for complete details.

---
**Status:** ✅ Ready to Use!  
**Cost:** FREE  
**Setup Time:** 5 minutes (geocoding existing leads)

