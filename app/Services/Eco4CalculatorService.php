<?php

namespace App\Services;

use App\Models\Eco4PartialScore;
use App\Models\Eco4FullScore;
use App\Models\GbisPartialScore;
use App\Models\Eco4Calculation;
use App\Models\Eco4Measure;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Eco4CalculatorService
{
    /**
     * Calculate ECO4/GBIS scores for given parameters
     */
    public function calculate(array $data): array
    {
        $scheme = strtoupper($data['scheme'] ?? 'GBIS');
        $calculationType = $data['calculation_type'] ?? 'partial';
        
        if ($calculationType === 'full') {
            return $this->calculateFullProject($data);
        }
        
        return $this->calculatePartial($data, $scheme);
    }

    /**
     * Calculate Partial Project scores
     */
    private function calculatePartial(array $data, string $scheme): array
    {
        // Get PPS ECO Rate from settings, fallback to provided value or default
        $defaultPpsEcoRate = Setting::get('eco4_pps_eco_rate', 21.0);
        $ppsEcoRate = (float)($data['pps_eco_rate'] ?? $defaultPpsEcoRate);
        
        $defaultInnovationMultiplier = Setting::get('eco4_innovation_multiplier', 1.0);
        $innovationMultiplier = (float)($data['innovation_multiplier'] ?? $defaultInnovationMultiplier);
        
        // Convert SAP band format for GBIS
        if ($scheme === 'GBIS' && isset($data['starting_sap_band'])) {
            $data['starting_sap_band'] = $this->convertSapBandToGbisFormat($data['starting_sap_band'], $data['starting_sap_score'] ?? null);
        }
        
        // Normalize floor area band (replace en-dash with regular hyphen)
        if (isset($data['floor_area_band'])) {
            $data['floor_area_band'] = str_replace('–', '-', $data['floor_area_band']);
        }
        
        $measures = $data['measures'] ?? [];
        $outputMeasures = [];
        $totalAbs = 0.0;
        $totalEcoValue = 0.0;
        
        foreach ($measures as $measure) {
            $result = $this->calculateMeasure($measure, $data, $scheme, $ppsEcoRate, $innovationMultiplier);
            
            if ($result) {
                $outputMeasures[] = $result;
                $totalAbs += $result['abs_value'];
                $totalEcoValue += $result['eco_value'];
            }
        }
        
        return [
            'success' => true,
            'summary' => [
                'scheme' => $scheme,
                'starting_band' => $data['starting_sap_band'] ?? null,
                'starting_score' => $data['starting_sap_score'] ?? null,
                'floor_area_band' => $data['floor_area_band'] ?? null,
                'total_abs' => round($totalAbs, 2),
                'total_eco_value' => round($totalEcoValue, 2),
                'pps_eco_rate' => $ppsEcoRate,
            ],
            'measures' => $outputMeasures,
        ];
    }

    /**
     * Calculate Full Project scores
     */
    private function calculateFullProject(array $data): array
    {
        $floorAreaBand = $data['floor_area_band'];
        $startingBand = $data['starting_sap_band'];
        $finishingBand = $data['finishing_sap_band'];
        
        $score = Eco4FullScore::findMatching([
            'floor_area_band' => $floorAreaBand,
            'starting_band' => $startingBand,
            'finishing_band' => $finishingBand,
        ]);
        
        if (!$score) {
            return [
                'success' => false,
                'message' => 'No matching full project score found',
            ];
        }
        
        // Get PPS ECO Rate from settings
        $defaultPpsEcoRate = Setting::get('eco4_pps_eco_rate', 21.0);
        $ppsEcoRate = (float)($data['pps_eco_rate'] ?? $defaultPpsEcoRate);
        
        $costSavings = (float)$score->cost_savings;
        $ecoValue = $costSavings * $ppsEcoRate;
        
        return [
            'success' => true,
            'summary' => [
                'scheme' => 'ECO4',
                'calculation_type' => 'full',
                'starting_band' => $startingBand,
                'finishing_band' => $finishingBand,
                'floor_area_band' => $floorAreaBand,
                'cost_savings' => round($costSavings, 2),
                'eco_value' => round($ecoValue, 2),
                'pps_eco_rate' => $ppsEcoRate,
            ],
        ];
    }

    /**
     * Calculate individual measure
     */
    private function calculateMeasure(array $measure, array $baseData, string $scheme, float $ppsEcoRate, float $innovMulti): ?array
    {
        $measureType = $measure['type'];
        $measureVariant = $measure['variant'] ?? null;
        $postHeatSource = $measure['post_heat_source'] ?? null;
        $percentageTreated = max(0, min(100, (int)($measure['percentage_treated'] ?? 100))) / 100;
        $isInnovation = (bool)($measure['is_innovation'] ?? false);
        
        // Build search criteria
        $criteria = [
            'measure_type' => $measureType,
            'floor_area_band' => $baseData['floor_area_band'],
            'starting_band' => $baseData['starting_sap_band'],
        ];
        
        // Add optional criteria
        if (isset($baseData['pre_main_heat_source']) && $baseData['pre_main_heat_source']) {
            $criteria['pre_main_heating_source'] = $baseData['pre_main_heat_source'];
        }
        
        if ($postHeatSource) {
            $criteria['post_main_heating_source'] = $postHeatSource;
        }
        
        // Find matching score
        $model = $scheme === 'GBIS' ? GbisPartialScore::class : Eco4PartialScore::class;
        $scores = $model::findMatching($criteria);
        
        if ($scores->isEmpty()) {
            Log::warning('No matching score found', [
                'scheme' => $scheme,
                'criteria' => $criteria,
            ]);
            
            return [
                'measure_type' => $measureType,
                'measure_variant' => $measureVariant,
                'error' => 'No matching matrix row found',
                'criteria' => $criteria,
                'abs_value' => 0,
                'pps_points' => 0,
                'eco_value' => 0,
            ];
        }
        
        // Use first matching score
        $score = $scores->first();
        $absCostSavings = (float)$score->cost_savings;
        
        // Calculate PPS and ECO Value
        $absValue = $absCostSavings * $percentageTreated;
        $ppsPoints = $absValue * $ppsEcoRate;
        $multiplier = $isInnovation ? $innovMulti : 1.0;
        $ecoValue = $ppsPoints * $multiplier;
        
        return [
            'measure_type' => $measureType,
            'measure_variant' => $measureVariant,
            'measure_category' => $score->measure_category,
            'post_heat_source' => $postHeatSource,
            'percentage_treated' => $percentageTreated * 100,
            'is_innovation' => $isInnovation,
            'abs_value' => round($absValue, 2),
            'pps_points' => round($ppsPoints, 2),
            'eco_value' => round($ecoValue, 2),
            'cost_savings_base' => round($absCostSavings, 2),
            'matrix_data' => [
                'id' => $score->id,
                'average_treatable_factor' => $score->average_treatable_factor,
            ],
        ];
    }

    /**
     * Save calculation to database
     */
    public function saveCalculation(Lead $lead, array $calculationData, array $measures): Eco4Calculation
    {
        return DB::transaction(function () use ($lead, $calculationData, $measures) {
            // Create calculation
            $calculation = Eco4Calculation::create([
                'lead_id' => $lead->id,
                'scheme' => $calculationData['scheme'],
                'calculation_type' => $calculationData['calculation_type'] ?? 'partial',
                'starting_sap_score' => $calculationData['starting_sap_score'] ?? null,
                'starting_sap_band' => $calculationData['starting_sap_band'],
                'finishing_sap_score' => $calculationData['finishing_sap_score'] ?? null,
                'finishing_sap_band' => $calculationData['finishing_sap_band'] ?? null,
                'floor_area_band' => $calculationData['floor_area_band'],
                'property_type' => $calculationData['property_type'] ?? null,
                'wall_type' => $calculationData['wall_type'] ?? null,
                'country' => $calculationData['country'] ?? null,
                'pre_main_heat_source' => $calculationData['pre_main_heat_source'] ?? null,
                'post_main_heat_source' => $calculationData['post_main_heat_source'] ?? null,
                'pps_eco_rate' => $calculationData['pps_eco_rate'] ?? Setting::get('eco4_pps_eco_rate', 21.0),
                'innovation_multiplier' => $calculationData['innovation_multiplier'] ?? Setting::get('eco4_innovation_multiplier', 1.0),
                'total_abs' => $calculationData['total_abs'] ?? 0,
                'total_eco_value' => $calculationData['total_eco_value'] ?? 0,
                'summary' => $calculationData['summary'] ?? null,
            ]);
            
            // Create measures
            foreach ($measures as $measure) {
                Eco4Measure::create([
                    'calculation_id' => $calculation->id,
                    'measure_type' => $measure['measure_type'],
                    'measure_variant' => $measure['measure_variant'] ?? null,
                    'measure_category' => $measure['measure_category'] ?? null,
                    'post_heat_source' => $measure['post_heat_source'] ?? null,
                    'percentage_treated' => $measure['percentage_treated'] ?? 100,
                    'is_innovation_measure' => $measure['is_innovation'] ?? false,
                    'abs_value' => $measure['abs_value'],
                    'pps_points' => $measure['pps_points'],
                    'eco_value' => $measure['eco_value'],
                    'matrix_data' => $measure['matrix_data'] ?? null,
                ]);
            }
            
            return $calculation->load('measures');
        });
    }

    /**
     * Get available measure types for a scheme
     */
    public function getAvailableMeasures(string $scheme = 'GBIS'): array
    {
        $model = $scheme === 'GBIS' ? GbisPartialScore::class : Eco4PartialScore::class;
        
        return $model::select('measure_type', 'measure_category')
            ->distinct()
            ->orderBy('measure_type')
            ->get()
            ->groupBy('measure_category')
            ->map(function ($items) {
                return $items->pluck('measure_type')->unique()->values();
            })
            ->toArray();
    }

    /**
     * Convert SAP band to GBIS format (e.g., "D" → "Low_D" or "High_D")
     */
    private function convertSapBandToGbisFormat(string $band, ?int $sapScore = null): string
    {
        // If already in GBIS format, return as is
        if (str_contains($band, '_')) {
            return $band;
        }
        
        // Define SAP band ranges (midpoint used to determine Low vs High)
        $bandRanges = [
            'G' => ['min' => 0, 'max' => 20, 'mid' => 15],      // <20
            'F' => ['min' => 21, 'max' => 38, 'mid' => 29],     // 21-38
            'E' => ['min' => 39, 'max' => 54, 'mid' => 46],     // 39-54
            'D' => ['min' => 55, 'max' => 68, 'mid' => 61],     // 55-68
            'C' => ['min' => 69, 'max' => 80, 'mid' => 74],     // 69-80
            'B' => ['min' => 81, 'max' => 91, 'mid' => 86],     // 81-91
            'A' => ['min' => 92, 'max' => 100, 'mid' => 96],    // 92+
        ];
        
        $band = strtoupper($band);
        
        // If we have SAP score, use it to determine Low vs High
        if ($sapScore !== null && isset($bandRanges[$band])) {
            $prefix = $sapScore <= $bandRanges[$band]['mid'] ? 'Low' : 'High';
            return "{$prefix}_{$band}";
        }
        
        // Default to Low if no score provided
        return "Low_{$band}";
    }
    
    /**
     * Get metadata for dropdowns
     */
    public function getMetadata(): array
    {
        return [
            'schemes' => ['GBIS', 'ECO4'],
            'sap_bands' => [
                ['code' => 'G', 'range' => '<10.5-20.4', 'variants' => ['Low_G', 'High_G']],
                ['code' => 'F', 'range' => '20.5-38.4', 'variants' => ['Low_F', 'High_F']],
                ['code' => 'E', 'range' => '38.5-54.4', 'variants' => ['Low_E', 'High_E']],
                ['code' => 'D', 'range' => '54.5-68.4', 'variants' => ['Low_D', 'High_D']],
                ['code' => 'C', 'range' => '68.5-80.4', 'variants' => ['Low_C', 'High_C']],
                ['code' => 'B', 'range' => '80.5-91.4', 'variants' => ['Low_B', 'High_B']],
                ['code' => 'A', 'range' => '91.5+', 'variants' => ['Low_A', 'High_A']],
            ],
            'floor_area_bands' => ['0-72', '73-97', '98-199', '200+'],
            'pre_main_heat_sources' => $this->getHeatingSourcesFromDatabase(),
            'measures' => [
                'eco4' => $this->getAvailableMeasures('ECO4'),
                'gbis' => $this->getAvailableMeasures('GBIS'),
            ],
            'settings' => [
                'pps_eco_rate' => Setting::get('eco4_pps_eco_rate', 21.0),
                'innovation_multiplier' => Setting::get('eco4_innovation_multiplier', 1.0),
            ],
        ];
    }

    private function getHeatingSourcesFromDatabase(): array
    {
        return Eco4PartialScore::select('pre_main_heating_source')
            ->whereNotNull('pre_main_heating_source')
            ->distinct()
            ->orderBy('pre_main_heating_source')
            ->pluck('pre_main_heating_source')
            ->toArray();
    }
}

