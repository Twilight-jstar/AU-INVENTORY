<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Unit::insert([
            ['name' => 'pcs'],
            ['name' => 'kg'],
            ['name' => 'box'],
        ]);
    }
}
