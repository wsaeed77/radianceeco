import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Line, Bar, Doughnut, Pie } from 'react-chartjs-2';
import {
    ChartBarIcon,
    UsersIcon,
    ClockIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    ArrowTrendingUpIcon
} from '@heroicons/react/24/outline';

// Register ChartJS components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

export default function Reports({
    agentPerformance,
    statusDistribution,
    stageDistribution,
    sourceAnalysis,
    leadsOverTime,
    statusProgress,
    conversionFunnel,
    activityStats,
    documentStats,
    topPerformers,
    recentStats,
    dateRange
}) {
    // Chart color palette
    const colors = {
        primary: 'rgb(59, 130, 246)',
        success: 'rgb(34, 197, 94)',
        warning: 'rgb(251, 191, 36)',
        danger: 'rgb(239, 68, 68)',
        info: 'rgb(96, 165, 250)',
        purple: 'rgb(168, 85, 247)',
        pink: 'rgb(236, 72, 153)',
        orange: 'rgb(251, 146, 60)',
        teal: 'rgb(20, 184, 166)',
        indigo: 'rgb(99, 102, 241)',
    };

    const chartColors = [
        colors.primary,
        colors.success,
        colors.warning,
        colors.danger,
        colors.info,
        colors.purple,
        colors.pink,
        colors.orange,
        colors.teal,
        colors.indigo,
    ];

    // Agent Performance Chart Data
    const agentPerformanceData = {
        labels: agentPerformance.map(agent => agent.name),
        datasets: [
            {
                label: 'Total Leads',
                data: agentPerformance.map(agent => agent.total_leads),
                backgroundColor: colors.primary,
                borderColor: colors.primary,
                borderWidth: 1,
            },
            {
                label: 'Completed Leads',
                data: agentPerformance.map(agent => agent.completed_leads),
                backgroundColor: colors.success,
                borderColor: colors.success,
                borderWidth: 1,
            },
            {
                label: 'Active Leads',
                data: agentPerformance.map(agent => agent.active_leads),
                backgroundColor: colors.warning,
                borderColor: colors.warning,
                borderWidth: 1,
            },
        ],
    };

    // Status Distribution Chart Data
    const statusDistributionData = {
        labels: statusDistribution.map(s => s.label),
        datasets: [
            {
                data: statusDistribution.map(s => s.count),
                backgroundColor: chartColors,
                borderColor: 'white',
                borderWidth: 2,
            },
        ],
    };

    // Stage Distribution Chart Data
    const stageDistributionData = {
        labels: stageDistribution.map(s => s.label),
        datasets: [
            {
                data: stageDistribution.map(s => s.count),
                backgroundColor: chartColors.slice(0, stageDistribution.length),
                borderColor: 'white',
                borderWidth: 2,
            },
        ],
    };

    // Source Analysis Chart Data
    const sourceAnalysisData = {
        labels: sourceAnalysis.map(s => s.label),
        datasets: [
            {
                label: 'Leads by Source',
                data: sourceAnalysis.map(s => s.count),
                backgroundColor: chartColors.slice(0, sourceAnalysis.length),
                borderColor: chartColors.slice(0, sourceAnalysis.length),
                borderWidth: 1,
            },
        ],
    };

    // Leads Over Time Chart Data
    const leadsOverTimeData = {
        labels: leadsOverTime.map(l => l.date),
        datasets: [
            {
                label: 'New Leads',
                data: leadsOverTime.map(l => l.count),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                fill: true,
                tension: 0.4,
            },
        ],
    };

    // Conversion Funnel Chart Data
    const conversionFunnelData = {
        labels: conversionFunnel.map(f => f.stage),
        datasets: [
            {
                label: 'Leads Count',
                data: conversionFunnel.map(f => f.count),
                backgroundColor: [
                    colors.primary,
                    colors.info,
                    colors.purple,
                    colors.warning,
                    colors.orange,
                    colors.success,
                ],
                borderColor: 'white',
                borderWidth: 1,
            },
        ],
    };

    // Activity Statistics Chart Data
    const activityStatsData = {
        labels: activityStats.map(a => a.label),
        datasets: [
            {
                data: activityStats.map(a => a.count),
                backgroundColor: chartColors,
                borderColor: 'white',
                borderWidth: 2,
            },
        ],
    };

    // Chart Options
    const barOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            },
        },
    };

    const lineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            },
        },
    };

    const doughnutOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            },
        },
    };

    return (
        <AppLayout>
            <Head title="Reports & Analytics" />

            <PageHeader
                title="Reports & Analytics"
                description="Comprehensive analytics and performance metrics"
            />

            {/* Recent Statistics Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">New Leads (7 Days)</p>
                                <p className="text-2xl font-bold text-gray-900 mt-2">
                                    {recentStats.new_leads_last_7_days}
                                </p>
                                <p className="text-xs text-gray-500 mt-1">
                                    {recentStats.new_leads_last_30_days} in last 30 days
                                </p>
                            </div>
                            <div className="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <UsersIcon className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Activities (7 Days)</p>
                                <p className="text-2xl font-bold text-gray-900 mt-2">
                                    {recentStats.activities_last_7_days}
                                </p>
                                <p className="text-xs text-gray-500 mt-1">
                                    {recentStats.activities_last_30_days} in last 30 days
                                </p>
                            </div>
                            <div className="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <CheckCircleIcon className="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Documents (7 Days)</p>
                                <p className="text-2xl font-bold text-gray-900 mt-2">
                                    {recentStats.documents_uploaded_last_7_days}
                                </p>
                                <p className="text-xs text-gray-500 mt-1">
                                    {recentStats.documents_uploaded_last_30_days} in last 30 days
                                </p>
                            </div>
                            <div className="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <DocumentTextIcon className="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Avg. Conversion Rate</p>
                                <p className="text-2xl font-bold text-gray-900 mt-2">
                                    {agentPerformance.length > 0
                                        ? (agentPerformance.reduce((sum, agent) => sum + agent.conversion_rate, 0) / agentPerformance.length).toFixed(1)
                                        : 0}%
                                </p>
                                <p className="text-xs text-gray-500 mt-1">
                                    Across all agents
                                </p>
                            </div>
                            <div className="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <ArrowTrendingUpIcon className="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Agent Performance */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Agent Performance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ height: '400px' }}>
                            <Bar data={agentPerformanceData} options={barOptions} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top Performers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {topPerformers.map((performer, index) => (
                                <div key={index} className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span className="text-blue-600 font-bold">{index + 1}</span>
                                        </div>
                                        <div>
                                            <p className="font-semibold text-gray-900">{performer.name}</p>
                                            <p className="text-sm text-gray-500">Completed Leads</p>
                                        </div>
                                    </div>
                                    <span className="text-xl font-bold text-blue-600">
                                        {performer.completed_leads}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Status and Stage Distribution */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Status Distribution</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ height: '350px' }}>
                            <Doughnut data={statusDistributionData} options={doughnutOptions} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Team/Stage Distribution</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ height: '350px' }}>
                            <Pie data={stageDistributionData} options={doughnutOptions} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Leads Over Time */}
            <Card className="mb-6">
                <CardHeader>
                    <CardTitle>Leads Over Time</CardTitle>
                </CardHeader>
                <CardContent>
                    <div style={{ height: '300px' }}>
                        <Line data={leadsOverTimeData} options={lineOptions} />
                    </div>
                </CardContent>
            </Card>

            {/* Conversion Funnel and Source Analysis */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Conversion Funnel</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ height: '350px' }}>
                            <Bar
                                data={conversionFunnelData}
                                options={{
                                    ...barOptions,
                                    indexAxis: 'y',
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    }
                                }}
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lead Source Analysis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ height: '350px' }}>
                            <Bar data={sourceAnalysisData} options={barOptions} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Activity Statistics */}
            <Card>
                <CardHeader>
                    <CardTitle>Activity Statistics</CardTitle>
                </CardHeader>
                <CardContent>
                    <div style={{ height: '350px' }}>
                        <Doughnut data={activityStatsData} options={doughnutOptions} />
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}

