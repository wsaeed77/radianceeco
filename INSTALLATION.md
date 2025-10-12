# Installation Guide - React + Inertia.js Frontend

## 🚀 Quick Start

Follow these steps to complete the frontend migration:

### Step 1: Install Dependencies

```bash
# Install Node.js dependencies
npm install

# Install PHP dependencies (Inertia & Ziggy)
composer install
```

### Step 2: Configure Ziggy Routes

Generate the Ziggy routes configuration:

```bash
php artisan ziggy:generate
```

### Step 3: Start Development Server

Open two terminal windows:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

Your application should now be running at `http://localhost:8000`

### Step 4: Build for Production

When ready to deploy:

```bash
npm run build
```

## ✅ What's Been Set Up

### ✨ Configuration Files
- ✅ `package.json` - Updated with React, Inertia, Tailwind, and all dependencies
- ✅ `composer.json` - Added Inertia Laravel and Ziggy packages
- ✅ `vite.config.js` - Configured for React with Vite plugin
- ✅ `tailwind.config.js` - Tailwind with custom Starline Care theme
- ✅ `postcss.config.js` - PostCSS configuration for Tailwind
- ✅ `jsconfig.json` - Path aliases for better imports (@/ = resources/js/)
- ✅ `.prettierrc` - Code formatting configuration

### 🎨 Component Library (14+ Components)
- ✅ **UI Components:** Card, Badge, Button, Alert, LoadingSpinner, Modal
- ✅ **Data Components:** Table (with Header, Body, Row, Head, Cell)
- ✅ **Form Components:** FormInput, FormSelect, FormTextarea, FormSection
- ✅ **Display Components:** StatCard, EmptyState, PageHeader

### 📐 Layouts
- ✅ **AppLayout** - Main authenticated layout with sidebar navigation
- ✅ **GuestLayout** - Clean layout for login/guest pages

### 📄 Example Pages
- ✅ **Dashboard** - Home page with stats cards
- ✅ **Auth/Login** - Login page with form validation
- ✅ **Leads/Index** - Full CRUD example with table, search, pagination
- ✅ **Leads/Create** - Complex form example with multiple sections

### 🔧 Backend Setup
- ✅ **HandleInertiaRequests.php** - Middleware for shared data (auth, flash)
- ✅ **app.blade.php** - Inertia root template
- ✅ **Kernel.php** - Updated to include Inertia middleware

### 🛠️ Utilities
- ✅ **utils/index.js** - Date formatting, currency, string manipulation, etc.

## 📋 Next Steps

### 1. Update Your Controllers

Convert your existing Blade views to Inertia responses:

**Before (Blade):**
```php
return view('leads.index', ['leads' => $leads]);
```

**After (Inertia):**
```php
use Inertia\Inertia;

return Inertia::render('Leads/Index', [
    'leads' => $leads,
]);
```

### 2. Create React Pages

For each Blade view, create a corresponding React component:

```
resources/views/leads/index.blade.php
↓
resources/js/Pages/Leads/Index.jsx
```

### 3. Update Routes

Your routes stay the same! Inertia works with your existing Laravel routes.

```php
// routes/web.php
Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
```

### 4. Share Data Globally

Edit `app/Http/Middleware/HandleInertiaRequests.php` to share data across all pages:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [...],
        'customData' => $yourData,
    ]);
}
```

## 🎯 Common Tasks

### Creating a New Page

1. **Create the component:**
   ```bash
   # Create: resources/js/Pages/YourPage.jsx
   ```

2. **Use the template:**
   ```jsx
   import { Head } from '@inertiajs/react';
   import AppLayout from '@/Layouts/AppLayout';
   import PageHeader from '@/Components/PageHeader';

   export default function YourPage({ data }) {
       return (
           <AppLayout>
               <Head title="Your Page" />
               <PageHeader title="Your Page" />
               {/* Your content */}
           </AppLayout>
       );
   }
   ```

3. **Return from controller:**
   ```php
   return Inertia::render('YourPage', ['data' => $data]);
   ```

### Adding a Form

```jsx
import { useForm } from '@inertiajs/react';

const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
});

const submit = (e) => {
    e.preventDefault();
    post(route('your.route'));
};
```

### Adding Flash Messages

**In Controller:**
```php
return redirect()->back()->with('success', 'Lead created successfully!');
```

**Automatic Display:** Flash messages are automatically shown via the AppLayout!

### Using Icons

```jsx
import { UserIcon, CheckIcon } from '@heroicons/react/24/outline';

<UserIcon className="h-6 w-6" />
```

Browse all icons: [https://heroicons.com/](https://heroicons.com/)

## 🐛 Troubleshooting

### Issue: "route is not defined"
**Solution:** Make sure Ziggy is generated:
```bash
php artisan ziggy:generate
```

### Issue: "Cannot find module '@/Components/...'"
**Solution:** Restart your dev server:
```bash
# Stop npm run dev (Ctrl+C)
npm run dev
```

### Issue: Tailwind classes not applying
**Solution:** 
1. Make sure Vite is running (`npm run dev`)
2. Clear browser cache
3. Rebuild: `npm run build`

### Issue: Changes not reflecting
**Solution:** 
- Hard refresh: `Ctrl+Shift+R` (Windows/Linux) or `Cmd+Shift+R` (Mac)
- Clear Vite cache: Delete `node_modules/.vite`

### Issue: "Inertia\Inertia not found"
**Solution:** 
```bash
composer require inertiajs/inertia-laravel
```

## 📚 Documentation

- **React:** [https://react.dev/](https://react.dev/)
- **Inertia.js:** [https://inertiajs.com/](https://inertiajs.com/)
- **Tailwind CSS:** [https://tailwindcss.com/](https://tailwindcss.com/)
- **Laravel Vite:** [https://laravel.com/docs/vite](https://laravel.com/docs/vite)

## 🎨 Design System

All colors and components follow the Starline Care brand guidelines:
- Primary color: Blue (`primary-*`)
- Success: Green (`success-*`)
- Warning: Yellow (`warning-*`)
- Danger: Red (`danger-*`)

See `tailwind.config.js` for the complete color palette.

## 💡 Tips

1. **Use the `@/` alias** instead of relative paths:
   ```jsx
   // Good ✅
   import Card from '@/Components/Card';
   
   // Avoid ❌
   import Card from '../../Components/Card';
   ```

2. **Leverage Inertia's form helpers** instead of managing state manually

3. **Use existing components** - We've built 14+ reusable components for you!

4. **Follow the example pages** (Dashboard, Leads/Index, Leads/Create) for patterns

5. **Keep components small** - Break down complex pages into smaller components

## 🚀 Deployment

Before deploying to production:

```bash
# Build optimized assets
npm run build

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## ✨ Features Included

- 🎨 Beautiful, modern UI with Tailwind CSS
- ⚡ Lightning-fast hot module replacement with Vite
- 🔄 SPA experience without building an API
- 📱 Fully responsive design
- ♿ Accessible components (WCAG compliant)
- 🎭 Form validation with error handling
- 🔔 Flash message notifications
- 🔐 Authentication ready
- 📊 Data tables with sorting and pagination
- 🎯 Type-safe routing with Ziggy
- 📅 Date utilities with date-fns

---

**Need help?** Check `FRONTEND_SETUP.md` for detailed component documentation and examples.

