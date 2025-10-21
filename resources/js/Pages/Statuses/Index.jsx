import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import Badge from '@/Components/Badge';
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/react/24/outline';

export default function StatusesIndex({ statuses }) {
    const handleDelete = (status) => {
        if (confirm(`Are you sure you want to delete "${status.name}"? This action cannot be undone.`)) {
            router.delete(route('statuses.destroy', status.id));
        }
    };

    const getColorVariant = (color) => {
        const variants = {
            'primary': 'primary',
            'secondary': 'secondary',
            'success': 'success',
            'danger': 'danger',
            'warning': 'warning',
            'info': 'info',
        };
        return variants[color] || 'secondary';
    };

    return (
        <AppLayout>
            <Head title="Status Management" />

            <PageHeader
                title="Status Management"
                breadcrumbs={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Status Management' },
                ]}
                actions={
                    <Link href={route('statuses.create')}>
                        <Button variant="primary" size="sm">
                            <PlusIcon className="-ml-1 mr-2 h-4 w-4" />
                            Add Status
                        </Button>
                    </Link>
                }
            />

            <Card padding={false}>
                <CardHeader>
                    <h3 className="text-lg font-semibold">All Statuses</h3>
                </CardHeader>
                <CardContent>
                    {statuses.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Color
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sort Order
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {statuses.map((status) => (
                                        <tr key={status.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {status.name}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {status.slug}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900">
                                                    {status.description || 'No description'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <Badge variant={getColorVariant(status.color)}>
                                                    {status.color}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {status.sort_order}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <Badge variant={status.is_active ? 'success' : 'secondary'}>
                                                    {status.is_active ? 'Active' : 'Inactive'}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex space-x-2">
                                                    <Link href={route('statuses.edit', status.id)}>
                                                        <Button variant="primary" size="sm">
                                                            <PencilIcon className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        variant="danger"
                                                        size="sm"
                                                        onClick={() => handleDelete(status)}
                                                    >
                                                        <TrashIcon className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-500 mb-4">No statuses found.</p>
                            <Link href={route('statuses.create')}>
                                <Button variant="primary">Create First Status</Button>
                            </Link>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppLayout>
    );
}
