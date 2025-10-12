import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Table, { TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/Components/Table';
import {
    UserGroupIcon,
    CheckCircleIcon,
    ClockIcon,
    PauseCircleIcon,
    PlusIcon,
    EyeIcon,
    ChartBarIcon,
} from '@heroicons/react/24/outline';
import { formatDate, formatRelativeTime, getInitials } from '@/utils';

export default function Dashboard({ stats, statusesWithCounts, stagesWithCounts, recentLeads }) {
    const getStatusColor = (statusValue) => {
        const colors = {
            installed: 'success',
            survey_done: 'info',
            need_data_match: 'warning',
            hold: 'danger',
        };
        return colors[statusValue] || 'default';
    };

    const getStageColor = (stageValue) => {
        const colors = {
            radiance_team: 'primary',
            rishi_submission: 'success',
            unknown: 'warning',
        };
        return colors[stageValue] || 'default';
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />

            {/* Hero Header */}
            <div className="mb-8 bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-lg p-6">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div className="text-white">
                        <h1 className="text-3xl font-bold mb-2 flex items-center">
                            <ChartBarIcon className="h-8 w-8 mr-3" />
                            Dashboard
                        </h1>
                        <p className="text-primary-100">Welcome to Radiance Eco Lead Management System</p>
                    </div>
                    <div className="mt-4 sm:mt-0">
                        <Link href={route('leads.create')}>
                            <Button variant="secondary" className="shadow-lg">
                                <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                Add New Lead
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            {/* Stats Overview */}
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                {/* Total Leads */}
                <Card className="border-l-4 border-primary-500">
                    <div className="flex items-center justify-between">
                        <div className="flex-1">
                            <p className="text-xs font-semibold text-primary-600 uppercase tracking-wide">
                                Total Leads
                            </p>
                            <div className="mt-2 flex items-baseline">
                                <p className="text-3xl font-bold text-gray-900">{stats.total_leads}</p>
                                <p className="ml-2 text-sm text-success-600 flex items-center">
                                    <ChartBarIcon className="h-4 w-4 mr-1" />
                                    Lead Management
                                </p>
                            </div>
                        </div>
                        <div className="bg-primary-100 p-4 rounded-full">
                            <UserGroupIcon className="h-8 w-8 text-primary-600" />
                        </div>
                    </div>
                </Card>

                {/* Installed */}
                <Card className="border-l-4 border-success-500">
                    <div className="flex items-center justify-between">
                        <div className="flex-1">
                            <p className="text-xs font-semibold text-success-600 uppercase tracking-wide">
                                Installed
                            </p>
                            <div className="mt-2 flex items-baseline">
                                <p className="text-3xl font-bold text-gray-900">{stats.installed}</p>
                                <p className="ml-2 text-sm text-success-600">
                                    {stats.installed_percentage}%
                                </p>
                            </div>
                        </div>
                        <div className="bg-success-100 p-4 rounded-full">
                            <CheckCircleIcon className="h-8 w-8 text-success-600" />
                        </div>
                    </div>
                </Card>

                {/* In Progress */}
                <Card className="border-l-4 border-warning-500">
                    <div className="flex items-center justify-between">
                        <div className="flex-1">
                            <p className="text-xs font-semibold text-warning-600 uppercase tracking-wide">
                                In Progress
                            </p>
                            <div className="mt-2 flex items-baseline">
                                <p className="text-3xl font-bold text-gray-900">{stats.in_progress}</p>
                                <p className="ml-2 text-sm text-warning-600">
                                    {stats.in_progress_percentage}%
                                </p>
                            </div>
                        </div>
                        <div className="bg-warning-100 p-4 rounded-full">
                            <ClockIcon className="h-8 w-8 text-warning-600" />
                        </div>
                    </div>
                </Card>

                {/* On Hold */}
                <Card className="border-l-4 border-danger-500">
                    <div className="flex items-center justify-between">
                        <div className="flex-1">
                            <p className="text-xs font-semibold text-danger-600 uppercase tracking-wide">
                                On Hold
                            </p>
                            <div className="mt-2 flex items-baseline">
                                <p className="text-3xl font-bold text-gray-900">{stats.on_hold}</p>
                                <p className="ml-2 text-sm text-danger-600">
                                    {stats.hold_percentage}%
                                </p>
                            </div>
                        </div>
                        <div className="bg-danger-100 p-4 rounded-full">
                            <PauseCircleIcon className="h-8 w-8 text-danger-600" />
                        </div>
                    </div>
                </Card>
            </div>

            {/* Leads by Status */}
            <Card className="mb-8">
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <CardTitle>Leads by Status</CardTitle>
                        <Link href={route('leads.index')}>
                            <Button variant="primary" size="sm">View All</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-center">Count</TableHead>
                                <TableHead>Distribution</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {statusesWithCounts.map((status) => (
                                <TableRow key={status.value}>
                                    <TableCell>
                                        <Badge variant={getStatusColor(status.value)}>
                                            {status.name}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-center font-bold">
                                        {status.count}
                                    </TableCell>
                                    <TableCell>
                                        <div className="w-full">
                                            <div className="flex items-center gap-2">
                                                <div className="flex-1 bg-gray-200 rounded-full h-2.5">
                                                    <div
                                                        className={`h-2.5 rounded-full bg-${getStatusColor(status.value)}-500`}
                                                        style={{ width: `${status.percentage}%` }}
                                                    ></div>
                                                </div>
                                                <span className="text-sm text-gray-600 w-12">
                                                    {status.percentage}%
                                                </span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button
                                            variant="primary"
                                            size="sm"
                                            onClick={() => router.visit(route('leads.index', { status: status.value }))}
                                        >
                                            <EyeIcon className="h-4 w-4 mr-1" />
                                            View
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {/* Leads by Team */}
            <Card className="mb-8">
                <CardHeader>
                    <CardTitle>Leads by Team</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {stagesWithCounts.map((stage) => (
                            <Card key={stage.value} className={`border-l-4 border-${getStageColor(stage.value)}-500`}>
                                <div className="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 className={`text-${getStageColor(stage.value)}-600 font-semibold mb-1`}>
                                            {stage.name}
                                        </h3>
                                        <p className="text-3xl font-bold text-gray-900">{stage.count}</p>
                                    </div>
                                </div>
                                <div className="mb-4">
                                    <div className="bg-gray-200 rounded-full h-2 mb-2">
                                        <div
                                            className={`h-2 rounded-full bg-${getStageColor(stage.value)}-500`}
                                            style={{ width: `${stage.percentage}%` }}
                                        ></div>
                                    </div>
                                    <p className="text-sm text-gray-600">{stage.percentage}% of total leads</p>
                                </div>
                                <div className="text-right">
                                    <Button
                                        variant={getStageColor(stage.value)}
                                        size="sm"
                                        onClick={() => router.visit(route('leads.index', { stage: stage.value }))}
                                    >
                                        <EyeIcon className="h-4 w-4 mr-1" />
                                        View Leads
                                    </Button>
                                </div>
                            </Card>
                        ))}
                    </div>
                </CardContent>
            </Card>

            {/* Recent Leads */}
            <Card>
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <CardTitle>Recent Leads</CardTitle>
                        <Link href={route('leads.index')}>
                            <Button variant="primary" size="sm">View All Leads</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Contact</TableHead>
                                <TableHead className="text-center">Status</TableHead>
                                <TableHead className="text-center">Team</TableHead>
                                <TableHead className="text-center">Created</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {recentLeads.length > 0 ? (
                                recentLeads.map((lead) => (
                                    <TableRow key={lead.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <div className="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold mr-3">
                                                    {getInitials(`${lead.first_name} ${lead.last_name}`)}
                                                </div>
                                                <div className="font-medium text-gray-900">
                                                    {lead.first_name} {lead.last_name}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                <div className="text-gray-900">{lead.email}</div>
                                                <div className="text-gray-500">{lead.phone}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <Badge variant={getStatusColor(lead.status)}>
                                                {lead.status_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <Badge variant={getStageColor(lead.stage)}>
                                                {lead.stage_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <div className="text-sm text-gray-900">
                                                {formatDate(lead.created_at)}
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                {formatRelativeTime(lead.created_at)}
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Link href={route('leads.show', lead.id)}>
                                                <Button variant="primary" size="sm">
                                                    <EyeIcon className="h-4 w-4 mr-1" />
                                                    Details
                                                </Button>
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center py-8">
                                        <div className="text-gray-400 mb-2">
                                            <UserGroupIcon className="h-12 w-12 mx-auto" />
                                        </div>
                                        <p className="text-gray-600 font-medium">No recent leads found.</p>
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </AppLayout>
    );
}

