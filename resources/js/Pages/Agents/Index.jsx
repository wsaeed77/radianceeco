import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card from '@/Components/Card';
import Table, { TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/Components/Table';
import Button from '@/Components/Button';
import EmptyState from '@/Components/EmptyState';
import { PlusIcon, UserGroupIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { formatDateTime } from '@/utils';

export default function AgentsIndex({ agents }) {
    const handleDelete = (agentId, agentName) => {
        if (confirm(`Are you sure you want to delete ${agentName}? This action cannot be undone.`)) {
            router.delete(route('agents.destroy', agentId));
        }
    };

    return (
        <AppLayout>
            <Head title="Agents" />

            <PageHeader
                title="Agents"
                description="Manage your team agents and their accounts"
                actions={
                    <Link href={route('agents.create')}>
                        <Button variant="primary">
                            <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                            Add New Agent
                        </Button>
                    </Link>
                }
            />

            <Card>
                {agents && agents.length > 0 ? (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Assigned Leads</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {agents.map((agent) => (
                                <TableRow key={agent.id}>
                                    <TableCell className="font-medium">
                                        {agent.name}
                                    </TableCell>
                                    <TableCell>{agent.email}</TableCell>
                                    <TableCell>{agent.phone || 'N/A'}</TableCell>
                                    <TableCell>
                                        <span className="text-sm text-gray-600">
                                            {agent.assigned_leads_count || 0} leads
                                        </span>
                                    </TableCell>
                                    <TableCell>{formatDateTime(agent.created_at)}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Link href={route('agents.edit', agent.id)}>
                                                <Button variant="secondary" size="sm">
                                                    <PencilIcon className="h-4 w-4 mr-1" />
                                                    Edit
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="danger"
                                                size="sm"
                                                onClick={() => handleDelete(agent.id, agent.name)}
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
                ) : (
                    <EmptyState
                        icon={UserGroupIcon}
                        title="No agents yet"
                        description="Get started by creating your first agent."
                        action={
                            <Link href={route('agents.create')}>
                                <Button variant="primary">
                                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                    Add New Agent
                                </Button>
                            </Link>
                        }
                    />
                )}
            </Card>
        </AppLayout>
    );
}

