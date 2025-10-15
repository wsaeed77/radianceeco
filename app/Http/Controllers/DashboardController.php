<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadStatus;
use App\Models\Lead;
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
        // Get lead counts by status
        $statusCounts = Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status->value => $item->count];
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
        
        // Calculate stats for cards
        $installedCount = $statusCounts['property_installed'] ?? 0;
        $holdCount = $statusCounts['hold'] ?? 0;
        
        // In Progress: all statuses except hold and property_installed
        $inProgressCount = 0;
        foreach ($statusCounts as $status => $count) {
            if ($status !== 'hold' && $status !== 'property_installed') {
                $inProgressCount += $count;
            }
        }
        
        // Get recent leads
        $recentLeads = Lead::latest()->take(5)->get();
        
        // Get all statuses with counts and percentages
        $statusesWithCounts = collect(LeadStatus::cases())->map(function ($status) use ($statusCounts, $totalLeads) {
            $count = $statusCounts[$status->value] ?? 0;
            $percentage = $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0;
            
            return [
                'value' => $status->value,
                'name' => $status->label(),
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