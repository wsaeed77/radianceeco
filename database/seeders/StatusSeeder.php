<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'New',
                'slug' => 'new',
                'description' => 'Newly created lead',
                'color' => 'primary',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Hold',
                'slug' => 'hold',
                'description' => 'Lead is on hold',
                'color' => 'danger',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Not Possible',
                'slug' => 'not-possible',
                'description' => 'Lead is not possible to proceed',
                'color' => 'secondary',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Need To Visit Property',
                'slug' => 'need-to-visit-property',
                'description' => 'Property visit is required',
                'color' => 'warning',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Property Visited',
                'slug' => 'property-visited',
                'description' => 'Property has been visited',
                'color' => 'info',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Survey Booked',
                'slug' => 'survey-booked',
                'description' => 'Survey has been booked',
                'color' => 'info',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Survey Done',
                'slug' => 'survey-done',
                'description' => 'Survey has been completed',
                'color' => 'success',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Data Updated In Google Drive',
                'slug' => 'data-updated-in-google-drive',
                'description' => 'Data has been updated in Google Drive',
                'color' => 'info',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Need To Send Data Match',
                'slug' => 'need-to-send-data-match',
                'description' => 'Data match needs to be sent',
                'color' => 'warning',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Data Match Sent',
                'slug' => 'data-match-sent',
                'description' => 'Data match has been sent',
                'color' => 'info',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Need To Book Installation',
                'slug' => 'need-to-book-installation',
                'description' => 'Installation needs to be booked',
                'color' => 'warning',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Installation Booked',
                'slug' => 'installation-booked',
                'description' => 'Installation has been booked',
                'color' => 'info',
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Property Installed',
                'slug' => 'property-installed',
                'description' => 'Property has been installed',
                'color' => 'success',
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Unknown',
                'slug' => 'unknown',
                'description' => 'Status is unknown',
                'color' => 'secondary',
                'sort_order' => 14,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
