import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card from '@/Components/Card';
import Table, { TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/Components/Table';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import EmptyState from '@/Components/EmptyState';
import { PlusIcon, UserGroupIcon, EyeIcon, PencilIcon, FunnelIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { formatDate } from '@/utils';

export default function LeadsIndex({ leads, statuses, stages, sources, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [stage, setStage] = useState(filters?.stage || '');
    const [source, setSource] = useState(filters?.source || '');

    const getStatusBadge = (status, statusLabel, statusColor) => {
        // Use the color from the status model if available, otherwise fallback to default
        const colorMap = {
            'primary': 'primary',
            'secondary': 'secondary', 
            'success': 'success',
            'danger': 'danger',
            'warning': 'warning',
            'info': 'info',
        };
        const variant = colorMap[statusColor] || 'default';
        return <Badge variant={variant}>{statusLabel}</Badge>;
    };

    const handleFilter = () => {
        // Only send filters that have values
        const filters = {};
        if (search) filters.search = search;
        if (status) filters.status = status;
        if (stage) filters.stage = stage;
        if (source) filters.source = source;
        
        router.get(route('leads.index'), filters, { preserveState: true });
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        setStage('');
        setSource('');
        router.get(route('leads.index'));
    };

    const hasActiveFilters = search || status || stage || source;

    return (
        <AppLayout>
            <Head title="Leads" />

            <PageHeader
                title="Leads"
                description="Manage your leads and track their progress"
                actions={
                    <Link href={route('leads.create')}>
                        <Button variant="primary">
                            <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                            New Lead
                        </Button>
                    </Link>
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

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        {/* Search */}
                        <div className="lg:col-span-2">
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input
                                type="text"
                                placeholder="Name, email, phone, postcode, agent..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleFilter()}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                        </div>

                        {/* Status Filter */}
                        <div>
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Statuses</option>
                                {statuses?.map((stat) => (
                                    <option key={stat.value} value={stat.value}>
                                        {stat.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Stage/Team Filter */}
                        <div>
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Assigned Team
                            </label>
                            <select
                                value={stage}
                                onChange={(e) => setStage(e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Teams</option>
                                {stages?.map((stg) => (
                                    <option key={stg.value} value={stg.value}>
                                        {stg.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Source Filter */}
                        <div>
                            <label className="block text-xs font-medium text-gray-700 mb-1">
                                Lead Source
                            </label>
                            <select
                                value={source}
                                onChange={(e) => setSource(e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Sources</option>
                                {sources?.map((src) => (
                                    <option key={src.value} value={src.value}>
                                        {src.label}
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

                {leads.data && leads.data.length > 0 ? (
                    <>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Address</TableHead>
                                    <TableHead>Postcode</TableHead>
                                    <TableHead>Agent</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leads.data.map((lead) => (
                                    <TableRow key={lead.id}>
                                        <TableCell className="font-medium">
                                            {lead.first_name} {lead.last_name}
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                {lead.address_line_1 ? (
                                                    <>
                                                        <div>{lead.address_line_1}</div>
                                                        {lead.address_line_2 && <div>{lead.address_line_2}</div>}
                                                        <div className="text-gray-500">
                                                            {lead.city && `${lead.city}, `}
                                                            {lead.assigned_to && `${lead.assigned_to} `}
                                                            {lead.zip_code}
                                                        </div>
                                                    </>
                                                ) : (
                                                    <span className="text-gray-400">No address</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                {lead.postcode || lead.zip_code ? (
                                                    <span className="font-mono">{lead.postcode || lead.zip_code}</span>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                {lead.agent ? (
                                                    <span>{lead.agent.name}</span>
                                                ) : (
                                                    <span className="text-gray-400">Not assigned</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>{getStatusBadge(lead.status_model?.name || lead.status, lead.status_model?.name || lead.status_label, lead.status_model?.color)}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('leads.show', lead.id)}>
                                                    <Button variant="primary" size="sm">
                                                        <EyeIcon className="h-4 w-4 mr-1" />
                                                        View
                                                    </Button>
                                                </Link>
                                                <Link href={route('leads.edit', lead.id)}>
                                                    <Button variant="secondary" size="sm">
                                                        <PencilIcon className="h-4 w-4 mr-1" />
                                                        Edit
                                                    </Button>
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        {leads.links && (
                            <div className="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
                                <div className="text-sm text-gray-700">
                                    Showing <span className="font-medium">{leads.from}</span> to{' '}
                                    <span className="font-medium">{leads.to}</span> of{' '}
                                    <span className="font-medium">{leads.total}</span> results
                                </div>
                                <div className="flex gap-2">
                                    {leads.links.map((link, index) => (
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
                    <EmptyState
                        icon={UserGroupIcon}
                        title="No leads found"
                        description="Get started by creating your first lead."
                        action={() => router.visit(route('leads.create'))}
                        actionLabel="Create Lead"
                    />
                )}
            </Card>
        </AppLayout>
    );
}

