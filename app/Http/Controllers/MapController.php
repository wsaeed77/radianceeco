<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use App\Enums\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the map page
     */
    public function index()
    {
        // Check permissions
        if (!Auth::user()->hasPermissionTo('lead.view')) {
            abort(403, 'Unauthorized');
        }

        // Get filter options
        $statuses = collect(LeadStatus::cases())->map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ]);

        $stages = collect(LeadStage::cases())->map(fn($stage) => [
            'value' => $stage->value,
            'label' => $stage->label(),
        ]);

        $sources = collect(LeadSource::cases())->map(fn($source) => [
            'value' => $source->value,
            'label' => $source->label(),
        ]);

        return Inertia::render('Map/Index', [
            'statuses' => $statuses,
            'stages' => $stages,
            'sources' => $sources,
        ]);
    }

    /**
     * Get leads data for map (API endpoint)
     */
    public function getLeads(Request $request)
    {
        // Check permissions
        if (!Auth::user()->hasPermissionTo('lead.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Lead::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('zip_code', 'like', "%{$search}%")
                  ->orWhere('address_line_1', 'like', "%{$search}%");
            });
        }

        // Get leads with only necessary fields for map
        $leads = $query->select([
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'address_line_1',
            'address_line_2',
            'city',
            'zip_code',
            'latitude',
            'longitude',
            'status',
            'stage',
            'source',
        ])->get();

        // Format for frontend
        $formattedLeads = $leads->map(function ($lead) {
            return [
                'id' => $lead->id,
                'name' => trim($lead->first_name . ' ' . $lead->last_name),
                'email' => $lead->email,
                'phone' => $lead->phone,
                'address' => trim($lead->address_line_1 . ' ' . $lead->address_line_2 . ', ' . $lead->city . ' ' . $lead->zip_code),
                'latitude' => (float) $lead->latitude,
                'longitude' => (float) $lead->longitude,
                'status' => $lead->status->value,
                'status_label' => $lead->status->label(),
                'stage' => $lead->stage->value,
                'stage_label' => $lead->stage->label(),
                'source' => $lead->source ? $lead->source->value : null,
                'source_label' => $lead->source ? $lead->source->label() : null,
            ];
        });

        return response()->json([
            'leads' => $formattedLeads,
            'total' => $formattedLeads->count(),
        ]);
    }
}
