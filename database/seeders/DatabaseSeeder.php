<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a set of users for testing each role
        // Note: We use Capitalized roles to match your AppServiceProvider Gates
        $users = [
            [
                'name' => 'ITC Administrator',
                'email' => 'admin@test.com',
                'username' => 'admin_itc',
                'role' => 'Admin', 
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Inventory Custodian',
                'email' => 'custodian@test.com',
                'username' => 'custodian_01',
                'role' => 'Custodian',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Inventory Clerk',
                'email' => 'clerk@test.com',
                'username' => 'clerk_01',
                'role' => 'Clerk',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Registry Viewer',
                'email' => 'viewer@test.com',
                'username' => 'viewer_01',
                'role' => 'Viewer',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // 2. Call existing specific seeders
        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
        ]);
    }
}