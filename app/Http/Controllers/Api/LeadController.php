<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|string',
            'stage' => 'sometimes|string',
            'search' => 'sometimes|string|max:255',
            'sort_by' => 'sometimes|string|in:client_name,created_at,status,stage',
            'sort_dir' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);
        
        $query = Lead::query()->with(['assignedAgent']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('stage')) {
            $query->where('stage', $request->stage);
        }
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('postcode', 'like', "%{$search}%")
                  ->orWhere('address_line', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Limit to agent's own leads if they're not admin/manager
        $user = Auth::user();
        if ($user && $user->isAgent()) {
            $query->where('agent_id', $user->id);
        }
        
        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $leads = $query->paginate($perPage);
        
        return response()->json($leads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateLeadData($request);
        
        // Create the lead
        $lead = Lead::create($data);
        
        // Log the activity
        Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::NOTE,
            'message' => 'Lead created',
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($lead, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $lead = Lead::with([
            'assignedAgent',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'activities.creator',
            'documents' => function ($query) {
                $query->orderBy('uploaded_at', 'desc');
            },
            'stageHistory' => function ($query) {
                $query->orderBy('changed_at', 'desc');
            },
        ])->findOrFail($id);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($lead);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $data = $this->validateLeadData($request, $id);
        
        // Track if status or stage has changed
        $statusChanged = isset($data['status']) && $data['status'] !== $lead->status->value;
        $stageChanged = isset($data['stage']) && $data['stage'] !== $lead->stage->value;
        
        // Update the lead
        $lead->update($data);
        
        // Log status change if applicable
        if ($statusChanged) {
            Activity::create([
                'lead_id' => $lead->id,
                'type' => ActivityType::STATUS_CHANGE,
                'message' => 'Status changed from ' . $lead->getOriginal('status') . ' to ' . $lead->status->value,
                'created_by' => Auth::id(),
            ]);
        }
        
        // Log stage change if applicable
        if ($stageChanged) {
            Activity::create([
                'lead_id' => $lead->id,
                'type' => ActivityType::STAGE_CHANGE,
                'message' => 'Stage changed from ' . $lead->getOriginal('stage') . ' to ' . $lead->stage->value,
                'created_by' => Auth::id(),
            ]);
            
            // Also record in stage history
            $lead->stageHistory()->create([
                'from_stage' => $lead->getOriginal('stage'),
                'to_stage' => $lead->stage->value,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        }
        
        return response()->json($lead);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Only admins can delete leads
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $lead->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Validate lead data from request.
     */
    protected function validateLeadData(Request $request, ?string $id = null): array
    {
        $rules = [
            'client_name' => 'required|string|max:255',
            'client_dob' => 'nullable|date',
            'client_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'house_number' => 'nullable|string|max:50',
            'street_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:500',
            'status_raw' => 'nullable|string|max:255',
            'stage_raw' => 'nullable|string|max:255',
            'status_notes_raw' => 'nullable|string',
            'status' => [
                'sometimes',
                Rule::in(array_column(\App\Enums\LeadStatus::cases(), 'value'))
            ],
            'stage' => [
                'sometimes',
                Rule::in(array_column(\App\Enums\LeadStage::cases(), 'value'))
            ],
            'grant' => 'nullable|string|max:255',
            'job_categories' => 'nullable|string|max:255',
            'possible_grant' => 'nullable|string|max:255',
            'benefit' => 'nullable|string|max:255',
            'poa' => 'nullable|string|max:255',
            'epc' => 'nullable|string|max:255',
            'gas_safe' => 'nullable|string|max:255',
            'council_tax_band' => 'nullable|string|max:20',
            'epr_report' => 'nullable|string|max:255',
            'benefit_holder_name' => 'nullable|string|max:255',
            'benefit_holder_dob' => 'nullable|date',
            'agent' => 'nullable|string|max:255',
            'agent_id' => 'nullable|exists:users,id',
        ];
        
        return $request->validate($rules);
    }
}
