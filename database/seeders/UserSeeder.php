<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@itc.com',
            'username' => 'admin_itc', // This fixes the "Field 'username' doesn't have a default value" error
            'role' => 'admin',         // Added as per your migration
            'password' => Hash::make('password'), // Use a secure password here
        ]);
    }
}