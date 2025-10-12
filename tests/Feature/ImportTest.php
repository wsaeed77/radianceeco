<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_import_service_can_import_leads()
    {
        // Create test CSV content
        $csvContent = "Client Name,Phone,Email,House Number,Street Name,City,Postcode,Status\n" .
                     "John Doe,07123456789,john@example.com,42,High Street,London,SW1A 1AA,Need Visit\n" .
                     "Jane Smith,07987654321,jane@example.com,15,Low Road,Manchester,M1 1AA,Property Visited";
        
        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);
        
        // Create a user
        $user = User::factory()->create();
        
        // Import the file
        $importService = new LeadImportService();
        $result = $importService->import($tempFile, $user->id);
        
        // Clean up the temporary file
        unlink($tempFile);
        
        // Assertions
        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        
        // Check that leads were created
        $this->assertDatabaseHas('leads', [
            'client_name' => 'John Doe',
            'phone' => '07123456789',
            'email' => 'john@example.com',
            'postcode' => 'SW1A 1AA',
            'status' => 'need_visit',
        ]);
        
        $this->assertDatabaseHas('leads', [
            'client_name' => 'Jane Smith',
            'phone' => '07987654321',
            'email' => 'jane@example.com',
            'postcode' => 'M1 1AA',
            'status' => 'property_visited',
        ]);
        
        // Check that activities were created
        $this->assertDatabaseCount('activities', 2);
        
        // Get the lead IDs
        $leadJohn = Lead::where('client_name', 'John Doe')->first();
        $leadJane = Lead::where('client_name', 'Jane Smith')->first();
        
        $this->assertDatabaseHas('activities', [
            'lead_id' => $leadJohn->id,
            'type' => ActivityType::NOTE->value,
            'created_by' => $user->id,
        ]);
        
        $this->assertDatabaseHas('activities', [
            'lead_id' => $leadJane->id,
            'type' => ActivityType::NOTE->value,
            'created_by' => $user->id,
        ]);
    }
}