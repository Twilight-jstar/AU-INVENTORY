<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name'     => 'ITC Administrator',
                'email'    => 'admin@itc.com',
                'username' => 'admin_itc',
                'role'     => 'Admin',
                'password' => $password,
            ],
            [
                'name'     => 'Inventory Custodian',
                'email'    => 'custodian@itc.com',
                'username' => 'custodian_01',
                'role'     => 'Custodian',
                'password' => $password,
            ],
            [
                'name'     => 'Inventory Clerk',
                'email'    => 'clerk@itc.com',
                'username' => 'clerk_01',
                'role'     => 'Clerk',
                'password' => $password,
            ],
            [
                'name'     => 'Registry Viewer',
                'email'    => 'viewer@itc.com',
                'username' => 'viewer_01',
                'role'     => 'Viewer',
                'password' => $password,
            ],
        ];

        foreach ($users as $userData) {
            // updateOrCreate ensures that if 'username' exists, it won't crash.
            // It will just update the details for that username.
            User::updateOrCreate(
                ['username' => $userData['username']], 
                $userData
            );
        }
    }
}