# Starline Care CRM - Frontend Setup Guide

## 🎨 Tech Stack

### Core Framework
- **React 18** - Modern JavaScript library for building user interfaces
- **Inertia.js** - Server-side routing with SPA experience (no API needed)

### Build Tools
- **Vite** - Fast, modern frontend build tool
- **Laravel Vite Plugin** - Integrates Vite with Laravel seamlessly

### Styling & UI
- **Tailwind CSS 3** - Utility-first CSS framework
- **@tailwindcss/forms** - Better form styling out of the box
- Custom Starline Care branded design system

### Icons
- **@heroicons/react** - Beautiful hand-crafted SVG icons (24/outline)
- **@headlessui/react** - Unstyled, accessible UI components

### Utilities
- **date-fns** - Modern JavaScript date utility library
- **Ziggy** - Use Laravel named routes in JavaScript

### State Management
- Inertia Form Helper - Built-in form state management
- React Hooks (useState, useEffect, etc.)
- No external state management needed (Redux, Zustand, etc.)

## 📦 Installation Steps

### 1. Install Node Dependencies

```bash
npm install
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Publish Inertia & Ziggy Configuration

```bash
php artisan inertia:middleware
php artisan vendor:publish --tag=ziggy-config
```

### 4. Build Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

## 🏗️ Project Structure

```
resources/
├── js/
│   ├── Components/          # Reusable UI components
│   │   ├── Alert.jsx
│   │   ├── Badge.jsx
│   │   ├── Button.jsx
│   │   ├── Card.jsx
│   │   ├── EmptyState.jsx
│   │   ├── FormInput.jsx
│   │   ├── FormSelect.jsx
│   │   ├── FormTextarea.jsx
│   │   ├── FormSection.jsx
│   │   ├── LoadingSpinner.jsx
│   │   ├── Modal.jsx
│   │   ├── PageHeader.jsx
│   │   ├── StatCard.jsx
│   │   └── Table.jsx
│   ├── Layouts/             # Page layouts
│   │   ├── AppLayout.jsx
│   │   └── GuestLayout.jsx
│   ├── Pages/               # Inertia pages
│   │   ├── Auth/
│   │   │   └── Login.jsx
│   │   └── Dashboard.jsx
│   ├── utils/               # Utility functions
│   │   └── index.js
│   ├── app.jsx             # Main React entry point
│   └── bootstrap.js        # Bootstrap configuration
├── css/
│   └── app.css             # Main CSS with Tailwind directives
└── views/
    └── app.blade.php       # Inertia root template
```

## 🎨 Component Library

### UI Components

#### Card
```jsx
import Card, { CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/Card';

<Card>
    <CardHeader>
        <CardTitle>Title</CardTitle>
    </CardHeader>
    <CardContent>Content here</CardContent>
    <CardFooter>Footer actions</CardFooter>
</Card>
```

#### Badge
```jsx
import Badge from '@/Components/Badge';

<Badge variant="success" size="md">Active</Badge>
```

Variants: `default`, `primary`, `success`, `warning`, `danger`, `info`

#### Button
```jsx
import Button from '@/Components/Button';

<Button variant="primary" size="md" onClick={handleClick}>
    Click Me
</Button>
```

#### Table
```jsx
import Table, { TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/Components/Table';

<Table>
    <TableHeader>
        <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Email</TableHead>
        </TableRow>
    </TableHeader>
    <TableBody>
        <TableRow>
            <TableCell>John Doe</TableCell>
            <TableCell>john@example.com</TableCell>
        </TableRow>
    </TableBody>
</Table>
```

#### StatCard
```jsx
import StatCard from '@/Components/StatCard';
import { UserGroupIcon } from '@heroicons/react/24/outline';

<StatCard
    title="Total Users"
    value="1,234"
    icon={UserGroupIcon}
    trend={12.5}
    trendLabel="vs last month"
    color="primary"
/>
```

#### EmptyState
```jsx
import EmptyState from '@/Components/EmptyState';
import { UserIcon } from '@heroicons/react/24/outline';

<EmptyState
    icon={UserIcon}
    title="No users found"
    description="Get started by creating a new user."
    action={handleCreate}
    actionLabel="Create User"
/>
```

### Form Components

#### FormInput
```jsx
import FormInput from '@/Components/FormInput';

<FormInput
    label="Email"
    type="email"
    value={data.email}
    onChange={(e) => setData('email', e.target.value)}
    error={errors.email}
    required
/>
```

#### FormSelect
```jsx
import FormSelect from '@/Components/FormSelect';

<FormSelect
    label="Status"
    value={data.status}
    onChange={(e) => setData('status', e.target.value)}
    error={errors.status}
    required
>
    <option value="">Select status</option>
    <option value="active">Active</option>
    <option value="inactive">Inactive</option>
</FormSelect>
```

#### FormTextarea
```jsx
import FormTextarea from '@/Components/FormTextarea';

<FormTextarea
    label="Description"
    value={data.description}
    onChange={(e) => setData('description', e.target.value)}
    error={errors.description}
    rows={4}
/>
```

#### FormSection
```jsx
import FormSection from '@/Components/FormSection';

<FormSection
    title="Personal Information"
    description="Update your personal details."
>
    {/* Form fields here */}
</FormSection>
```

### Utility Components

#### Alert
```jsx
import Alert from '@/Components/Alert';

<Alert
    type="success"
    message="Operation completed successfully!"
    onClose={() => {}}
/>
```

#### Modal
```jsx
import Modal, { ModalHeader, ModalBody, ModalFooter } from '@/Components/Modal';

<Modal show={isOpen} onClose={() => setIsOpen(false)} maxWidth="2xl">
    <ModalHeader>Modal Title</ModalHeader>
    <ModalBody>Modal content here</ModalBody>
    <ModalFooter>
        <Button onClick={() => setIsOpen(false)}>Close</Button>
    </ModalFooter>
</Modal>
```

#### LoadingSpinner
```jsx
import LoadingSpinner from '@/Components/LoadingSpinner';

<LoadingSpinner size="lg" />
```

## 🎨 Color Palette

### Primary (Blue)
- `primary-50` to `primary-950`
- Main brand color

### Secondary (Gray)
- `secondary-50` to `secondary-950`
- Used for text and backgrounds

### Success (Green)
- `success-50` to `success-900`

### Warning (Yellow)
- `warning-50` to `warning-900`

### Danger (Red)
- `danger-50` to `danger-900`

## 🛠️ Utility Functions

```javascript
import {
    formatDate,
    formatDateTime,
    formatRelativeTime,
    truncate,
    formatCurrency,
    debounce,
    getInitials
} from '@/utils';

// Format dates
formatDate('2024-01-15'); // "Jan 15, 2024"
formatDateTime('2024-01-15 14:30'); // "Jan 15, 2024 2:30 PM"
formatRelativeTime('2024-01-15'); // "2 hours ago"

// Format strings
truncate('Very long text...', 20); // "Very long text..."
getInitials('John Doe'); // "JD"

// Format currency
formatCurrency(1234.56); // "$1,234.56"

// Debounce function
const debouncedSearch = debounce(handleSearch, 300);
```

## 🔄 Using Inertia.js

### Navigation
```jsx
import { Link, router } from '@inertiajs/react';

// Using Link component
<Link href={route('leads.show', lead.id)}>View Lead</Link>

// Programmatic navigation
router.visit(route('leads.index'));
router.get(route('leads.show', lead.id));
router.post(route('leads.store'), data);
```

### Forms
```jsx
import { useForm } from '@inertiajs/react';

const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    email: '',
});

const submit = (e) => {
    e.preventDefault();
    post(route('leads.store'), {
        onSuccess: () => reset(),
    });
};
```

### Accessing Shared Data
```jsx
import { usePage } from '@inertiajs/react';

const { auth, flash } = usePage().props;
console.log(auth.user); // Current authenticated user
console.log(flash.success); // Flash messages
```

## 📝 Creating New Pages

1. Create a new component in `resources/js/Pages/`:

```jsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';

export default function MyPage({ data }) {
    return (
        <AppLayout>
            <Head title="My Page" />
            
            <PageHeader title="My Page" />
            
            {/* Your content here */}
        </AppLayout>
    );
}
```

2. Return the page from your controller:

```php
use Inertia\Inertia;

return Inertia::render('MyPage', [
    'data' => $data,
]);
```

## 🚀 Best Practices

1. **Component Organization**: Keep components small and focused
2. **Props Validation**: Document expected props in comments
3. **Accessibility**: Use semantic HTML and ARIA labels
4. **Performance**: Use React.memo() for expensive components
5. **Naming**: Use PascalCase for components, camelCase for utilities
6. **State Management**: Keep state close to where it's used
7. **Error Handling**: Always handle form errors and loading states

## 📚 Additional Resources

- [React Documentation](https://react.dev/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Heroicons](https://heroicons.com/)
- [date-fns Documentation](https://date-fns.org/)

## 🐛 Troubleshooting

### Assets not loading
```bash
npm run dev
# or
npm run build
```

### Hot reload not working
- Check that Vite dev server is running
- Clear browser cache
- Restart Vite dev server

### Tailwind classes not working
```bash
npm run build
```

### Route helper not working
```bash
php artisan ziggy:generate
```

