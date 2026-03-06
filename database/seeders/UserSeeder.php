<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // Admin account
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@itc.com',
            'username' => 'admin_itc',
            'role' => 'Admin',
            'password' => $password,
        ]);

        // Clerk account
        User::create([
            'name' => 'Inventory Clerk',
            'email' => 'clerk@itc.com',
            'username' => 'clerk_01',
            'role' => 'Clerk',
            'password' => $password,
        ]);

        // Custodian account
        User::create([
            'name' => 'Stock Custodian',
            'email' => 'custodian@itc.com',
            'username' => 'custodian_01',
            'role' => 'Custodian',
            'password' => $password,
        ]);

        // Viewer account
        User::create([
            'name' => 'Guest Viewer',
            'email' => 'viewer@itc.com',
            'username' => 'viewer_01',
            'role' => 'Viewer',
            'password' => $password,
        ]);
        }
}