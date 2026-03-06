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
        // 1. Create the Administrator manually to include required fields
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@test.com',
            'username' => 'admin_itc', // This satisfies the missing field error
            'role' => 'admin',         // Ensure the role is set
            'password' => Hash::make('password'),
        ]);

        // 2. Call your existing specific seeders
        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
        ]);
    }
}