<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadStatus;
use App\Enums\LeadSource;
use App\Enums\DocumentKind;
use App\Models\Lead;
use App\Models\Activity;
use App\Enums\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeadViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        // Filter by status if provided
        $query = Lead::query();

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('stage') && $request->stage !== '') {
            $query->where('stage', $request->stage);
        }

        if ($request->has('source') && $request->source !== '') {
            $query->where('source', $request->source);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Get leads with pagination
        $leads = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get all statuses, stages, and sources for filters
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
            'label' => $source->value,
        ]);

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'statuses' => $statuses,
            'stages' => $stages,
            'sources' => $sources,
            'filters' => $request->only(['search', 'status', 'stage', 'source']),
        ]);
    }
    
    public function show($id)
    {
        $lead = Lead::with(['activities.user', 'activities.documents', 'stageHistories.user', 'documents'])->findOrFail($id);
        
        $activityTypes = collect(ActivityType::userSelectable())->map(fn($type) => [
            'value' => $type->value,
            'name' => $type->label(),
        ]);
        
        $documentKinds = collect(DocumentKind::cases())->map(fn($kind) => [
            'value' => $kind->value,
            'label' => ucwords(str_replace('_', ' ', $kind->name)),
        ]);
        
        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'activityTypes' => $activityTypes,
            'documentKinds' => $documentKinds,
            'epc_certificates' => session('epc_certificates', []),
        ]);
    }
    
    public function create()
    {
        // Check if user has permissions
        if (!Auth::user()->hasPermissionTo('lead.create')) {
            return redirect()->route('leads.index')
                ->with('error', 'You do not have permission to create leads.');
        }
        
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
            'label' => $source->value,
        ]);
        
        $agents = \App\Models\User::role('agent')->orderBy('name')->get();
        
        return Inertia::render('Leads/Create', [
            'statuses' => $statuses,
            'stages' => $stages,
            'sources' => $sources,
            'agents' => $agents,
        ]);
    }
    
    public function store(Request $request)
    {
        // Check if user has permissions
        if (!Auth::user()->hasPermissionTo('lead.create')) {
            return redirect()->route('leads.index')
                ->with('error', 'You do not have permission to create leads.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|string|max:50',
            'zip_code' => 'required|string|max:20',
            'status' => 'required|string',
            'stage' => 'required|string',
            'source' => 'nullable|string|max:255',
            'source_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'grant_type' => 'nullable|string|max:50',
            
            // Eligibility Details
            'occupancy_type' => 'nullable|string|max:255',
            'possible_grant_types' => 'nullable|string|max:255',
            'benefit_type' => 'nullable|string|max:255',
            'poa_info' => 'nullable|string',
            'epc_rating' => 'nullable|string|max:10',
            'epc_details' => 'nullable|string',
            'gas_safe_info' => 'nullable|string|max:255',
            'council_tax_band' => 'nullable|string|max:10',
            'eligibility_client_dob' => 'nullable|date',
            
            // Additional Information
            'epr_report' => 'nullable|string|max:50',
            
            // EPR Fields
            'epr_measures' => 'nullable|array',
            'epr_pre_rating' => 'nullable|string|max:50',
            'epr_post_rating' => 'nullable|string|max:50',
            'epr_abs' => 'nullable|numeric',
            'epr_amount_funded' => 'nullable|numeric',
            'epr_payments' => 'nullable|array',
            
            // Data Match Fields
            'benefit_holder_name' => 'nullable|string|max:255',
            'benefit_holder_dob' => 'nullable|date',
            'data_match_status' => 'nullable|string|max:50',
            'data_match_remarks' => 'nullable|string',
        ]);
        
        // Process phone numbers
        if ($request->has('multi_phone_labels') && $request->has('multi_phone_numbers')) {
            $labels = $request->multi_phone_labels;
            $numbers = $request->multi_phone_numbers;
            $phoneData = [];
            
            for ($i = 0; $i < count($labels); $i++) {
                if (!empty($numbers[$i])) {
                    $phoneData[] = [
                        'label' => $labels[$i] ?? 'Phone ' . ($i + 1),
                        'number' => $numbers[$i]
                    ];
                }
            }
            
            $validated['multi_phone_numbers'] = $phoneData;
        }
        
        // Set default values for new leads
        if (empty($validated['status'])) {
            $validated['status'] = LeadStatus::NEW;
        }
        if (empty($validated['stage'])) {
            $validated['stage'] = LeadStage::RADIANCE_TEAM;
        }
        
        // Create the lead
        $lead = Lead::create($validated);
        
        // Log lead creation
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => ActivityType::LEAD_CREATED->value,
            'description' => 'Lead created by ' . Auth::user()->name,
        ]);
        
        // Create an activity log
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => ActivityType::NOTE->value,
            'description' => 'Lead created',
        ]);
        
        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead created successfully!');
    }
    
    public function edit($id)
    {
        // Check if user has permissions
        if (!Auth::user()->hasPermissionTo('lead.edit')) {
            return redirect()->route('leads.index')
                ->with('error', 'You do not have permission to edit leads.');
        }
        
        $lead = Lead::findOrFail($id);
        
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
            'label' => $source->value,
        ]);
        
        $agents = \App\Models\User::role('agent')->orderBy('name')->get();
        
        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'statuses' => $statuses,
            'stages' => $stages,
            'sources' => $sources,
            'agents' => $agents,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        // Check if user has permissions
        if (!Auth::user()->hasPermissionTo('lead.edit')) {
            return redirect()->route('leads.index')
                ->with('error', 'You do not have permission to update leads.');
        }
        
        $lead = Lead::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|string|max:50',
            'zip_code' => 'required|string|max:20',
            'status' => 'required|string',
            'stage' => 'required|string',
            'source' => 'nullable|string|max:255',
            'agent_id' => 'nullable|exists:users,id',
            'source_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'grant_type' => 'nullable|string|max:50',
            
            // Eligibility Details
            'occupancy_type' => 'nullable|string|max:255',
            'possible_grant_types' => 'nullable|string|max:255',
            'benefit_type' => 'nullable|string|max:255',
            'poa_info' => 'nullable|string',
            'epc_rating' => 'nullable|string|max:10',
            'epc_details' => 'nullable|string',
            'gas_safe_info' => 'nullable|string|max:255',
            'council_tax_band' => 'nullable|string|max:10',
            'eligibility_client_dob' => 'nullable|date',
            
            // Additional Information
            'epr_report' => 'nullable|string|max:50',
            
            // EPR Fields
            'epr_measures' => 'nullable|array',
            'epr_pre_rating' => 'nullable|string|max:50',
            'epr_post_rating' => 'nullable|string|max:50',
            'epr_abs' => 'nullable|numeric',
            'epr_amount_funded' => 'nullable|numeric',
            'epr_payments' => 'nullable|array',
            
            // Data Match Fields
            'benefit_holder_name' => 'nullable|string|max:255',
            'benefit_holder_dob' => 'nullable|date',
            'data_match_status' => 'nullable|string|max:50',
            'data_match_remarks' => 'nullable|string',
        ]);
        
        // Process phone numbers
        if ($request->has('multi_phone_labels') && $request->has('multi_phone_numbers')) {
            $labels = $request->multi_phone_labels;
            $numbers = $request->multi_phone_numbers;
            $phoneData = [];
            
            for ($i = 0; $i < count($labels); $i++) {
                if (!empty($numbers[$i])) {
                    $phoneData[] = [
                        'label' => $labels[$i] ?? 'Phone ' . ($i + 1),
                        'number' => $numbers[$i]
                    ];
                }
            }
            
            $validated['multi_phone_numbers'] = $phoneData;
        }
        
        // Check if status has changed
        $statusChanged = $lead->status->value !== $validated['status'];
        $stageChanged = $lead->stage->value !== $validated['stage'];
        $oldStatus = $lead->status->label();
        $oldStage = $lead->stage->label();
        
        // Update the lead
        $lead->update($validated);
        
        // Create an activity log for status change
        if ($statusChanged) {
            // Get the new status name after update
            $newStatusName = LeadStatus::tryFrom($validated['status'])->label();
            
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => ActivityType::STATUS_CHANGE->value,
                'description' => 'Status changed from ' . $oldStatus . ' to ' . $newStatusName,
            ]);
        }
        
        // Create a stage history log and activity for stage change
        if ($stageChanged) {
            // Get the new stage name after update
            $newStageName = LeadStage::tryFrom($validated['stage'])->label();
            
            // Create stage history
            $lead->stageHistories()->create([
                'previous_stage' => $lead->getOriginal('stage'),
                'new_stage' => $validated['stage'],
                'user_id' => Auth::id(),
                'notes' => 'Stage updated through edit form',
            ]);
            
            // Create activity log for stage change
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => ActivityType::STAGE_CHANGE->value,
                'description' => 'Team changed from ' . $oldStage . ' to ' . $newStageName,
            ]);
        }
        
        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead updated successfully!');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy($id)
    {
        // Check if user has permissions
        if (!Auth::user()->hasPermissionTo('lead.delete')) {
            return redirect()->route('leads.index')
                ->with('error', 'You do not have permission to delete leads.');
        }
        
        $lead = Lead::findOrFail($id);
        
        // Log lead deletion before deleting (keep activity with lead_id for audit trail)
        $leadName = trim("{$lead->first_name} {$lead->last_name}") ?: $lead->email ?: 'Unknown';
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => ActivityType::LEAD_DELETED->value,
            'description' => "Lead '{$leadName}' deleted by " . Auth::user()->name,
        ]);
        
        // Delete associated records
        $lead->stageHistories()->delete();
        $lead->documents()->delete();
        $lead->eco4Calculations()->delete();
        
        // Delete the lead (activities will have null lead_id but remain as audit trail)
        $lead->delete();
        
        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully!');
    }
}
