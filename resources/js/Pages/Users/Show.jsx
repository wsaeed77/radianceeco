import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import { ArrowLeftIcon, PencilIcon, ShieldCheckIcon } from '@heroicons/react/24/outline';

export default function ShowUser({ user, userPermissions, rolePermissions }) {
    const getRoleBadge = (userRoles) => {
        if (!userRoles || userRoles.length === 0) return <Badge variant="default">No Role</Badge>;
        
        const roleName = userRoles[0].name;
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
            <Head title={user.name} />

            <PageHeader
                title={user.name}
                breadcrumbs={[
                    { label: 'User Management', href: route('users.index') },
                    { label: user.name },
                ]}
                actions={
                    <div className="flex gap-2">
                        <Link href={route('users.edit', user.id)}>
                            <Button variant="primary">
                                <PencilIcon className="-ml-1 mr-2 h-4 w-4" />
                                Edit User
                            </Button>
                        </Link>
                        <Link href={route('users.index')}>
                            <Button variant="secondary">
                                <ArrowLeftIcon className="-ml-1 mr-2 h-4 w-4" />
                                Back
                            </Button>
                        </Link>
                    </div>
                }
            />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* User Information */}
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Name:</dt>
                                <dd className="col-span-2 text-gray-900">{user.name}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Email:</dt>
                                <dd className="col-span-2 text-gray-900">{user.email}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Phone:</dt>
                                <dd className="col-span-2 text-gray-900">{user.phone || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Role:</dt>
                                <dd className="col-span-2">{getRoleBadge(user.roles)}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Assigned Leads:</dt>
                                <dd className="col-span-2 text-gray-900">{user.leads?.length || 0}</dd>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Permissions Summary */}
                <Card>
                    <CardHeader>
                        <CardTitle>Permissions Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div>
                                <h4 className="text-sm font-semibold text-gray-700 mb-2">Role Permissions</h4>
                                <p className="text-2xl font-bold text-primary-600">{rolePermissions.length}</p>
                                <p className="text-xs text-gray-500">From role assignment</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-semibold text-gray-700 mb-2">Direct Permissions</h4>
                                <p className="text-2xl font-bold text-success-600">{user.permissions?.length || 0}</p>
                                <p className="text-xs text-gray-500">Directly assigned</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-semibold text-gray-700 mb-2">Total Permissions</h4>
                                <p className="text-2xl font-bold text-gray-900">{userPermissions.length}</p>
                                <p className="text-xs text-gray-500">Combined total</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* All Permissions */}
            <Card className="mt-6">
                <CardHeader>
                    <div className="flex items-center gap-2">
                        <ShieldCheckIcon className="h-5 w-5 text-gray-600" />
                        <CardTitle>All Permissions</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="flex flex-wrap gap-2">
                        {userPermissions.map((permission) => (
                            <Badge key={permission} variant="default">
                                {permission}
                            </Badge>
                        ))}
                        {userPermissions.length === 0 && (
                            <p className="text-gray-500">No permissions assigned</p>
                        )}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}

