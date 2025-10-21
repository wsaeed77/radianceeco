<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get lead counts by status (using dynamic statuses)
        $statusCounts = Lead::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status_id => $item->count];
            })
            ->toArray();
        
        // Get lead counts by stage
        $stageCounts = Lead::select('stage', DB::raw('count(*) as count'))
            ->groupBy('stage')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->stage->value => $item->count];
            })
            ->toArray();
        
        // Get total leads
        $totalLeads = Lead::count();
        
        // Get statuses for categorization
        $statuses = Status::active()->get()->keyBy('id');
        
        // Calculate stats for cards
        $installedCount = 0;
        $holdCount = 0;
        $inProgressCount = 0;
        
        foreach ($statusCounts as $statusId => $count) {
            $status = $statuses->get($statusId);
            if ($status) {
                if ($status->slug === 'property-installed') {
                    $installedCount += $count;
                } elseif ($status->slug === 'hold') {
                    $holdCount += $count;
                } else {
                    $inProgressCount += $count;
                }
            }
        }
        
        // Get recent leads
        $recentLeads = Lead::latest()->take(5)->get();
        
        // Get all statuses with counts and percentages (using dynamic statuses)
        $statusesWithCounts = Status::active()->ordered()->get()->map(function ($status) use ($statusCounts, $totalLeads) {
            $count = $statusCounts[$status->id] ?? 0;
            $percentage = $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0;
            
            return [
                'id' => $status->id,
                'value' => $status->slug,
                'name' => $status->name,
                'color' => $status->color,
                'count' => $count,
                'percentage' => $percentage,
            ];
        });
        
        // Get all stages with counts and percentages
        $stagesWithCounts = collect(LeadStage::cases())->map(function ($stage) use ($stageCounts, $totalLeads) {
            $count = $stageCounts[$stage->value] ?? 0;
            $percentage = $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0;
            
            return [
                'value' => $stage->value,
                'name' => $stage->label(),
                'count' => $count,
                'percentage' => $percentage,
            ];
        });
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_leads' => $totalLeads,
                'installed' => $installedCount,
                'in_progress' => $inProgressCount,
                'on_hold' => $holdCount,
                'installed_percentage' => $totalLeads > 0 ? round(($installedCount / $totalLeads) * 100) : 0,
                'in_progress_percentage' => $totalLeads > 0 ? round(($inProgressCount / $totalLeads) * 100) : 0,
                'hold_percentage' => $totalLeads > 0 ? round(($holdCount / $totalLeads) * 100) : 0,
            ],
            'statusesWithCounts' => $statusesWithCounts,
            'stagesWithCounts' => $stagesWithCounts,
            'recentLeads' => $recentLeads,
        ]);
    }
}