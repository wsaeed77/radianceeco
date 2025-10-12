# Application Name Update

## ✅ Change Complete

The application name has been successfully changed from **"Starline Care"** to **"Radiance Eco"**.

## 📝 Files Updated

### Backend Configuration
1. **`config/app.php`**
   - Changed default app name from `'Laravel'` to `'Radiance Eco'`
   - Line 19: `'name' => env('APP_NAME', 'Radiance Eco'),`

### Frontend Views
2. **`resources/views/app.blade.php`**
   - Updated page title fallback
   - Line 7: `<title inertia>{{ config('app.name', 'Radiance Eco') }}</title>`

### React Components
3. **`resources/js/app.jsx`**
   - Updated app name constant
   - Line 8: `const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Radiance Eco';`

4. **`resources/js/Layouts/AppLayout.jsx`**
   - Updated logo alt text (3 occurrences)
   - Updated sidebar title text
   - Line 81: `<span className="ml-2 text-xl font-bold text-gray-900">Radiance Eco</span>`

5. **`resources/js/Layouts/GuestLayout.jsx`**
   - Updated logo alt text
   - Updated footer copyright
   - Line 21: `<p>&copy; {new Date().getFullYear()} Radiance Eco. All rights reserved.</p>`

6. **`resources/js/Pages/Auth/Login.jsx`**
   - Updated login page welcome message
   - Line 38: `Welcome back to Radiance Eco CRM`

## 🔍 Where "Radiance Eco" Now Appears

1. **Browser Tab Title**: Shows "Radiance Eco" in the page title
2. **Sidebar Logo**: Main navigation sidebar displays "Radiance Eco"
3. **Login Page**: Welcome message says "Welcome back to Radiance Eco CRM"
4. **Footer**: Copyright notice shows "© 2025 Radiance Eco. All rights reserved."
5. **Mobile Menu**: Header shows "Radiance Eco"
6. **Email Templates**: Any emails sent will use "Radiance Eco" as sender name
7. **Notifications**: System notifications reference "Radiance Eco"

## 🚀 Build Status

✅ **Frontend built successfully** - All assets compiled with new branding

## 💡 Environment Variable (Optional)

If you want to customize the name via environment variables, add to your `.env` file:

```env
APP_NAME="Radiance Eco"
```

**Note**: This is optional - the default is already set to "Radiance Eco" in `config/app.php`

## 🎨 Additional Branding Recommendations

To complete the rebranding, consider updating:

1. **Logo Image**: Replace `/public/images/logo.svg` with Radiance Eco logo
2. **Favicon**: Update `/public/favicon.ico`, `/public/favicon.png`, `/public/favicon.svg`
3. **Email Templates**: Update any custom email templates
4. **Documentation**: Update README.md and other documentation files
5. **Meta Tags**: Add Open Graph tags for social media sharing

### Quick Logo Update

```bash
# Backup old logo
cp public/images/logo.svg public/images/logo-old.svg

# Replace with new Radiance Eco logo
# Place your new logo at: public/images/logo.svg
```

## ✅ Verification Checklist

- [x] Config file updated
- [x] Blade templates updated
- [x] React components updated
- [x] Login page updated
- [x] Sidebar branding updated
- [x] Footer updated
- [x] Frontend assets built
- [ ] Logo images updated (optional)
- [ ] Favicon updated (optional)
- [ ] Environment variable set (optional)

## 🔄 Rollback Instructions

If you need to revert to "Starline Care":

```bash
# Search and replace
grep -rl "Radiance Eco" resources/ config/ | xargs sed -i 's/Radiance Eco/Starline Care/g'

# Rebuild
npm run build
```

---

**Status**: ✅ Complete  
**Date**: 2025  
**New Name**: Radiance Eco  
**Previous Name**: Starline Care

