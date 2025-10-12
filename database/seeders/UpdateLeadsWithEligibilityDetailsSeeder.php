<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class UpdateLeadsWithEligibilityDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();
        $occupancyTypes = ['owner', 'tenant'];
        $possibleGrants = ['Loft only', 'Loft+TRV+Thermostate', 'Boiler', 'Boiler+Loft'];
        $benefits = ['Universal Credit', 'Child Benefit', 'Pension Credit', 'Child Tax Credit', 'Income Support', 'Job Seeker Allowance', 'No Benefit'];
        $epcRatings = ['A', 'B', 'C', 'D', 'E', 'F'];
        $councilTaxBands = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        foreach ($leads as $lead) {
            $lead->update([
                'occupancy_type' => $occupancyTypes[array_rand($occupancyTypes)],
                'possible_grant_types' => $possibleGrants[array_rand($possibleGrants)],
                'benefit_type' => $benefits[array_rand($benefits)],
                'poa_info' => rand(0, 1) ? 'POA Ref: POA-' . rand(10000, 99999) : null,
                'epc_rating' => $epcRatings[array_rand($epcRatings)],
                'epc_details' => rand(0, 1) ? 'EPC issued on ' . date('Y-m-d', strtotime('-' . rand(1, 60) . ' days')) . '. Valid until ' . date('Y-m-d', strtotime('+' . rand(365, 1095) . ' days')) : null,
                'gas_safe_info' => rand(0, 1) ? 'GS-' . rand(100000, 999999) : null,
                'council_tax_band' => $councilTaxBands[array_rand($councilTaxBands)],
            ]);
        }
        
        $this->command->info('Added eligibility details to ' . $leads->count() . ' leads.');
    }
}