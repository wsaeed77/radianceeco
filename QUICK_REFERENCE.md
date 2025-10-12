# React + Inertia.js Quick Reference

## 🔥 Essential Commands

```bash
# Development
npm run dev              # Start Vite dev server
php artisan serve        # Start Laravel server
php artisan ziggy:generate  # Regenerate routes

# Production
npm run build            # Build for production

# Installation
npm install              # Install Node dependencies
composer install         # Install PHP dependencies
```

## 🎯 Inertia Patterns

### Navigation
```jsx
import { Link, router } from '@inertiajs/react';

// Link component
<Link href={route('leads.show', lead.id)}>View</Link>

// Programmatic
router.visit(route('leads.index'));
router.get(url);
router.post(route('leads.store'), data);
```

### Forms
```jsx
import { useForm } from '@inertiajs/react';

const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
    name: '',
    email: '',
});

// Methods
setData('name', 'John');
setData({ name: 'John', email: 'john@example.com' });

// Submit
post(route('leads.store'));
put(route('leads.update', lead.id));
destroy(route('leads.destroy', lead.id));
```

### Shared Data
```jsx
import { usePage } from '@inertiajs/react';

const { auth, flash, errors } = usePage().props;
```

## 🎨 Component Cheat Sheet

### Card
```jsx
<Card>
    <CardHeader><CardTitle>Title</CardTitle></CardHeader>
    <CardContent>Content</CardContent>
    <CardFooter>Footer</CardFooter>
</Card>
```

### Badge
```jsx
<Badge variant="success" size="md">Active</Badge>
// Variants: default, primary, success, warning, danger, info
```

### Button
```jsx
<Button variant="primary" size="md" onClick={handleClick}>
    Click Me
</Button>
// Variants: primary, secondary, success, danger, warning
```

### Table
```jsx
<Table>
    <TableHeader>
        <TableRow>
            <TableHead>Name</TableHead>
        </TableRow>
    </TableHeader>
    <TableBody>
        <TableRow onClick={handleClick}>
            <TableCell>John Doe</TableCell>
        </TableRow>
    </TableBody>
</Table>
```

### StatCard
```jsx
<StatCard
    title="Total Users"
    value="1,234"
    icon={UserGroupIcon}
    trend={12.5}
    trendLabel="vs last month"
    color="primary"
/>
```

### EmptyState
```jsx
<EmptyState
    icon={UserIcon}
    title="No data"
    description="Get started now"
    action={handleCreate}
    actionLabel="Create"
/>
```

### PageHeader
```jsx
<PageHeader
    title="Dashboard"
    description="Welcome back"
    breadcrumbs={[
        { label: 'Home', href: '/' },
        { label: 'Dashboard' }
    ]}
    actions={<Button>Action</Button>}
/>
```

### Form Inputs
```jsx
<FormInput
    label="Name"
    value={data.name}
    onChange={(e) => setData('name', e.target.value)}
    error={errors.name}
    required
    isFocused
/>

<FormSelect
    label="Status"
    value={data.status}
    onChange={(e) => setData('status', e.target.value)}
    error={errors.status}
>
    <option>Select...</option>
</FormSelect>

<FormTextarea
    label="Description"
    value={data.description}
    onChange={(e) => setData('description', e.target.value)}
    error={errors.description}
    rows={4}
/>
```

### FormSection
```jsx
<FormSection
    title="Personal Info"
    description="Your details"
>
    {/* Form fields */}
</FormSection>
```

### Alert
```jsx
<Alert
    type="success"
    message="Success!"
    onClose={() => {}}
/>
// Types: success, error, warning, info
```

### Modal
```jsx
<Modal show={isOpen} onClose={() => setIsOpen(false)} maxWidth="2xl">
    <ModalHeader>Title</ModalHeader>
    <ModalBody>Content</ModalBody>
    <ModalFooter>
        <Button onClick={() => setIsOpen(false)}>Close</Button>
    </ModalFooter>
</Modal>
```

### LoadingSpinner
```jsx
<LoadingSpinner size="lg" />
// Sizes: sm, md, lg
```

## 🛠️ Utilities

```jsx
import {
    formatDate,
    formatDateTime,
    formatRelativeTime,
    truncate,
    formatCurrency,
    debounce,
    getInitials
} from '@/utils';

formatDate('2024-01-15');              // "Jan 15, 2024"
formatDateTime('2024-01-15 14:30');    // "Jan 15, 2024 2:30 PM"
formatRelativeTime('2024-01-15');      // "2 hours ago"
truncate('Long text...', 20);          // "Long text..."
formatCurrency(1234.56);               // "$1,234.56"
getInitials('John Doe');               // "JD"

const debouncedFn = debounce(handleSearch, 300);
```

## 🎨 Tailwind Colors

```
primary-50 to primary-950   (Blue - Brand)
secondary-50 to secondary-950 (Gray)
success-50 to success-900   (Green)
warning-50 to warning-900   (Yellow)
danger-50 to danger-900     (Red)
```

## 🔗 Icons

```jsx
import { UserIcon, PlusIcon, CheckIcon } from '@heroicons/react/24/outline';

<UserIcon className="h-6 w-6 text-gray-500" />
```

Browse: https://heroicons.com/

## 📋 Page Template

```jsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';

export default function MyPage({ data }) {
    return (
        <AppLayout>
            <Head title="My Page" />
            
            <PageHeader title="My Page" />
            
            {/* Your content */}
        </AppLayout>
    );
}
```

## 🔄 Controller Pattern

```php
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index()
    {
        return Inertia::render('Leads/Index', [
            'leads' => Lead::paginate(10),
        ]);
    }
    
    public function store(Request $request)
    {
        Lead::create($request->validated());
        
        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully!');
    }
}
```

## ⚡ Pro Tips

1. **Use @ alias:** `import Card from '@/Components/Card'`
2. **Preserve state:** `router.get(url, data, { preserveState: true })`
3. **Only for modified:** `post(route('...'), { only: ['leads'] })`
4. **Lazy props:** Share expensive data lazily in HandleInertiaRequests
5. **Form shortcuts:** Inertia forms auto-handle CSRF, errors, loading states

## 🐛 Debug Commands

```bash
# Clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate
php artisan config:cache
php artisan route:cache
php artisan ziggy:generate

# Node issues
rm -rf node_modules package-lock.json
npm install
npm run dev
```

## 📂 File Locations

```
resources/js/
├── Components/      # Reusable components
├── Layouts/         # Page layouts
├── Pages/          # Inertia pages (maps to controllers)
└── utils/          # Helper functions

app/Http/Middleware/
└── HandleInertiaRequests.php  # Shared data

resources/views/
└── app.blade.php   # Inertia root template
```

## 🔐 Auth Example

```jsx
import { usePage } from '@inertiajs/react';

const { auth } = usePage().props;

if (auth.user) {
    console.log(auth.user.name);
    console.log(auth.user.roles);
    console.log(auth.user.permissions);
}
```

## 🚀 Remember

- Run `npm run dev` for development
- Run `npm run build` before production deploy
- Flash messages auto-display in AppLayout
- All routes use Laravel's `route()` helper via Ziggy
- Forms handle CSRF tokens automatically
- Errors automatically passed to forms

---

**Full docs:** See `INSTALLATION.md` and `FRONTEND_SETUP.md`

