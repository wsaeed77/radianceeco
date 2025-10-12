<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_admin_can_view_leads()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $admin->assignRole(Role::ADMIN->value);

        // Create some test leads
        Lead::create([
            'client_name' => 'Test Client',
            'postcode' => 'AB12 3CD',
            'house_number' => '123',
            'street_name' => 'Test Street',
            'city' => 'Test City',
        ]);

        // Test API response
        $response = $this->actingAs($admin)->getJson('/api/leads');
        
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['client_name' => 'Test Client']);
    }
    
    public function test_readonly_user_cannot_create_leads()
    {
        // Create a readonly user
        $user = User::factory()->create([
            'role' => Role::READONLY,
        ]);
        $user->assignRole(Role::READONLY->value);

        // Attempt to create a lead
        $response = $this->actingAs($user)->postJson('/api/leads', [
            'client_name' => 'Test Client',
            'postcode' => 'AB12 3CD',
            'house_number' => '123',
        ]);
        
        $response->assertStatus(403);
    }
    
    public function test_status_change_creates_activity_record()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $admin->assignRole(Role::ADMIN->value);

        // Create a lead
        $lead = Lead::create([
            'client_name' => 'Test Client',
            'postcode' => 'AB12 3CD',
            'house_number' => '123',
            'street_name' => 'Test Street',
            'city' => 'Test City',
        ]);

        // Change the lead status
        $response = $this->actingAs($admin)->postJson("/api/leads/{$lead->id}/status", [
            'status' => 'need_visit',
        ]);
        
        $response->assertStatus(200);
        
        // Check that an activity record was created
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'status_change',
        ]);
        
        // Check that the lead status was updated
        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'need_visit',
        ]);
    }
}