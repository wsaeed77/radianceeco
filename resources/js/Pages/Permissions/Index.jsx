import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import Badge from '@/Components/Badge';
import { ShieldCheckIcon, CheckIcon } from '@heroicons/react/24/outline';

export default function PermissionsIndex({ roles, permissions }) {
    const [selectedRole, setSelectedRole] = useState(roles[0] || null);
    const [selectedPermissions, setSelectedPermissions] = useState(selectedRole?.permissions || []);
    const [saving, setSaving] = useState(false);

    const handleRoleChange = (role) => {
        setSelectedRole(role);
        setSelectedPermissions(role.permissions);
    };

    const togglePermission = (permissionName) => {
        setSelectedPermissions(prev =>
            prev.includes(permissionName)
                ? prev.filter(p => p !== permissionName)
                : [...prev, permissionName]
        );
    };

    const toggleModule = (module) => {
        const modulePermissions = module.permissions.map(p => p.name);
        const allSelected = modulePermissions.every(p => selectedPermissions.includes(p));
        
        if (allSelected) {
            setSelectedPermissions(prev => prev.filter(p => !modulePermissions.includes(p)));
        } else {
            setSelectedPermissions(prev => [...new Set([...prev, ...modulePermissions])]);
        }
    };

    const handleSave = () => {
        setSaving(true);
        router.post(
            route('permissions.updateRole', selectedRole.id),
            { permissions: selectedPermissions },
            {
                onFinish: () => setSaving(false),
            }
        );
    };

    const getRoleBadge = (roleName) => {
        const variants = {
            admin: 'danger',
            manager: 'warning',
            agent: 'primary',
            readonly: 'default',
        };
        return <Badge variant={variants[roleName] || 'default'}>{roleName.toUpperCase()}</Badge>;
    };

    return (
        <AppLayout>
            <Head title="Permission Management" />

            <PageHeader
                title="Permission Management"
                description="Configure role-based permissions for the application"
            />

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* Roles List */}
                <Card className="lg:col-span-1">
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {roles.map((role) => (
                                <button
                                    key={role.id}
                                    onClick={() => handleRoleChange(role)}
                                    className={`w-full text-left px-4 py-3 rounded-lg border-2 transition-colors ${
                                        selectedRole?.id === role.id
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}
                                >
                                    <div className="flex items-center justify-between">
                                        <div>
                                            {getRoleBadge(role.name)}
                                            <p className="text-xs text-gray-600 mt-1">
                                                {role.users_count} {role.users_count === 1 ? 'user' : 'users'}
                                            </p>
                                        </div>
                                        {selectedRole?.id === role.id && (
                                            <CheckIcon className="h-5 w-5 text-primary-600" />
                                        )}
                                    </div>
                                </button>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Permissions Grid */}
                <Card className="lg:col-span-3">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Permissions for {selectedRole?.label}</CardTitle>
                                <p className="text-sm text-gray-600 mt-1">
                                    Select permissions for this role
                                </p>
                            </div>
                            <Button
                                variant="primary"
                                onClick={handleSave}
                                disabled={saving}
                            >
                                {saving ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {selectedRole ? (
                            <div className="space-y-6">
                                {permissions.map((module) => {
                                    const modulePermissions = module.permissions.map(p => p.name);
                                    const allSelected = modulePermissions.every(p => selectedPermissions.includes(p));
                                    const someSelected = modulePermissions.some(p => selectedPermissions.includes(p));

                                    return (
                                        <div key={module.key} className="border border-gray-200 rounded-lg p-4">
                                            <div className="flex items-center justify-between mb-4">
                                                <h4 className="font-semibold text-gray-900">{module.name}</h4>
                                                <button
                                                    type="button"
                                                    onClick={() => toggleModule(module)}
                                                    className="text-sm text-primary-600 hover:text-primary-800 font-medium"
                                                >
                                                    {allSelected ? 'Deselect All' : 'Select All'}
                                                </button>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                {module.permissions.map((permission) => (
                                                    <label
                                                        key={permission.name}
                                                        className="flex items-start space-x-3 cursor-pointer group"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedPermissions.includes(permission.name)}
                                                            onChange={() => togglePermission(permission.name)}
                                                            className="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                        />
                                                        <div className="flex-1">
                                                            <span className="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                                                {permission.label}
                                                            </span>
                                                            {permission.description && (
                                                                <p className="text-xs text-gray-500 mt-0.5">
                                                                    {permission.description}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <ShieldCheckIcon className="mx-auto h-12 w-12 text-gray-400" />
                                <p className="mt-2 text-sm text-gray-600">Select a role to manage permissions</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

