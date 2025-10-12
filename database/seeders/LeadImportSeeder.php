<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadImportSeeder extends Seeder
{
    /**
     * Run the database seeds to import lead data.
     */
    public function run(): void
    {
        // Get Radiance Team user for assigned_to
        $radianceTeamUser = User::where('name', 'like', '%Radiance Team%')->first();
        $rishiUser = User::where('name', 'like', '%Rishi%')->first();
        
        // Use default user if specific users not found
        $defaultUserId = User::first()->id ?? 1;
        $radianceTeamId = $radianceTeamUser->id ?? $defaultUserId;
        $rishiId = $rishiUser->id ?? $defaultUserId;
        
        // Map data from the provided CSV-like text
        $leadsData = [
            [
                'grant_type' => 'GBIS',
                'benefit' => 'With Benefit',
                'client_name' => 'Mohammed Abdul Salam',
                'first_name' => 'Mohammed',
                'last_name' => 'Abdul Salam',
                'client_dob' => '1967-09-25',
                'benefit_holder_name' => 'Mrs Sanu Begum',
                'benefit_holder_dob' => '1971-09-16',
                'occupancy_type' => 'Owner',
                'phone' => '07376961940 (Son)',
                'email' => 'mohammed.salam@example.com',
                'house_number' => '71',
                'street_name' => 'Stanley Road',
                'postcode' => 'NN8 1EA',
                'city' => 'Wellingborough',
                'data_match_status' => 'Property Installed',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Loft/TRV/Thermostat',
                'benefit_type' => 'Universal Credit',
                'poa_info' => 'Council Tax',
                'epc_details' => 'No Record',
                'gas_safe_info' => '7',
                'council_tax_band' => 'B',
                'assigned_to' => $rishiId,
                'notes' => 'Drive and Dropbox Updated. EPR in progress. (18/8 AA). Utility Bill, Data Matched on Sanu Begum (21/08). Loft and Heating controls installed in property on 1st Sep. Felt vent installation pics, thermostat - app pics & extractor fan on 16th Sep taken. (SS). Ready for submission.',
                'status' => LeadStatus::PROPERTY_INSTALLED,
                'stage' => LeadStage::RISHI_SUBMISSION,
            ],
            [
                'grant_type' => 'GBIS',
                'benefit' => 'With Benefit',
                'client_name' => 'Salim',
                'first_name' => 'Salim',
                'last_name' => '',
                'client_dob' => '1986-09-05',
                'occupancy_type' => 'Owner',
                'phone' => '07475459071',
                'email' => 'salim@example.com',
                'house_number' => '17',
                'street_name' => 'Tiverton Road',
                'postcode' => 'MK40 3DL',
                'city' => 'Bedford',
                'data_match_status' => 'Need to send data match',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Loft/TRV/Thermostat',
                'benefit_type' => 'Universal Credit',
                'poa_info' => 'Pending',
                'epc_details' => 'D (65), Exp Dec 2021, 66sqm, 150mm loft insulation, no TRV\'s only programmer and room thermostat',
                'gas_safe_info' => 'No Record',
                'council_tax_band' => 'C',
                'assigned_to' => $radianceTeamId,
                'notes' => 'Survey done (27/08). Sister in Law UC benefit transferred on this address, waiting for updated UC Letter and POA. Survey pictures updated. EPR ready by Rahim (15/09). Waiting for documents.',
                'status' => LeadStatus::SURVEY_DONE,
                'stage' => LeadStage::RADIANCE_TEAM,
            ],
            [
                'grant_type' => 'GBIS',
                'benefit' => 'With Benefit',
                'client_name' => 'Mohammed Miah',
                'first_name' => 'Mohammed',
                'last_name' => 'Miah',
                'client_dob' => '1977-01-08',
                'benefit_holder_name' => 'Mariam Sultana',
                'benefit_holder_dob' => '1986-01-04',
                'occupancy_type' => 'Owner',
                'phone' => '07901202343',
                'email' => 'mohammed.miah@example.com',
                'house_number' => '120',
                'street_name' => 'Melton Road North',
                'postcode' => 'NN8 1PP',
                'city' => 'Wellingborough',
                'data_match_status' => 'Data updated in google drive',
                'agent' => 'Abrar/Faisal/Rahim',
                'job_categories' => 'Loft/TRV/Thermostat',
                'benefit_type' => 'Universal Credit',
                'poa_info' => 'Council Tax & Gas Bill',
                'epc_details' => 'No Record',
                'gas_safe_info' => '2016',
                'council_tax_band' => 'B',
                'assigned_to' => $radianceTeamId,
                'notes' => 'Survey done on (02/06). 14 Lightings. Data Match sent (11/06). Data Matched (17/06). Emailed OFGEM. (09/7, AA). Survey Pictures folder updated. Windows, glazing gap and door width pictures missing.',
                'status' => LeadStatus::DATA_UPDATED_IN_GOOGLE_DRIVE,
                'stage' => LeadStage::RADIANCE_TEAM,
            ],
            [
                'grant_type' => 'GBIS',
                'benefit' => 'Without Benefit',
                'client_name' => 'Mark',
                'first_name' => 'Mark',
                'last_name' => 'Johnson',
                'occupancy_type' => 'Owner',
                'phone' => '07743748087',
                'email' => 'mark.johnson@example.com',
                'house_number' => '51',
                'street_name' => 'Station Road',
                'postcode' => 'E7 0EU',
                'city' => 'London',
                'data_match_status' => 'Survey Done',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Loft Only',
                'benefit_type' => 'No Benefit',
                'poa_info' => 'Council Tax, Bank Statement',
                'epc_details' => 'D(66), Exp Aug 2032, Mid-Terrace, 70 sqm, Solid wall, 100 mm loft, Ext Cavity wall, ext flat roof limited insulation.',
                'gas_safe_info' => '08/2022',
                'council_tax_band' => 'C',
                'assigned_to' => $radianceTeamId,
                'notes' => 'Wants EPC rating updated to C. Pictures saved in google drive (RA, 25/08).',
                'status' => LeadStatus::SURVEY_DONE,
                'stage' => LeadStage::RADIANCE_TEAM,
            ],
            [
                'grant_type' => 'GBIS',
                'benefit' => 'Without Benefit',
                'client_name' => 'Younis (Owner)',
                'first_name' => 'Younis',
                'last_name' => 'Ahmed',
                'occupancy_type' => 'Tenant',
                'phone' => '07748493009',
                'email' => 'younis.ahmed@example.com',
                'house_number' => '5',
                'street_name' => 'Thurcaston Rd',
                'postcode' => 'LE4 5PG',
                'city' => 'Leicester',
                'data_match_status' => 'Hold',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Loft Only',
                'benefit_type' => 'No Benefit',
                'poa_info' => 'Electric Bill & Water Bill',
                'epc_details' => 'F (37), Exp Jan 2024, Detach house, 83 sqm, solidwall, 150mm loft, HCz recommended.',
                'gas_safe_info' => '03/2014',
                'council_tax_band' => 'B',
                'assigned_to' => $radianceTeamId,
                'notes' => 'Survey done on 16/7. Survey pictures and Electric and Water bill uploaded in drive (AA, 16,7). Data uploaded in google drive and dropbox. Emailed OFGEM (RA, 22/07). OFFGEM will send letter by post. Hold on installation from client side.',
                'status' => LeadStatus::HOLD,
                'stage' => LeadStage::RADIANCE_TEAM,
            ]
        ];
        
        // Continue with more data...
        $moreLeadsData = [
            [
                'grant_type' => 'GBIS',
                'benefit' => 'Without Benefit',
                'client_name' => 'TBC',
                'first_name' => 'Tenant',
                'last_name' => 'TBC',
                'client_dob' => null,
                'occupancy_type' => 'Tenant',
                'phone' => 'Landlord:- Asif 07949038957',
                'email' => 'tenant.tbc@example.com',
                'house_number' => '107',
                'street_name' => 'Elkington Street',
                'postcode' => 'CV6 7GJ',
                'city' => 'Coventry',
                'data_match_status' => 'Hold',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Loft Only',
                'benefit_type' => 'TBC',
                'poa_info' => 'Pending',
                'epc_details' => 'D (64), Exp Mar 2033, Semi-Detach, 90 sqm, Solid wall, 50mm loft, Lot reommended.',
                'gas_safe_info' => '11/2018',
                'council_tax_band' => 'B',
                'assigned_to' => $radianceTeamId,
                'notes' => "Need to visit for Survey. Survey done @11AM 25/06. Tenant Leaving property this month. Survey pictures added in drive (AA 25/6). Need Council Tax Letter (AA, 25/6). Property uploaded in dropbox (RA, 27/06). EPR in progress, need some info on dimensions section (RA, 11/07). On hold, as new tenants coming in property.",
                'status' => LeadStatus::HOLD,
                'stage' => LeadStage::RADIANCE_TEAM,
            ],
            [
                'grant_type' => 'ECO4',
                'benefit' => 'With Benefit',
                'client_name' => 'Mr Aklisur Rehman',
                'first_name' => 'Aklisur',
                'last_name' => 'Rehman',
                'benefit_holder_name' => 'Mrs Dianne Frau',
                'benefit_holder_dob' => '1957-10-12',
                'occupancy_type' => 'Tenant',
                'phone' => '07985231923',
                'email' => 'Fraudianne@yahoo.co.uk',
                'house_number' => '73',
                'street_name' => 'Palmerston Road',
                'postcode' => 'RM20 4YH',
                'city' => 'Grays (ROMFORD)',
                'data_match_status' => 'Need to book installation',
                'agent' => 'Abrar/Faisal',
                'job_categories' => 'Boiler',
                'benefit_type' => 'Universal Credit',
                'poa_info' => 'Council Tax',
                'epc_details' => 'D(62) - Mar 2026, Mid-Terrace, 80sqm, 75mm loft, Solid Wall',
                'gas_safe_info' => 'No Record',
                'council_tax_band' => 'C',
                'assigned_to' => $radianceTeamId,
                'notes' => 'Property with pension credit holder. Folder complete (06/03, RA). Benefit letter received (07/07). Data Match sent (RA, 07/07). Land Registry taken (RA,09/07). Marriage certificate and birth certificate taken (RA, 14/07). Emailed OFGEM (RA, 24/07). Extension wall thickness required. Relationship Declaration esigned recieved (AA, 19/09). Pre EPR D 60, Post C69. Boiler Installation booked @ 09/10/25 10AM-11AM.',
                'status' => LeadStatus::NEED_TO_BOOK_INSTALLATION,
                'stage' => LeadStage::RADIANCE_TEAM,
            ]
        ];
        
        // Merge all lead data
        $leadsData = array_merge($leadsData, $moreLeadsData);
        
        // Delete existing leads to prevent duplicates
        // Use DB facade directly to disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Lead::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Existing leads deleted. Adding new leads...');
        
        // Insert all leads
        foreach ($leadsData as $leadData) {
            Lead::create($leadData);
        }
        
        $this->command->info('Added ' . count($leadsData) . ' new leads to the database.');
    }
}