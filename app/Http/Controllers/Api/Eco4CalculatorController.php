<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Eco4CalculatorService;
use App\Models\Lead;
use App\Models\Eco4Calculation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Eco4CalculatorController extends Controller
{
    public function __construct(
        private Eco4CalculatorService $calculatorService
    ) {}

    /**
     * Get metadata for dropdowns and options
     */
    public function metadata()
    {
        try {
            $metadata = $this->calculatorService->getMetadata();
            return response()->json($metadata);
        } catch (\Exception $e) {
            Log::error('ECO4 Metadata Error', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Failed to load metadata',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate ECO4/GBIS scores
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'scheme' => 'required|in:GBIS,ECO4',
            'calculation_type' => 'nullable|in:partial,full',
            'starting_sap_score' => 'nullable|integer|min:1|max:100',
            'starting_sap_band' => 'required|string',
            'finishing_sap_score' => 'nullable|integer|min:1|max:100',
            'finishing_sap_band' => 'nullable|string',
            'floor_area_band' => 'required|string',
            'property_type' => 'nullable|string',
            'wall_type' => 'nullable|string',
            'country' => 'nullable|string',
            'pre_main_heat_source' => 'nullable|string',
            'post_main_heat_source' => 'nullable|string',
            'pps_eco_rate' => 'nullable|numeric|min:0',
            'innovation_multiplier' => 'nullable|numeric|min:1',
            'measures' => 'required|array|min:1',
            'measures.*.type' => 'required|string',
            'measures.*.variant' => 'nullable|string',
            'measures.*.post_heat_source' => 'nullable|string',
            'measures.*.percentage_treated' => 'nullable|integer|min:0|max:100',
            'measures.*.is_innovation' => 'nullable|boolean',
        ]);

        try {
            $result = $this->calculatorService->calculate($validated);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('ECO4 Calculation Error', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Calculation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save calculation to lead
     */
    public function save(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'calculation_data' => 'required|array',
            'measures' => 'required|array',
        ]);

        try {
            $calculation = $this->calculatorService->saveCalculation(
                $lead,
                $validated['calculation_data'],
                $validated['measures']
            );

            return response()->json([
                'success' => true,
                'message' => 'Calculation saved successfully',
                'calculation' => $calculation,
            ]);
        } catch (\Exception $e) {
            Log::error('ECO4 Save Error', [
                'error' => $e->getMessage(),
                'lead_id' => $lead->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save calculation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get calculations for a lead
     */
    public function getByLead(Lead $lead)
    {
        try {
            $calculations = $lead->eco4Calculations()
                ->with('measures')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'calculations' => $calculations,
            ]);
        } catch (\Exception $e) {
            Log::error('ECO4 Get By Lead Error', [
                'error' => $e->getMessage(),
                'lead_id' => $lead->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load calculations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a calculation
     */
    public function delete(Eco4Calculation $calculation)
    {
        try {
            $calculation->delete();

            // Check if this is an API request or web request
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Calculation deleted successfully',
                ]);
            }

            // For web requests, redirect back with success message
            return redirect()->back()->with('success', 'Calculation deleted successfully');
        } catch (\Exception $e) {
            Log::error('ECO4 Delete Error', [
                'error' => $e->getMessage(),
                'calculation_id' => $calculation->id,
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete calculation',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete calculation');
        }
    }
}
