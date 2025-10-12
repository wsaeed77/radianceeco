import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card from '@/Components/Card';
import Table, { TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/Components/Table';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    FunnelIcon,
    XMarkIcon,
    EyeIcon,
    ShieldCheckIcon
} from '@heroicons/react/24/outline';

export default function UsersIndex({ users, roles, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [role, setRole] = useState(filters?.role || '');

    const handleFilter = () => {
        const filters = {};
        if (search) filters.search = search;
        if (role) filters.role = role;
        
        router.get(route('users.index'), filters, { preserveState: true });
    };

    const clearFilters = () => {
        setSearch('');
        setRole('');
        router.get(route('users.index'));
    };

    const handleDelete = (user) => {
        if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
            router.delete(route('users.destroy', user.id));
        }
    };

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

    const hasActiveFilters = search || role;

    return (
        <AppLayout>
            <Head title="User Management" />

            <PageHeader
                title="User Management"
                description="Manage users, roles, and permissions"
                actions={
                    <div className="flex gap-2">
                        <Link href={route('permissions.index')}>
                            <Button variant="secondary">
                                <ShieldCheckIcon className="-ml-1 mr-2 h-5 w-5" />
                                Manage Permissions
                            </Button>
                        </Link>
                        <Link href={route('users.create')}>
                            <Button variant="primary">
                                <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                New User
                            </Button>
                        </Link>
                    </div>
                }
            />

            <Card>
                {/* Filters Section */}
                <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2">
                            <FunnelIcon className="h-5 w-5 text-gray-500" />
                            <h3 className="text-sm font-semibold text-gray-700">Filters</h3>
                        </div>
                        {hasActiveFilters && (
                            <button
                                onClick={clearFilters}
                                className="text-sm text-danger-600 hover:text-danger-800 flex items-center gap-1"
                            >
                                <XMarkIcon className="h-4 w-4" />
                                Clear Filters
                            </button>
                        )}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="md:col-span-2">
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input
                                type="text"
                                placeholder="Name or email..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleFilter()}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Role
                            </label>
                            <select
                                value={role}
                                onChange={(e) => setRole(e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Roles</option>
                                {roles?.map((r) => (
                                    <option key={r.value} value={r.value}>
                                        {r.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="mt-4 flex justify-end">
                        <Button onClick={handleFilter} variant="primary" size="sm">
                            <FunnelIcon className="h-4 w-4 mr-1" />
                            Apply Filters
                        </Button>
                    </div>
                </div>

                {users.data && users.data.length > 0 ? (
                    <>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Permissions</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-medium">
                                            {user.name}
                                        </TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell>{user.phone || '-'}</TableCell>
                                        <TableCell>{getRoleBadge(user.roles)}</TableCell>
                                        <TableCell>
                                            <span className="text-sm text-gray-600">
                                                {user.permissions?.length || 0} custom
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('users.show', user.id)}>
                                                    <Button variant="primary" size="sm">
                                                        <EyeIcon className="h-4 w-4 mr-1" />
                                                        View
                                                    </Button>
                                                </Link>
                                                <Link href={route('users.edit', user.id)}>
                                                    <Button variant="secondary" size="sm">
                                                        <PencilIcon className="h-4 w-4 mr-1" />
                                                        Edit
                                                    </Button>
                                                </Link>
                                                <Button 
                                                    variant="danger" 
                                                    size="sm"
                                                    onClick={() => handleDelete(user)}
                                                >
                                                    <TrashIcon className="h-4 w-4 mr-1" />
                                                    Delete
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        {users.links && (
                            <div className="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
                                <div className="text-sm text-gray-700">
                                    Showing <span className="font-medium">{users.from}</span> to{' '}
                                    <span className="font-medium">{users.to}</span> of{' '}
                                    <span className="font-medium">{users.total}</span> results
                                </div>
                                <div className="flex gap-2">
                                    {users.links.map((link, index) => (
                                        <button
                                            key={index}
                                            onClick={() => link.url && router.visit(link.url)}
                                            disabled={!link.url}
                                            className={`px-3 py-1 text-sm rounded ${
                                                link.active
                                                    ? 'bg-primary-600 text-white'
                                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                            } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </>
                ) : (
                    <div className="text-center py-12">
                        <ShieldCheckIcon className="mx-auto h-12 w-12 text-gray-400" />
                        <h3 className="mt-2 text-sm font-semibold text-gray-900">No users found</h3>
                        <p className="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                        <div className="mt-6">
                            <Link href={route('users.create')}>
                                <Button variant="primary">
                                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                    New User
                                </Button>
                            </Link>
                        </div>
                    </div>
                )}
            </Card>
        </AppLayout>
    );
}

