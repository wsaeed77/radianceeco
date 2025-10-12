<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityType;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\StageHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeadActionController extends Controller
{
    /**
     * Add a note to a lead.
     */
    public function addNote(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'message' => 'required|string',
        ]);
        
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::NOTE,
            'message' => $request->message,
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($activity, 201);
    }
    
    /**
     * Change the status of a lead.
     */
    public function changeStatus(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string',
        ]);
        
        // Validate status exists in enum
        try {
            $status = LeadStatus::from($request->status);
        } catch (\ValueError $e) {
            throw ValidationException::withMessages([
                'status' => ['Invalid status value.'],
            ]);
        }
        
        // Skip if status hasn't changed
        if ($lead->status === $status) {
            return response()->json(['message' => 'Status unchanged'], 200);
        }
        
        $oldStatus = $lead->status;
        
        // Update lead status
        $lead->status = $status;
        $lead->save();
        
        // Create activity record
        $message = 'Status changed from ' . $oldStatus->value . ' to ' . $status->value;
        if ($request->note) {
            $message .= ': ' . $request->note;
        }
        
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::STATUS_CHANGE,
            'message' => $message,
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($activity);
    }
    
    /**
     * Change the stage of a lead.
     */
    public function changeStage(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'stage' => 'required|string',
            'note' => 'nullable|string',
        ]);
        
        // Validate stage exists in enum
        try {
            $stage = LeadStage::from($request->stage);
        } catch (\ValueError $e) {
            throw ValidationException::withMessages([
                'stage' => ['Invalid stage value.'],
            ]);
        }
        
        // Skip if stage hasn't changed
        if ($lead->stage === $stage) {
            return response()->json(['message' => 'Stage unchanged'], 200);
        }
        
        $oldStage = $lead->stage;
        
        // Update lead stage
        $lead->stage = $stage;
        $lead->save();
        
        // Create activity record
        $message = 'Stage changed from ' . $oldStage->value . ' to ' . $stage->value;
        if ($request->note) {
            $message .= ': ' . $request->note;
        }
        
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::STAGE_CHANGE,
            'message' => $message,
            'created_by' => Auth::id(),
        ]);
        
        // Create stage history record
        $stageHistory = StageHistory::create([
            'lead_id' => $lead->id,
            'from_stage' => $oldStage->value,
            'to_stage' => $stage->value,
            'note' => $request->note,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
        
        return response()->json([
            'activity' => $activity,
            'stage_history' => $stageHistory,
        ]);
    }
    
    /**
     * Book an installation for a lead.
     */
    public function bookInstallation(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Only admin and manager can book installations
        if (Auth::user() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'installation_date' => 'required|date|after:today',
            'note' => 'nullable|string',
        ]);
        
        // Update lead status
        $lead->status = LeadStatus::NEED_INSTALL_BOOKING;
        $lead->save();
        
        // Create activity record
        $message = 'Installation booked for ' . $request->installation_date;
        if ($request->note) {
            $message .= ': ' . $request->note;
        }
        
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::INSTALL_BOOKED,
            'message' => $message,
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($activity);
    }
    
    /**
     * Mark installation as completed for a lead.
     */
    public function completeInstallation(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        
        // Only admin and manager can complete installations
        if (Auth::user() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'completion_date' => 'required|date',
            'note' => 'nullable|string',
        ]);
        
        // Update lead status
        $lead->status = LeadStatus::INSTALLED;
        $lead->save();
        
        // Create activity record
        $message = 'Installation completed on ' . $request->completion_date;
        if ($request->note) {
            $message .= ': ' . $request->note;
        }
        
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::INSTALL_COMPLETED,
            'message' => $message,
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($activity);
    }
}
