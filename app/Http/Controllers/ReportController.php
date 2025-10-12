<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadStatus;
use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\Activity;
use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Date range filters
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->startOfDay());
        $endDate = $request->input('end_date', Carbon::now()->endOfDay());

        // Agent Performance Report
        $agentPerformance = $this->getAgentPerformance($startDate, $endDate);

        // Status Distribution
        $statusDistribution = $this->getStatusDistribution();

        // Stage/Team Distribution
        $stageDistribution = $this->getStageDistribution();

        // Lead Source Analysis
        $sourceAnalysis = $this->getSourceAnalysis();

        // Leads Over Time (Last 6 months)
        $leadsOverTime = $this->getLeadsOverTime($startDate, $endDate);

        // Status Progress Over Time
        $statusProgress = $this->getStatusProgressOverTime($startDate, $endDate);

        // Conversion Funnel
        $conversionFunnel = $this->getConversionFunnel();

        // Activity Statistics
        $activityStats = $this->getActivityStatistics($startDate, $endDate);

        // Document Statistics
        $documentStats = $this->getDocumentStatistics();

        // Top Performers
        $topPerformers = $this->getTopPerformers();

        // Recent Statistics
        $recentStats = $this->getRecentStatistics();

        return Inertia::render('Reports/Index', [
            'agentPerformance' => $agentPerformance,
            'statusDistribution' => $statusDistribution,
            'stageDistribution' => $stageDistribution,
            'sourceAnalysis' => $sourceAnalysis,
            'leadsOverTime' => $leadsOverTime,
            'statusProgress' => $statusProgress,
            'conversionFunnel' => $conversionFunnel,
            'activityStats' => $activityStats,
            'documentStats' => $documentStats,
            'topPerformers' => $topPerformers,
            'recentStats' => $recentStats,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    private function getAgentPerformance($startDate, $endDate)
    {
        $agents = User::role('agent')
            ->withCount([
                'leads as total_leads',
                'leads as completed_leads' => function ($query) {
                    $query->where('status', 'property_installed');
                },
                'leads as active_leads' => function ($query) {
                    $query->whereNotIn('status', ['property_installed', 'not_possible', 'hold']);
                },
            ])
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($agent) {
                $conversionRate = $agent->total_leads > 0
                    ? round(($agent->completed_leads / $agent->total_leads) * 100, 2)
                    : 0;

                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'total_leads' => $agent->total_leads,
                    'completed_leads' => $agent->completed_leads,
                    'active_leads' => $agent->active_leads,
                    'conversion_rate' => $conversionRate,
                ];
            });

        return $agents;
    }

    private function getStatusDistribution()
    {
        $statusCounts = Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $data = collect(LeadStatus::cases())->map(function ($status) use ($statusCounts) {
            $count = $statusCounts->firstWhere('status', $status)?->count ?? 0;

            return [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => $count,
            ];
        });

        return $data;
    }

    private function getStageDistribution()
    {
        $stageCounts = Lead::select('stage', DB::raw('count(*) as count'))
            ->groupBy('stage')
            ->get();

        $data = collect(LeadStage::cases())->map(function ($stage) use ($stageCounts) {
            $count = $stageCounts->firstWhere('stage', $stage)?->count ?? 0;

            return [
                'stage' => $stage->value,
                'label' => $stage->label(),
                'count' => $count,
            ];
        });

        return $data;
    }

    private function getSourceAnalysis()
    {
        $sourceCounts = Lead::select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->get();

        $data = collect(LeadSource::cases())->map(function ($source) use ($sourceCounts) {
            $count = $sourceCounts->firstWhere('source', $source)?->count ?? 0;

            return [
                'source' => $source->value,
                'label' => $source->value,
                'count' => $count,
            ];
        });

        return $data;
    }

    private function getLeadsOverTime($startDate, $endDate)
    {
        $leads = Lead::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $leads->map(function ($item) {
            return [
                'date' => $item->date,
                'count' => $item->count,
            ];
        });
    }

    private function getStatusProgressOverTime($startDate, $endDate)
    {
        // Get leads created in date range grouped by status and week
        $data = Lead::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEARWEEK(created_at) as week'),
                'status',
                DB::raw('count(*) as count')
            )
            ->groupBy('week', 'status')
            ->orderBy('week')
            ->get()
            ->groupBy('week')
            ->map(function ($weekData) {
                $result = ['week' => $weekData->first()->week];
                foreach ($weekData as $item) {
                    $result[$item->status->value] = $item->count;
                }
                return $result;
            })
            ->values();

        return $data;
    }

    private function getConversionFunnel()
    {
        $totalLeads = Lead::count();
        $visitedLeads = Lead::whereIn('status', ['property_visited', 'survey_booked', 'survey_done', 'data_match_sent', 'installation_booked', 'property_installed'])->count();
        $surveyDone = Lead::whereIn('status', ['survey_done', 'data_match_sent', 'installation_booked', 'property_installed'])->count();
        $dataMatchSent = Lead::whereIn('status', ['data_match_sent', 'installation_booked', 'property_installed'])->count();
        $installationBooked = Lead::whereIn('status', ['installation_booked', 'property_installed'])->count();
        $installed = Lead::where('status', 'property_installed')->count();

        return [
            ['stage' => 'Total Leads', 'count' => $totalLeads],
            ['stage' => 'Property Visited', 'count' => $visitedLeads],
            ['stage' => 'Survey Completed', 'count' => $surveyDone],
            ['stage' => 'Data Match Sent', 'count' => $dataMatchSent],
            ['stage' => 'Installation Booked', 'count' => $installationBooked],
            ['stage' => 'Installed', 'count' => $installed],
        ];
    }

    private function getActivityStatistics($startDate, $endDate)
    {
        $activityCounts = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        return $activityCounts->map(function ($item) {
            return [
                'type' => $item->type->value,
                'label' => $item->type->label(),
                'count' => $item->count,
            ];
        });
    }

    private function getDocumentStatistics()
    {
        $documentCounts = Document::select('kind', DB::raw('count(*) as count'))
            ->groupBy('kind')
            ->get();

        return $documentCounts->map(function ($item) {
            return [
                'type' => $item->kind->value,
                'label' => ucwords(str_replace('_', ' ', $item->kind->name)),
                'count' => $item->count,
            ];
        });
    }

    private function getTopPerformers()
    {
        $topAgents = User::role('agent')
            ->withCount([
                'leads as completed_leads' => function ($query) {
                    $query->where('status', 'property_installed');
                }
            ])
            ->orderBy('completed_leads', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($agent) {
                return [
                    'name' => $agent->name,
                    'completed_leads' => $agent->completed_leads,
                ];
            });

        return $topAgents;
    }

    private function getRecentStatistics()
    {
        $last30Days = Carbon::now()->subDays(30);
        $last7Days = Carbon::now()->subDays(7);

        return [
            'new_leads_last_7_days' => Lead::where('created_at', '>=', $last7Days)->count(),
            'new_leads_last_30_days' => Lead::where('created_at', '>=', $last30Days)->count(),
            'activities_last_7_days' => Activity::where('created_at', '>=', $last7Days)->count(),
            'activities_last_30_days' => Activity::where('created_at', '>=', $last30Days)->count(),
            'documents_uploaded_last_7_days' => Document::where('created_at', '>=', $last7Days)->count(),
            'documents_uploaded_last_30_days' => Document::where('created_at', '>=', $last30Days)->count(),
        ];
    }
}

