<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@radianceeco.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::ADMIN,
        ]);
        $admin->assignRole(RoleEnum::ADMIN->value);
        
        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@radianceeco.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::MANAGER,
        ]);
        $manager->assignRole(RoleEnum::MANAGER->value);
        
        // Create agent users
        $agent1 = User::create([
            'name' => 'Agent One',
            'email' => 'agent1@radianceeco.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::AGENT,
        ]);
        $agent1->assignRole(RoleEnum::AGENT->value);
        
        $agent2 = User::create([
            'name' => 'Agent Two',
            'email' => 'agent2@radianceeco.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::AGENT,
        ]);
        $agent2->assignRole(RoleEnum::AGENT->value);
        
        // Create readonly user
        $readonly = User::create([
            'name' => 'Read Only User',
            'email' => 'readonly@radianceeco.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::READONLY,
        ]);
        $readonly->assignRole(RoleEnum::READONLY->value);
    }
}
