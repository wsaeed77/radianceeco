import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';

export default function EditUser({ user, roles, allPermissions }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        phone: user.phone || '',
        role: user.role || '',
        permissions: user.permissions || [],
    });

    const [selectedPermissions, setSelectedPermissions] = useState(user.permissions || []);

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('users.update', user.id));
    };

    const togglePermission = (permissionName) => {
        const updated = selectedPermissions.includes(permissionName)
            ? selectedPermissions.filter(p => p !== permissionName)
            : [...selectedPermissions, permissionName];
        
        setSelectedPermissions(updated);
        setData('permissions', updated);
    };

    return (
        <AppLayout>
            <Head title={`Edit ${user.name}`} />

            <PageHeader
                title={`Edit User: ${user.name}`}
                breadcrumbs={[
                    { label: 'User Management', href: route('users.index') },
                    { label: 'Edit User' },
                ]}
                actions={
                    <Link href={route('users.index')}>
                        <Button variant="secondary">
                            <ArrowLeftIcon className="-ml-1 mr-2 h-4 w-4" />
                            Back
                        </Button>
                    </Link>
                }
            />

            <form onSubmit={handleSubmit}>
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                        required
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={e => setData('email', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                        required
                                    />
                                    {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input
                                        type="text"
                                        value={data.phone}
                                        onChange={e => setData('phone', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                                    <select
                                        value={data.role}
                                        onChange={e => setData('role', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                        required
                                    >
                                        <option value="">Select Role</option>
                                        {roles.map(role => (
                                            <option key={role.value} value={role.value}>{role.label}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        New Password (leave blank to keep current)
                                    </label>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm Password
                                    </label>
                                    <input
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={e => setData('password_confirmation', e.target.value)}
                                        className="w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Permissions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {allPermissions.map((module) => (
                                    <div key={module.key} className="border border-gray-200 rounded-lg p-4">
                                        <h4 className="font-semibold text-gray-900 mb-3">{module.name}</h4>
                                        <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            {module.permissions.map((permission) => (
                                                <label key={permission.name} className="flex items-center space-x-2">
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedPermissions.includes(permission.name)}
                                                        onChange={() => togglePermission(permission.name)}
                                                        className="rounded border-gray-300 text-primary-600"
                                                    />
                                                    <span className="text-sm">{permission.label}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-4">
                        <Link href={route('users.index')}>
                            <Button variant="secondary">Cancel</Button>
                        </Link>
                        <Button variant="primary" type="submit" disabled={processing}>
                            {processing ? 'Updating...' : 'Update User'}
                        </Button>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}

