import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';

export default function CreateUser({ roles, permissions }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
        role: '',
        permissions: [],
    });

    const [selectedPermissions, setSelectedPermissions] = useState([]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('users.store'));
    };

    const togglePermission = (permissionName) => {
        const updated = selectedPermissions.includes(permissionName)
            ? selectedPermissions.filter(p => p !== permissionName)
            : [...selectedPermissions, permissionName];
        
        setSelectedPermissions(updated);
        setData('permissions', updated);
    };

    const toggleModule = (module) => {
        const modulePermissions = module.permissions.map(p => p.name);
        const allSelected = modulePermissions.every(p => selectedPermissions.includes(p));
        
        if (allSelected) {
            // Remove all module permissions
            const updated = selectedPermissions.filter(p => !modulePermissions.includes(p));
            setSelectedPermissions(updated);
            setData('permissions', updated);
        } else {
            // Add all module permissions
            const updated = [...new Set([...selectedPermissions, ...modulePermissions])];
            setSelectedPermissions(updated);
            setData('permissions', updated);
        }
    };

    return (
        <AppLayout>
            <Head title="Create User" />

            <PageHeader
                title="Create New User"
                breadcrumbs={[
                    { label: 'User Management', href: route('users.index') },
                    { label: 'Create User' },
                ]}
                actions={
                    <Link href={route('users.index')}>
                        <Button variant="secondary">
                            <ArrowLeftIcon className="-ml-1 mr-2 h-4 w-4" />
                            Back to Users
                        </Button>
                    </Link>
                }
            />

            <form onSubmit={handleSubmit}>
                <div className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        required
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Email <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={e => setData('email', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        required
                                    />
                                    {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Phone
                                    </label>
                                    <input
                                        type="text"
                                        value={data.phone}
                                        onChange={e => setData('phone', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Role <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={data.role}
                                        onChange={e => setData('role', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        required
                                    >
                                        <option value="">Select Role</option>
                                        {roles.map(role => (
                                            <option key={role.value} value={role.value}>
                                                {role.label}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.role && <p className="mt-1 text-sm text-red-600">{errors.role}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Password <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        required
                                    />
                                    {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm Password <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={e => setData('password_confirmation', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        required
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Additional Permissions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Permissions (Optional)</CardTitle>
                            <p className="text-sm text-gray-600 mt-1">
                                Role-based permissions will be automatically assigned. Add extra permissions if needed.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {permissions.map((module) => (
                                    <div key={module.key} className="border border-gray-200 rounded-lg p-4">
                                        <div className="flex items-center justify-between mb-3">
                                            <h4 className="font-semibold text-gray-900">{module.name}</h4>
                                            <button
                                                type="button"
                                                onClick={() => toggleModule(module)}
                                                className="text-sm text-primary-600 hover:text-primary-800"
                                            >
                                                {module.permissions.every(p => selectedPermissions.includes(p.name))
                                                    ? 'Deselect All'
                                                    : 'Select All'}
                                            </button>
                                        </div>
                                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                            {module.permissions.map((permission) => (
                                                <label
                                                    key={permission.name}
                                                    className="flex items-center space-x-2 cursor-pointer"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedPermissions.includes(permission.name)}
                                                        onChange={() => togglePermission(permission.name)}
                                                        className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                    />
                                                    <span className="text-sm text-gray-700">{permission.label}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex justify-end gap-4">
                        <Link href={route('users.index')}>
                            <Button variant="secondary" type="button">
                                Cancel
                            </Button>
                        </Link>
                        <Button variant="primary" type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create User'}
                        </Button>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}

