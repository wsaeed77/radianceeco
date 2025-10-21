import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardContent } from '@/Components/Card';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import { PencilIcon } from '@heroicons/react/24/outline';

export default function ShowStatus({ status }) {
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
            <Head title={`Status - ${status.name}`} />

            <PageHeader
                title="Status Details"
                breadcrumbs={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Statuses', href: route('statuses.index') },
                    { label: status.name },
                ]}
                actions={
                    <div className="flex gap-2">
                        <Link href={route('statuses.edit', status.id)}>
                            <Button variant="primary" size="sm">
                                <PencilIcon className="-ml-1 mr-2 h-4 w-4" />
                                Edit Status
                            </Button>
                        </Link>
                        <Link href={route('statuses.index')}>
                            <Button variant="secondary" size="sm">Back to Statuses</Button>
                        </Link>
                    </div>
                }
            />

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card>
                    <CardHeader>
                        <h3 className="text-lg font-semibold">Status Information</h3>
                    </CardHeader>
                    <CardContent>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Name</dt>
                                <dd className="mt-1 text-sm text-gray-900">{status.name}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Slug</dt>
                                <dd className="mt-1 text-sm text-gray-900 font-mono">{status.slug}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Description</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {status.description || 'No description provided'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Color</dt>
                                <dd className="mt-1">
                                    <Badge variant={getColorVariant(status.color)}>
                                        {status.color}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Sort Order</dt>
                                <dd className="mt-1 text-sm text-gray-900">{status.sort_order}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Status</dt>
                                <dd className="mt-1">
                                    <Badge variant={status.is_active ? 'success' : 'secondary'}>
                                        {status.is_active ? 'Active' : 'Inactive'}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Created</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {new Date(status.created_at).toLocaleDateString()}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {new Date(status.updated_at).toLocaleDateString()}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <h3 className="text-lg font-semibold">Usage Statistics</h3>
                    </CardHeader>
                    <CardContent>
                        <div className="text-center py-8">
                            <div className="text-3xl font-bold text-gray-900 mb-2">
                                {status.leads_count || 0}
                            </div>
                            <div className="text-sm text-gray-500">
                                Leads using this status
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
